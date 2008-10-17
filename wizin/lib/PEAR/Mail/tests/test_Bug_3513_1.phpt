--TEST--
Bug #3513   support of RFC2231 in header fields. (ISO-8859-1)
--SKIPIF--
--FILE--
<?php
error_reporting(E_ALL);
$test = "F��b�r.txt";
require_once('Mail/mime.php');
$Mime=new Mail_Mime();
$Mime->_build_params['ignore-iconv'] = true;
$Mime->addAttachment('testfile',"text/plain", $test, FALSE, 'base64', 'attachment', 'ISO-8859-1');
$root = $Mime->_addMixedPart();
$enc = $Mime->_addAttachmentPart($root, $Mime->_parts[0]);
print($enc->_headers['Content-Disposition']);
--EXPECT--
attachment;
 filename*="ISO-8859-1''F%F3%F3b%E6r.txt";
