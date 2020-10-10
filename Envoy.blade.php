@setup
$repository = 'git@gitlab.com:logistica-project/logistica-web.git';
$releases_dir = '/var/www/vhosts/luggage-sa.co/api.luggage-sa.co/releases';
$app_dir = '/var/www/vhosts/luggage-sa.co/api.luggage-sa.co';
$release = date('YmdHis');
$new_release_dir = $releases_dir .'/'. $release;
@endsetup
@servers(['web' => ['adminssh@luggage-sa.co']])
@task('clone_repository')
echo 'Cloning repository'
[ -d {{ $releases_dir }} ] || mkdir {{ $releases_dir }}
git clone  -b prod --single-branch --depth 1 {{ $repository }} {{ $new_release_dir }}
cd {{ $new_release_dir }}
git reset --hard {{ $commit }}
@endtask

@task('run_composer')
echo "Starting deployment ({{ $release }})"
cd {{ $new_release_dir }}
/opt/plesk/php/7.3/bin/php composer.phar install -vvv

@endtask
@task('update_symlinks')
echo "Linking storage directory"
rm -rf {{ $new_release_dir }}/storage
ln -nfs {{ $app_dir }}/storage {{ $new_release_dir }}/storage

echo 'Linking .env file'
ln -nfs {{ $app_dir }}/.env {{ $new_release_dir }}/.env

echo 'Linking current release'
ln -nfs {{ $new_release_dir }} {{ $app_dir }}/current
@endtask
@task('seed')
cd {{ $new_release_dir }}
/opt/plesk/php/7.3/bin/php artisan migrate
/opt/plesk/php/7.3/bin/php artisan storage:link
@endtask
@story('deploy')
clone_repository
run_composer
seed
update_symlinks
@endstory
