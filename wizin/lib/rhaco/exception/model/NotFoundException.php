<?php
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class NotFoundException extends ExceptionBase{
	function NotFoundException($properties=array()){
		$this->message = Message::_n("{1} is not found.");		
		parent::ExceptionBase((is_array($properties)) ? $properties : func_get_args());
	}
}
?>