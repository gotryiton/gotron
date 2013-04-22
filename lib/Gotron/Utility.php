<?php

/**
 * Joins file paths with proper slashes
 *
 * @parameters string (each file path that you would like to join)
 *
 * @return string
 */
function file_join() {
    $args = func_get_args();
    $paths = array();

    foreach($args as $arg) {
      $paths = array_merge($paths, (array)$arg);
    }

    foreach($paths as $i => &$path) {
        if ($i === 0) {
            $path = rtrim($path, '/');
        }
        else {
            $path = trim($path, '/');
        }
    }

    return join('/', $paths);
}

/**
 * Ensures a protocol-less url gets a protocol
 *
 * @param string $str The url to be modified
 * @param string $protocol The protocol to be added if missing
 *
 * @return string
 */
function add_protocol($str, $protocol = 'http') {
    if (strpos($str, '//') === 0) {
        return $protocol . ':' . $str;
    }
    return $str;
}


if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }
}

?>
