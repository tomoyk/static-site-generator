<?php

$file_content = <<<EOF
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>{$title}|サイトタイトル22</title>
  <!-- viewport -->
  <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/3.18.1/build/cssreset/cssreset-min.css">
</head>
<body>
<div id="container">
  <h1>サイトタイトル22</h1>
  <nav>
{$navigation}
  </nav>
  <h2>{$title}</h2>
  <dl>
    <dt>日付</dt>
    <dd>{$date}</dd>
    <dt>作者</dt>
    <dd>{$author}</dd>
  </dl>
  <div>
  {$content}
  </div>
</div>
</body>
</html>
EOF;

?>
