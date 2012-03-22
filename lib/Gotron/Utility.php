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

	foreach($paths as &$path) {
	  $path = trim($path, '/');
	}

	if (substr($args[0], 0, 1) == '/') {
	  $paths[0] = '/' . $paths[0];
	}

	return join('/', $paths);
}

?>