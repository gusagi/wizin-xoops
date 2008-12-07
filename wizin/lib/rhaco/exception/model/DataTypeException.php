<?php
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DataTypeException extends ExceptionBase{
	function DataTypeException($properties=array()){
		$this->message = Message::_n("Argument data format is mismatched [{1}].");
		parent::ExceptionBase((is_array($properties)) ? $properties : func_get_args());
	}
}
?>