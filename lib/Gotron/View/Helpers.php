<?php

use Gotron\Assets,
    Gotron\View\PhpView,
    Gotron\Config;

/**
 * View helper methods included in the global namespace 
 */

function build_attributes($attributes) {
    return implode(
                        " ",
                        array_map(function($key, $value) { return "{$key}=\"{$value}\""; },
                        array_keys($attributes),
                        $attributes)
                    );
}

/**
 * Creates JS tag inside of a view, does not validate the path exists. Uses the javascript path
 * specified in configuration
 *
 * @param string $name Name of the view, converted to $name.js
 * @return string
 */
function javascript_tag($name, $additional_attributes = []) {
    $attributes = build_attributes($additional_attributes);

    return "<script type=\"text/javascript\" src=\"" . Assets::javascript($name) . "\" $attributes></script>\n";
}

/**
 * Creates CSS tag inside of a view, does not validate the path exists. Uses the CSS path
 * specified in configuration
 *
 * @param string $name
 * @return string
 */
function css_tag($name, $additional_attributes = []) {
    $attributes = build_attributes($additional_attributes);

    return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Assets::css($name) . "\" $attributes/>\n";
}

/**
 * Creates img tag inside of a view, does not validate the path exists. Uses the images path
 * specified in configuration
 *
 * @param string $name Filename of the image
 * @return string
 */
function img_tag($filename, $additional_attributes = []) {
    $attributes = build_attributes($additional_attributes);

    return "<img src=\"" . Assets::image($filename) . "\" $attributes/>\n";
}

/**
 * Creates CSS tag inside of a view, does not validate the path exists. Uses the CSS path
 * specified in configuration
 *
 * @param string $name
 * @return string
 */
function meta_tag($attributes = []) {
    return "<meta " . build_attributes($attributes) . " />\n";
}

/**
 * Creates Javascript tags inside of a view (used in layouts)
 *
 * @param array $includes
 * @return $string JS tags
 */
function javascript_includes($includes) {
    $tags = "";
    foreach($includes['js'] as $include) {
        $tags .= javascript_tag($include);
    }
    return $tags;
}

/**
 * Creates CSS tags inside of a view (used in layouts)
 *
 * @param array $includes
 * @return $string css tags
 */
function css_includes($includes) {
    $tags = "";
    foreach($includes['css'] as $include) {
        $tags .= css_tag($include);
    }
    return $tags;
}

function meta_includes($includes) {
    $tags = "";
    foreach ($includes['meta'] as $include) {
        $tags .= meta_tag($include);
    }

    return $tags;
}

/**
 * Renders a partial view
 *
 * @param string $name Name of the partial, gets converted to _$name.php
 * @param string $context Context path for the view (controller name)
 * @param string $data Data to be passed to the view
 * @return string Content of the view
 */
function render_partial($name, $context, $data = array()) {
    $path = realpath(file_join(Config::get('root_directory'), Config::get('view_directory'), $context, "_{$name}.php"));
    $view_data = PhpView::render($data, $path);
    return $view_data->content;
}

function select_tag($id = "", $start, $finish) {
    $tag = "<select id=\"$id\">\n";
    if ($start < $finish) {
        for ($i = $start; $i <= $finish; $i++) {
            $tag.= "    <option value=\"{$i}\">{$i}</option>\n";
        }
    }
    else {
        for ($i = $start; $i >= $finish; $i--) {
            $tag.= "    <option value=\"{$i}\">{$i}</option>\n";
        }
    }
    $tag .= "</select>";
    return $tag;
}

/**
 * Checks if the key exists in the params array,
 * returns value of key or empty string
 *
 * @param string $key
 * @param array $params
 * @return string
 */
function value_for($key, $params) {
    if (is_array($params)) {
        if (array_key_exists($key, $params)) {
            return $params[$key];
        }

        foreach ($params as $param) {
            if (is_array($param) && array_key_exists($key, $param)) {
                return $param[$key];
            }
            elseif (is_object($param) && isset($param->$key)) {
                return $param->$key;
            }
        }
    }
    elseif (is_object($params)) {
        if (method_exists($params, $key)) {
            return $params->$key;
        }
    }

    return "";
}

function load_field_name($name, $namespace) {
    $field_name = $name;
    if (!is_null($namespace)) {
        $field_name = "{$namespace}[{$name}]";
    }

    return $field_name;
}

function text_field($name, $params = [], $namespace, $additional_attributes = []) {
    $attributes = build_attributes($additional_attributes);
    $field_name = load_field_name($name, $namespace);

    return "<input type=\"text\" id=\"$name\" name=\"$field_name\" value=\"" . value_for($name, $params) . "\" $attributes />";

}

function check_box($name, $params = null, $namespace, $additional_attributes = []) {
    $attributes = build_attributes($additional_attributes);
    $field_name = load_field_name($name, $namespace);

    if (!is_null($params)) {
        if (is_array($params) || is_object($params)) {
            $value = value_for($name, $params);
        }
        else {
            $value = $params;
        }
    }

    $checked = ($value == true || $value == 1) ? " checked=\"true\"" : "";

    return "<input type=\"hidden\" name=\"$field_name\" value=\"0\" />\n <input type=\"checkbox\" id=\"$name\" value=\"1\" name=\"$field_name\"" . $checked . " $attributes />";
}

?>
