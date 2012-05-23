<?php

use Gotron\Assets,
    Gotron\View\PhpView,
    Gotron\Config;

/**
 * View helper methods included in the global namespace 
 */

/**
 * Creates JS tag inside of a view, does not validate the path exists. Uses the javascript path
 * specified in configuration
 *
 * @param string $name Name of the view, converted to $name.js
 * @return string
 */
function javascript_tag($name) {
    return "<script type=\"text/javascript\" src=\"" . Assets::javascript("$name.js") . "\" ></script>\n";
}

/**
 * Creates CSS tag inside of a view, does not validate the path exists. Uses the CSS path
 * specified in configuration
 *
 * @param string $name
 * @return string
 */
function css_tag($name) {
    return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Assets::css("$name.css") . "\" />\n";
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

?>