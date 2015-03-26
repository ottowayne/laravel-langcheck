# Laravel LangCheck

## Compatibility

* Laravel 5.0

## Installation

Install via composer:

```
composer require ottowayne/langcheck --dev
```

The *dev*-paramter is optional but I recommend using this package in local environments only.

Finally add the service provider to your app.php:

```
'Ottowayne\LangCheck\LangCheckServiceProvider',
```

## Configuration

You may export the configuration file via

```
php artisan config:publish ottowayne/langcheck
```

and edit it as you like.

## Usage

You can use the command

```
php artisan lang:check
```

to search for differences in all language files or edit the configuration settings to either log or throw an exception if an unused key is found.

If you want to define your own behaviour you can listen for the *LangCheck::missingtranslation* event.
