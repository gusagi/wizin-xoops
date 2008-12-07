<?php
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class IllegalStateException extends ExceptionBase{
	function IllegalStateException($properties=array()){
		$this->message = Message::_n("an illegal state occurred [{1}].");		
		parent::ExceptionBase((is_array($properties)) ? $properties : func_get_args());
	}
}
?>