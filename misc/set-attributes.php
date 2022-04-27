<?php

/**
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
    setupMigration($attributes, $modelName);
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
 *  description:text
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

        $attributes[] = [
            "name" => trim($line[0]),
            "type" => trim($line[1]),
            "required" => isset($line[2]) ? trim($line[2]) : false,
        ];
    }
    fclose($file);
    return $attributes;
}

function setupMigration($attributes, $modelName)
{
    $snakeModel = snakeCase($modelName);

    foreach ($attributes as $key => $attribute) {
        $attributeName = $attribute["name"];
        $attributeType = $attribute["type"];
        $attributeRequired = $attribute["required"];

        $linesToFile[$key] = "\$table->$attributeType('$attributeName');";
        if ($attributeRequired) {
            $linesToFile[$key] .= "->required();";
        }
        $linesToFile[$key] .= ";\n";
    }

    $linesToFile[] = "\$table->timestamps();\n";

    $migrations = glob("database/migrations/*$snakeModel*.php");
    if (count($migrations) > 1) {
        echo "Error: More than one migration found.\n";
        exit(1);
    }

    $filePath = $migrations[0];
    if (file_exists($filePath)) {
        $file = fopen($filePath, "r");
        $fileContent = fread($file, filesize($filePath));
        fclose($file);

        // @todo: implode lines to file correctly and setup space tabs
        $fileContent = str_replace("\$table->timestamps();", $linesToFile, $fileContent);

        $file = fopen($filePath, "w");
        fwrite($file, $fileContent);
        fclose($file);
    }
}

function snakeCase($word)
{
    # thanks Jan Jakeš: https://stackoverflow.com/a/19533226/6394559
    return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $word)), '_');
}

main($argv);
