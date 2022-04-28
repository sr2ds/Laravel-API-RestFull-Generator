<?php

/**
 * WIP: This not working yet.
 * This file is part of the Laravel Stubs Custom API RestFull with OpenApi
 * The goals is rewrite files with correct attributes to full automatically rest api
 * Usage: php setup-atributes.php path/to/file.txt ModelName
 */

function main($argv)
{
    checkNumOfParams($argv);
    $modelName = $argv[2];

    echo "Run PHP artisan...";
    shell_exec("php artisan make:model -c -f -m --api -R --test $modelName");

    echo "Getting attributes from file...\n";
    $attributes = getAttributeListFromFile($argv[1]);

    echo "Setuping files to be modified...\n";
    writeMigration($attributes, $modelName);
    writeModel($attributes, $modelName);
    writeFactory($attributes, $modelName);
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
    $nbSpace = str_repeat(" ", 12);
    $contentToWrite = getContentToWriteMigration($attributes, $nbSpace);
    $filePath = getMigrationPath($modelName);

    $file = fopen($filePath, "r");
    $fileContent = fread($file, filesize($filePath));
    fclose($file);

    $fileContent = str_replace("$nbSpace\$table->timestamps();", $contentToWrite, $fileContent);

    $file = fopen($filePath, "w");
    fwrite($file, $fileContent);
    fclose($file);
}

function getContentToWriteMigration($attributes, $formatationSpaces)
{
    foreach ($attributes as $key => $attribute) {
        $attributeName = $attribute["name"];
        $attributeType = $attribute["type"];
        $attributeRequired = $attribute["required"];

        $linesToFile[$key] = "$formatationSpaces\$table->$attributeType('$attributeName')";
        if ($attributeRequired) {
            $linesToFile[$key] .= "->required()";
        }
        $linesToFile[$key] .= ";";
    }

    $linesToFile[] = "$formatationSpaces\$table->timestamps();";
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

function snakeCase($word)
{
    # thanks Jan Jakeš: https://stackoverflow.com/a/19533226/6394559
    return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $word)), '_');
}


function writeModel($attributes, $modelName)
{
    //@todo: write swagger attributes
    //@todo: write casts to integer attributes

    $nbSpace = str_repeat(" ", 8);
    $contentToWrite = getContentToWriteModelFillable($attributes, $nbSpace);
    $filePath = "app/Models/$modelName.php";

    $file = fopen($filePath, "r");
    $fileContent = fread($file, filesize($filePath));

    fclose($file);

    $fileContent = str_replace("'created_at',", $contentToWrite, $fileContent);

    $file = fopen($filePath, "w");
    fwrite($file, $fileContent);
    fclose($file);
}

function getContentToWriteModelFillable($attributes, $formatationSpaces)
{
    $fillable = [];
    foreach ($attributes as $key => $attribute) {
        $fillable[] = "'" . $attribute["name"] . "'";
    }

    $content = implode(",\n$formatationSpaces", $fillable) . ",\n";
    $content .= "$formatationSpaces'created_at',";

    return $content;
}

function writeFactory($attributes, $modelName)
{
    $nbSpace = str_repeat(" ", 12);
    $contentToWrite = getContentToWriteFactory($attributes, $nbSpace);
    $filePath = "database/factories/$modelName" . "Factory.php";

    $file = fopen($filePath, "r");
    $fileContent = fread($file, filesize($filePath));

    fclose($file);

    $fileContent = str_replace("$nbSpace//", $contentToWrite, $fileContent);

    $file = fopen($filePath, "w");
    fwrite($file, $fileContent);
    fclose($file);
}

function getContentToWriteFactory($attributes, $formatationSpaces)
{
    $content = [];
    foreach ($attributes as $key => $attribute) {
        $dataType = getDataTypeToFactory($attribute['type']);
        $content[] = "$formatationSpaces'" . $attribute["name"] . "' => \$this->faker->$dataType()";
    }

    return  implode(",\n", $content) . ",";
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

main($argv);
