<?php
/**
 * tag.model.SimpleTagで利用するパラメータクラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class SimpleTagParameter{
	var $id;
	var $value;
	var $name;

	function SimpleTagParameter($id="",$value=""){
		$this->setId($id);
		$this->setValue($value);
	}
	function getId(){
		return $this->id;
	}
	function setId($value){
		$this->name	= $value;
		$this->id	= strtolower($value);
	}
	function getName(){
		return $this->name;
	}
	function getValue(){
		return $this->value;
	}
	function setValue($value){
		if(is_bool($value)){
			$value = $value?"true":"false";
		}
		$this->value = $value;
	}
}
?>