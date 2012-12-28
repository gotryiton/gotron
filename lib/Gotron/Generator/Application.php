<?php

namespace Gotron\Generator;

class AppGenerator {

    public static $directories = [
        'app/controllers' => [
            'ApplicationController.php',
            'HomepageController.php'
        ],
        'app/helpers' => [
            '.gitkeep'
        ],
        'app/models' => [
            '.gitkeep'
        ],
        'app/modules' => [
            '.gitkeep'
        ],
        'app/presenters/v1' => [
            'StatusPresenter.php'
        ],
        'app/processes' => [
            '.gitkeep'
        ],
        'app/sass' => [
            'screen.scss'
        ],
        'app/views/layouts' => [
            'layout.php'
        ],
        'app/views/homepage' => [
            'index.php'
        ],
        'bin' => [
            'test'
        ],
        'config' => [
            'application.php',
            'autoload.php',
            'database.yml',
            'deploy.rb',
            'routes.php'
        ],
        'config/environments' => [
            'test.php',
            'development.php',
            'staging.php',
            'production.php'
        ],
        'config/deploy' => [
            'staging.rb',
            'production.rb'
        ],
        'config/localization' => [
            'en.yaml'
        ],
        'db/migrate' => [
            '.gitkeep'
        ],
        'public' => [
            'index.php'
        ],
        'public/assets/css' => [
            '.gitkeep'
        ],
        'public/assets/images' => [
            '.gitkeep'
        ],
        'public/assets/js' => [
            '.gitkeep'
        ],
        'tests' => [
            'bootstrap.php'
        ],
        'tests/app/controllers' => [
            '.gitkeep'
        ],
        'tests/app/models' => [
            '.gitkeep'
        ],
        'tests/app/modules' => [
            '.gitkeep'
        ],
        'tests/fixtures' => [
            '.gitkeep'
        ],
        'tests/helpers' => [
            'UnitTestClass.php'
        ],
        'tmp' => [
            '.gitkeep'
        ],
        'vendor' => [
            '.gitkeep'
        ],
        'Capfile',
        'Gemfile',
        'Guardfile',
        'Rakefile',
        'phpunit.xml'
    ];

    public static function run($name, $directory) {
    }

}

?>
