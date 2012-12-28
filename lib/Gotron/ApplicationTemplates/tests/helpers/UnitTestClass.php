<?php

namespace @app_namespace;

use GTIOUnit,
    GTIOUnit\UnitDB\Utils,
    Gotron\Header,
    Gotron\Cookie,
    Gotron\Cache,
    Gotron\Config,
    Gotron\Util\Version;

class UnitTest extends GTIOUnit\UnitTest {

    public static function setUpBeforeClass() {
        @app_class::initialize();
        @app_class::initialize_routes();

        $app = @app_class::instance();
        $app->version_by_request(Version::parse(@app_class::VERSION));

        $db = new Utils;
        $db->clear_db(Config::get('database'));

        Cache::flush();
    }

    public static function tearDownAfterClass() {
        Header::flush();
        Cookie::flush();
    }

    public static function application() {
        return @app_class::instance();
    }

}

?>
