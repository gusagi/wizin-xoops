<?php
/**
 * Wizin framework renderer class extends Smarty
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Core_Renderer') ) {
    if ( ! class_exists('Smarty') ) {
        require_once WIZIN_ROOT_PATH . '/lib/Smarty/libs/Smarty.class.php';
    }
    class Wizin_Core_Renderer extends Smarty
    {
        public function __construct()
        {
            $this->engine = 'smarty';
            $this->compile_id = 'Wizin';
            $this->left_delimiter = '<{';
            $this->right_delimiter = '}>';
            $this->cache_dir = WIZIN_ROOT_PATH . '/work/cache/';
            $this->compile_dir = WIZIN_ROOT_PATH . '/work/compile/';
            $this->template_dir = WIZIN_ROOT_PATH . '/templates/';
            array_unshift( $this->plugins_dir , WIZIN_ROOT_PATH . '/plugins/smarty/' ) ;
            if ( defined(WIZ_DEBUG_FLAG) ) {
                $this->force_compile = true;
            }
        }
    }
}
