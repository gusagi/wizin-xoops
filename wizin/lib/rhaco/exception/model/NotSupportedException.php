<?php
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
/**
 * @author Makoto Tsuyuki
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class NotSupportedException extends ExceptionBase{
	function NotSupportedException($properties=array()){
		$this->message = Message::_n("{1} is not supported.");		
		parent::ExceptionBase((is_array($properties)) ? $properties : func_get_args());
	}
}
?>