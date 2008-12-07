<?php
Rhaco::import("lang.ArrayUtil");
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class GenericException extends ExceptionBase{
	function GenericException($message="",$properties=array()){
		$this->message = Message::_n($message);
		parent::ExceptionBase((is_array($properties)) ? $properties : ArrayUtil::arrays(func_get_args(),1));
	}
}
?>