<?php
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DuplicateException extends ExceptionBase{
	function DuplicateException($properties=array()){
		$this->message = Message::_n("there are duplicates [{1}].");
		parent::ExceptionBase((is_array($properties)) ? $properties : func_get_args());
	}
}
?>