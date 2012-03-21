<?php

namespace TestApp;

TestApplication::define_routes(array(
    '/test' => 'Test:index'
));

/*
 * Documentation for route definition
 * 
 * :var_name		|	semi-colons indicate variable names.  any text matched in this part of the route will get set to the var_name
 * 					example route:	'/looks/:outfitId' 			=> 'Looks:index'
 * 					example url:	/looks/xyz
 * 					parameters: 	outfitId	=>	'xyz'
 * 
 * [action]		|	brackets indicate action names.  any text matched in this part of the route will get set as the action name (and converted to underscores)
 * 					example route:	'/looks/:outfitId/[action]' => 'Looks'
 * 					example url:	/looks/xyz/clear-cache
 * 					action: 		Looks:clear_cache
 * 
 * ( ... ) 		|	parentheses indicate optional parameters that can be excluded or included in any order
 * 
 * (~var_name)		|	tilde indicate named custom variables.  any text matched in this part of the route will make the next part matched get set to the var_name
 * 					example route:	'/browse(~page)' => 'Browse'
 * 					example url:	/browse/page/2
 * 					parameters: 	page	=>	'2'
 * 
 * 
 * (=:var_name)	|	equals,colon indicate boolean variables.  any text matched in this part of the route will make the next part matched get set to the var_name
 * 					note: these variables can appear anywhere in the url structure
 * 					example route:	'/browse(=:popular)' => 'Browse'
 * 					example url:	/browse/popular/
 * 					parameters: 	popular	=>	true
 * 
 * (*:var_name)	|	asterix,colon indicate array variables.  any text matched in any part of the url that doesnt match other parts will get entered into an array of name var_name.  
 * 					note: these variables can appear anywhere in the url structure
 * 					note: currently only supporting ONE of these per route
 * 					example route:	'/browse(*:keyword)' => 'Browse'
 * 					example url:	/browse/key1/key2/key3
 * 					parameters: 	keyword	=>	{ 'key1','key2','key3'}
 * 
 * Requests to / will go to the Homepage controller's 'index' action
 *
 */

?>