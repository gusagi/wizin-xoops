<?php
/**
 * PHP Versions 4
 *
 * @package  WizMobile
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 *
 */

if ( ! defined('XOOPS_ROOT_PATH') || ! defined('XOOPS_TRUST_PATH') ) {
    exit();
}

$modversion = array();
$modversion['name']        = 'WizMobile';
$modversion['version']     = '0.01';
$modversion['description'] = 'WizMobile';
$modversion['credits']     = 'Makoto Hashiguchi / gusagi';
$modversion['author']      = 'Makoto Hashiguchi / gusagi &lt;gusagi&#64;gusagi.com&gt;<br />url : http://www.gusagi.com';
$modversion['help']        = 'help.html';
$modversion['license']     = 'GPL see LICENSE';
$modversion['official']    = 0;
$modversion['image']       = file_exists( dirname($frontFile) .'/modicon.png' ) ? 'modicon.png' : 'modicon.php';
$modversion['dirname']     = basename( dirname($frontFile) );

//$modversion['sqlfile']['mysql'] = "sql/mysql.sql";
//$modversion['sqlfile']['postgresql'] = "sql/pgsql.sql";
//$modversion['tables'] = array(
//);

//$modversion['onInstall']   = 'index.php';
//$modversion['onUpdate']    = 'index.php';
//$modversion['onUninstall'] = 'index.php';

$modversion['use_smarty'] = 0;

/*
$modversion['templates'][] = array(
    'file' => 'AdminAction.tpl',
    'description' => ''
);
*/

//$modversion['blocks'][1] = array(
//  'file'          => 'ModSample0_block1.php' ,
//  'name'          => _MI_MODSAMPLE0_BLOCK_BLOCK1 ,
//  'description'   => _MI_MODSAMPLE0_BLOCK_BLOCK1_DESC ,
//  'show_func'     => 'ModSample0_block1_show' ,
//  'edit_func'     => 'ModSample0_block1_edit' ,
//  'template'      => 'ModSample0_block1.html' ,
//  'options'       => '10|20' ,
//  'can_clone'     => true
//) ;

$modversion['hasMain']   = 0;
$modversion['read_any']  = true;

//$modversion['sub'][1]=array(
//  'name' => _MI_MODSAMPLE0_SUBMENU1,
//  'url'  => "submenu1.php",
//);

$modversion['hasSearch'] = 0;

//$modversion['search'] = array(
//  'file' => "include/search.inc.php",
//  'func' => "ModSample0_search",
//);

$modversion['hasComments'] = 0;

//$modversion['comments'] = array(
//  'itemName' => 'event_id',
//  'pageName' => 'index.php',
//  'callbackFile' => 'include/comment_functions.php',
//  'callback' => array(
//      'approve' => 'ModSample0_comments_approve',
//      'update' => 'ModSample0_comments_update',
//  ),
//);

$modversion['hasAdmin'] = 0;
//$modversion['adminindex'] = "index.php";
//$modversion['adminmenu'] = "index.php";

$modversion['hasconfig'] = 0;
//$modversion['config'][1] = array(
//  'name'          => 'ModSample0_field1' ,
//  'title'         => '_MI_MODSAMPLE0_FIELD1' ,
//  'description'   => '_MI_MODSAMPLE0_FIELD1_DESC' ,
//  'formtype'      => 'textbox' ,
//  'valuetype'     => 'text' ,
//  'default'       => 'DEFAULT' ,
//  'options'       => array(),
//);

$modversion['hasNotification'] = 0;
//$modversion['notification']['lookup_file'] = 'include/notification.inc.php';
//$modversion['notification']['lookup_func'] = 'ModSample0_notify_iteminfo';

//$modversion['notification']['category'][1] = array(
//  'name' => 'global',
//  'title' => _MI_MODSAMPLE0_GLOBAL_NOTIFY,
//  'description' => _MI_MODSAMPLE0_GLOBAL_NOTIFYDSC,
//  'subscribe_from' => array(
//      'index.php',
//  ),
//  'item_name' => 'cid',
//  'allow_bookmark' => 0,
//);

//$modversion['notification']['event'][1] = array(
//  'name' => 'new_event',
//  'category' => 'global',
//  'title' => _MI_MODSAMPLE0_GLOBAL_NEWEVENT_NOTIFY,
//  'caption' => _MI_MODSAMPLE0_GLOBAL_NEWEVENT_NOTIFYCAP,
//  'description' => _MI_MODSAMPLE0_GLOBAL_NEWEVENT_NOTIFYDSC,
//  'mail_template' => 'global_newevent_notify',
//  'mail_subject' => _MI_MODSAMPLE0_GLOBAL_NEWEVENT_NOTIFYSBJ,
//);
