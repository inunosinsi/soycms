<!DOCTYPE html>
<html>
<head>
<base href="<?php echo SOYSHOP_BASE_URL; ?>">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="./css/soy2/style.css?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8">
<link rel="stylesheet" href="./css/admin/style.css?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8">
<link rel="stylesheet" href="./css/jquery-ui/themes/base/jquery-ui.css?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8">
<script type="text/javascript" src="./js/jquery.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<script type="text/javascript" src="./js/jquery-ui.min.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<script type="text/javascript" src="./js/main.pack.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<?php
foreach($css as $link){
	echo '<link rel="stylesheet" href="' . $link . '?' . SOYSHOP_BUILD_TIME . '" charset="utf-8">';
	echo "\n";
}
foreach($scripts as $script){
	$script = str_replace(".pack","",$script);
	echo '<script type="text/javascript" src="' . $script . '?' . SOYSHOP_BUILD_TIME . '" charset="utf-8"></script>';
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
		<h1><a href="<?php echo soyshop_get_site_url(true); ?>" target="_blank">SOY Shop - <?php echo $shopName; ?></a></h1>

		<div id="header_menu">
			<ul>
				<?php if($appAuth){?>
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
				<?php }?>
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
			<li class="news">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>">新着</a>
			</li>
			<li class="order">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Order">注文</a>
			</li>
			<li class="user">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/User">顧客</a>
			</li>
			<li class="item">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Item">商品</a>
			</li>
			<?php if($isReview){?>
			<li class="review">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Review">レビュー</a>
			</li>
			<?php }?>
			<?php if($appLimit){ ?>
			<li class="config">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Config">設定</a>
			</li>
			<li class="plugin">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Plugin">プラグイン</a>
			</li>
			<?php } ?>
			<!--
			<li class="help">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Help">ヘルプ</a>
			</li>
			-->
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
		<iframe src="<?php echo SOYCMS_ADMIN_URL; ?>index.php/Account"></iframe>
		<p class="close"></p>
	</div>
	
</div>
</body>