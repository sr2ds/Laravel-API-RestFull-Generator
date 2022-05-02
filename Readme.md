# Laravel API RestFull Generator

This project is a simple tool for help you with your CRUDs.

What have in here?

1. Some stubs to a razoable API RestFull with Tests and OpenAPI documentation.
2. PHP script to write attributes in all files generated

Is required that your application has L5-Swagger setuped to correct run. 


# How to usage

## 1. Download this files to your laravel application 

```
mkdir stubs
cd stubs
git clone https://github.com/sr2ds/Laravel-API-RestFull-Generator .
```

## 2. Setup your schema attributes like file `stubs/misc/file.txt`

## 3. Run Script to generate files and write attributes

```
    php stubs/misc/set-attributes.php stubs/misc/file.txt Example
```

## TODO

1. Improve and increment more data types
2. Refactor

## Extra Help

If necessary, you can check this repository with all configuration, fully works and video demonstrations:
https://github.com/sr2ds/laravel-9-tutorial