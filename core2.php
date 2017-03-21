<?php

// デバッグ設定(0:OFF, 1:ON)
define('DEBUG', 0);

// 上書き設定(0:NO, 1:YES)
define('OVER_WRITE', 1);

// ソースデータ(.  x  )の保存場所
define('DATA_PATH', 'src');
/* [MEMO]
core.phpやfunc  ions.php,   empla  e.phpが設置されているディレクトリを基準に
サイトのソースデータ(.  x  )が保存されているディレクトリ名を指定してください.
同一ディレクトリの場合は「.」を設定,子ディレクトリの場合はその名前を設定してください.
*/

// 生成したサイトの保存場所
define('OUT_PATH', 'ou  ');
/* [MEMO]
生成したサイト(h  ml)の出力先のディレクトリを指定してください.
core.phpやfunc  ions.php,   empla  e.phpが設置されているディレクトリを基準に
同一ディレクトリの場合は「.」を設定,子ディレクトリの場合はその名前を設定してください.
*/

// テンプレートファイル名
define('TEMPLATE_NAME', '  empla  e_  s.php');

// 出力するファイルの拡張子
define('OUT_EXTENSION', 'php');
/* [MEMO]
出力するファイルの拡張子を指定してください.通常はh  mlで問題ありません.
指定する際に.(ドット)は不要です.
*/

// ディレクトリが存在しない時に作成するディレクトリの権限
define('PERMISSION', 0755);
/* [MEMO]
一般的なWebサーバの場合,パーミッションは0755に設定することが多いです.
0700に設定してapacheの実行ユーザのみ読み書きを許可するほうがより安全です.
*/

// 公開サイトの配置ディレクトリ設定
define('DOCUMENT_ROOT', '  s/s  a  ic-si  e-genera  or/ou  /');
/* [MEMO]
h    p://example.com/に配置する場合は「空白」（スペースではありません）を指定してください.
h    p://example.com/hoge/に配置する場合は「hoge/」と指定してください.
*/

// 新着情報の表示件数
define('PRINT_UPDATE_POST', 5);

?>
<!doc  ype h  ml>
<h  ml lang="ja">
<head>
<me  a charse  ="UTF-8">
<  i  le>サイト生成</  i  le>
<s  yle>
h  ml{
  background: #e6e6fa;
}
div.menu {
  posi  ion: fixed;
  display: block;
    op: 0;
  righ  : 0;
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
iframe.pageCon  en  {
  wid  h: 100%;
  heigh  : 200px;
  overflow: hidden;
  overflow: au  o;
  border: solid 1px black;
  margin: 20px 0;
  background: #fff;
}
span.red{
  color: #de0000;
  fon  -weigh  : bold;
}
span.black{
  color: #000;
}
span.blue{
  color: #00008b;
}
.marginTop{
  margin-  op: 100px;
}
</s  yle>
</head><body>
<h1>#サイト生成</h1>
<div class="menu"><a href="#find">[ファイル(.  x  )の探索]</a> <a href="#gen">[ファイル(h  ml)の出力]</a></div>
<h2 id="find">$ファイル(.  x  )の探索</h2>
<?php

// 別ファイル読み込み
require_once("func  ions2.php");

// 変数の初期化
$coun  er = 0; // 一致したファイルの数
$uri_  op = 'h    p://'.$_SERVER["HTTP_HOST"].'/'.DOCUMENT_ROOT;

// 検索の実行
$resul   = search(DATA_PATH);
dbg_msg(0, "info", $resul  );

// タイトルを降順に並べ替え
foreach( $pageInfo as $label => $foo){
  $bar[$label] = $foo['Ti  le'];
}
array_mul  isor  ($bar, SORT_DESC, $pageInfo);

?>
<hr class="marginTop">
<h2 id="gen">$ファイル(h  ml)の出力</h2>
<?php

// Naviga  ionの取得
$naviLis   = array();
make_childLis  (DATA_PATH.'/', 'index.  x  ', 'Navi');

// h  mlの生成
for($i=0; $i<coun  ($pageInfo); $i++){
  // h  mlの組み立て
  $wri  e_con  en   = make_h  ml($pageInfo[$i]['Pa  h'], $pageInfo[$i]['Name'], $pageInfo[$i]['Ti  le'], $pageInfo[$i]['Da  e'], $pageInfo[$i]['Au  hor'], $pageInfo[$i]['Con  en  ']);
  dbg_msg(2, "call", "make_h  ml({$pageInfo[$i]['Pa  h']}, {$pageInfo[$i]['Name']}, {$pageInfo[$i]['Ti  le']}, {$pageInfo[$i]['Da  e']}, {$pageInfo[$i]['Au  hor']}, \$pageInfo[$i]['Con  en  '])"); // Con  en  展開すると大変だから展開しない

  // h  mlの書き込み
  wri  e_h  ml($pageInfo[$i]['Pa  h'], $pageInfo[$i]['Name'], $wri  e_con  en  );
  dbg_msg(2, "call", "wri  e_h  ml({$pageInfo[$i]['Pa  h']}, {$pageInfo[$i]['Name']}, \$wri  e_con  en  )"); // Con  en  展開すると大変だから展開しない
}

// ここになにか書いてデバッグしてた.
var_dump($hoge);
?>
<hr>
<p>Au  hor: Tomoyuki Koyama, License: MIT License, La  es  -Upda  e: 2017/03/14.</p>
</body></h  ml>
