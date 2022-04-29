<?php

/**
 * WIP: This not fully working yet.
 * This file is part of the Laravel Stubs Custom API RestFull with OpenApi
 * The goals is rewrite files with correct attributes to full automatically rest api
 * Usage: php stubs/misc/set-attributes.php stubs/misc/file.txt Example
 */

function main($argv)
{
	checkNumOfParams($argv);
	$modelName = $argv[2];

	echo "Creating default files with PHP artisan...\n";
	shell_exec("php artisan make:model -c -f -m --api -R --test $modelName");

	echo "Getting attributes from file...\n";
	$attributes = getAttributeListFromFile($argv[1]);

	echo "Changing files ...\n";

	writeMigration($attributes, $modelName);
	writeModel($attributes, $modelName);
	writeFactory($attributes, $modelName);
	writeStoreRequest($attributes, $modelName);
	writeUpdateRequest($attributes, $modelName);

	echo "Done! Now you need add route to your api and fix test file @todo lines.\n";
}

function checkNumOfParams($argv)
{
	if (count($argv) < 3) {
		echo "Error: Missing parameters.\n";
		echo "Usage: php setup-atributes.php path/to/file.txt ModelName\n";
		exit(1);
	}
}

/**
 * Get Attributes from TXT file
 * Model fo sample:
 * attribute_name:attribute_type(eloquent_compatible)
 *  name:string
 *  creator_uuid:uuid
 *  description:text:required
 */
function getAttributeListFromFile($filePath)
{
	if (!file_exists($filePath)) {
		echo "Error: File not found.\n";
		exit(1);
	}

	$file = fopen($filePath, "r");
	$attributes = [];
	while (!feof($file)) {
		$line = fgets($file);
		$line = trim($line);
		$line = explode(":", $line);

		// @todo: improve this to get dinamic params better
		$attributes[] = [
			"name" => trim($line[0]),
			"type" => trim($line[1]),
			"required" => isset($line[2]) ? trim($line[2]) : false,
		];
	}
	fclose($file);
	return $attributes;
}

function writeMigration($attributes, $modelName)
{
	$settings = getFileSettings("MIGRATION", $modelName);
	$contentToWrite = $settings['get_content_to_write']($attributes, $settings['spaces']);
	$fileContent = getFileContent($settings['path']);
	$fileContent = str_replace($settings['replace_rule'], $contentToWrite, $fileContent);
	writeContentInFile($settings['path'], $fileContent);
}

function writeModel($attributes, $modelName)
{
	//@todo: write swagger attributes
	//@todo: write casts to integer attributes
	$settings = getFileSettings("MODEL", $modelName);
	$contentToWrite = $settings['get_content_to_write']($attributes, $settings['spaces']);
	$fileContent = getFileContent($settings['path']);
	$fileContent = str_replace($settings['replace_rule'], $contentToWrite, $fileContent);
	writeContentInFile($settings['path'], $fileContent);
}

function writeFactory($attributes, $modelName)
{
	$settings = getFileSettings("FACTORY", $modelName);
	$contentToWrite = $settings['get_content_to_write']($attributes, $settings['spaces']);
	$fileContent = getFileContent($settings['path']);
	$fileContent = str_replace($settings['replace_rule'], $contentToWrite, $fileContent);
	writeContentInFile($settings['path'], $fileContent);
}

function writeStoreRequest($attributes, $modelName)
{
	$settings = getFileSettings("STORE_REQUEST", $modelName);
	$contentToWrite = $settings['get_content_to_write']($attributes, $settings['spaces']);
	$fileContent = getFileContent($settings['path']);
	$fileContent = str_replace($settings['replace_rule'], $contentToWrite, $fileContent);
	writeContentInFile($settings['path'], $fileContent);
}

function writeUpdateRequest($attributes, $modelName)
{
	$settings = getFileSettings("UPDATE_REQUEST", $modelName);
	$contentToWrite = $settings['get_content_to_write']($attributes, $settings['spaces'], true);
	$fileContent = getFileContent($settings['path']);
	$fileContent = str_replace($settings['replace_rule'], $contentToWrite, $fileContent);
	writeContentInFile($settings['path'], $fileContent);
}

function getContentToWriteMigration($attributes, $nbSpace)
{
	foreach ($attributes as $key => $attribute) {
		$attributeName = $attribute["name"];
		$attributeType = $attribute["type"];
		$attributeRequired = $attribute["required"];

		$linesToFile[$key] = "$nbSpace\$table->$attributeType('$attributeName')";
		if ($attributeRequired) {
			$linesToFile[$key] .= "->required()";
		}
		$linesToFile[$key] .= ";";
	}

	$linesToFile[] = "$nbSpace\$table->timestamps();";
	$content = implode("\n", $linesToFile);

	return $content;
}

function getMigrationPath($modelName)
{
	$snakeModel = snakeCase($modelName);

	// @todo: fix to plural and singular name
	$snakeModelWithoutLastWord = substr($snakeModel, 0, -1);
	$migrations = glob("database/migrations/*$snakeModelWithoutLastWord*.php");
	if (!count($migrations)) {
		echo "Error: Migration not found.\n";
		exit(1);
	}

	$filePath = $migrations[0];
	if (file_exists($filePath)) {
		return $filePath;
	} else {
		echo "Error: FilePath can not be read: $filePath.\n";
		exit(1);
	}
}

function getContentToWriteModelFillable($attributes, $nbSpace)
{
	$content = [];
	foreach ($attributes as $key => $attribute) {
		$content[$key] = "'" . $attribute["name"] . "'";
	}

	$content = implode(",\n$nbSpace", $content) . ",\n";
	$content .= "$nbSpace'created_at',";

	return $content;
}

function getContentToWriteFactory($attributes, $nbSpace)
{
	$content = [];
	foreach ($attributes as $key => $attribute) {
		$dataType = getDataTypeToFactory($attribute['type']);
		$content[] = "$nbSpace'" . $attribute["name"] . "' => \$this->faker->$dataType()";
	}

	return implode(",\n", $content) . ",";
}

function getDataTypeToFactory($type)
{
	$types = [
		"string" => "word",
		"integer" => "randomDigit",
		"boolean" => "boolean",
		"uuid" => "uuid",
		"date" => "dateTime",
		"datetime" => "dateTime",
		"text" => "text",
	];
	return $types[$type];
}

function getContentToWriteRequest($attributes, $nbSpace, $sometimes = false)
{
	$content = [];
	foreach ($attributes as $key => $attribute) {
		if ($sometimes) {
			$content[$key] = "$nbSpace'data.$attribute[name]' => 'sometimes|$attribute[type]";
		} else {
			$content[$key] = "$nbSpace'data.$attribute[name]' => '$attribute[type]";
		}
		if ($attribute["required"]) {
			$content[$key] .= "|required";
		}
		$content[$key] .= "'";
	}
	return implode(",\n", $content) . ",";
}

function getFileContent($path)
{
	$file = fopen($path, "r");
	$fileContent = fread($file, filesize($path));
	fclose($file);

	return $fileContent;
}

function writeContentInFile($path, $content)
{
	$file = fopen($path, "w");
	fwrite($file, $content);
	fclose($file);
}

function snakeCase($word)
{
	return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $word)), '_');
}

function getFileSettings($file, $modelName)
{
	$files = [
		"MODEL" => [
			"path" => "app/Models/$modelName.php",
			"spaces" => str_repeat(" ", 8),
			"get_content_to_write" => "getContentToWriteModelFillable",
			"replace_rule"=> "'created_at',",
		],
		"FACTORY" => [
			"path" => "database/factories/$modelName" . "Factory.php",
			"spaces" => str_repeat(" ", 12),
			"get_content_to_write" => "getContentToWriteFactory",
			"replace_rule"=> str_repeat(" ", 12) . "//",
		],
		"STORE_REQUEST" => [
			"path" => "app/Http/Requests/Store$modelName" . "Request.php",
			"spaces" => str_repeat(" ", 12),
			"get_content_to_write" => "getContentToWriteRequest",
			"replace_rule"=> str_repeat(" ", 12) . "//",
		],
		"UPDATE_REQUEST" => [
			"path" => "app/Http/Requests/Update$modelName" . "Request.php",
			"spaces" => str_repeat(" ", 12),
			"get_content_to_write" => "getContentToWriteRequest",
			"replace_rule"=> str_repeat(" ", 12) . "//",
		],
		"MIGRATION" => [
			"path" => getMigrationPath($modelName),
			"spaces" => str_repeat(" ", 12),
			"get_content_to_write" => "getContentToWriteMigration",
			"replace_rule"=> str_repeat(" ", 12) . "\$table->timestamps();",
		]
	];
	return $files[$file];
}

main($argv);
