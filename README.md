# Make Modules

## Instalation

Add this to composer.json, then run `composer update`.

```
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:erikfig/laravel-make-module.git"
        }
    ],
    "require": {
        "erikfig/make-module": "dev-master"
    },
```

## To use

```
php artisan make:module [name] [type]
```

Example:

```
php artisan make:module dashboard layouts
```

Any type can be used, example:

 - layouts
 - features
 - commands
 - qwerasd
 - others

The type layouts has a different template module, over time i want to add others.

Att. Erik Figueiredo
