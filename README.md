<p align="center">
    <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400">
    </a>
</p>

# Kokoro API
## Minimum Requirements

- Php 7.4.9
- Mysql 8.2.21
- MariaDB 14.4.13
- Apache 2.4.46

[You find everything here](https://www.wampserver.com/en/)

## Software Requirements (Develop)
- [Composer](https://getcomposer.org/)
- [Laravel 8.x](https://laravel.com/docs/8.x) 

## Deployment - Dev
Migrate tables and initial data
```console
php artisan migrate:refresh --seed
```
Start the server
```console
php artisan serve
```
The server starts running on
```console
localhost:8000
```

## All documentation
[Laravel](https://laravel.com/docs/8.x)
