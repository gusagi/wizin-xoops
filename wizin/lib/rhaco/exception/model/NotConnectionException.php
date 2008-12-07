<?php
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
/**
 * @author Makoto Tsuyuki
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class NotConnectionException extends ExceptionBase{
	function NotConnectionException($properties=array()){
		$this->message = Message::_n("{1} is not connected.");
		parent::ExceptionBase((is_array($properties)) ? $properties : func_get_args());
	}
}
?>