<?php

namespace Gotron\Util;

class Version {

    public $major;
    public $minor;
    public $patch;
    public $full;

    /**
     * Magic method to render this version as a string
     *
     * @return string
     */
    public function __toString() {
        return $this->to_s();
    }

    /*
     * Render this version as a string
     *
     * @return string
     */
    public function to_s() {
        if (isset ($this->full)) {
            return $this->full;
        }
        else {
            $this->full = "{$this->major}.{$this->minor}.{$this->patch}";
            return $this->full;
        }
    }

    /*
     * Check if this version is equal to the one passed
     *
     * @param Version $version
     * @return bool
     */
    public function eq($version) {
        return static::compare_versions($this, $version) === 0;
    }

    /*
     * Check if this version is greater than the one passed
     *
     * @param Version $version
     * @return bool
     */
    public function gt($version) {
        return static::compare_versions($this, $version) < 0;
    }

    /*
     * Check if this version is greater than or equal to the one passed
     *
     * @param Version $version
     * @return bool
     */
    public function gt_eq($version) {
        return static::compare_versions($this, $version) <= 0;
    }

    /*
     * Check if this version is less than the one passed
     *
     * @param Version $version
     * @return bool
     */
    public function lt($version) {
        return static::compare_versions($this, $version) > 0;
    }

    /*
     * Check if this version is less than or equal to the one passed
     *
     * @param Version $version
     * @return bool
     */
    public function lt_eq($version) {
        return static::compare_versions($this, $version) >= 0;
    }

    /*
     * Find the largest version in a list of string keys
     *
     * @param array $keys
     * @return Version
     */
    public static function find_largest_version($keys) {
        $versions = static::parse_multiple($keys);
        usort($versions, 'static::compare_versions');
        return $versions[0];
    }

    /*
     * Compare two versions, follows requirements for a usort cmp_function
     *
     * returns  0 if a and b are equal
     * returns -1 if a is larger
     * returns  1 if b is larger
     *
     * @param Version $a
     * @param Version $b
     * @return int
     */
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

    /*
     * Parse an array of string versions to Version objects
     *
     * @param array $versions
     * @return array
     */
    public static function parse_multiple($versions) {
        $compiled = [];
        foreach ($versions as $version) {
            $compiled[$version] = static::parse($version);
        }

        return $compiled;
    }

    /*
     * Parse a string version to a Version object
     *
     * @param string $version
     * @return Version
     */
    public static function parse($version) {
        preg_match_all("/(\d+)(\.*)/", $version, $matches);
        $version_matches = $matches[1];

        $instance = new self;
        $instance->major = array_key_exists(0, $version_matches) ? $version_matches[0] : 0;
        $instance->minor = array_key_exists(1, $version_matches) ? $version_matches[1] : 0;
        $instance->patch = array_key_exists(2, $version_matches) ? $version_matches[2] : 0;

        return $instance;
    }

}

?>
