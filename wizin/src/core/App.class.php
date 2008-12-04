<?php
/**
 * Wizin framework core application class
 *
 * PHP Version 5.2 or Upper version
 *
 * @package  giftbox.in
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Core_App') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    /**
     * Wizin framework core controller class
     *
     */
    class Wizin_Core_App extends Wizin_StdClass
    {
        /**
         * Return 'Wizin_Core_App' singleton object
         */
        public function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_Core_App();
            }
            return $instance;
        }

        /**
         * System total control function.
         *
         */
        public function execute()
        {
            // set member objects
            $this->_setObjects();
            // check client
            $lookup = ( defined('WIZ_SYS_LOOKUP') ) ? WIZ_SYS_LOOKUP : false;
            $this->_checkClient( $lookup );
            /* TODO : refine MVC design
             *      : write input filter in request class
             */
            // input filter
            //$this->_addInputFilter();
            //$this->_executeInputFilter();

            // execute common action
            $this->_executeCommon();
            // execute controller process
            $this->oController->execute();
        }

        /**
         * set member objects
         *
         */
        protected function _setObjects()
        {
            // set extra headers
            $this->sExtraHeader = '';
            // set user class object
            $user =& Wizin_User::getSingleton();
            $this->oUser = new Wizin_Ref( $user );
            // set filter class object
            $filter =& Wizin_Filter::getSingleton();
            $this->oFilter = new Wizin_Ref( $filter );
        }

        /**
         * check user by client
         *
         */
        protected function _checkClient( $lookup = false )
        {
            // check client
            $this->oUser->checkClient( $lookup );
        }

        /**
         * Add input filter functions
         */
        protected function _addInputFilter()
        {
            $params = array( $this->oUser->sEncoding );
            $this->oFilter->addInputFilter( array( $this->oFilter, 'filterInputEncoding' ), $params );
        }

        /**
         * Execute input filter functions
         */
        protected function _executeInputFilter()
        {
            $this->oFilter->executeInputFilter();
        }

        /**
         * Execute common action
         */
        protected function _executeCommon()
        {
            //
            // analyze action from 'PATH_INFO
            //
            $pathInfo = getenv( 'PATH_INFO' );
            if ( ! empty($pathInfo) ) {
                if ( strpos($pathInfo, '.') !== false ) {
                    $pathInfoArray = explode( '.', $pathInfo );
                    array_pop( $pathInfoArray );
                    $pathInfo = implode( '.', $pathInfoArray );
                }
            } else {
                $pathInfo = '/';
            }
            $this->sPathInfo = $pathInfo;
            $this->sPathTranslated = $this->_getPathTranslated( $pathInfo );
            $this->_dispatch( $this->sPathTranslated );
        }

        /**
         * return translated path
         *
         * @param string $pathInfo
         * @return string
         */
        protected function _getPathTranslated( $pathInfo = '/' )
        {
            $pathTranslated = $pathInfo;
            // if last character is '/', add default action
            if ( substr($pathTranslated, -1, 1) === '/' ) {
                $pathTranslated .= 'index';
            }
            $pathTranslated = substr( dirname($pathTranslated), 1 ) . '/' .
                ucfirst( basename($pathTranslated) );
            if ( substr($pathTranslated, 0, 1) === '/' ) {
                $pathTranslated = substr( $pathTranslated, 1 );
            }
            return $pathTranslated;
        }

        protected function _dispatch( $pathTranslated )
        {
            $pathArray = explode( '/', $pathTranslated );
            $pathArray = array_map( 'ucfirst', $pathArray );
            // controller
            $controllerClass = WIZIN_DEFAULT_CONTROLLER;
            $controllerPath = WIZ_SITE_ROOT . '/controllers/' . $pathTranslated . '.php';
            if ( file_exists($controllerPath) ) {
                require $controllerPath;
                $class = WIZ_SYS_PREFIX . '_' . implode( '_', $pathArray ) . '_Controller';
                if ( class_exists($class) ) {
                    $controllerClass = $class;
                }
            }
            $controller = new $controllerClass();
            $this->oController = new Wizin_Ref( $controller );
            // view
            $viewClass = WIZIN_DEFAULT_VIEW;
            $viewPath = WIZ_SITE_ROOT . '/views/' . $pathTranslated . '.php';
            if ( file_exists($viewPath) ) {
                require $viewPath;
                $class = WIZ_SYS_PREFIX . '_' . implode( '_', $pathArray ) . '_View';
                if ( class_exists($class) ) {
                    $viewClass = $class;
                }
            }
            $view = new $viewClass();
            $this->oController->setView( $view );
        }

    }
}
