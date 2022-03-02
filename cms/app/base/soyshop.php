<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<link rel="stylesheet" href="<?php echo CMSApplication::getShopRoot(); ?>css/soy2/style.css" />
<link rel="stylesheet" href="<?php echo CMSApplication::getShopRoot(); ?>css/admin/style.css" />
<link rel="stylesheet" href="<?php echo CMSApplication::getShopRoot(); ?>js/tools/soy2_date_picker.css" />

<script type="text/javascript" src="<?php echo CMSApplication::getShopRoot(); ?>js/jquery.js"></script>
<script type="text/javascript" src="<?php echo CMSApplication::getShopRoot(); ?>js/main.pack.js"></script>
<script type="text/javascript" src="<?php echo CMSApplication::getShopRoot(); ?>js/tools/soy2_date_picker.js"></script>

<?php CMSApplication::printScript(); ?>
<?php CMSApplication::printLink(); ?>

<title><?php echo CMSApplication::getTitle(); ?></title>
</head>
<body class="layout_full shop">
<div id="wrapper" class="w950 _w750">

	<div id="header">
		<a href="<?php echo CMSApplication::getApplicationRoot(); ?>">
			<img src="<?php echo CMSApplication::getShopRoot(); ?>img/logo.png" />
		</a>
		<h1><a href="<?php echo CMSApplication::getApplicationRoot(); ?>"><?php echo CMSApplication::getApplicationName(); ?></a></h1>

		<div id="header_menu">
			<ul>
				<li>
				<?php if(CMSApplication::isDirectLogin()){ ?>
					<a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Login/Logout"); ?>">ログアウト</a>
				<?php }else{ ?>
					<a href="<?php echo SOY2PageController::createRelativeLink("../admin/"); ?>">CMS管理</a>
				<?php } ?>
				</li>
			</ul>
		</div>
	</div>
	
	<div id="menu">
		<?php CMSApplication::printTabs(); ?>
	</div>

	<div id="main">
		<?php CMSApplication::printApplication(); ?>
	</div>

	<div id="footer" class="clearfix">
		<br class="footer_bottom" />
	</div>

</div>
</body>