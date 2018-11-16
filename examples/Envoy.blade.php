@servers(['dev' => 'x.x.x.x -p 23522', 'stage' => 'x.x.x.x', 'prod' => 'x.x.x.x'])

@setup
    $env = $env ?? 'dev';
    $server = $env;

    $projectName = 'Project';
    $gitRepository = 'https://xxx:yyy@gitlab.ffflabel-dev.com/yura.tovt/repo.git';

    $hasErrors = false;

    $slackWebHookUrl = 'https://hooks.slack.com/services/xxx/xxx/xxx';
    $slackChannel = '#xxx';

    $serversData = [
        'dev' => [
            'url' => '',
            'releasesDir' => '/var/www/Project/releases/',
            'sharedDir' => '/var/www/Project/shared/',
            'gitBranch' => 'ci-setup',
            'dbConfigFile' => 'db.dev.php'
        ],
        'stage' => [
            'url' => '',
            'releasesDir' => '/var/www/Project/releases/',
            'sharedDir' => '/var/www/Project/shared/',
            'gitBranch' => 'stage',
            'dbConfigFile' => 'db.stage.php'
        ],
        'prod' => [
            'url' => '',
            'releasesDir' => '/var/www/Project/releases/',
            'sharedDir' => '/var/www/Project/shared/',
            'gitBranch' => 'prod',
            'dbConfigFile' => 'db.prod.php'
        ]
    ];

    $releaseDir = $serversData[$env]['releasesDir'] . date('YmdHis') . '/';
    $vendorDir = $serversData[$env]['sharedDir'] . 'vendor/';
    $publicDir = $serversData[$env]['sharedDir'] . 'public/';

    $currentLink = $serversData[$env]['releasesDir'] . 'current';
@endsetup

@task('create-release-dir')
    echo 'Creating release dir {{ $releaseDir }}...'
    mkdir -p {{ $releaseDir }}
@endtask

@task('setup-shared-dirs')
    echo 'Creating shared dirs {{ $vendorDir }}, {{ $publicDir }}...'
    mkdir -p {{ $vendorDir }}
    mkdir -p {{ $publicDir }}
@endtask

@task('git-clone')
    echo 'Cloning repository {{ $gitRepostory }}...'
    cd {{ $releaseDir }}
    git clone {{ $gitRepository }} --branch {{ $serversData[$env]['gitBranch'] }} --depth 1 .
    echo 'Replacing vendor and public dirs with symlinks to shared dirs...'
    ln -sf {{ $vendorDir }} {{ $releaseDir }}/vendor
    ln -sf {{ $publicDir }} {{ $releaseDir }}/web/public
@endtask

@task('configure')
    echo 'Configuring application...'
    cd {{ $releaseDir }}
    rm config/db.php
    cp config/{{ $serversData[$env]['dbConfigFile'] }} config/db.php
@endtask

@task('composer-install')
    echo 'Installing composer dependencies...'
    cd {{ $releaseDir }}
    composer install
@endtask

@task('run-migrations')
    echo 'Running migrations...'
    cd {{ $releaseDir }}
    php yii migrate --interactive=0
@endtask

@task('change-owner')
    echo 'Changing owner to www-data:www-data...'
    chown www-data:www-data {{ $releaseDir }} -R
@endtask

@task('switch-release')
    echo 'Switching to new release...'
    rm -f {{ $currentLink }}
    ln -sf {{ $releaseDir }} {{ $currentLink }}
@endtask

@task('restart-services')
    echo 'Restart nginx and php-fpm...'
    service nginx restart
    service php7.2-fpm restart
@endtask

@task('cleanup-releases')
    echo 'Cleaning up old releases...'
@endtask

@story('deploy', ['on' => $server])
    create-release-dir
    setup-shared-dirs
    git-clone
    composer-install
    configure
    run-migrations
    switch-release
    change-owner
    restart-services
    cleanup-releases
@endstory

@error
    @slack($slackWebHookUrl, $slackChannel, ":robot_face: Oops,something went wrong while deploying $projectName project deployment to $server server")
@enderror

@finished
    @slack($slackWebHookUrl, $slackChannel, ":robot_face: $projectName project deployment to $server server finished")
@endfinished
