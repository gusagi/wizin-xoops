/**************************************************
 * 【 モジュール名 】WizMobile
 * 【  バージョン  】0.23
 * 【   権 利 者   】Makoto Hashiguchi a.k.a. gusagi
 * 【   作 成 者   】Makoto Hashiguchi a.k.a. gusagi
 * 【  ライセンス  】GNU General Public License Version2 with the special exception
 * 【 ホームページ 】http://www.gusagi.com/
 * 【メールアドレス】gusagi@gusagi.com
 * 【   動作環境   】XOOPS Cube Legacy 2.1.4以降
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


【必須設定】（※ホダ塾ディストリビューションをご利用の場合は、この設定は不要です）
同梱のsite_custom.ini.phpを、XOOPS_ROOT_PATH/settingsにコピーして下さい。
既にsite_custom.ini.phpが存在する場合は、[RenderSystems][Legacy]
[Legacy_Controller][Legacy_WizMobileRenderSystem]に関する記述を追記して下さい。


【0.1系からの変更点】
site_custom.ini.phpで設定していた項目の幾つかを管理画面で設定するように変更しました。
また、以下の新機能を追加しています。
・簡単ログイン
・画像のリサイズ
・ページ分割
・携帯からアクセスした場合に非表示にするブロックの設定


【picoをご利用の方へ】
GIJOE氏のモジュール、picoを利用していて、".htaccess.rewrite_normal"によるURL書き換えを
行っている場合、picoによるURL書き換えと、WizMobileによるブロック表示用リンクが
衝突してしまい、404 Not foundのエラーが発生してしまいます。
お手数ではありますが、同梱の".htaccess.pico.rewrite_normal"を".htaccess"にリネームして、
pico用の.htaccessに上書きして下さい。


【注意事項】
本モジュール自体のライセンスはGPL2ですが、修正BSDライセンスの
フレームワーク"Wizin"に依存しています。


【今後の予定】
・絵文字対応


【謝辞】
・本モジュールのアイコンは、Argon氏が作成して下さいました。
・本モジュールで利用しているテーマ下テンプレート、並びにGチケットはGIJOE氏が開発したものを
　利用しています。
本当にありがとうございます。


--------------

2008年 2月 26日 作成
2008年 5月 26日 更新
