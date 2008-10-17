--TEST--
Bug #30     Mail_Mime: _encodeHeaders is not RFC-2047 compliant. (ISO-8859-1, quoted-printable)
--SKIPIF--
--FILE--
<?php
error_reporting(E_ALL); // ignore E_STRICT
include("Mail/mime.php");
include("Mail/mimeDecode.php");
$encoder = new Mail_mime();
$encoder->_build_params['ignore-iconv'] = true;

$input[] = "Just a simple test";
$input[] = "_this=?Q?U:I:T:E_a_test?=";
$input[] = "_=?S�per?=_";
$input[] = "_ = ? S�per ? = _";
$input[] = "S�per gr�se tolle gr��e?! Fur mir!?";
$input[] = "S�per = gr�se tolle gr��e von mir";
$input[] = "TEST  S�per gr�se tolle gr��e von mir S�per gr�se tolle gr��e von mir S�per gr�se tolle gr��e von mir!!!?";
$input[] = '"German Umlauts ���"';



$encoded = $encoder->_encodeHeaders($input, array('head_encoding' => 'quoted-printable'));
print_r($encoded);
--EXPECT--
Array
(
    [0] => Just a simple test
    [1] => _this=?Q?U:I:T:E_a_test?=
    [2] => =?ISO-8859-1?Q?=5F=3D=3FS=FCper=3F=3D=5F?=
    [3] => =?ISO-8859-1?Q?=5F_=3D_=3F_S=FCper_=3F_=3D_=5F?=
    [4] => =?ISO-8859-1?Q?S=FCper_gr=F6se_tolle_gr=FC=DFe=3F!_Fur_mir!=3F?=
    [5] => =?ISO-8859-1?Q?S=FCper_=3D_gr=F6se_tolle_gr=FC=DFe_von_mir?=
    [6] => =?ISO-8859-1?Q?TEST__S=FCper_gr=F6se_tolle_gr=FC=DFe_von_mir_S=FCper_?=
 =?ISO-8859-1?Q?gr=F6se_tolle_gr=FC=DFe_von_mir_S=FCper_gr=F6se_tolle_gr?=
 =?ISO-8859-1?Q?=FC=DFe_von_mir!!!=3F?=
    [7] => =?ISO-8859-1?Q?"German_Umlauts_=F6=E4=FC"?=
)
