<?php

namespace TestApp;

use Gotron\Config;

class ConfigTest extends UnitTest {

    public function test_initialize() {
        $num = 12345678;
        Config::initialize(function($config) use($num){
            $config->set('unset_number', $num);
        });

        $this->assertEquals($num, Config::get('unset_number'));
    }

    public function test_set_environment() {
        $config = Config::instance();
        $env = 'nonexistent_environment';
        $config->set_environment($env);
        $this->assertEquals($env, Config::get('environment'));
        $config->set_environment('test');
        $this->assertEquals('test', Config::get('environment'));
    }

    public function test_get_environment() {
        $config = Config::instance();
        $this->assertEquals('development', $config->get_environment());
        $_ENV['APP_ENVIRONMENT'] = 'some_other_environment';
        $this->assertEquals('some_other_environment', $config->get_environment());
        unset($_ENV['APP_ENVIRONMENT']);
    }

    public function test_define_app_version() {
        $this->assertEquals("0ad335cdbc6611da404f80266f2d5b7cf199150d", Config::get('app_version'));
        $version = '100023456';
        $revision_file = file_join(Config::get('root_directory'), 'ASSET_REVISION');
        file_put_contents($revision_file, $version);
        $config = Config::instance();
        $config->define_app_version();
        $this->assertEquals($version, Config::get('app_version'));
        unlink($revision_file);
    }

    public function test_load_from_yaml() {
        $yaml_file = __DIR__ . "/helpers/test.yml";
        $config = Config::instance();
        $config->load_from_yaml(array('sample_param', 'other_sample_param'), $yaml_file);
        $this->assertEquals(123456, Config::get('sample_param'));
        $this->assertEquals('something else', Config::get('other_sample_param'));
    }

    public function test_get() {
        $config = Config::instance();
        $val = 1982634;
        $config->set('test_property', $val);
        $this->assertEquals($val, Config::Get('test_property'));

        $this->setExpectedException("Gotron\Exception", "Configuration property not_set_property is not set");
        $instance = Config::get('not_set_property');
    }

    public function test_bool() {
        $config = Config::instance();
        $this->assertFalse(Config::bool('not_set_bool_property'));
        $config->set('bool_property_false',  false);
        $this->assertFalse(Config::bool('bool_property_false'));
        $config->set('bool_property_true',  true);
        $this->assertTrue(Config::bool('bool_property_true'));
    }

    public function test_set() {
        $config = Config::instance();
        $value = 102345;
        $config->set('test.namespaced.value', $value);
        $this->assertEquals($value, Config::get('test.namespaced.value'));

        // test overwriting value
        $value = 111111;
        $config->set('test.namespaced.value', $value);
        $this->assertEquals($value, Config::get('test.namespaced.value'));
    }

    public function test_set_constant() {
        $config = Config::instance();
        $value = 9999999;
        $config->set_constant('UNSET_CONSTANT', $value);
        $this->assertEquals($value, UNSET_CONSTANT);

        $config->set_constant('UNSET_CONSTANT', '1111111');
        $this->assertEquals($value, UNSET_CONSTANT);
    }

    public function test_array_access() {
        $config = Config::instance();
        $value = 'some value';
        $config->set('some.namespaced.value', $value);
        $this->assertEquals($value, $config['some']['namespaced']['value']);

        $this->assertFalse(array_key_exists('not_set', $config));
        $this->assertFalse(array_key_exists('not_set', $config['some']));
    }

}

?>
