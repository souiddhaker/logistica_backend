#!/bin/sh
/opt/plesk/php/7.3/bin/php /usr/lib/plesk-9.0/composer.phar install
/opt/plesk/php/7.3/bin/php artisan migrate migrate:fresh --seed
chown -R dhaker:psaserv *
