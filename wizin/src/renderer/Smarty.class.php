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

if ( ! class_exists('Wizin_Renderer') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    if ( ! class_exists('Smarty') ) {
        require_once WIZIN_ROOT_PATH . '/lib/smarty/libs/Smarty.class.php';
    }
    /**
     * Wizin framework renderer class extends Smarty
     *
     */
    class Wizin_Renderer extends Smarty
    {
        /**
         * framework user class object
         *
         * @var object
         */
        protected $_oUser;

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
            $this->cache_dir = WIZIN_CACHE_DIR;
            $this->compile_dir = WIZIN_COMPILE_DIR;
            $this->template_dir = WIZIN_ROOT_PATH . '/templates/';
            array_unshift( $this->plugins_dir , WIZIN_ROOT_PATH . '/plugins/smarty/' ) ;
            $this->register_prefilter( array($this, 'skipFilter') );
            // As for this filter necessity?
            //$this->register_prefilter( array($this, 'shortDelimFilter') );

            // set user object
            if ( ! isset($this->_oUser) ) {
                $user =& Wizin_User::getSingleton();
                $this->_oUser = $user;
            }
            // add suffix to $this->compile_id
            $this->compile_id .= '_' . $this->_oUser->sCarrier;

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
                    $this->_oUser->iCarrierId . '|%', Criteria::LIKE );
                $criteria->addAscendingOrderByColumn( Crio_Orm::strpos(MstTemplatePeer::SUPPORT_CARRIER,
                    '|' . $this->_oUser->iCarrierId . '|') );
                $mstTemplate = MstTemplatePeer::doSelectOne( $criteria );
                if ( ! empty($mstTemplate) && is_object($mstTemplate) ) {
                    $mstTemplateArray[$templateName] = $mstTemplate;
                } else {
                    $mstTemplateArray[$templateName] = null;
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
        public function shortDelimFilter( $tplSource, &$smarty )
        {
            $tplSource = str_replace( '<{', $this->left_delimiter, $tplSource );
            $tplSource = str_replace( '}>', $this->right_delimiter, $tplSource );
            return $tplSource;
        }


        /**
         * assigns values to template variables
         *
         * @param array|string $tpl_var the template variable name(s)
         * @param mixed $value the value to assign
         * @param boolean $escape escape flag
         */
        public function assign( $tplVar, $value = null, $escape = true )
        {
            if ( $escape ) {
                $value = $this->_escape( $value );
            }
            parent::assign( $tplVar, $value );
        }

        /**
         * escape assigned values
         *
         */
        protected function _escape( $value = null )
        {
            if ( ! is_null($value) ) {
                if ( is_array($value) ) {
                    $value = array_map( array($this, '_escape'), $value );
                } else if ( is_string($value) ) {
                    $value = htmlspecialchars( $value, ENT_QUOTES );
                }
            }
            return $value;
        }
    }
}
