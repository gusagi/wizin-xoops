/**************************************************
 * 【 モジュール名 】WizMobile
 * 【  バージョン  】0.1.3
 * 【   権 利 者   】gusagi
 * 【   作 成 者   】gusagi
 * 【  ライセンス  】GNU General Public License Version 2(GPL2)
 * 【 ホームページ 】http://www.gusagi.com/
 * 【メールアドレス】gusagi@gusagi.com
 * 【   動作環境   】XOOPS Cube Legacy 2.1.3以降
**************************************************/

【はじめに】
なお、このモジュールを使用することで何らかの問題が発生した場合、
開発者は責任を負いかねます。
申し訳ありませんが、使用に関しては自己責任ということでお願い致します。


【概要】
モジュールをインストールするだけで、XOOPS Cube Legacyで構築したサイトを
携帯でも利用可能にするモジュールです。
現時点では、日本の携帯キャリア（Docomo、AU、SoftBank）に対応しています。


【機能】
以下の機能を実装しています。
----
・XOOPS Cube Legacyで構築したサイトを携帯でも利用可能
・日本の携帯キャリアごとに、出力するエンコーディングを変換
・ブロックを含め、PCとほぼ同じ内容を携帯でも操作可能（Java Scriptは対象外）


【導入方法】
XOOPS_ROOT_PATH側にhtml配下を、XOOPS_TRUST_PATH側にxoops_trust_path配下を
アップロードして下さい。
アップロード後は、XOOPS Cubeの管理画面からモジュールインストールを実行して下さい。
モジュールの管理画面は、現時点では存在しません。
インストールが完了した時点で導入完了となります。


【追加設定】
(1) host名によるチェックを有効にしたい場合
XOOPS_ROOT_PATH/settings/site_custom.ini.phpに
----
[Mobile]
lookup = false
----
と記入して下さい。
XOOPS_ROOT_PATH/settings/site_custom.ini.phpが存在しない場合は、作成して下さい。

(2) 携帯用のテーマを変更したい場合
XOOPS_ROOT_PATH/settings/site_custom.ini.phpに
----
[Mobile]
theme = 変更したいテーマ名
----
と記入して下さい。
XOOPS_ROOT_PATH/settings/site_custom.ini.phpが存在しない場合は、作成して下さい。
また、携帯用テーマ内の構成、manifesto.ini.phpは、mobileと同じ構成にして下さい。
（site_custom.ini.phpに上記を記入の上で、mobileをリネームした方が簡単ですが・・・）

(3) WillcomのAdvanced/W-ZERO3[es]なども携帯として扱いたい場合
XOOPS_ROOT_PATH/settings/site_custom.ini.phpに
----
[Mobile]
othermobile = true
----
と記入して下さい。
XOOPS_ROOT_PATH/settings/site_custom.ini.phpが存在しない場合は、作成して下さい。


【注意事項】
本モジュール自体のライセンスはGPL2ですが、本モジュールが依存する
フレームワーク"Wizin"は、Creative Commons(表示 - 非営利 - 継承)となります。
フレームワークの使用許諾条件を満たせない場合、フレームワーク部分については
使用を制限させて頂く場合がありますので、ご注意下さい。
Creative Commonsの使用許諾条件は、下記URLをご確認下さい。
  http://creativecommons.org/licenses/by-nc-sa/2.1/jp/
  http://creativecommons.org/licenses/by-nc-sa/2.1/jp/legalcode


【今後の予定】
・簡単ログイン
・画像のリサイズ
・絵文字対応


【謝辞】
・本モジュールのアイコンは、Argon氏が作成して下さいました。
　本当にありがとうございます。


--------------

2008年 2月 26日 作成
