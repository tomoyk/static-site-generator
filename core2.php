<?php

// デバッグ設定(0:OFF, 1:ON)
define('DEBUG', 0);

// 上書き設定(0:NO, 1:YES)
define('OVER_WRITE', 1);

// ソースデータ(.txt)の保存場所
define('DATA_PATH', 'src');
/* [MEMO]
core.phpやfunctions.php, template.phpが設置されているディレクトリを基準に
サイトのソースデータ(.txt)が保存されているディレクトリ名を指定してください.
同一ディレクトリの場合は「.」を設定,子ディレクトリの場合はその名前を設定してください.
*/

// 生成したサイトの保存場所
define('OUT_PATH', 'out');
/* [MEMO]
生成したサイト(html)の出力先のディレクトリを指定してください.
core.phpやfunctions.php, template.phpが設置されているディレクトリを基準に
同一ディレクトリの場合は「.」を設定,子ディレクトリの場合はその名前を設定してください.
*/

// テンプレートファイル名
define('TEMPLATE_NAME', 'template_ts.php');

// 出力するファイルの拡張子
define('OUT_EXTENSION', 'php');
/* [MEMO]
出力するファイルの拡張子を指定してください.通常はhtmlで問題ありません.
指定する際に.(ドット)は不要です.
*/

// ディレクトリが存在しない時に作成するディレクトリの権限
define('PERMISSION', 0755);
/* [MEMO]
一般的なWebサーバの場合,パーミッションは0755に設定することが多いです.
0700に設定してapacheの実行ユーザのみ読み書きを許可するほうがより安全です.
*/

// 公開サイトの配置ディレクトリ設定
define('DOCUMENT_ROOT', 'ts/static-site-generator/out/');
/* [MEMO]
http://example.com/に配置する場合は「空白」（スペースではありません）を指定してください.
http://example.com/hoge/に配置する場合は「hoge/」と指定してください.
*/

// 新着情報の表示件数
define('PRINT_UPDATE_POST', 5);

?>
<!doctype html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>サイト生成</title>
<style>
html{
  background: #e6e6fa;
}
div.menu {
  position: fixed;
  display: block;
  top: 0;
  right: 0;
  margin: 10px 0;
  background: #ffb6c1;
  border: solid 1px #db7093;
  padding: 10px 15px;
  z-index: 20;
}
div.menu > a{
  color: #000;
}
h2#gen, h2#find{
  display: block;
  background: #6495ed;
  padding: 5px 10px;
}
iframe.pageContent{
  width: 100%;
  height: 200px;
  overflow: hidden;
  overflow: auto;
  border: solid 1px black;
  margin: 20px 0;
  background: #fff;
}
span.red{
  color: #de0000;
  font-weight: bold;
}
span.black{
  color: #000;
}
span.blue{
  color: #00008b;
}
.marginTop{
  margin-top: 100px;
}
</style>
</head><body>
<h1>#サイト生成</h1>
<div class="menu"><a href="#find">[ファイル(.txt)の探索]</a> <a href="#gen">[ファイル(html)の出力]</a></div>
<h2 id="find">$ファイル(.txt)の探索</h2>
<?php

// 別ファイル読み込み
require_once("functions2.php");

// 変数の初期化
$counter = 0; // 一致したファイルの数
$uri_base = 'http://'.$_SERVER["HTTP_HOST"].'/'.DOCUMENT_ROOT;

// 検索の実行
$result = search(DATA_PATH);
dbg_msg(0, "info", $result);

// タイトルを降順に並べ替え
foreach( $pageInfo as $label => $foo){
  $bar[$label] = $foo['Title'];
}
array_multisort($bar, SORT_DESC, $pageInfo);

?>
<hr class="marginTop">
<h2 id="gen">$ファイル(html)の出力</h2>
<?php

// Navigationの取得
$naviList = array();
make_childList(DATA_PATH.'/', 'index.txt', 'Navi');

// htmlの生成
for($i=0; $i<count($pageInfo); $i++){
  // htmlの組み立て
  $write_content = make_html($pageInfo[$i]['Path'], $pageInfo[$i]['Name'], $pageInfo[$i]['Title'], $pageInfo[$i]['Date'], $pageInfo[$i]['Author'], $pageInfo[$i]['Content']);
  dbg_msg(2, "call", "make_html({$pageInfo[$i]['Path']}, {$pageInfo[$i]['Name']}, {$pageInfo[$i]['Title']}, {$pageInfo[$i]['Date']}, {$pageInfo[$i]['Author']}, \$pageInfo[$i]['Content'])"); // Content展開すると大変だから展開しない

  // htmlの書き込み
  write_html($pageInfo[$i]['Path'], $pageInfo[$i]['Name'], $write_content);
  dbg_msg(2, "call", "write_html({$pageInfo[$i]['Path']}, {$pageInfo[$i]['Name']}, \$write_content)"); // Content展開すると大変だから展開しない
}

// ここになにか書いてデバッグしてた.

?>
<hr>
<p>static-site-generator | Tomoyuki Koyama | github.com/tomoyk/static-site-generator</p>
<hr>
</body></html>
