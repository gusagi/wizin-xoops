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
                $xcRoot->mDelegateManager->add( 'Site.CheckLogin.Success', array($wizMobile, 'sessionRegenerateId') );
                $xcRoot->mDelegateManager->add( 'Site.Logout', array($wizMobile, 'sessionRegenerateId'), XCUBE_DELEGATE_PRIORITY_FIRST );
                $xcRoot->mDelegateManager->add( 'Site.Logout.Success', array($wizMobile, 'directLogout'), XCUBE_DELEGATE_PRIORITY_FINAL+1 );
                $xcRoot->mDelegateManager->add( 'Legacy_AdminControllerStrategy.SetupBlock', array($wizMobile, 'denyAccessAdminArea'), XCUBE_DELEGATE_PRIORITY_FIRST );
                // check session
                $wizMobile->checkMobileSession();
                // insert session_id
                ob_start( array($wizMobile, '_obTransSid') );
                // get block contents
                $theme =& $xcRoot->mController->_mStrategy->getMainThemeObject();
                if (!is_object($theme)) {
                    die("Could not found any themes.");
                }
                $xcRoot->mContext->mBaseRenderSystemName = $theme->get('render_system');

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
