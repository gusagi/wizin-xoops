<?php
Rhaco::import("lang.Variable");
/**
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2008- rhaco project. All rights reserved.
 */
class SetupViewsFilter{
	function publish($src,&$parser){
		/*** #pass */
		if(Variable::istype("HtmlParser",$parser)) $src = $parser->_exec2101_Form($src);
		return $src;
	}
}
?>