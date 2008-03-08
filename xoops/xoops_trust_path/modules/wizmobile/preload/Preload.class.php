<?php
/**
 * WizMobile module preload script for XOOPS Cube Legacy2.1
 *
 * PHP Versions 4
 *
 * @package  WizMobile
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 *
 */

if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;

if ( ! class_exists('CallWizMobile') ) {
    class CallWizMobile extends XCube_ActionFilter
    {
        function preBlockFilter()
        {
            $initScript = dirname( dirname(__FILE__) ) . '/init.php';
            if ( file_exists($initScript) && is_readable($initScript) ) {
                require_once $initScript;
            }
            parent::preBlockFilter();
        }

        function postFilter()
        {
            $user = & Wizin_User::getSingleton();
            if ( $user->bIsMobile ) {
                $xcRoot =& XCube_Root::getSingleton();
                // exchange theme
                $wizMobile =& WizMobile::getSingleton();
                $wizMobile->exchangeTheme();
                // regenerate session id
                $xcRoot->mDelegateManager->add( 'Site.CheckLogin.Success', array($wizMobile, 'directLoginSuccess') );
                $xcRoot->mDelegateManager->add( 'Site.CheckLogin.Fail', array($wizMobile, 'directLoginFail') );
                $xcRoot->mDelegateManager->add( 'Site.Logout.Success', array($wizMobile, 'directLogout'), XCUBE_DELEGATE_PRIORITY_FINAL+1 );
                $xcRoot->mDelegateManager->add( 'Site.Logout.Fail', array($wizMobile, 'directLogout'), XCUBE_DELEGATE_PRIORITY_FINAL+1 );
                $xcRoot->mDelegateManager->add( 'Legacy_AdminControllerStrategy.SetupBlock', array($wizMobile, 'denyAccessAdminArea'), XCUBE_DELEGATE_PRIORITY_FIRST );
                // check session
                $wizMobile->checkMobileSession();
                // insert session_id
                ob_start( array($wizMobile, '_obTransSid') );
                ob_start( array($wizMobile, '_obDirectRedirect') );
            }
            parent::postFilter();
        }
    }
}

$mod_dir = basename( dirname(dirname($frontFile)) );
preg_match( "/(\w+)\.class\.php/", basename($frontFile), $matches );
$className = ucfirst($mod_dir) . "_" . $matches[1];
if ( ! class_exists($className) ) {
    eval( "class $className extends CallWizMobile {}" );
}
