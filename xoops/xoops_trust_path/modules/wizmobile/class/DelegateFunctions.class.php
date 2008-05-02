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

require_once XOOPS_ROOT_PATH . "/core/XCube_Theme.class.php";

class LegacyWizMobileRender_DelegateFunctions
{
	/**
	 * Search themes that Legacy_RenderSystem can render in file system.
	 */
	function getInstalledThemes(&$results)
	{
		if ($handler = opendir(XOOPS_THEME_PATH)) {
			while (($dirname = readdir($handler)) !== false) {
				if ($dirname == "." || $dirname == "..") {
					continue;
				}

				$themeDir = XOOPS_THEME_PATH . "/" . $dirname;
				if (is_dir($themeDir)) {
				    if ( ! file_exists($themeDir . '/.legacy_wizmobilerendersystem') ) {
				        continue;
				    }

					$theme =& new XCube_Theme();
					$theme->mDirname = $dirname;

					if ($theme->loadManifesto($themeDir . "/manifesto.ini.php")) {
						if ($theme->mRenderSystemName == 'Legacy_WizMobileRenderSystem') {
							$results[] =& $theme;
						}
					}
					else {
						if (file_exists($themeDir . "/theme.html")) {
							$theme->mName = $dirname;
							$theme->mRenderSystemName = 'Legacy_WizMobileRenderSystem';
							$theme->mFormat = "XOOPS2 Legacy Style";
							$results[] =& $theme;
						}
					}

					unset($theme);
				}
			}
			closedir($handler);
		}
	}
}

?>