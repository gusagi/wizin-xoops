<?php
/**
 * Wizin framework renderer class extends Smarty
 *
 * PHP Version 5.2 or Upper version
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Core_Renderer') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    if ( ! class_exists('Smarty') ) {
        require_once WIZIN_ROOT_PATH . '/lib/smarty/libs/Smarty.class.php';
    }
    /**
     * Wizin framework core renderer class
     *
     */
    class Wizin_Core_Renderer extends Smarty
    {
        /**
         * constructor
         */
        public function __construct()
        {
            // call parent constructor
            parent::__construct();
            // set variables
            $this->engine = 'smarty';
            if ( empty($this->compile_id) ) {
                $this->compile_id = 'wizin';
            }
            $this->left_delimiter = '<!--{';
            $this->right_delimiter = '}-->';
            $this->cache_dir = WIZIN_ROOT_PATH . '/work/cache';
            $this->compile_dir = WIZIN_ROOT_PATH . '/work/compile';
            $this->template_dir = WIZIN_ROOT_PATH . '/templates/';
            array_unshift( $this->plugins_dir , WIZIN_ROOT_PATH . '/plugins/smarty/' ) ;
            $this->register_prefilter( array($this, 'skipFilter') );
            // As for this filter necessity?
            //$this->register_prefilter( array($this, 'shortDelimFilter') );

            // set user object
            if ( ! isset($this->user) ) {
                $user =& Wizin_User::getSingleton();
                $this->user = $user;
            }
            // add suffix to $this->compile_id
            $this->compile_id .= '_' . $this->user->sCarrier;

            // if MstTemplate class exists, resource type is database.
            if ( ! class_exists('Propel') || ! class_exists('MstTemplate') ) {
                return true;
            }
            $this->default_resource_type = 'db';
            $this->register_resource(
                'db',
                array(
                    array(&$this, 'getSource'),
                    array(&$this, 'getTimestamp'),
                    array(&$this, 'getSecure'),
                    array(&$this, 'getTrusted')
                )
            );
        }

        /**
         * return MstTemplate class object
         *
         */
        protected function _getMstTemplate( $templateName )
        {
            static $mstTemplateArray;
            if ( ! isset($mstTemplateArray) ) {
                $mstTemplateArray = array();
            }
            if ( ! isset($mstTemplateArray[$templateName]) ) {
                // get MstTemplate class object
                $criteria = new Criteria();
                $criteria->add( MstTemplatePeer::TEMPLATE_NAME, $templateName );
                $criteria->addAnd( MstTemplatePeer::SUPPORT_CARRIER, '%|' .
                    $this->user->iCarrierId . '|%', Criteria::LIKE );
                $criteria->addAscendingOrderByColumn( Crio_Orm::strpos(MstTemplatePeer::SUPPORT_CARRIER,
                    '|' . $this->user->iCarrierId . '|') );
                $mstTemplate = MstTemplatePeer::doSelectOne( $criteria );
                if ( ! empty($mstTemplate) && is_object($mstTemplate) ) {
                    $mstTemplateArray[$templateName] = $mstTemplate;
                }
            }
            return $mstTemplateArray[$templateName];
        }

        /**
         * get template source
         *
         */
        public function getSource( $templateName, &$source, &$smarty )
        {
            // get MstTemplate object in this instance
            $mstTemplate = $this->_getMstTemplate( $templateName );
            if ( ! empty($mstTemplate) && is_object($mstTemplate) ) {
                $source = $mstTemplate->getSource();
                return $source;
            } else {
                return false;
            }
        }


        /**
         * get template update timestamp
         *
         */
        public function getTimestamp( $templateName, &$timestamp, &$smarty )
        {
            // get MstTemplate object in this instance
            $mstTemplate = $this->_getMstTemplate( $templateName );
            if ( ! empty($mstTemplate) && is_object($mstTemplate) ) {
                $updateAt = $mstTemplate->getUpdatedAt();
                $timestamp = strtotime( $updateAt );
                return $timestamp;
            } else {
                return false;
            }
        }

        /**
         * It checks whether template source is secure
         *
         */
        public function getSecure( $file, &$smarty )
        {
            return true;
        }

        /**
         * It checks whether template source is trusted
         *
         */
        public function getTrusted( $file, &$smarty )
        {
        }

        /**
         * clear compiled cache
         */
        public function clearCompiledCache( $compileDir = '' )
        {
            if ( $handler = opendir($this->compile_dir) ) {
                while ( ($file = readdir($handler)) !== false ) {
                    if ( $file === '.' || $file === '..' ) {
                        continue;
                    }
                    if ( substr($file, -4) === '.php' ) {
                        unlink( $this->compile_dir . '/' . $file );
                    }
                }
                closedir($handler);
            }
        }

        /**
         * clear "{skip}***{/skip}" string
         *
         */
        public function skipFilter( $tplSource, &$smarty )
        {
            $startTag = $this->left_delimiter . 'skip' . $this->right_delimiter;
            $endTag = $this->left_delimiter . '/skip' . $this->right_delimiter;
            $pattern = "($startTag)(.*?)($endTag)";
            $pattern = '/' . strtr($pattern, array('{' => '\{', '}' => '\}', '/' => '\/')) . '/is';
            $tplSource = preg_replace( $pattern, '', $tplSource );
            return $tplSource;
        }

        /**
         * replace short delimiters
         *
         */
        public function shortDelimFilter($tplSource, &$smarty )
        {
            $tplSource = str_replace( '<{', $this->left_delimiter, $tplSource );
            $tplSource = str_replace( '}>', $this->right_delimiter, $tplSource );
            return $tplSource;
        }

    }
}
