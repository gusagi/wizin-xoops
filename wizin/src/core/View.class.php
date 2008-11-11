<?php
/**
 * Wizin framework core view class
 *
 * PHP Version 5.2 or Upper version
 *
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @link http://www.gusagi.com/
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 *
 */

if ( ! class_exists('Wizin_Core_View') ) {
	require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
	/**
	 * Wizin framework core view class
	 *
	 */
	class Wizin_Core_View extends Wizin_StdClass
	{
		protected $_renderer = 'Wizin_Core_Renderer';

		public function __construct()
		{
			// set default output filter
			$this->_defaultFilter();
			// set contents strings
			$this->sContents = '';
		}

		protected function _defaultFilter()
		{
			$app =& call_user_func( array(WIZIN_DEFAULT_APP, 'getSingleton') );
            $params = array( $app->oUser->sEncoding, $app->oUser->sCharset );
            $app->oFilter->addOutputFilter(
            	array($app->oFilter, 'filterOutputEncoding'), $params );
		}

		public function execute()
		{
			// get page contents
			$contents = $this->_getPageContents();
			// get app contents
			$contents = $this->_getAppContents();
			// execute output filter
			$contents = $this->_executeFilter();
			// display all contents
			$this->_display();
		}

		protected function _getPageContents()
		{
			$app =& call_user_func( array(WIZIN_DEFAULT_APP, 'getSingleton') );
			$renderer = new $this->_renderer();
			$templateName = $app->sPathTranslated . '.html';
			$templateExists = $renderer->template_exists( $templateName );
			$this->sContents = ob_get_clean();
			if ( $templateExists ) {
				$this->sContents = $renderer->fetch( $templateName );
			}
			unset( $renderer );
		}

		protected function _getAppContents()
		{
			$renderer = new $this->_renderer();
			$this->_defaultAssign( $renderer );
			$this->sContents = $renderer->fetch( 'file:' . $renderer->template_dir . 'Layout.html' );
			unset( $renderer );
		}

		protected function _defaultAssign( & $renderer )
		{
			$app =& call_user_func( array(WIZIN_DEFAULT_APP, 'getSingleton') );
			$renderer->assign( 'siteTitle', 'Wizin initial template.' );
			$renderer->assign( 'doctype', $app->oUser->sDoctype, false );
			$renderer->assign( 'extraHeader', $this->sExtraHeader, false );
			$renderer->assign( 'pageContents', $this->sContents, false );
		}

		protected function _executeFilter()
		{
			$app =& call_user_func( array(WIZIN_DEFAULT_APP, 'getSingleton') );
			$app->oFilter->executeOutputFilter( $this->sContents );
		}

		protected function _display()
		{
			$app =& call_user_func( array(WIZIN_DEFAULT_APP, 'getSingleton') );
			if ( $app->oUser->bIsMobile ) {
				$contentType = 'application/xhtml+xml';
			} else {
				$contentType = 'text/html';
			}
			header( 'Content-Type:' . $contentType . '; charset=' . $app->oUser->sCharset );
			header( 'Content-Length: ' . strlen($this->sContents) );
			echo $this->sContents;
			unset( $this->sContents );
		}

	}
}
