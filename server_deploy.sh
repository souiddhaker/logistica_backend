#!/bin/sh
composer install
php artisan migrate migrate:fresh --seed
chown -R dhaker:psaserv *
