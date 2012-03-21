<?php

use Gotron\Assets;

/**
 * View helper methods included in the global namespace 
 *
 */

function javascript_tag($name) {
    return "<script type=\"text/javascript\" src=\"" . Assets::javascript("$name.js") . "\" ></script>\n";
}

function css_tag($name) {
    return "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . Assets::css("$name.css") . "\" />\n";
}

function javascript_includes($includes) {
    $tags = "";
    foreach($includes['js'] as $include) {
        $tags .= javascript_tag($include);
    }
    return $tags;
}

function css_includes($includes) {
    $tags = "";
    foreach($includes['css'] as $include) {
        $tags .= css_tag($include);
    }
    return $tags;
}

?>