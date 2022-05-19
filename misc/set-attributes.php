<?php

use Illuminate\Support\Str;

require __DIR__.'/../../vendor/autoload.php';

/**
 * WIP: This works but need improvements
 * This file is part of the Laravel Stubs Custom API RestFull with OpenApi
 * The goals is rewrite files with correct attributes to full automatically rest api
 * Usage: php stubs/misc/set-attributes.php stubs/misc/file.txt Example
 */

function main($argv)
{
	checkNumOfParams($argv);
	$modelName = $argv[2];

	echo "\n\nCreating default files with PHP artisan...\n";
	shell_exec("php artisan make:model -c -f -m --api -R --test $modelName");

	echo "Getting attributes from file...\n";
	$attributes = getAttributesListFromFile($argv[1]);

	echo "Changing files ...\n";
	$settings = getFileSettings($modelName);

	// Loop writing all files from settings array
	foreach ($settings as $setting) {
		$fileContent = str_replace(
			$setting['replace_rule'],
			$setting['get_content_to_write']($attributes, $setting['spaces'], $modelName),
			getFileContent($setting['path'])
		);
		writeContentInFile($setting['path'], $fileContent);
	}

	echo "Done! \n";

	$routeName = str_replace('_', '-', Str::snake(Str::pluralStudly(class_basename($modelName))));
	echo "Setup your file routes/api.php: \n\n";
	echo "Route::apiResource('$routeName', $modelName" . "Controller::class);\n\n";
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
function getAttributesListFromFile($filePath)
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

function getMigrationPath($modelName)
{
	$migrationName = Str::snake(Str::pluralStudly(class_basename($modelName)));
	$migrations = glob("database/migrations/*$migrationName" . "_table.php");

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

function getContentToWriteModelCasts($attributes, $nbSpace)
{
	$contentAttr = [];
	foreach ($attributes as $key => $attribute) {
		if ($attribute["type"] == 'integer') {
			$contentAttr[$key] = $nbSpace . "'" . $attribute["name"] . "' => '" . $attribute["type"] . "',";
		}
	}

	$content = "protected \$casts = [\n";
	$content .= implode(",\n$nbSpace", $contentAttr) . "\n";
	$content .= str_repeat(" ", 4) . "];";

	return $content;
}

function getContentToWriteModelSwagger($attributes, $nbSpace, $modelName)
{
	$content[] = " *   @OA\Property(type=\"integer\",description=\"id of {$modelName}\",title=\"id\",property=\"id\",example=\"1\",readOnly=\"true\")";
	foreach ($attributes as $key => $attribute) {
		$content[$key+1] = " *   @OA\Property(type=\"{$attribute["type"]}\",description=\"{$attribute["name"]} of {$modelName}\",title=\"{$attribute["name"]}\",property=\"{$attribute["name"]}\")";
	}
	return implode(",\n", $content) . ',';
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

function getContentToWriteStoreRequest($attributes, $nbSpace)
{
	return getContentToWriteRequest($attributes, $nbSpace, false);
}

function getContentToWriteUpdateRequest($attributes, $nbSpace)
{
	return getContentToWriteRequest($attributes, $nbSpace, true);
}

function getContentToWriteTest($attributes, $nbSpace, $modelName)
{
	$content = [];
	$tableName = Str::snake(Str::pluralStudly(class_basename($modelName)));
	$routeName = str_replace('_', '-', $tableName);

	$content[] = $nbSpace . "private \$path = 'api/$routeName';";
	$content[] .= $nbSpace . "private \$model = \\App\\Models\\$modelName::class;";
	$content[] .= $nbSpace . "private \$table = '$tableName';";
	return implode("\n", $content);
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

function getFileSettings($modelName, $file = null)
{
	$files = [
		"MODEL_CASTS" => [
			"path" => "app/Models/$modelName.php",
			"spaces" => str_repeat(" ", 8),
			"get_content_to_write" => "getContentToWriteModelCasts",
			"replace_rule" => "protected \$casts = [];",
		],
		"FACTORY" => [
			"path" => "database/factories/$modelName" . "Factory.php",
			"spaces" => str_repeat(" ", 12),
			"get_content_to_write" => "getContentToWriteFactory",
			"replace_rule" => str_repeat(" ", 12) . "//",
		],
		"MODEL_FILLABLE" => [
			"path" => "app/Models/$modelName.php",
			"spaces" => str_repeat(" ", 8),
			"get_content_to_write" => "getContentToWriteModelFillable",
			"replace_rule" => "'created_at',",
		],
		"STORE_REQUEST" => [
			"path" => "app/Http/Requests/Store$modelName" . "Request.php",
			"spaces" => str_repeat(" ", 12),
			"get_content_to_write" => "getContentToWriteStoreRequest",
			"replace_rule" => str_repeat(" ", 12) . "//",
		],
		"MODEL_SWAGGER" => [
			"path" => "app/Models/$modelName.php",
			"spaces" => str_repeat(" ", 8),
			"get_content_to_write" => "getContentToWriteModelSwagger",
			"replace_rule" => ' *   @OA\Property(type="integer",description="id of ' . $modelName . '",title="id",property="id",example="1",readOnly="true"),',
		],
		"UPDATE_REQUEST" => [
			"path" => "app/Http/Requests/Update$modelName" . "Request.php",
			"spaces" => str_repeat(" ", 12),
			"get_content_to_write" => "getContentToWriteUpdateRequest",
			"replace_rule" => str_repeat(" ", 12) . "//",
		],
		"MIGRATION" => [
			"path" => getMigrationPath($modelName),
			"spaces" => str_repeat(" ", 12),
			"get_content_to_write" => "getContentToWriteMigration",
			"replace_rule" => str_repeat(" ", 12) . "\$table->timestamps();",
		],
		"TEST" => [
			"path" => "tests/Feature/Models/" . $modelName . "Test.php",
			"spaces" => str_repeat(" ", 4),
			"get_content_to_write" => "getContentToWriteTest",
			"replace_rule" => str_repeat(" ", 4) . "// 'PATH_MODEL_TABLE'",
		],

	];
	return $file ? $files[$file] : $files;
}

main($argv);
