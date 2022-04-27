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

    echo "Getting attributes from file...\n";
    $attributes = getAttributeListFromFile($argv[1]);

    echo "Setuping files to be modified...\n";
    $modelName = $argv[2];
    writeMigration($attributes, $modelName);
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
    $migrations = glob("database/migrations/*$snakeModel*.php");
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

main($argv);
