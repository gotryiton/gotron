<?php

namespace Gotron;

class Loader extends \Aura\Autoload\Loader {

    public $framework_namespace = "Gotron";

    public $framework_class_paths = array();

    /**
	 * Adds path to the class paths array
	 *
	 * @param array $paths 
	 * @return void
	 */
	public function addFrameworkClassPath($path) {
    	$this->framework_class_paths[] = rtrim($path, DIRECTORY_SEPARATOR);
    }

    public function addFrameworkClassPaths($paths, $namespace = "Gotron") {
        $this->framework_namespace = $namespace;
        foreach ((array) $paths as $path) {
            $this->framework_class_paths[] = rtrim($path, DIRECTORY_SEPARATOR);
        }
    }

    public function setFrameworkPaths(array $paths = array()){
        foreach ($paths as $val) {
            $this->addFrameworkClassPath($val);
        }
    }

    public function find($spec) {
        if (isset($this->classes[$spec])) {
            return $this->classes[$spec];
        }

        $this->tried_paths = array();

        $pos = strrpos($spec, '\\');
		$namespace = substr($spec, 0, $pos);

		if($namespace === $this->framework_namespace) {
			$non_namespace = str_replace($namespace, "", $spec);
			$ctf = $this->classToFile($non_namespace);
			foreach($this->framework_class_paths as $i => $path) {
				$this->tried_paths[] = "#{$i}: {$path}";

				// convert the remaining spec to a file name
                $file = $path . DIRECTORY_SEPARATOR . $ctf;

				if(is_readable($file)) {
					return $file;
				}
			}
		}

        return parent::find($spec);
    }

}

?>