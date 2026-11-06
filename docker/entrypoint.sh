#!/usr/bin/env sh
set -eu

php artisan migrate --force

if php artisan list --raw 2>/dev/null | grep -q '^library:ensure-admin'; then
    php artisan library:ensure-admin
fi

exec php -S 0.0.0.0:8000 -t public public/index.php
