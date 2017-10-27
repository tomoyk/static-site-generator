# static-site-generator

## 0.What's this?
The static-site-generator by PHP.

## 1.Getting started

### 1-a.Download this

You could download this app on github by any ways.

```
$ git clone https://github.com/tomoyk/static-site-generator.git
```

### 1-b.Edit core2.php

You would set any configurations on core2.php .

```
$ vim core2.php
```

## 2.Write pages

### 2-a.Set your file
You would set files on DATA_PATH that configure in core2.php .

e.g) DATA_PATH: src/

- src/index.txt
- src/yourPage.txt
- src/yourDirectory/index.txt
- src/yourDirectory/yourPage.txt

**Now, This app  only support first directory. This will support second directory later.**

### 2-b.Edit these files

Supported tags:

- [CHILD_LIST]
- [SITEMAP]
- [UPDATE_LIST]

**Format**

```
[META]
  [Title] YourPageTitle
  [Date] 2017/01/01
  [Author] John Doe
COMMENT
[/META]
<p>You can write HTML</p>
<hr>
[SITEMAP]
<hr>
<ul>
  <li>foo</li>
  <li>bar</li>
</ul>
```

## 3.Edit export template

You would edit export format that configure in template.php .

Defined variable: 

- {$title}  --> PageTitle
- {$date} --> PageDate
- {$author} --> PageAuthor
- {$content} --> PageContent
- {$navi} --> Navigation (bug)
- {$navi2} -->  Navigation[expand]
- {$sub_navi} -->  Sub Navigation
- {$uri_base} --> BASE URI

## 4.How to build

Access core2.php on Web-Browser or CUI-Browser.

At the moment, Your site is built automatically by this app.

```
http://yourHost/yourPath/core2.php
```

## 5.License

- MIT License

## 6.Version

-  1.0.1

## 7.GOMEN

日本語で書けば分かりやすいはず。から、そのうち書き直す。
