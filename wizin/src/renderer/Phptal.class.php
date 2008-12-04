<?php
/**
 * Wizin framework renderer class extends PHPTAL
 *
 * PHP Version 5.2 or Upper version
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Renderer') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    if ( ! class_exists('PHPTAL') ) {
        if ( ! defined('PHPTAL_PHP_CODE_DESTINATION') ) {
            define( 'PHPTAL_PHP_CODE_DESTINATION', WIZIN_COMPILE_DIR );
        }
        require_once WIZIN_ROOT_PATH . '/lib/phptal/PHPTAL.php';
    }
    /**
     * Wizin framework renderer class extends PHPTAL
     *
     */
    class Wizin_Renderer extends PHPTAL
    {
        public $template_dir;

        public function __construct( $path = false )
        {
            parent::__construct( $path );
            if ( ! isset($this->template_dir) ) {
                $this->template_dir = WIZIN_ROOT_PATH . '/templates/';
            }
            $this->setTemplateRepository( $this->template_dir );
        }

        public function assign( $key = '', $value = '' )
        {
            if ( ! is_null($key) && $key !== '' ) {
                $this->__set( $key, $value );
            }
        }

        public function template_exists( $templateName )
        {
            $this->setTemplate( $templateName );
            try {
                $this->findTemplate();
                return true;
            } catch ( Exception $e ) {
                return false;
            }
        }

        public function fetch( $templateName )
        {
            $this->setTemplate( $templateName );
            return $this->execute();
        }

        public function display( $templateName )
        {
            echo $this->fetch( $templateName );
        }
    }
}
