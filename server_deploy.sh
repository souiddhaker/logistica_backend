#!/bin/sh
 /opt/plesk/php/7.2/bin/php artisan migrate
 /opt/plesk/php/7.2/bin/php artisan db:seed --class=UserSeeder
chown -R dhaker:psaserv *
