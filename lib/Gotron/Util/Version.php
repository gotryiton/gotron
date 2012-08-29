<?php

namespace Gotron\Util;

class Version {

    public $major;
    public $minor;
    public $patch;
    public $full;

    public static function find_largest_version($keys) {
        $versions = static::parse_versions($keys);
        usort($versions, 'static::compare_versions');
        return $versions[0];
    }

    public static function compare_versions($a, $b) {
        switch (true) {
            case ($a->major < $b->major):
                return 1;
            case ($a->major > $b->major):
                return -1;
            case ($a->minor < $b->minor):
                return 1;
            case ($a->minor > $b->minor):
                return -1;
            case ($a->patch < $b->patch):
                return 1;
            case ($a->patch > $b->patch):
                return -1;
        }

        return 0;
    }


    public static function parse_keys($versions) {
        $compiled = [];
        foreach ($versions as $version => $value) {
            $version = static::parse_version($version);
            $compiled[$version->full] = $value;
        }

        return $compiled;
    }

    public static function parse_versions($versions) {
        $compiled = [];
        foreach ($versions as $version) {
            $compiled[$version] = static::parse_version($version);
        }

        return $compiled;
    }

    public static function parse_version($version) {
        preg_match_all("/(\d+)(\.*)/", $version, $matches);
        $version_matches = $matches[1];

        $instance = new self;
        $instance->major = array_key_exists(0, $version_matches) ? $version_matches[0] : 0;
        $instance->minor = array_key_exists(1, $version_matches) ? $version_matches[1] : 0;
        $instance->patch = array_key_exists(2, $version_matches) ? $version_matches[2] : 0;
        $instance->full = "{$instance->major}.{$instance->minor}.{$instance->patch}"; 

        return $instance;
    }

    public function __toString() {
        return $this->full;
    }

}

?>
