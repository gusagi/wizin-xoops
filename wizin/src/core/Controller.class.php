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
    /**
     * Controller class
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
        public function execute( $output )
        {
            $this->_addInputFilter();
            $this->_executeInputFilter();
            if ($output) {
                $this->_addOutputFilter();
                $contents = $this->_executeOutputFilter();
                $this->_display( $contents );
            }
        }

        /**
         * Add input filter functions
         */
        protected function _addInputFilter()
        {
            $user = & Wizin_User::getSingleton();
            $filter =& Wizin_Filter::getSingleton();
            $params = array( $user->sEncoding );
            $filter->addInputFilter( array( $filter, 'filterInputEncoding' ), $params );
            $filter->executeInputFilter();
        }

        /**
         * Execute input filter functions
         */
        protected function _executeInputFilter()
        {
            $filter =& Wizin_Filter::getSingleton();
            $filter->executeInputFilter();
        }

        /**
         * Add output filter functions
         */
        protected function _addOutputFilter()
        {
            $user = & Wizin_User::getSingleton();
            $filter =& Wizin_Filter::getSingleton();
            $params = array( $user->sEncoding, $user->sCharset );
            $filter->addOutputFilter( array($filter, 'filterOutputEncoding'), $params );
        }

        /**
         * Execute output filter functions
         */
        protected function _executeOutputFilter()
        {
            $user = & Wizin_User::getSingleton();
            $filter =& Wizin_Filter::getSingleton();
            $siteTpl = new Wizin_Core_Renderer();
            $pageContents = ob_get_clean();
            // insert $pageContents into site.tpl
            $siteTpl->assign( 'doctype', $user->sDoctype );
            $siteTpl->assign( 'pageContents', $pageContents );
            $contents = $siteTpl->fetch( 'site.tpl' );
            $filter->executeOutputFilter( $contents );
            return $contents;
        }

        /**
         * Display contents
         */
        protected function _display( $contents )
        {
            $user = & Wizin_User::getSingleton();
            if ( $user->bIsMobile ) {
                $contentType = 'application/xhtml+xml';
            } else {
                $contentType = 'text/html';
            }
            header( 'Content-Type:' . $contentType . '; charset=' . $user->sCharset );
            header( 'Content-Length: ' . strlen($contents) );
            echo $contents;
        }

    }
}
