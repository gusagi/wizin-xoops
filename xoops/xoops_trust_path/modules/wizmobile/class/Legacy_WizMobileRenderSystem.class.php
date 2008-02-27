<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

if ( !defined('LEGACY_RENDERSYSTEM_BANNERSETUP_BEFORE') ) {
    include_once( XOOPS_ROOT_PATH . '/modules/legacyRender/kernel/Legacy_RenderSystem.class.php' );
}
include_once( XOOPS_TRUST_PATH . '/modules/wizmobile/class/Legacy_WizMobileRenderTarget.class.php' );

if( ! class_exists( 'Legacy_WizMobileRenderSystem' ) ) {
    class Legacy_WizMobileRenderSystem extends Legacy_RenderSystem
    {
    	/**
    	 * @deprecated
    	 */
    	function sendHeader()
    	{
    		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    		header('Cache-Control: no-store, no-cache, must-revalidate');
    		header('Cache-Control: post-check=0, pre-check=0', false);
    		header('Pragma: no-cache');
    	}

    	/**
    	 * @TODO This function is not cool!
    	 */
    	function &getThemeRenderTarget($isDialog = false)
    	{
    		$screenTarget = $isDialog ? new Legacy_WizMobileDialogRenderTarget() : new Legacy_WizMobileThemeRenderTarget();
    		return $screenTarget;
    	}

        function renderBlock(&$target)
        {
            $this->_commonPrepareRender();

            if (isset($GLOBALS['xoopsUserIsAdmin'])) {
                $this->mXoopsTpl->assign('xoops_isadmin', $GLOBALS['xoopsUserIsAdmin']);
            }

            //
            // Temporary
            //
            $this->mXoopsTpl->xoops_setCaching(0);

            foreach($target->getAttributes() as $key=>$value) {
                $this->mXoopsTpl->assign( $key,$value );
            }

            $targetBid = intval( $target->getAttribute("bid") );
            if ( ! empty($_REQUEST['mobilebid']) && intval($_REQUEST['mobilebid']) === $targetBid ) {
                $wizMobileBlockContents =& $this->mXoopsTpl->fetchBlock( $target->getTemplateName(), $target->getAttribute("bid") );
                $this->mXoopsTpl->assign( 'wizMobileBlockContents', $wizMobileBlockContents );
            }

            //
            // Reset
            //
            foreach($target->getAttributes() as $key=>$value) {
                $this->mXoopsTpl->clear_assign($key);
            }

            parent::renderBlock( $target );
        }
    }
}
