<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>WebDiaView Converter</title>
</head>
<body>
<h1>WebDiaView Converter</h1>
<h2>使い方</h2>
<p>
1.下記フォームにお手持ちのoudファイルを指定してください。<br>
2.変換ボタンを押すとJSON文字列が表示されますので、ブラウザ画面を右クリックし、適当に名前を付け拡張子jsonとして「名前を付けて保存」してください。<br>
3.WebDiaViewで変換されたjsonファイルを指定することにより、時刻表表示がご利用いただけます。
</p>
<form action="download.php" method="post" enctype="multipart/form-data">
<input type="file" name="upfile" size="30"><br><br>
<input type="submit" value="変換">
<h2>使用上の注意</h2>
<ul>
<li>変換されたJSONファイルの著作権その他権利に関しましては、変換元oudファイルのものに準じます。法律に抵触するファイルを変換されたことで生じた責任は一切負いかねますのでご了承ください。</li>
<li>当サイトではアップロードされたoudファイルは一時ファイルとして、処理終了後に削除されますが、念のため著作権上問題のあるファイルのアップロードはおやめください。</li>
</ul>
<h2>Tips</h2>
<ul>
<li>変換したデータの列車データの並び順は、OuDiaで閲覧した際の並び順に準じています。並べ替え機能はついていませんので、事前にOuDia側で並べ替えを行っておくことを推奨します。</li>
<li>種別データは二文字で収まるよう略称の方を取得しています。</li>
</ul>
</body>
</html>