<?php
/**
 * PHP Versions 4.4.X or upper version
 *
 * @package  WizMobile
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license GNU General Public License Version2
 *
 */

/**
 * GNU General Public License Version2
 *
 * Copyright (C) 2008  < Makoto Hashiguchi a.k.a. gusagi >
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

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
            $wizMobile =& WizMobile::getSingleton();
            // display block
            $wizMobileAction =& $wizMobile->getActionClass();
            $nondisplayBlocks = $wizMobileAction->getNondisplayBlocks();
            $legacy_BlockContents =& $root->mContext->mAttributes['legacy_BlockContents'];
            $blockFlagMap = array( 'xoops_showlblock', 'xoops_showcblock', 'xoops_showcblock',
                'xoops_showcblock', 'xoops_showrblock' );
            if ( ! empty($legacy_BlockContents) ) {
                foreach ( $legacy_BlockContents as $index => $blockArea ) {
                    foreach ( $blockArea as $key => $block ) {
                        $blockId = intval( $block['id'] );
                        if ( ! in_array($blockId, $nondisplayBlocks) ) {
                            if ( ! empty($_REQUEST['mobilebid']) && intval($_REQUEST['mobilebid']) === $blockId ) {
                                $this->mXoopsTpl->assign( 'wizMobileBlockContents', $block['content'] );
                            }
                        } else {
                            unset( $root->mContext->mAttributes['legacy_BlockContents'][$index][$key] );
                        }
                    }
                    if ( count($root->mContext->mAttributes['legacy_BlockContents'][$index]) === 0 ) {
                        $root->mContext->mAttributes['legacy_BlockShowFlags'][$index] = false;
                    }
                }
            }
            // display sub menu
            $subMenuContents = '';
            if ( function_exists('b_legacy_mainmenu_show') ) {
                $xoopsModule =& $root->mContext->mXoopsModule;
                if ( isset($xoopsModule) && is_object($xoopsModule) ) {
                    if ( $xoopsModule->getVar('hasmain') == 1 && $xoopsModule->getVar('weight') > 0 ) {
                        $dirname = $xoopsModule->getVar( 'dirname' );
                        $modname = $xoopsModule->getVar( 'name' );
                        $subMenuContents .= '<a href="' . XOOPS_URL . '/modules/' . htmlspecialchars( $dirname, ENT_QUOTES ) .
                            '/">[' . htmlspecialchars( $modname, ENT_QUOTES ) . ']</a>&nbsp;';
                        $subLinks = $xoopsModule->subLink();
                        foreach ( $subLinks as $index => $subLink ) {
                            if ( $index !== 0 ) {
                                $subMenuContents .= "&nbsp;/&nbsp;";
                            }
                            $subMenuContents .= '<a href="' . $subLink['url'] . '">' . $subLink['name'] . '</a>';
                        }
                        $this->mXoopsTpl->assign( 'wizMobileSubMenuContents', $subMenuContents );
                    }
                }
            }
            parent::renderTheme( $target );
        }

    }
}
