# Internal Project using Laravolt Platform

## Server Requirements

1. PHP 8.x
1. Composer 2
1. PostgreSQL (recommended) or MariaDB

## Local Setup

1. Clone repository
1. Jalankan `composer install`
1. Sesuaikan konfigurasi database dan lain-lain
1. Jalankan `php artisan key:generate`
1. Jalankan `php artisan migrate:fresh --seed`
1. Jalankan `php artisan storage:link`
1. Jalankan `php artisan vendor:publish --tag=laravolt-assets`
1. Pastikan folder-folder berikut _writeable_:
   1. bootstrap/cache
   1. storage
1. Jalankan `php artisan serve`

## Deployment

1. Clone repository
1. Salin `.env.example` ke `.env`
1. Jalankan `choown -R 33:33 ./`
1. Jalankan `docker compose up -d`
1. Jalankan `docker compose exec php bash`
1. Jalankan `composer install`
1. Jalankan `php artisan key:generate`
1. Jalankan `php artisan migrate:fresh --seed`
1. Jalankan `php artisan storage:link`
1. Jalankan `php artisan laravolt:link`
