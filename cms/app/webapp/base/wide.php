<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<!-- Framework CSS -->
<link rel="stylesheet" href="<?php echo CMSApplication::getRoot(); ?>css/blueprint/print.css" type="text/css" media="print">
<!--[if IE]><link rel="stylesheet" href="<?php echo CMSApplication::getRoot(); ?>css/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->

<link rel="stylesheet" href="<?php echo CMSApplication::getRoot(); ?>css/wide.css" />
<link rel="stylesheet" href="<?php echo CMSApplication::getRoot(); ?>css/layer/layer.css" />

<script type="text/javascript" src="<?php echo CMSApplication::getRoot(); ?>js/jquery.js"></script>
<script type="text/javascript" src="<?php echo CMSApplication::getRoot(); ?>js/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?php echo CMSApplication::getRoot(); ?>js/soycms_widget.js"></script>
<script type="text/javascript" src="<?php echo CMSApplication::getRoot(); ?>js/soy2js/soy2js.js"></script>
<script type="text/javascript" src="<?php echo CMSApplication::getRoot(); ?>js/tools/advanced_textarea.js"></script>
<script type="text/javascript" src="<?php echo CMSApplication::getRoot(); ?>js/main.js"></script>

<?php CMSApplication::printScript(); ?>
<?php CMSApplication::printLink(); ?>

<title><?php echo CMSApplication::getTitle(); ?></title>
</head>
<body>

<div id="wrapper" class="container">

<div id="logo" class="span-12">
<h1><a href="<?php echo CMSApplication::getApplicationRoot(); ?>"><?php echo CMSApplication::getApplicationName(); ?></a></h1>
</div>

<?php if(CMSApplication::hasUpperMenu()){ ?>
<div id="upperMenu" class="span-8 last" style="text-align:right;">
	<?php CMSApplication::printUpperMenu(); ?>
</div>
<?php } ?>

<div id="tabs" class="span-20">
	<?php CMSApplication::printTabs(); ?>
</div>

<div id="content" class="span-20 last"><?php CMSApplication::printApplication(); ?></div>

<div class="span-20" style="text-align:center;margin-top:10px;">
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
</div>

<div id="footer" class="span-20">
	<div id="footer_left"></div>
	<div id="footer_right"></div>
	<div id="copyright">
		Copyright &copy; 2008<?php echo file_exists(dirname(dirname(__FILE__)) . "/" . $self->applicationId . "/application.ini") ? date("-Y", filemtime(dirname(dirname(__FILE__)) . "/" . $self->applicationId . "/application.ini")) : "" ; ?>,
		Nippon Institute of Agroinformatics Ltd.
	</div>
</div>

</div>

</body>
</html>
