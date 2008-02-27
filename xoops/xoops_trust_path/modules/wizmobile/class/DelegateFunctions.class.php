<?php
/**
 * @package legacyMobileRender
 * @version $Id: DelegateFunctions.class.php,v 1.1 2008/02/23 02:35:07 gusagi Exp $
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