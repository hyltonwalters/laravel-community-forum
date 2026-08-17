#!/bin/sh
set -eu

: "${PORT:=10000}"

php artisan config:clear
php artisan migrate --force

php -r '
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
exit(App\User::query()->exists() ? 0 : 1);
' || php artisan db:seed --force

php artisan view:clear
php artisan route:clear
php artisan config:cache

exec php artisan serve --host=0.0.0.0 --port="$PORT"
