<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<script type="text/javascript" src="<?php echo CMSApplication::getRoot(); ?>js/jquery.js"></script>
<script type="text/javascript" src="<?php echo CMSApplication::getRoot(); ?>js/jquery-ui.min.js"></script>
<?php CMSApplication::printScript(); ?>
<?php CMSApplication::printLink(); ?>
<title><?php echo CMSApplication::getTitle(); ?></title>
</head>
<body>

<section id="wrapper">
<?php if(!IFRAME_DISPLAY_MODE){?>
<header>
	<h1><a href="<?php echo CMSApplication::getApplicationRoot(); ?>"><?php echo CMSApplication::getApplicationName(); ?></a></h1>
	
	<?php if(CMSApplication::hasUpperMenu()){ ?>
	<section id="Menu">
		<?php CMSApplication::printUpperMenu(); ?>
	</section>
	<?php } ?>
</header>


<navi id="tabs">
	<?php CMSApplication::printTabs(); ?>
</navi>
<?php }?>

<section id="content"><?php CMSApplication::printApplication(); ?></section>

<?php if(!IFRAME_DISPLAY_MODE){?>
<footer>
<?php if(CMSApplication::isDirectLogin()){ ?>
	<a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Login/Logout"); ?>">ログアウト</a>
<?php }else{ ?>
	<a href="<?php echo SOY2PageController::createRelativeLink("../admin/"); ?>">CMS管理</a>
	&nbsp;
<?php if(CMSApplication::checkUseSiteDb()){ ?>
	<a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Site/Login/") . CMSApplication::getLoginedSiteId(); ?>">ログイン中のサイトへ</a>
<?php }else{ ?>
	<a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Site"); ?>">サイト一覧</a>
<?php }?>
	&nbsp;
	<a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Application"); ?>">アプリケーション一覧</a>
<?php } ?>

</footer>
<?php }?>

</section><!-- //wrapper.end -->

<div id="account_form_el" class="popup" style="display:none;">
	<iframe src="<?php echo SOYCMS_ADMIN_URL; ?>index.php/Account"></iframe>
	<p class="close">☒</p>
</div>

</body>
</html>