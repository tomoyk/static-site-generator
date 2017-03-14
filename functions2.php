<?php

/* ************************* ファイルの探索 ************************* */
function search($target_path){
  global $counter,$flag,$pageInfo;

  // ディレクトリ内のファイル一覧の取得
  $dir_items = array_diff( scandir($target_path) , array('..', '.', 'image') );

  // ディレクトリ内の探索ループ
  foreach($dir_items as $items){
    dbg_msg(2, "finding", "${target_path}/${items} をチェックしています...");
  
    // 文字列が一致しているかチェック
    if( preg_match('/^[^\/\s]*.txt$/', $items) ){
      dbg_msg(0, "found", "$target_path/$items"."が見つかりました." );
      if(DEBUG==1) echo "\n<iframe class=\"pageContent\" src=\"$target_path/$items\"></iframe>";
      
      // ページ情報の取得,格納
      setInfo("$target_path/", $items, $counter);
      dbg_msg(2, "call", "setInfo($target_path/, $items, $counter)");

      $counter++;
    }
    
    // ディレクトリかつ、ディレクトリ内にファイルが存在する
    if( is_dir("$target_path/$items") && count( scandir("$target_path/$items") )>0 ){
      // 親(カレント)ディレクトリを検索する
      if($flag==true){
        $flag=false;
        search($items);
      
      // 子ディレクトリ内を検索する
      }else{ 
        search("$target_path/$items");
      }
    }
  }
  
  // 検索結果
  if($counter==0){
    return "ページデータ(.txt)を含むディレクトリ,ファイルが見つかりません.";
  }else{
    return "ページデータ(.txt)を含む $counter 件のディレクトリ,ファイルが見つかりました.";
  }
}

/* ************************* 書き込むコンテンツを組み立て ************************* */
function make_html($fpath, $fname, $title, $date, $author, $content){
  global $pageInfo, $navi, $sub_navi, $uri_top, $naviList;

  // 改行で分割して配列に代入
  $content = explode("\n", $content);
  
  // 子ページタグに一致する条件
  define('PATTERN_TAG', "/^\s*\[CHILD_LIST\][^\t]*$/");

  // サブナビの取得
  $getSubNavi = make_childList($fpath, 'index.txt');
  $sub_navi = ($fname=='index.txt' ? '' : $getSubNavi);

  // 展開形ナビゲーションの取得
  $navi2="\n<ul class=\"childList mainNav\">";
  foreach($naviList as $key => $value){
    // パスが一致している時
    if(DATA_PATH."/$key"==$fpath){
      // ナビゲーションの要素にサブナビを挿入（置換）
      $navi2.=preg_replace("#</a></li>$#", "</a>$getSubNavi</li>", $naviList[$key]);
    }else{
      $navi2.=$naviList[$key];
    }
  }
  $navi2.="\n</ul>\n";

  // タグ[xxxx]の置換
  foreach($content as &$tmp){
    // [CHILD_LIST]が存在する時
    if( preg_match(PATTERN_TAG, $tmp) ){
      // [CHILD_LIST]を置換
      $result = make_childList($fpath, $fname, 'echoContent');
      $new_content .= preg_replace(PATTERN_TAG, $result, $tmp);

    // タグが存在しない時
    }else{
      $new_content .= "$tmp\n";
    }
  }
  $content = $new_content;

  // htmlテンプレートの読み込み
  require('template.php');
  
  // htmlを返す
  return $file_content;
}

/* ************************* ファイルの書き込み ************************* */
function write_html($fpath, $fname, $html){
  // OUT_PATHの末尾に/を追加
  if( preg_match("#^[^/\s]+$#", OUT_PATH) ){
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
        $remove_specialTag = preg_replace("/(<style>.+<\/style>|<script>.+<\/script>)/", '', $remove_spaceIndent);
        $remove_htmlTag = strip_tags($remove_specialTag);

        // 最初の50文字を抽出
        $description = "\n<div>".mb_strcut($remove_htmlTag, 0, 140, 'UTF-8')."...</div>\n";

        $echoTitle = "<span>{$pageInfo[$i]['Title']}</span>";
      }else{
        $echoTitle = $pageInfo[$i]['Title'];
      }

      // htmlの組み立て
      $handle = "<li>\n<a href=\"$new_fpath$new_fname\">".$echoTitle."$description</a>\n</li>";
      $list_html .= $handle;
      
      // nav2(展開式ナビゲーション)用のナビゲーションアイテムリストを作成
      if($mode=='Navi'){
        $label=preg_replace("#^".DATA_PATH."/#", '', $pageInfo[$i]['Path']);
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
