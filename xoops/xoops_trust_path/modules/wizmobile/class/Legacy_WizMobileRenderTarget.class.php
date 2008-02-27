<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

if ( !defined('LEGACY_RENDER_TARGET_TYPE_BUFFER') ) {
    include_once( XOOPS_ROOT_PATH . '/modules/legacyRender/kernel/Legacy_RenderTarget.class.php' );
}

if( ! class_exists( 'Legacy_WizMobileThemeRenderTarget' ) ) {
    class Legacy_WizMobileThemeRenderTarget extends Legacy_ThemeRenderTarget
    {
    	function sendHeader()
    	{
    		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    		header('Cache-Control: no-store, no-cache, must-revalidate');
    		header('Cache-Control: post-check=0, pre-check=0', false);
    		header('Pragma: no-cache');
    	}
    }
}

if( ! class_exists( 'Legacy_WizMobileDialogRenderTarget' ) ) {
    class Legacy_WizMobileDialogRenderTarget extends Legacy_DialogRenderTarget
    {
    	function sendHeader()
    	{
    		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    		header('Cache-Control: no-store, no-cache, must-revalidate');
    		header('Cache-Control: post-check=0, pre-check=0', false);
    		header('Pragma: no-cache');
    	}
    }
}
