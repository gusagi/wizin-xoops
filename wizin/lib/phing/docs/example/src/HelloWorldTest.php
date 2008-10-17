<?php

    require_once "PHPUnit2/Framework/TestCase.php";
    require_once "HelloWorld.php";

    /**
    * Test class for HelloWorld
    *
    * @author Michiel Rook
    * @version $Id: HelloWorldTest.php 123 2006-09-14 20:19:08Z mrook $
    * @package hello.world
    */
    class HelloWorldTest extends PHPUnit2_Framework_TestCase
    {
        public function testSayHello()
        {
            $hello = new HelloWorld();
            $this->assertEquals("Hello World!", $hello->sayHello());
        }
    }

?>
