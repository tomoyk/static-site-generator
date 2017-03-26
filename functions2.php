<?php
// Error
ini_set('display_errors', 'Off');

/* ************************* ファイルの探索 ************************* */
function search($target_path){
  global $counter,$pageInfo;

  // ディレクトリ内のファイル一覧の取得
  $dir_items = array_diff( scandir($target_path) , array('..', '.') );

  // ディレクトリ内の探索ループ
  foreach($dir_items as $items){
    dbg_msg(2, "finding", "${target_path}/${items} をチェックしています...");

    // ドキュメントルート直下のディレクトリを取得（データディレクトリを除く）
    $rm_dataDir = preg_replace("#^".DATA_PATH."/#", '', $target_path);
    $dir = explode('/', $rm_dataDir);

    // ドキュメントルート直下のメディアディレクトリであるか かつ .（ドット）から始まるファイル名でない
    define('DATA_DIR', 'image|common');
    if( preg_match('/^('.DATA_DIR.')$/', $dir[0]) && !is_dir("$target_path/$items") && !preg_match("/^\..+$/", $items) ){
			// ファイルのコピー
      dbg_msg(0, "found", "$target_path/$items"."が見つかりました." );
      copyFile($target_path, $items);

    // ファイルの拡張子がtxt かつ .（ドット）から始まるファイル名でない
    }else if( preg_match('/^[^\/\s.]{1}[^\/\s]*.txt$/', $items) ){
      dbg_msg(0, "found", "$target_path/$items"."が見つかりました." );
      if(DEBUG==1) echo "\n<iframe class=\"pageContent\" src=\"$target_path/$items\"></iframe>";
      
      // ページ情報の取得,格納
      setInfo("$target_path/", $items, $counter);
      dbg_msg(2, "call", "setInfo($target_path/, $items, $counter)");

      // カウンタ増加
      $counter++;

    }
    
    // ディレクトリかつ、ディレクトリ内にファイルが存在する
    if( is_dir("$target_path/$items") && count( scandir("$target_path/$items") )>0 ){
      // 子ディレクトリ内を検索する
      search("$target_path/$items");
    }
  }
  
  // 検索結果
  if($counter==0){
    return "ページデータ(.txt)を含むディレクトリ,ファイルが見つかりません.";
  }else{
    return "ページデータ(.txt)を含む $counter 件のディレクトリ,ファイルが見つかりました.";
  }
}

/* ************************* メディアファイルのコピー ************************* */
function copyFile($src_fpath, $src_fname){
  // OUT_PATHの末尾に/を追加
  if( preg_match("#^[^/\s]{2,}$#", OUT_PATH) ){
    $replace = OUT_PATH."/";
  }else{
    $replace = '';
  }
  
  // 宛先ファイルパスを取得
  $dest_fpath = preg_replace('#^'.DATA_PATH.'/#', $replace, $src_fpath);

  // 元,宛先ファイルパスの取得
  $src = $src_fpath."/".$src_fname;
  $dest = $dest_fpath."/".$src_fname;

  // ファイルの上書き(ファイルかリンクが存在 かつ 上書き設定off）
  if( (is_file($dest) || is_link($dest)) && OVER_WRITE==0 ){
    return;

  // ディレクトリの作成
  }else if( !is_dir($dest_fpath) ){
    dbg_msg(0, "info", "ディレクトリ{$dest_fpath}が存在しません.");

    if( mkdir($dest_fpath, PERMISSION, true) ){
      dbg_msg(0, "copy", "ディレクトリ{$dest_fpath}を作成に成功しました.");
    }else{
      dbg_msg(1, "copy", "ディレクトリ{$dest_fpath}を作成に失敗しました.");
      return;
    }
  }
  
  // コピーの実行
  if( copy($src, $dest) ){
    dbg_msg(0, "copy", "ファイルを{$src}から{$dest}へコピーしました.");
  }else{
    dbg_msg(1, "copy", "ファイルを{$src}から{$dest}へのコピーに失敗しました.");
  }
}

/* ************************* 投稿の情報を取得,保持 ************************* */
function setInfo($fpath, $fname, $number){
  global $pageInfo;

  // ファイルのフルパスを取得
  $read = file($fpath.$fname);

  // タグの一覧(パイプ区切りで指定)
  $tags = 'Title|Date|Author';

  // フラグの初期化
  $state = 0;

  // 連想配列の初期化
  $pageInfo[$number] = array();
  
  // ファイルの内容を1行ずつ読み込んでMETAの内容を取得
  foreach($read as &$tmp){
    // METAの開始&終了の判定
    if( preg_match("/^\s*\[META\]\s*$/", $tmp) ){
      $state=1;
      continue;
    }else if( preg_match("/^\s*\[\/META\]\s*$/", $tmp) ){
      $state=2;
      continue;
    }

    // META内のタグと文字列を分離して取得
    if( $state==1 && preg_match("/^\s*\[($tags)\][^\t]*$/", $tmp) ){
      // タグだけを取得
      preg_match("/\[($tags)\]/", $tmp, $pick);
      // タグから[と]を除外
      preg_match("/[^\[\]]+/", $pick[0] ,$label);

      // 文字列(設定値)だけを取得
      $del_tag=preg_replace("/^\s*\[($tags)\]\s*/", '', $tmp);
      // 末尾のスペース,タブ,改行を削除
      $del_tail=preg_replace("/\s*?$/", '', $del_tag);

      // 連想配列へ代入
      $pageInfo[$number]["$label[0]"] = $del_tail;
      dbg_msg(2, "info", "\$pageInfo[$number][${label[0]}] == $del_tail");

    // METAタグより下(コンテンツ)を結合
    }else if($state==2){
      $pageInfo[$number]['Content'] .= $tmp;
    }
  }

  // pageInfo配列が空,METAが存在しない時は終了
  if( empty($pageInfo) || $state==0 ){
    dbg_msg(1, "error", "META情報が不足しています.");
    return 0;

  // METAが存在する
  }else if($state==2){
    // パスとファイル名を代入
    $pageInfo[$number]['Path'] = $fpath;
    $pageInfo[$number]['Name'] = $fname;
    dbg_msg(2, "info", "\$pageInfo[$number][Path] == $fpath");
    dbg_msg(2, "info", "\$pageInfo[$number][Name] == $fname");
  }
}

/* ************************* 書き込むコンテンツを組み立て ************************* */
function make_html($fpath, $fname, $title, $date, $author, $content){
  global $pageInfo, $navi, $sub_navi, $uri_top, $naviList, $uri_base;

  // 改行で分割して配列に代入
  $content = explode("\n", $content);
  
  // サブナビの取得
  $getSubNavi = make_childList($fpath, 'index.txt');
  $sub_navi = ($fname=='index.txt'||$fpath==DATA_PATH."/" ? '' : $getSubNavi);

  // 展開形ナビゲーションの取得
  $navi2 = "\n<ul class=\"childList mainNav\">";
  // 配列の添字を比較
  foreach($naviList as $key => $value){
    // パスが一致 かつ ファイル名index.txtでない かつ ルートでない
    if(DATA_PATH."/$key"==$fpath) {
      // クラスを付与
      $tmp = preg_replace("#<li>#", "<li class=\"selected\">", $naviList[$key]);
      // ナビゲーションの要素にサブナビを挿入（置換）
      $navi2 .= preg_replace("#</a>\n</li>$#", "</a>$getSubNavi</li>", $tmp);

    // key(添字)が拡張子txt かつ データパスがドキュメントルート直下
    } else if(preg_match("#[^/ ]+.txt$#", $key) && DATA_PATH."/"==$fpath) {
      // ドキュメントルート直下のページ
      $navi2 .= preg_replace("#<li>#", "<li class=\"selected\">", $naviList[$key]);

    } else {
      $navi2.=$naviList[$key];
    }
  }
  $navi2.="\n</ul>\n";

  // タグの一覧
  $checkTags = array('CHILD_LIST', 'SITEMAP', 'UPDATE_LIST', 'BASE_URI');

  // タグ[xxxx]の置換
  // contentから1行ずつ読み出す
  foreach($content as &$tmp){
    $tagState = 0;

    // タグ一覧から1つずつ照合
    foreach($checkTags as &$foo){
      $ptn = "/\[".$foo."\]/";
      // [xxxx]が存在する時
      if( preg_match($ptn, $tmp) ){
        $tagState = 1;
        // 一致したタグの場合分け
        switch($foo){
          case "CHILD_LIST":
            $after = make_childList($fpath, $fname, 'echoContent');
            break;
          case "SITEMAP":
            $after = make_sitemap();
            break;
          case "UPDATE_LIST":
            $after = make_updateList();
            break;
          case "BASE_URI":
            $after = 'http://'.$_SERVER["HTTP_HOST"].'/'.DOCUMENT_ROOT;
            break;
        }
        // 置換
        $new_content .= preg_replace($ptn, $after, $tmp);

      }
    }

    // タグが存在しない時
    if($tagState==0){
      $new_content .= "$tmp\n";
    }
  }
  $content = $new_content;

  // htmlテンプレートの読み込み
  require(TEMPLATE_NAME);
  
  // htmlを返す
  return $file_content;
}

/* ************************* 子ページ（カレントディレクトリ内）リストを出力 ************************* */
function make_childList($filePath, $fileName, $mode){
  global $pageInfo,$navi,$naviList;
  $list_html = "\n<ul class=\"childList\">";

  // pageInfoの中を探索
  for($i=0;$i<count($pageInfo);$i++){
    // echo "$filePath ==? {$pageInfo[$i]['Path']} , $fileName ==? {$pageInfo[$i]['Name']}\n<br>";
  
    // ファイル名がindex.txtかつ子ディレクトリ または
    // ディレクトリ名（パス）が同一かつファイル名が同一でない
    if( ($pageInfo[$i]['Name']=="index.txt" && preg_match("#^".$filePath."[^\/\s]+/#", $pageInfo[$i]['Path']))
      ||($filePath==$pageInfo[$i]['Path'] && $fileName!=$pageInfo[$i]['Name']) ){

      dbg_msg(2, "info", "次の条件で一致しました. $filePath ==? {$pageInfo[$i]['Path']} , $fileName ==? {$pageInfo[$i]['Name']}");

      // ソースのパスを書き込むパスに変更
      $new_fpath = 'http://'.$_SERVER["HTTP_HOST"].'/'.DOCUMENT_ROOT.preg_replace("#^".DATA_PATH."/#", '', $pageInfo[$i]['Path']);

      // txtをhtmlに変換
      $new_fname = preg_replace("/.txt$/", '.'.OUT_EXTENSION, $pageInfo[$i]['Name']);

      // [CHILD_LIST]の時(記事の抜粋を出力)
      if($mode=="echoContent"){
        // スペース,タブ,改行を削除
        $remove_spaceIndent = preg_replace("/\s+/", '', $pageInfo[$i]['Content']);

        // scriptタグとstyleタグを削除
        $remove_specialTag = preg_replace("/(<style>.+<\/style>|<script>.+<\/script>|\[[A-Z_]+\])/", '', $remove_spaceIndent);
        $remove_htmlTag = strip_tags($remove_specialTag);

        // 最初の50文字を抽出
        $description = "\n<div>".mb_strcut($remove_htmlTag, 0, 140, 'UTF-8')."...</div>\n";
      }

      // htmlの組み立て
      $handle = "<li>\n<a href=\"$new_fpath$new_fname\"><span>{$pageInfo[$i]['Title']}</span>$description</a>\n</li>";
      $list_html .= $handle;
      
      // nav2(展開式ナビゲーション)用のナビゲーションアイテムリストを作成
      if($mode=='Navi'){
        $label=preg_replace("#^".DATA_PATH."/#", '', $pageInfo[$i]['Path']);
        $label=($label=='' ? $pageInfo[$i]['Name'] : $label);
        $naviList[$label]=$handle;
      }
    }
  }
  $list_html .= "\n</ul>\n";

  // 最初だけナビゲーションとして設定
  if( !isset($navi) ){
    $navi = $list_html;
  }

  return $list_html;
}

/* ************************* サイトマップの生成 ************************* */
function make_sitemap(){
  global $pageInfo;
  
  // ページをPathについて並べ替え
  $pages = $pageInfo;
  foreach( $pages as $label => $foo){
    $bar[$label] = $foo['Path'];
  }
  array_multisort($bar, SORT_ASC, $pages);

  // リストの組み立て
  $result = "<ul class=\"sitemap\">\n";
  for($i=0;$i<count($pages);$i++) {
    $new_path = 'http://'.$_SERVER["HTTP_HOST"].'/'.DOCUMENT_ROOT.preg_replace("#^".DATA_PATH."/#", '', $pages[$i]['Path']);
    $new_name = preg_replace("/.txt$/", ".".OUT_EXTENSION, $pages[$i]['Name']);
    $uri = $new_path.$new_name;
    $uri_i = $new_path."index.".OUT_EXTENSION;
    
    // 今のディレクトリと前のディレクトリを取得
    $before = preg_replace("#^".DATA_PATH."/#", '', $pages[$i-1]['Path']);
    $new = preg_replace("#^".DATA_PATH."/#", '', $pages[$i]['Path']);
      
    // 最後が子ディレクトリ
    if($i==count($pages)-1 && $before!=''){
      $result .= "</ul>\n";

    // indexファイルは飛ばす(ドキュメントルート直下は例外)
    }else if($pages[$i]['Name'] == "index.txt" && $pages[$i]['Path'] != DATA_PATH."/"){
      continue;

    // 前と同じディレクトリ
    }else if($before == $new){
      $result .= "<li><a href=\"$uri\">{$pages[$i]['Title']}</a></li>\n";

    // 前と違うディレクトリ
    }else{
      // 前のディレクトリがドキュメント直下
      if($before!=''){
        $result .= "</ul>\n</li>\n";
      }

      // indexの含まれる配列の要素の添字を取得
      for($j=$i;$pages[$j]['Name']!="index.txt";$j++);
      
      $result .= "<li><a href=\"$uri_i\">{$pages[$j]['Title']}</a>\n";
      $result .= "<ul>\n<li><a href=\"$uri\">{$pages[$i]['Title']}</a></li>\n";
    }
  }
  $result .= "\n</ul>";

  return $result;
}

/* ************************* 新着情報の生成 ************************* */
function make_updateList(){
  global $pageInfo;

  // 日付が新しい順に並べ替え
  $latestPosts = $pageInfo;
  foreach( $latestPosts as $label => $foo){
    $bar[$label] = $foo['Date'];
  }
  array_multisort($bar, SORT_DESC, $latestPosts);

  // リストの組み立て
  $result = "<ul class=\"updateList\">";
  for($i=0;$i<PRINT_UPDATE_POST;$i++){
    $new_path = 'http://'.$_SERVER["HTTP_HOST"].'/'.DOCUMENT_ROOT.preg_replace("#^".DATA_PATH."/#", '', $latestPosts[$i]['Path']);
    $new_name = preg_replace("/.txt$/", ".".OUT_EXTENSION, $latestPosts[$i]['Name']);
    $uri = $new_path.$new_name;
    $result .= "<li><span>{$latestPosts[$i]['Date']}</span><a href=\"$uri\">{$latestPosts[$i]['Title']}</a>が更新されました.</li>\n";
  }
  $result .= "</ul>";

  return $result;
}

/* ************************* ファイルの書き込み ************************* */
function write_html($fpath, $fname, $html){
  // OUT_PATHの末尾に/を追加
  if( preg_match("#^[^/\s]{2,}$#", OUT_PATH) ){
    $out_path=OUT_PATH."/";
  }else{
    $out_path='';
  }

  // ソースのパスを書き込むパスに変更
  $new_fpath = preg_replace("#^".DATA_PATH."/#", $out_path, $fpath);
  $new_fpath = ($new_fpath=='' ? './' : $new_fpath);

  // 拡張子の変更(txt -> ?)
  $new_fname = preg_replace("/.txt$/", '.'.OUT_EXTENSION, $fname);
  dbg_msg(0, "write", "$new_fname を $new_fpath へ書き込む準備が完了しました.");

  // 重複するファイル,ディレクトリのチェック
  if( file_exists($new_fpath.$new_fname) && OVER_WRITE==0 ) {
    dbg_msg(1, "info", "既に $new_fname と同名のファイル,ディレクトリが存在しています. 既に存在するファイルを削除するか移動してください.");

  // 書き込み可能かチェック
  }else if( !is_writable($new_fpath) ){
    // ディレクトリが存在(権限不足)
    if( file_exists($new_path) ) {
      dbg_msg(1, "info", "$new_fname をディレクトリへ書き込む権限がありません.");

    // ディレクトリが存在しない
    }else{
      dbg_msg(0, "info", "$new_fname を書き込むディレクトリ $new_fpath がありません.");

      // ディレクトリ作成に成功
      if( mkdir($new_fpath, PERMISSION, true) ){

        dbg_msg(0, "info", "$new_fname を書き込むディレクトリ $new_fpath を作成に成功しました.");
        if( is_writable($new_fpath) ){
          file_put_contents($new_fpath.$new_fname, $html, LOCK_EX);
          dbg_msg(0, "info", "$new_fpath$new_fname を書き込みました.");
          if(DEBUG==1) echo "\n<iframe class=\"pageContent\" src=\"$new_fpath$new_fname\"></iframe>";
        }else{
          dbg_msg(1, "info", "$new_fpath の権限を確認してください.");
        }

      // ディレクトリ作成に失敗
      }else{
        dbg_msg(1, "info", "$new_fname を書き込むディレクトリ $new_fpath を作成に失敗しました.");
      }
    }

  // ファイルの書き込み
  }else{
    file_put_contents($new_fpath.$new_fname, $html, LOCK_EX);
    dbg_msg(0, "info", "$new_fpath$new_fname を書き込みました.");
    if(DEBUG==1) echo "\n<iframe class=\"pageContent\" src=\"$new_fpath$new_fname\"></iframe>";
  }
}

/* ************************* デバッグメッセージ関数 ************************* */
function dbg_msg($mode, $type, $msg){
  // ログモード
  $color = array(
    0 => "black", // 平常時
    1 => "red", // エラー
    2 => "blue", // 詳細
  );
  // 呼び出し元の関数
  $dbg = debug_backtrace();
  $src_func = $dbg[1]['function'];

  // デバッグモードON
  if(DEBUG+1>=$mode){
    echo "<span class=\"${color[$mode]}\">";
    echo "[$type] ".($src_func ? "$src_func():" : "main():")." $msg<br>\n</span>";
  }
}

?>
