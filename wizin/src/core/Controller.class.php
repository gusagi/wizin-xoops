<?php
/**
 * Wizin framework core controller class
 *
 * PHP Version 5.2 or Upper version
 *
 * @package  giftbox.in
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Core_Controller') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    /**
     * Wizin framework core controller class
     *
     */
    class Wizin_Core_Controller extends Wizin_StdClass
    {
        /**
         * Return 'Wizin_Core_Controller' singleton object
         */
        public function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_Core_Controller();
            }
            return $instance;
        }

        /**
         * System total control function.
         *
         */
        public function execute()
        {
            // set member variables
            $this->_setVariables();
            // input filter
            $this->_addInputFilter();
            $this->_executeInputFilter();
            // execute common action
            $this->_executeCommon();
            // execute page action
            $this->_executePage();
            // output filter
            $this->_addOutputFilter();
            $contents = $this->_executeOutputFilter();
            // display
            $this->_display( $contents );
        }

        /**
         * set member variables
         *
         */
        protected function _setVariables()
        {
            // set user class object
            $user =& Wizin_User::getSingleton();
            $this->user = $user;
            // check client
            $this->_checkUser();
            // set filter class object
            $filter =& Wizin_Filter::getSingleton();
            $this->filter = $filter;
            // set renderer class object
            $this->siteRenderer = new Wizin_Core_Renderer();
        }

        /**
         * check user by client
         *
         */
        protected function _checkUser()
        {
            // check client
            $this->user->checkClient();
        }

        /**
         * Add input filter functions
         */
        protected function _addInputFilter()
        {
            $params = array( $this->user->sEncoding );
            $this->filter->addInputFilter( array( $this->filter, 'filterInputEncoding' ), $params );
            $this->filter->executeInputFilter();
        }

        /**
         * Execute input filter functions
         */
        protected function _executeInputFilter()
        {
            $this->filter->executeInputFilter();
        }

        /**
         * Add output filter functions
         */
        protected function _addOutputFilter()
        {
            $params = array( $this->user->sEncoding, $this->user->sCharset );
            $this->filter->addOutputFilter( array($this->filter, 'filterOutputEncoding'), $params );
        }

        /**
         * Execute output filter functions
         */
        protected function _executeOutputFilter()
        {
            $pageContents = ob_get_clean();
            $this->siteRenderer->assign( 'siteUrl', 'http://' . getenv('SERVER_NAME') );
            $this->siteRenderer->assign( 'siteTitle', 'Wizin initial template.' );
            $this->siteRenderer->assign( 'doctype', $this->user->sDoctype );
            $this->siteRenderer->assign( 'pageContents', $pageContents );
            $contents = $this->siteRenderer->fetch(
                'file:' . $this->siteRenderer->template_dir . 'site.html' );
            $this->filter->executeOutputFilter( $contents );
            return $contents;
        }

        /**
         * Display contents
         */
        protected function _display( $contents )
        {
            if ( $this->user->bIsMobile ) {
                $contentType = 'application/xhtml+xml';
            } else {
                $contentType = 'text/html';
            }
            header( 'Content-Type:' . $contentType . '; charset=' . $this->user->sCharset );
            header( 'Content-Length: ' . strlen($contents) );
            echo $contents;
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
        }

        /**
         * return translated path
         *
         * @param string $pathInfo
         * @return stromg
         */
        protected function _getPathTranslated( $pathInfo = '/' )
        {
            $pathTranslated = $pathInfo;
            // if last character is '/', add default action
            if ( substr($pathTranslated, -1, 1) === '/' ) {
                $pathTranslated .= 'index';
            }
            return $pathTranslated;
        }

        /**
         * execute page action
         */
        protected function _executePage()
        {
            $isAccessible = $this->_isAccessible();
            $pageRenderer = clone $this->siteRenderer;
            // the user can access this page
            if ( $isAccessible ) {
                $template = $this->sPathTranslated . '.html';
                $templateExists = $this->siteRenderer->template_exists( $template );
                $script = WIZ_SITE_ROOT . '/pages' . $this->sPathTranslated . '.php';
                $scriptExists = ( file_exists($script) && is_readable($script) ) ? true : false;
                if ( $templateExists || $scriptExists ) {
                    // execute page process
                    if ( $scriptExists ) {
                        require $script;
                        $pathArray = explode( '/', $this->sPathTranslated );
                        $pathArray = array_map( 'ucfirst', $pathArray );
                        $class = WIZ_SYS_PREFIX . '_Page' . implode( '_', $pathArray );
                        if ( class_exists($class) ) {
                            $page = new $class( $pageRenderer );
                        }
                    }
                    // display template
                    if ( $templateExists ) {
                        $pageRenderer->display( $template );
                    }
                } else {
                    // display error page
                    $pageRenderer->display( '/error.html' );
                }
            } else {
                // display error page
                $pageRenderer->display( '/error.html' );
            }
        }

        /**
         * is the user can access this page
         *
         */
        protected function _isAccessible()
        {
            return true;
        }

    }
}
