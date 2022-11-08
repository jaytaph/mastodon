<?php
namespace Deployer;


require 'contrib/rsync.php';
require 'recipe/symfony.php';

set('application', 'mastodon');

set('repository', 'git@github.com:jaytaph/mastodon');

set('http_user', 'dhpt');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

task('deploy:update_code')->disable();
after('deploy:update_code', 'rsync');

add('shared_files', []);
add('shared_dirs', [ 'var/uploads' ]);

add('writable_dirs', [ 'var/uploads' ]);
set('allow_anonymous_stats', false);

set('rsync_src', __DIR__);

host('dhpt.nl')
    ->set('labels', ['stage' => 'prod'])
    ->set('remote_user', 'dhpt')
    ->set('deploy_path', '/home/dhpt/domains/dhpt.nl/{{application}}')
;

after('deploy:failed', 'deploy:unlock');

