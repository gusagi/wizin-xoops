<?php
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("resources.Message");
Rhaco::import("util.Logger");
/**
 * @deprecated 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class HttpPostFilesException extends ExceptionBase{
	function HttpPostFilesException($errorNo,$properties=array()){
		Logger::deprecated();
		if($errorNo == UPLOAD_ERR_INI_SIZE){
			$this->message = Message::_n("It is over the upload_max_filesize value set as php.ini [{1}].");
		}else if($errorNo == UPLOAD_ERR_FORM_SIZE){
			$this->message = Message::_n("It is over the MAX_FILE_SIZE value set up in form [{1}].");
		}else if($errorNo == UPLOAD_ERR_PARTIAL){
			$this->message = Message::_n("Only the part is uploaded [{1}].");
		}else{
			$this->message = Message::_n("It did not upload [{1}].");
		}
		parent::ExceptionBase((is_array($properties)) ? $properties : func_get_args());
	}
}
?>