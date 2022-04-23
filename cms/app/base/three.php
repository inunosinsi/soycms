<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="robots" content="noindex">
	<title><?php echo CMSApplication::getTitle(); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

<?php
	$soycmsDir = rtrim(dirname(CMSApplication::getRoot()), "/") . "/soycms";
	$time = time();
?>

<link rel="stylesheet" type="text/css" href="<?php echo $soycmsDir;?>/css/dashboard.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $paths["css"]["bootstrap"]; ?>">
<link type="text/css" rel="stylesheet" href="<?php echo $paths["css"]["metis"]; ?>">
<link type="text/css" rel="stylesheet" href="<?php echo $paths["css"]["sb-admin-2"]; ?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/dist/css/soycms_cp.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $paths["css"]["morris"]; ?>">
<link type="text/css" rel="stylesheet" href="<?php echo $paths["css"]["fontawesome"]; ?>">
<link rel="stylesheet" type="text/css" href="<?php echo $paths["css"]["jquery-ui"]; ?>">
<style>.navbar-static-top{background: linear-gradient(#<?php echo $backgroundColor; ?>,#ffffff);}</style>
<?php CMSApplication::printLink(); ?>
<script src="<?php echo $paths["js"]["jquery"]; ?>" type="text/JavaScript" charset="utf-8"></script>
<script src="<?php echo $paths["js"]["jquery-ui"]; ?>" type="text/JavaScript" charset="utf-8"></script>
<?php if($hideSideMenu) { ?>
<style type="text/css">
@media (min-width: 768px) {
	#page-wrapper{
		margin-left: 50px;
	}
}
</style>
<?php } ?>
</head>

<body>
	<div id="wrapper">
		<!-- Navigation -->
		<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0;">			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			 </div>
			<!-- /.navbar-header -->

			<img src="<?php echo $logoPath; ?>" class="navbar-brand" alt="logo">

			<ul id="top_menu_site" class="nav navbar-top-links navbar-left">
				<li><p><a style="text-decoration:none;color:black;" href="<?php echo CMSApplication::getApplicationRoot(); ?>"><?php echo CMSApplication::getApplicationName(); ?></a><?php echo CMSApplication::getApplicationNameAdding(); ?></p></li>
			</ul>

			<ul id="top_menu" class="nav navbar-top-links navbar-right">
				<?php if(CMSApplication::isDirectLogin()){ ?>
					<?php if(CMSApplication::getDisplayAccountEditPanelConfig()) {?><li><a href="javascript:void(0);" data-toggle="modal" data-target="#accountModal"><i class="fa fa-user fa-fw"></i>ユーザ情報</a></li><?php }?>
					<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Login/Logout"); ?>"><i class="fa fa-sign-out fa-fw"></i>ログアウト</a></li>
				<?php }else{ ?>
					<?php if(CMSApplication::checkAuthWithSiteOnly()){?>
						<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/"); ?>"><i class="fa fa-home fa-fw"></i>CMS管理</a></li>&nbsp;
					<?php } ?>
				<?php if(CMSApplication::checkUseSiteDb()){ ?>
					<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Site/Login/") . CMSApplication::getLoginedSiteId(); ?>"><i class="fa fa-sitemap fa-fw"></i>ログイン中のサイトへ</a></li>
				<?php }else{ ?>
					<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Site"); ?>"><i class="fa fa-sitemap fa-fw"></i>サイト一覧</a></li>
				<?php }?>
					<?php if(CMSApplication::checkAuthWithSiteOnly()){?>
						&nbsp;
						<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Application"); ?>"><i class="fa fa-arrows-alt fa-fw"></i>アプリケーション一覧</a></li>
					<?php } ?>
				<?php } ?>
			</ul>
			<!-- /.navbar-top-links -->

			<?php if($hideSideMenu) { ?>
			<div class="navbar-default sidebar sidebar-narrow" role="navigation">
			<?php }else{ ?>
			<div class="navbar-default sidebar" role="navigation">
			<?php } ?>

				<div class="sidebar-nav navbar-collapse">
					<?php CMSApplication::printTabs(); ?>
				</div>
				<!-- /.sidebar-collapse -->
			</div>
			<!-- /.navbar-static-side -->
		</nav>

		<div id="page-wrapper" style="padding-top: 30px;">
			<?php CMSApplication::printApplication(); ?>
		</div><!-- /#page-wrapper -->

		<footer class="text-right">
			<div id="copyright" class=""><?php echo (defined("SOYCMS_CMS_NAME")) ? SOYCMS_CMS_NAME : "SOY CMS";?> developing. Copyright &copy; 2007-2017, <?php echo (defined("SOYCMS_DEVELOPER_NAME")) ? SOYCMS_DEVELOPER_NAME : "Brassica, Inc."?></div>
		</footer>
	</div><!-- /#wrapper -->

<!-- Bootstrap Core JavaScript -->
<script src="<?php echo $paths["js"]["bootstrap"]; ?>"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="<?php echo $paths["js"]["metis"]; ?>"></script>

<!-- Morris Charts JavaScript -->
<script src="<?php echo $paths["js"]["raphael"]; ?>"></script>
<script src="<?php echo $paths["js"]["morris"]; ?>"></script>

<!-- Custom Theme JavaScript -->
<script src="<?php echo $paths["js"]["sb-admin-2"]; ?>"></script>
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/dist/js/soycms-common.js?<?php echo $time;?>"></script>
<script src="<?php echo $soycmsDir;?>/js/lang/ja.js?<?php echo $time;?>"></script>

<script src="<?php echo $paths["js"]["jquery-cookie"]; ?>" type="text/javascript"></script>
<?php CMSApplication::printScript(); ?>

<!-- モーダル -->
<?php if(CMSApplication::getDisplayAccountEditPanelConfig()) {?>
<div class="modal fade" id="accountModal" tabindex="-1" role="dialog" aria-labelledby="AccountLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<iframe src="<?php echo rtrim(dirname(CMSApplication::getRoot()), "/"); ?>/admin/index.php/Account" style="width:100%;height:460px;"></iframe>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php }?>

<script type="text/javascript">
$(function(){
	$("#toggle-side-menu").click(function(){
		if($("#side-menu li a span").is(":hidden")){
			$("#page-wrapper").css({'margin-left': '250px'});
			$("#side-menu li a span").show();
			$(".sidebar").css({'width': '250px'});
			$("#toggle-side-menu i").removeClass("fa-angle-right").addClass("fa-angle-left");
			$("#toggle-side-menu").removeClass("active").blur();
			$.cookie('app-hide-side-menu', false);

			//soyapp_iframeがある場合は、soyapp_iframeの高さを変更
			if($("#soyapp_iframe")){
				$("#soyapp_iframe").css("height", "400px");
			}

		}else{
			$("#page-wrapper").css({'margin-left': '50px'});
			$("#side-menu li a span").hide();
			$(".sidebar").css({'width': '50px'});
			$("#toggle-side-menu i").removeClass("fa-angle-left").addClass("fa-angle-right");
			$("#toggle-side-menu").removeClass("active").blur();
			$.cookie('app-hide-side-menu', true);

			if($("#soyapp_iframe")){
				$("#soyapp_iframe").css("height", "50px");
			}
		}
	});
});
</script>
</body>
</html>
