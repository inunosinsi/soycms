<!DOCTYPE html>
<html>
<head>
<base href="<?php echo SOY2PageController::createRelativeLink("index.php", true); ?>" />
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="./css/soy2/style.css?<?php echo SOYSHOP_BUILD_TIME; ?>">
<link rel="stylesheet" href="./css/admin/style.css?<?php echo SOYSHOP_BUILD_TIME; ?>">
<link rel="stylesheet" href="./css/jquery-ui/themes/base/jquery-ui.css?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8">
<script type="text/javascript" src="./js/jquery.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<script type="text/javascript" src="./js/jquery-ui.min.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<script type="text/javascript" src="./js/main.pack.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<title><?php echo $title; ?></title>
</head>
<body class="layout_full">
<div id="wrapper" class="w950 _w750">

	<div id="header">
		<a href="<?php echo SOYSHOP_ADMIN_URL; ?>">
			<img src="./img/logo.png" />
		</a>
		<h1>SOY Shop</h1>

		<div id="header_menu">
			<ul>
				<li>
					<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Navigation">ショップ管理</a>
				</li>
				<li>
					<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site">サイト管理</a>
				</li>
				<li>
					<a href="<?php echo SOYCMS_ADMIN_URL; ?>">CMS管理に戻る</a>
				</li>
				<li class="user">
					<a href="javascript:void(0);" onclick="return ChangeAccountInfo.popup();">ユーザー情報</a>
				</li>
			</ul>
		</div>
	</div>

	<div id="menu">
		<ul class="clearfix">
			<li>
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>">新着</a>
			</li>
			<li>
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Order">注文</a>
			</li>
			<li>
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Item">商品</a>
			</li>
			<li>
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Config">設定</a>
			</li>
			<li>
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Plugin">プラグイン</a>
			</li>
			<li>
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Help">ヘルプ</a>
			</li>
		</ul>
	</div>

	<div id="main">
		<h1>エラーが発生しました</h1>
		<textarea style="border:solid 1px #ccc;width:100%;height:500px;"><?php echo $html; ?></textarea>
	</div>

	<div id="footer" class="clearfix">

		<br class="footer_bottom" />
	</div>
	
	<div id="account_form_el" class="popup" style="display:none;">
		<iframe src="<?php echo SOYCMS_ADMIN_URL; ?>/index.php/Account"></iframe>
		<p class="close"></p>
	</div>

</div>
</body>