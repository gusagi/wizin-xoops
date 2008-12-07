<?php
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class ClassTypeException extends ExceptionBase{
	function ClassTypeException($properties=array()){
		$this->message = Message::_n("Class type is mismatched '{1}' expected '{2}' result '{3}'.");
		parent::ExceptionBase((is_array($properties)) ? $properties : func_get_args());
	}
}
?>