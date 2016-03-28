<?php
	header("HTTP/1.1 404 Not Found");
?>
<html>

<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<title>SOY CMS</title>

<link rel="stylesheet" href="<?php echo SOY2PageController::createRelativeLink("../admin/css/"); ?>style.css"/>
<link rel="stylesheet" href="<?php echo SOY2PageController::createRelativeLink("../admin/css/"); ?>form.css"/>
<link rel="stylesheet" href="<?php echo SOY2PageController::createRelativeLink("../admin/css/"); ?>table.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo SOY2PageController::createRelativeLink("../admin/css/"); ?>global_page/globalpage.css"/>
<script type="text/JavaScript" charset="utf-8" src="<?php echo SOY2PageController::createRelativeLink("../admin/js/"); ?>common.js"></script>
<script type="text/JavaScript" charset="utf-8" src="<?php echo SOY2PageController::createRelativeLink("../admin/js/"); ?>prototype.js"></script></head>

<style type="text/css">

	#stack_trace .stacktrace {
		margin: 0pt;
		padding: 0pt;
		overflow: visible;
	}

	#stack_list {
		padding-left:20px;
	}

	#exception_message div {
		padding-left:20px;
	}

	#resolve_message div {
		padding-left:20px;
	}
	#resolve_message p {
		padding-left:20px;
	}
	#resolve_message div p{
		padding-left:0px;
	}

	h3{
		border-color:red;
		margin-top:10px;
	}

	h4{
		margin-top:10px;
		margin-bottom:5px;
	}

	#content{
		margin-top:10px;

	}

	#stack_trace textarea{
		width:100%;
		height:240px;

	}

</style>

<body>

<div id="wrapper">

	<div id="upperMenu">
		<img src="<?php echo SOY2PageController::createRelativeLink("../admin/css/"); ?>img/logo_big.gif" alt="logo" />
		<div style="clear:both;"></div>
	</div>

	<div id="content">

		<h2>致命的なエラーが発生しました / Fatal error</h2>

		<div id="exception_message">
			<h3>エラーの内容 / Error message</h3>
			<div>
				<h4>データベースに接続できません。</h4>
				<p>データベースの設定ファイルが存在しません。</p>

				<hr style="margin: 1em 0;">

				<h4>Can not get a data source.</h4>
				<p>No database configuration file is found.<br/>Check your database configuration in SOY CMS.</p>
			</div>
		</div>

		<div id="resolve_message">
			<h3>解決策 / Solution</h3>
			<div>
				<h4>設定ファイルを置いてください。</h4>
				<p>データベースの設定ファイルを <strong><?php echo htmlspecialchars(SOY2::RootDir()."config/db/".SOYCMS_DB_TYPE.".php",ENT_QUOTES,"UTF-8"); ?></strong> に置いてください。<br>
				<p>設定ファイルの例は <strong><?php echo htmlspecialchars(SOY2::RootDir()."config/db/".SOYCMS_DB_TYPE.".sample.php",ENT_QUOTES,"UTF-8"); ?></strong> にあります。
				<?php if(SOYCMS_DB_TYPE=="mysql"){ ?>'<p>設定ファイルの書き方は「<a href="http://www.soycms.net/man/mysql_configuration.html">MySQLの設定</a>」で読むことができます。<?php } ?>

				<hr style="margin: 1em 0;">

				<h4>Create a database configuration file.</h4>
				<p>A configuration file should exist at <strong><?php echo htmlspecialchars(SOY2::RootDir()."config/db/".SOYCMS_DB_TYPE.".php",ENT_QUOTES,"UTF-8"); ?></strong>.<br>
				<p>See <strong><?php echo htmlspecialchars(SOY2::RootDir()."config/db/".SOYCMS_DB_TYPE.".sample.php",ENT_QUOTES,"UTF-8"); ?></strong> for example.
			</div>
			<p style="margin-top:30px">
				解決策や内容がご不明な場合は<a href="http://www.soycms.org/">フォーラム</a>をご利用ください。
			</p>
		</div>

		<!-- no stack_trace -->

	</div>

	<div>
		<div id="footer">
			<div id="footer_left"></div>
			<div id="footer_right"></div>
			<div id="copyright">Copyright © 2007-2013, Nippon Institute of Agroinformatics Ltd.</div>
		</div>
	</div>

</div>
</html>
