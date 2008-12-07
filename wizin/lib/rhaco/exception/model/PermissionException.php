<?php
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class PermissionException extends ExceptionBase{
	function PermissionException($properties=array()){
		$this->message = Message::_n("You don't have a permission[{1}].");		
		parent::ExceptionBase((is_array($properties)) ? $properties : func_get_args());
	}
}
?>