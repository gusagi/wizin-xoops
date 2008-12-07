<?php
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class MinLengthException extends ExceptionBase{
	function MinLengthException($properties=array()){
		$this->message = Message::_n("lower the length limit [{1}] up to [{2}s].");		
		parent::ExceptionBase((is_array($properties)) ? $properties : func_get_args());
	}
}
?>