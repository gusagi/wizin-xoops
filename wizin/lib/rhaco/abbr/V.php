<?php
Rhaco::import("lang.Variable");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.ArrayUtil");
/**
 * lang.Variableのエイリアス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class V extends Variable{
	/**
	 * Variable::copyPropertiesのエイリアス
	 *
	 * @param object $fromobject
	 * @param object $toobject
	 * @param boolean $propertyOnly
	 * @param array $excludeProperty
	 * @return object
	 */
	function cp($fromobject,$toobject,$propertyOnly=false,$excludeProperty=array()){
		/*** unit("lang.VariableTest"); */
		return Variable::copyProperties($fromobject,$toobject,$propertyOnly,$excludeProperty);
	}
	
	/**
	 * ObjectUtil::hashConvObjectのエイリアス
	 *
	 * @param array $hash
	 * @param object $object
	 * @param boolean $isMethod
	 * @return object
	 */
	function ho($hash,&$object,$isMethod=true){
		/*** unit("lang.VariableTest"); */
		return ObjectUtil::hashConvObject($hash,$object,$isMethod);
	}
	
	/**
	 * ObjectUtil::objectConvHashのエイリアス
	 *
	 * @param object $object
	 * @param array $hash
	 * @param boolean $isMethod
	 * @return array
	 */
	function oh($object,$hash=array(),$isMethod=false){
		/*** unit("lang.VariableTest"); */
		return ObjectUtil::objectConvHash($object,$hash,$isMethod);
	}
	
	/**
	 * ArrayUtil::dictのエイリアス
	 *
	 * @param string $dict
	 * @param array $keys
	 * @param boolean $fill
	 * @return array
	 */
	function dict($dict,$keys,$fill=true){
		/***
		 * $dict = "name=hogehoge,title='rhaco',arg=get\,tha";
		 * $keys = array("name","arg","description","title");
		 * $result = V::dict($dict,$keys);
		 * eq(4,sizeof($result));
		 * foreach($result as $key => $value) $$key = $value;
		 * eq("hogehoge",$name);
		 * eq("rhaco",$title);
		 * eq(null,$description);
		 * eq("get,tha",$arg);
		 * 
		 */
		return ArrayUtil::dict($dict,$keys,$fill);
	}
	
	/**
	 * ArrayUtil::ishashのエイリアス
	 *
	 * @param array $var
	 * @return boolean
	 */
	function ishash($var){
		/***
		 * assert(!V::ishash(array("A","B","C")));
		 * assert(!V::ishash(array(0=>"A",1=>"B",2=>"C")));
		 * assert(V::ishash(array(1=>"A",2=>"B",3=>"C")));
		 * assert(V::ishash(array("a"=>"A","b"=>"B","c"=>"C")));
		 * assert(!V::ishash(array("0"=>"A","1"=>"B","2"=>"C")));
		 * assert(!V::ishash(array(0=>"A",1=>"B","2"=>"C")));
		 */
		return ArrayUtil::ishash($var);		
	}
	
	/**
	 * ArrayUtil::implodeのエイリアス
	 *
	 * @param array $array
	 * @param string $glue
	 * @param integer $offset
	 * @param integer $length
	 * @param boolean $fill
	 * @return string
	 */
	function implode($array,$glue="",$offset=0,$length=0,$fill=false){
		/***
		 * eq("hogekokepopo",V::implode(array("hoge","koke","popo")));
		 * eq("koke:popo",V::implode(array("hoge","koke","popo"),":",1));
		 * eq("koke",V::implode(array("hoge","koke","popo"),":",1,1));
		 * eq("hoge:koke:popo::",V::implode(array("hoge","koke","popo"),":",0,5,true));
		 * 
		 */
		return ArrayUtil::implode($array,$glue,$offset,$length,$fill);
	}
	
	/**
	 * ArrayUtil::hgetのエイリアス
	 *
	 * @param string $name
	 * @param array $array
	 * @return unknown
	 */
	function hget($name,$array){
		/***
		 * $list = array("ABC"=>"AA","deF"=>"BB","gHi"=>"CC");
		 * 
		 * eq("AA",V::hget("abc",$list));
		 * eq("BB",V::hget("def",$list));
		 * eq("CC",V::hget("ghi",$list));
		 * 
		 * eq(null,V::hget("jkl",$list));
		 * eq(null,V::hget("jkl","ABCD"));
		 */		
		return ArrayUtil::hget($name,$array);
	}
	

	/**
	 * ArrayUtil::arraysのエイリアス
	 *
	 * @param array $array
	 * @param integer $offset
	 * @param integer $length
	 * @param boolean $fill
	 * @return array
	 */
	function arrays($array,$offset=0,$length=0,$fill=false){
		/***
		 * eq(1,sizeof(ArrayUtil::arrays(array(0,1),1,1)));
		 * eq(2,sizeof(ArrayUtil::arrays(array(0,1,2),0,2)));
		 * eq(3,sizeof(ArrayUtil::arrays(array(0,1),0,3,true)));
		 * eq(2,sizeof(ArrayUtil::arrays(array(0,1,2,3,4),3,6)));
		 */
		return ArrayUtil::arrays($array,$offset,$length,$fill);
	}
	
	/**
	 * ObjectUtil::mixinのエイリアス
	 *
	 * @return objet
	 */
	function mixin(){
		/*** unit("lang.VariableTest"); */
		$args = func_get_args();
		return call_user_func_array(array("ObjectUtil","mixin"),$args);
	}
}
?>