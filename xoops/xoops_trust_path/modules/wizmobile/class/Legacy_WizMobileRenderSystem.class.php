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

        function renderTheme(&$target)
        {
            $root =& XCube_Root::getSingleton();
            $legacy_BlockContents =& $root->mContext->mAttributes['legacy_BlockContents'];
            if ( ! empty($_REQUEST['mobilebid']) && ! empty($legacy_BlockContents) ) {
                foreach ( $legacy_BlockContents as $index => $blockArea ) {
                    foreach ( $blockArea as $key => $block ) {
                        $blockId = intval( $block['id'] );
                        if ( intval($_REQUEST['mobilebid']) === $blockId ) {
                            $this->mXoopsTpl->assign( 'wizMobileBlockContents', $block['content'] );
                        }
                    }
                }
            }
            parent::renderTheme( $target );
        }
    }
}
