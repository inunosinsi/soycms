<!DOCTYPE html>
<html>
<head>
<base href="<?php echo SOYSHOP_BASE_URL; ?>">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="./css/soy2/style.css?<?php echo SOYSHOP_BUILD_TIME; ?>">
<link rel="stylesheet" href="./css/admin/style.css?<?php echo SOYSHOP_BUILD_TIME; ?>">
<link rel="stylesheet" href="./css/jquery-ui/themes/base/jquery-ui.css?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8">
<?php if($path != "Site.File"){ ?>
<script type="text/javascript" src="./js/jquery.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<script type="text/javascript" src="./js/jquery-ui.min.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<script type="text/javascript" src="./js/main.pack.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<?php }?>
<?php
foreach($css as $link){
	echo '<link rel="stylesheet" href="' . $link . '?' . SOYSHOP_BUILD_TIME . '">';
	echo "\n";
}
foreach($scripts as $script){
	echo '<script type="text/javascript" src="' . $script . '?'.SOYSHOP_BUILD_TIME.'" charset="utf-8"></script>';
	echo "\n";
}
?>
<title><?php echo $title; ?></title>
</head>
<body class="<?php echo "$layout $pageClass"; ?>" id="<?php echo $activeTab;?>">
<div id="wrapper" class="w950 _w750">

	<div id="header">
		<a href="<?php echo SOYSHOP_ADMIN_URL; ?>">
			<img src="./img/logo.png" />
		</a>
		<h1><a href="<?php echo soyshop_get_site_url(true); ?>" target="_blank">SOY Shop - <?php echo $shopName; ?></a> - サイト管理</h1>

		<div id="header_menu">
			<ul>
				<?php if($inquiryUseSiteDb){?><li>
					<a href="<?php echo $createAppLink; ?>/inquiry">お問い合わせフォーム</a>
				</li><?php }?>
				<?php if($mailUseSiteDb){?><li>
					<a href="<?php echo $createAppLink; ?>/mail">メールマガジン</a>
				</li><?php }?>
				<li class="shop">
					<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Navigation">ショップ管理</a>
				</li>
				<li class="site">
					<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site">サイト管理</a>
				</li>
				<li class="user">
					<a href="javascript:void(0);" onclick="return ChangeAccountInfo.popup();">ユーザー情報</a>
				</li>
				<li>
				<?php if($showLogoutLink){ ?>
					<a href="<?php echo SOYCMS_ADMIN_URL; ?>index.php/Login/Logout">ログアウト</a>
				<?php }else{ ?>
					<a href="<?php echo SOYCMS_ADMIN_URL; ?>">CMS管理</a>
				<?php } ?>
				</li>
			</ul>
		</div>
	</div>

	<div id="menu">
		<ul class="clearfix">
			<li class="site">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site/">サイト管理</a>
			</li>
			<li class="site_pages">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site/Pages">ページ設定</a>
			</li>
			<li class="site_template">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site/Template">テンプレート管理</a>
			</li>
			<li class="site_file">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site/File">ファイル管理</a>
			</li>
			<li class="site_config">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site/Config">設定</a>
			</li>
		</ul>
	</div>

	<div id="sub">
		<?php echo $subMenu; ?>
	</div>

	<div id="main">
		<?php echo $html; ?>
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