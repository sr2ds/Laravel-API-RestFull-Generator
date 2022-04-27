<?php

/**
 * WIP: This not working yet.
 * This file is part of the Laravel Stubs Custom API RestFull with OpenApi
 * The goals is rewrite files with correct attributes to full automatically rest api
 * Usage: php stubs/misc/setup-atributes.phpstubs/misc/file.txt ModelName
 */

function main($argv)
{
    checkNumOfParams($argv);

    echo "Getting attributes from file...";
    $attributes = getAttributeListFromFile($argv[1]);

    echo "Setuping files to be modified...";
    $modelName = $argv[2];
    setupController($attributes, $modelName);
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
        if (strlen($line) > 0) {
            $attributes[] = $line;
        }
    }
    fclose($file);
    return $attributes;
}

function setupController($modelName)
{
}

function setupMigration($modelName)
{
}

main($argv);
