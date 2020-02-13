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
<title><?php echo htmlspecialchars($title,ENT_QUOTES,"UTF-8"); ?></title>
</head>
<body class="layout_full">
<div id="wrapper" class="w950 _w750">

	<div id="header">
		<a href="<?php echo htmlspecialchars(SOYSHOP_ADMIN_URL,ENT_QUOTES,"UTF-8"); ?>">
			<img src="<?php echo $appLogoPath; ?>" />
		</a>
		<h1><?php echo $appName; ?></h1>

		<div id="header_menu">
			<ul>
				<?php if(AUTH_SOYAPP){?>
				<?php if(USE_INQUIRY_SITE_DB){?><li>
					<a href="<?php echo SOYAPP_LINK; ?>/inquiry">お問い合わせフォーム</a>
				</li><?php }?>
				<?php if(USE_MAIL_SITE_DB){?><li>
					<a href="<?php echo SOYAPP_LINK; ?>/mail">メールマガジン</a>
				</li><?php }?>
				<?php }?>
				<?php if(AUTH_SITE){?>
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
				<?php if(SHOW_LOGOUT_LINK){ ?>
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
			<?php if(AUTH_HOME){?>
			<li class="news">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>">新着</a>
			</li>
			<?php }?>
			<?php if(AUTH_EXTENSION && count($extConts)) {
				foreach($extConts as $plgId => $cont){
					echo "<li class=\"extention\">";
					echo "<a href=\"" . SOYSHOP_ADMIN_URL . "/Extension/" . $plgId . "\">" . $cont["tab"] . "</a>";
					echo "</li>";
				}
			}?>
			<?php if(AUTH_ORDER){?>
			<li class="order">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Order">注文</a>
			</li>
			<?php }?>
			<?php if(AUTH_USER){?>
			<li class="user">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/User">顧客</a>
			</li>
			<?php }?>
			<?php if(AUTH_ITEM){?>
			<li class="item">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Item">商品</a>
			</li>
			<?php }?>
			<?php if(AUTH_REVIEW){?>
			<li class="review">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Review">レビュー</a>
			</li>
			<?php }?>
			<?php if(AUTH_CONFIG){ ?>
			<li class="config">
				<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Config">設定</a>
			</li>
			<?php } ?>
			<?php if(AUTH_PLUGIN){ ?>
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
