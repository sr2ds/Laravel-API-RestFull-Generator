# Laravel Stubs

This repository contains some stubs to simplify yout life with Laravel.

Is required that your application has L5-Swagger setuped to correct run. If necessary, you can check this repository with all configuration, fully works and video demonstrations:

https://github.com/sr2ds/laravel-9-tutorial

# How this package can be better?

If do you have some idea or suggestion about this, feel free to open a Issue or tell me (my contacts there are in my profile).

My wish is create a Laravel package on composer to get attributes on command execution to create automaticly migration rules, swagger attributes and Requests validations. Maybe on the future we can work with this idea.

The good points about this stubs:

1. Crud fully tested mode easy;
2. OpenAPI with Swagger is simplified;
3. Keep your system with good standard;
4. Really more fast than create all manually.

Bad points:
1. Need write attributes manually (for a while);
2. Need re-write 3 lines in test files generated;

# How to usage

## Download this files to your laravel application 

```
mkdir stubs
cd stubs
git clone https://github.com/sr2ds/laravel-api-stubs .
```

## To generate resource api

```
php artisan make:model -c -f -m --api -R --test Product
```

## Additional configuration - After files generated

1. Is necessary change 3 `//@todo` lines in your tests files generated;
2. Write your attributes in `migrations` and `Model`(fillable and on swagger block) and `Requests`;
3. Create the route on `routes/api.php`