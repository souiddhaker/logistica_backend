#!/bin/sh
su - dhaker
/opt/plesk/php/7.3/bin/php /usr/lib/plesk-9.0/composer.phar install
/opt/plesk/php/7.3/bin/php artisan migrate:fresh --seed
#chown -R dhaker:psacln *
