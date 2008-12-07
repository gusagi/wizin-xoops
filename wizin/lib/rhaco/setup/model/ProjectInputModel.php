<?php
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotFoundException");
/**
 * setup.php用　data model
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class ProjectInputModel{
	var $name = "";
	var $value = "";
	var $title = "";
	var $description = "";
	var $type = "";
	var $dataList = array();

	function ProjectInputModel($name="",$value=""){
		$this->setName($name);
		$this->setValue($value);
	}
	function setName($value){
		$this->name = $value;
	}
	function setValue($value){
		$this->value = str_replace(array('\\',"\n",'"'),array('\\\\',"\\n",'\"'),StringUtil::toULD($value));
	}
	
	function toInstance($inputTag){
		/*** #pass */
		$input = ObjectUtil::copyProperties($inputTag,new ProjectInputModel());

		switch(strtolower($inputTag->getName())){
			case "input":
				$input->type		= "input";
				$input->title		= $inputTag->getInValue("title");
				$input->description	= $inputTag->getInValue("description");
				$input->value		= (Rhaco::constant($input->name) != null) ? Rhaco::constant($input->name) : $inputTag->getInValue("data");
				break;
			case "password":
				$input->type		= "password";
				$input->title		= $inputTag->getInValue("title");
				$input->description	= $inputTag->getInValue("description");
				$input->value		= (Rhaco::constant($input->name) != null) ? Rhaco::constant($input->name) : $inputTag->getInValue("data");
				break;
			case "text":
				$input->type		= "text";
				$input->title		= $inputTag->getInValue("title");
				$input->description	= $inputTag->getInValue("description");
				$input->value		= (Rhaco::constant($input->name) != null) ? Rhaco::constant($input->name) : $inputTag->getInValue("data");
				break;
			case "select":
				$input->type		= "select";
				$input->title		= $inputTag->getInValue("title");
				$input->description	= $inputTag->getInValue("description");
				$input->value		= Rhaco::constant($input->name);
				
				foreach($inputTag->getIn("data") as $dataTag){
					$input->dataList[$dataTag->getParameter("caption",$dataTag->getValue())] = $dataTag->getValue();
				}
		}
		if(empty($input->name)) ExceptionTrigger::raise(new NotFoundException(Message::_("name of {1} tag",$input->type)));
		return $input;
	}
	

}
?>