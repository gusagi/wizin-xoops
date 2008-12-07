<?php
require_once("../../Rhaco.php");
Rhaco::import("setup.util.PotGenerator");
Rhaco::import("setup.util.SetupUtil");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
Rhaco::constant("CONTEXT_PATH",dirname(dirname(dirname(__FILE__)))."/");
$flow = new PotGenerator();
$flow->requestAttach("_at",SetupUtil::template());
$flow->setVariable("generalmenu",array("-"=>"#"));			
$flow->setVariable("mainmenu",array("-"=>"#"));
$flow->setVariable("submenu",array());
$flow->setVariable("projectname","po of rhaco");
$flow->setVariable("projectdesc","");
$flow->setVariable("projectver",Rhaco::rhacoversion());
$flow->write();
?>