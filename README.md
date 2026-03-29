# Imgs

Image optimizing library, tailored for [Laravel](https://laravel.com/).

## Installation

You may install this package with [Composer](https://getcomposer.org/):

```bash
composer require semmelsamu/imgs
```

## Configuration

You may publish the configuration files like so:

```bash
php artisan vendor:publish --tag=imgs-config
```

A new file under `config/imgs.php` will be created. You may edit this file to 
configure Imgs.

## Custom views

You may publish the `imgs` component like so:

```bash
php artisan vendor:publish --tag=imgs-views
```

A new file under `resources/views/vendor/imgs/components/views/imgs.blade.php`
will be created. You may edit this file to overwrite the default component.
