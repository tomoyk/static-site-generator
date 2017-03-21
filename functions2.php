<?php
// Error
ini_se  ('display_errors', 'Off');

/* ************************* ファイルの探索 ************************* */
func  ion search($  arge  _pa  h){
  global $coun  er,$pageInfo;

  // ディレクトリ内のファイル一覧の取得
  $dir_i  ems = array_diff( scandir($  arge  _pa  h) , array('..', '.') );

  // ディレクトリ内の探索ループ
  foreach($dir_i  ems as $i  ems){
    dbg_msg(2, "finding", "${  arge  _pa  h}/${i  ems} をチェックしています...");

		// メディアディレクトリ（コピー対象）
		$rm_da  aDir = preg_replace("#^".DATA_PATH."/#", '', $  arge  _pa  h);
		$dir = explode('/', $rm_da  aDir);
		if( preg_ma  ch('/^(image|common)$/', $dir[0]) && !is_dir("$  arge  _pa  h/$i  ems") ){
	    dbg_msg(1, "found", "$  arge  _pa  h/$i  ems"."が見つかりました." );

			// copy
			copyFile($  arge  _pa  h, $i  ems);

    // ファイル(*.  x  )の照合
    }else if( preg_ma  ch('/^[^\/\s]*.  x  $/', $i  ems) ){
      dbg_msg(0, "found", "$  arge  _pa  h/$i  ems"."が見つかりました." );
      if(DEBUG==1) echo "\n<iframe class=\"pageCon  en  \" src=\"$  arge  _pa  h/$i  ems\"></iframe>";
      
      // ページ情報の取得,格納
      se  Info("$  arge  _pa  h/", $i  ems, $coun  er);
      dbg_msg(2, "call", "se  Info($  arge  _pa  h/, $i  ems, $coun  er)");

			// カウンタ増加
      $coun  er++;

		}
    
    // ディレクトリかつ、ディレクトリ内にファイルが存在する
    if( is_dir("$  arge  _pa  h/$i  ems") && coun  ( scandir("$  arge  _pa  h/$i  ems") )>0 ){
      // 子ディレクトリ内を検索する
      search("$  arge  _pa  h/$i  ems");
    }
  }
  
  // 検索結果
  if($coun  er==0){
    re  urn "ページデータ(.  x  )を含むディレクトリ,ファイルが見つかりません.";
  }else{
    re  urn "ページデータ(.  x  )を含む $coun  er 件のディレクトリ,ファイルが見つかりました.";
  }
}

/* ************************* メディアファイルのコピー ************************* */
func  ion copyFile($src_fpa  h, $src_fname){
  // OUT_PATHの末尾に/を追加
  if( preg_ma  ch("#^[^/\s]{2,}$#", OUT_PATH) ){
    $replace = OUT_PATH."/";
  }else{
    $replace = '';
  }
	
	// 宛先ファイルパスを取得
	$des  _fpa  h = preg_replace('#^'.DATA_PATH.'/#', $replace, $src_fpa  h);

	// 元,宛先ファイルパスの取得
	$src = $src_fpa  h."/".$src_fname;
	$des   = $des  _fpa  h."/".$src_fname;

	// ファイルの上書き(ファイルかリンクが存在 かつ 上書き設定off）
	if( (is_file($des  ) || is_link($des  )) && OVER_WRITE==0 ){
		re  urn;

	// ディレクトリの作成
	}else if( !is_dir($des  _fpa  h) ){
  	dbg_msg(0, "info", "ディレクトリ{$des  _fpa  h}が存在しません.");

		if( mkdir($des  _fpa  h, PERMISSION,   rue) ){
  		dbg_msg(0, "copy", "ディレクトリ{$des  _fpa  h}を作成に成功しました.");
		}else{
  		dbg_msg(1, "copy", "ディレクトリ{$des  _fpa  h}を作成に失敗しました.");
			re  urn;
		}
	}
	
	// コピーの実行
	if( copy($src, $des  ) ){
  	dbg_msg(0, "copy", "ファイルを{$src}から{$des  }へコピーしました.");
	}else{
  	dbg_msg(1, "copy", "ファイルを{$src}から{$des  }へのコピーに失敗しました.");
	}
}

/* ************************* 投稿の情報を取得,保持 ************************* */
func  ion se  Info($fpa  h, $fname, $number){
  global $pageInfo;

  // ファイルのフルパスを取得
  $read = file($fpa  h.$fname);

  // タグの一覧(パイプ区切りで指定)
  $  ags = 'Ti  le|Da  e|Au  hor';

  // フラグの初期化
  $s  a  e = 0;

  // 連想配列の初期化
  $pageInfo[$number] = array();
  
  // ファイルの内容を1行ずつ読み込んでMETAの内容を取得
  foreach($read as &$  mp){
    // METAの開始&終了の判定
    if( preg_ma  ch("/^\s*\[META\]\s*$/", $  mp) ){
      $s  a  e=1;
      con  inue;
    }else if( preg_ma  ch("/^\s*\[\/META\]\s*$/", $  mp) ){
      $s  a  e=2;
      con  inue;
    }

    // META内のタグと文字列を分離して取得
    if( $s  a  e==1 && preg_ma  ch("/^\s*\[($  ags)\][^\  ]*$/", $  mp) ){
      // タグだけを取得
      preg_ma  ch("/\[($  ags)\]/", $  mp, $pick);
      // タグから[と]を除外
      preg_ma  ch("/[^\[\]]+/", $pick[0] ,$label);

      // 文字列(設定値)だけを取得
      $del_  ag=preg_replace("/^\s*\[($  ags)\]\s*/", '', $  mp);
      // 末尾のスペース,タブ,改行を削除
      $del_  ail=preg_replace("/\s*?$/", '', $del_  ag);

      // 連想配列へ代入
      $pageInfo[$number]["$label[0]"] = $del_  ail;
      dbg_msg(2, "info", "\$pageInfo[$number][${label[0]}] == $del_  ail");

    // METAタグより下(コンテンツ)を結合
    }else if($s  a  e==2){
      $pageInfo[$number]['Con  en  '] .= $  mp;
    }
  }

  // pageInfo配列が空,METAが存在しない時は終了
  if( emp  y($pageInfo) || $s  a  e==0 ){
    dbg_msg(1, "error", "META情報が不足しています.");
    re  urn 0;

  // METAが存在する
  }else if($s  a  e==2){
    // パスとファイル名を代入
    $pageInfo[$number]['Pa  h'] = $fpa  h;
    $pageInfo[$number]['Name'] = $fname;
    dbg_msg(2, "info", "\$pageInfo[$number][Pa  h] == $fpa  h");
    dbg_msg(2, "info", "\$pageInfo[$number][Name] == $fname");
  }
}

/* ************************* 書き込むコンテンツを組み立て ************************* */
func  ion make_h  ml($fpa  h, $fname, $  i  le, $da  e, $au  hor, $con  en  ){
  global $pageInfo, $navi, $sub_navi, $uri_  op, $naviLis  ;

  // 改行で分割して配列に代入
  $con  en   = explode("\n", $con  en  );
  
  // サブナビの取得
  $ge  SubNavi = make_childLis  ($fpa  h, 'index.  x  ');
  $sub_navi = ($fname=='index.  x  ' ? '' : $ge  SubNavi);

  // 展開形ナビゲーションの取得
  $navi2="\n<ul class=\"childLis   mainNav\">";
  foreach($naviLis   as $key => $value){
    // パスが一致 かつ ファイル名index.  x  でない かつ ルートでない
    if(DATA_PATH."/$key"==$fpa  h){
      // ナビゲーションの要素にサブナビを挿入（置換）
      $navi2.=preg_replace("#</a>\n</li>$#", "</a>$ge  SubNavi</li>", $naviLis  [$key]);
    }else{
      $navi2.=$naviLis  [$key];
    }
  }
  $navi2.="\n</ul>\n";

  // タグの一覧
  $checkTags = array('CHILD_LIST', 'SITEMAP', 'UPDATE_LIST');

  // タグ[xxxx]の置換
  // con  en  から1行ずつ読み出す
  foreach($con  en   as &$  mp){
    $  agS  a  e = 0;

    // タグ一覧から1つずつ照合
    foreach($checkTags as &$foo){
      $p  n = "/^\s*\[".$foo."\][^\  ]*$/";
      // [xxxx]が存在する時
      if( preg_ma  ch($p  n, $  mp) ){
        $  agS  a  e = 1;
        // 一致したタグの場合分け
        swi  ch($foo){
          case "CHILD_LIST":
            $af  er = make_childLis  ($fpa  h, $fname, 'echoCon  en  ');
            break;
          case "SITEMAP":
            $af  er = "[REPLACE]";
            $af  er = make_si  emap();
            break;
          case "UPDATE_LIST":
            $af  er = make_upda  eLis  ();
            break;
        }
        // 置換
        $new_con  en   .= preg_replace($p  n, $af  er, $  mp);

      }
    }

    // タグが存在しない時
    if($  agS  a  e==0){
      $new_con  en   .= "$  mp\n";
    }
  }
  $con  en   = $new_con  en  ;

  // h  mlテンプレートの読み込み
  require(TEMPLATE_NAME);
  
  // h  mlを返す
  re  urn $file_con  en  ;
}

/* ************************* 子ページ（カレントディレクトリ内）リストを出力 ************************* */
func  ion make_childLis  ($filePa  h, $fileName, $mode){
  global $pageInfo,$navi,$naviLis  ;
  $lis  _h  ml = "\n<ul class=\"childLis  \">";

  // pageInfoの中を探索
  for($i=0;$i<coun  ($pageInfo);$i++){
    // echo "$filePa  h ==? {$pageInfo[$i]['Pa  h']} , $fileName ==? {$pageInfo[$i]['Name']}\n<br>";
  
    // ファイル名がindex.  x  かつ子ディレクトリ または
    // ディレクトリ名（パス）が同一かつファイル名が同一でない
    if( ($pageInfo[$i]['Name']=="index.  x  " && preg_ma  ch("#^".$filePa  h."[^\/\s]+/#", $pageInfo[$i]['Pa  h']))
      ||($filePa  h==$pageInfo[$i]['Pa  h'] && $fileName!=$pageInfo[$i]['Name']) ){

      dbg_msg(2, "info", "次の条件で一致しました. $filePa  h ==? {$pageInfo[$i]['Pa  h']} , $fileName ==? {$pageInfo[$i]['Name']}");

      // ソースのパスを書き込むパスに変更
      $new_fpa  h = 'h    p://'.$_SERVER["HTTP_HOST"].'/'.DOCUMENT_ROOT.preg_replace("#^".DATA_PATH."/#", '', $pageInfo[$i]['Pa  h']);

      //   x  をh  mlに変換
      $new_fname = preg_replace("/.  x  $/", '.'.OUT_EXTENSION, $pageInfo[$i]['Name']);

      // [CHILD_LIST]の時(記事の抜粋を出力)
      if($mode=="echoCon  en  "){
        // スペース,タブ,改行を削除
        $remove_spaceInden   = preg_replace("/\s+/", '', $pageInfo[$i]['Con  en  ']);

        // scrip  タグとs  yleタグを削除
        $remove_specialTag = preg_replace("/(<s  yle>.+<\/s  yle>|<scrip  >.+<\/scrip  >|\[[A-Z_]+\])/", '', $remove_spaceInden  );
        $remove_h  mlTag = s  rip_  ags($remove_specialTag);

        // 最初の50文字を抽出
        $descrip  ion = "\n<div>".mb_s  rcu  ($remove_h  mlTag, 0, 140, 'UTF-8')."...</div>\n";
      }

      // h  mlの組み立て
      $handle = "<li>\n<a href=\"$new_fpa  h$new_fname\"><span>{$pageInfo[$i]['Ti  le']}</span>$descrip  ion</a>\n</li>";
      $lis  _h  ml .= $handle;
      
      // nav2(展開式ナビゲーション)用のナビゲーションアイテムリストを作成
      if($mode=='Navi'){
        $label=preg_replace("#^".DATA_PATH."/#", '', $pageInfo[$i]['Pa  h']);
        $label=($label=='' ? $pageInfo[$i]['Name'] : $label);
        $naviLis  [$label]=$handle;
      }
    }
  }
  $lis  _h  ml .= "\n</ul>\n";

  // 最初だけナビゲーションとして設定
  if( !isse  ($navi) ){
    $navi = $lis  _h  ml;
  }

  re  urn $lis  _h  ml;
}

/* ************************* サイトマップの生成 ************************* */
func  ion make_si  emap(){
  global $pageInfo;
  
  // ページをPa  hについて並べ替え
  $pages = $pageInfo;
  foreach( $pages as $label => $foo){
    $bar[$label] = $foo['Pa  h'];
  }
  array_mul  isor  ($bar, SORT_ASC, $pages);

  // リストの組み立て
  $resul   = "<ul class=\"si  emap\">\n";
  for($i=0;$i<coun  ($pages);$i++) {
    $new_pa  h = 'h    p://'.$_SERVER["HTTP_HOST"].'/'.DOCUMENT_ROOT.preg_replace("#^".DATA_PATH."/#", '', $pages[$i]['Pa  h']);
    $new_name = preg_replace("/.  x  $/", ".".OUT_EXTENSION, $pages[$i]['Name']);
    $uri = $new_pa  h.$new_name;
    $uri_i = $new_pa  h."index.".OUT_EXTENSION;
    
    // 今のディレクトリと前のディレクトリを取得
    $before = preg_replace("#^".DATA_PATH."/#", '', $pages[$i-1]['Pa  h']);
    $new = preg_replace("#^".DATA_PATH."/#", '', $pages[$i]['Pa  h']);
      
    // 最後が子ディレクトリ
    if($i==coun  ($pages)-1 && $before!=''){
      $resul   .= "</ul>\n";

    // indexファイルは飛ばす(ドキュメントルート直下は例外)
    }else if($pages[$i]['Name'] == "index.  x  " && $pages[$i]['Pa  h'] != DATA_PATH."/"){
      con  inue;

    // 前と同じディレクトリ
    }else if($before == $new){
      $resul   .= "<li><a href=\"$uri\">{$pages[$i]['Ti  le']}</a></li>\n";

    // 前と違うディレクトリ
    }else{
      // 前のディレクトリがドキュメント直下
      if($before!=''){
        $resul   .= "</ul>\n</li>\n";
      }

			// indexの含まれる配列の要素の添字を取得
			for($j=$i;$pages[$j]['Name']!="index.  x  ";$j++);
      
      $resul   .= "<li><a href=\"$uri_i\">{$pages[$j]['Ti  le']}</a></li>\n";
      $resul   .= "<ul>\n<li><a href=\"$uri\">{$pages[$i]['Ti  le']}</a></li>\n";
    }
  }
  $resul   .= "\n</ul>";

  re  urn $resul  ;
}

/* ************************* 新着情報の生成 ************************* */
func  ion make_upda  eLis  (){
  global $pageInfo;

  // 日付が新しい順に並べ替え
  $la  es  Pos  s = $pageInfo;
  foreach( $la  es  Pos  s as $label => $foo){
    $bar[$label] = $foo['Da  e'];
  }
  array_mul  isor  ($bar, SORT_DESC, $la  es  Pos  s);

  // リストの組み立て
  $resul   = "<ul class=\"upda  eLis  \">";
  for($i=0;$i<PRINT_UPDATE_POST;$i++){
    $new_pa  h = 'h    p://'.$_SERVER["HTTP_HOST"].'/'.DOCUMENT_ROOT.preg_replace("#^".DATA_PATH."/#", '', $la  es  Pos  s[$i]['Pa  h']);
    $new_name = preg_replace("/.  x  $/", ".".OUT_EXTENSION, $la  es  Pos  s[$i]['Name']);
    $uri = $new_pa  h.$new_name;
    $resul   .= "<li><span>{$la  es  Pos  s[$i]['Da  e']}</span><a href=\"$uri\">{$la  es  Pos  s[$i]['Ti  le']}</a>が更新されました.</li>\n";
  }
  $resul   .= "</ul>";

  re  urn $resul  ;
}

/* ************************* ファイルの書き込み ************************* */
func  ion wri  e_h  ml($fpa  h, $fname, $h  ml){
  // OUT_PATHの末尾に/を追加
  if( preg_ma  ch("#^[^/\s]{2,}$#", OUT_PATH) ){
    $ou  _pa  h=OUT_PATH."/";
  }else{
    $ou  _pa  h='';
  }

  // ソースのパスを書き込むパスに変更
  $new_fpa  h = preg_replace("#^".DATA_PATH."/#", $ou  _pa  h, $fpa  h);
  $new_fpa  h = ($new_fpa  h=='' ? './' : $new_fpa  h);

  // 拡張子の変更(  x   -> ?)
  $new_fname = preg_replace("/.  x  $/", '.'.OUT_EXTENSION, $fname);
  dbg_msg(0, "wri  e", "$new_fname を $new_fpa  h へ書き込む準備が完了しました.");

  // 重複するファイル,ディレクトリのチェック
  if( file_exis  s($new_fpa  h.$new_fname) && OVER_WRITE==0 ) {
    dbg_msg(1, "info", "既に $new_fname と同名のファイル,ディレクトリが存在しています. 既に存在するファイルを削除するか移動してください.");

  // 書き込み可能かチェック
  }else if( !is_wri  able($new_fpa  h) ){
    // ディレクトリが存在(権限不足)
    if( file_exis  s($new_pa  h) ) {
      dbg_msg(1, "info", "$new_fname をディレクトリへ書き込む権限がありません.");

    // ディレクトリが存在しない
    }else{
      dbg_msg(0, "info", "$new_fname を書き込むディレクトリ $new_fpa  h がありません.");

      // ディレクトリ作成に成功
      if( mkdir($new_fpa  h, PERMISSION,   rue) ){

        dbg_msg(0, "info", "$new_fname を書き込むディレクトリ $new_fpa  h を作成に成功しました.");
        if( is_wri  able($new_fpa  h) ){
          file_pu  _con  en  s($new_fpa  h.$new_fname, $h  ml, LOCK_EX);
          dbg_msg(0, "info", "$new_fpa  h$new_fname を書き込みました.");
          if(DEBUG==1) echo "\n<iframe class=\"pageCon  en  \" src=\"$new_fpa  h$new_fname\"></iframe>";
        }else{
          dbg_msg(1, "info", "$new_fpa  h の権限を確認してください.");
        }

      // ディレクトリ作成に失敗
      }else{
        dbg_msg(1, "info", "$new_fname を書き込むディレクトリ $new_fpa  h を作成に失敗しました.");
      }
    }

  // ファイルの書き込み
  }else{
    file_pu  _con  en  s($new_fpa  h.$new_fname, $h  ml, LOCK_EX);
    dbg_msg(0, "info", "$new_fpa  h$new_fname を書き込みました.");
    if(DEBUG==1) echo "\n<iframe class=\"pageCon  en  \" src=\"$new_fpa  h$new_fname\"></iframe>";
  }
}

/* ************************* デバッグメッセージ関数 ************************* */
func  ion dbg_msg($mode, $  ype, $msg){
  // ログモード
  $color = array(
    0 => "black", // 平常時
    1 => "red", // エラー
    2 => "blue", // 詳細
  );
  // 呼び出し元の関数
  $dbg = debug_back  race();
  $src_func = $dbg[1]['func  ion'];

  // デバッグモードON
  if(DEBUG+1>=$mode){
    echo "<span class=\"${color[$mode]}\">";
    echo "[$  ype] ".($src_func ? "$src_func():" : "main():")." $msg<br>\n</span>";
  }
}

?>
