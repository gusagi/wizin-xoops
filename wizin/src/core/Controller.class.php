<?php
/**
 * Wizin framework core controller class
 *
 * PHP Version 5.2 or Upper version
 *
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @link http://www.gusagi.com/
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
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
        public function __construct()
        {
        }

        public function setView( & $viewObject )
        {
            $this->_oView = new Wizin_Ref( $viewObject );
        }

        protected function _setVar( $tplVar, $value = null, $escape = true )
        {
            $this->_oView->setVar( $tplVar, $value, $escape );
        }

        public function execute()
        {
            // execute main process
            $this->_main();
            // execute view
            $this->_executeView();
        }

        protected function _main()
        {
        }

        protected function _executeView()
        {
            $this->_oView->execute();
        }

    }
}
