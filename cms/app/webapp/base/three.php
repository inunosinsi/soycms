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
	$soycmsDir = dirname(CMSApplication::getRoot()) . "/soycms";
	$time = time();
?>

<link rel="stylesheet" type="text/css" href="<?php echo $soycmsDir;?>/css/dashboard.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/bootstrap/css/bootstrap.min.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/metisMenu/metisMenu.min.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/dist/css/sb-admin-2.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/dist/css/soycms_cp.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/morrisjs/morris.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/font-awesome/css/font-awesome.min.css?<?php echo $time;?>">
<link rel="stylesheet" type="text/css" href="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery-ui/jquery-ui.min.css?<?php echo $time;?>">
<style>.navbar-static-top{background: linear-gradient(#ffeaef,#ffffff);}</style>
<?php CMSApplication::printLink(); ?>
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery/jquery.min.js?1510124446" type="text/JavaScript" charset="utf-8"></script>
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery-ui/jquery-ui.min.js?1510124446" type="text/JavaScript" charset="utf-8"></script>
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

			<img src="<?php echo CMSApplication::getRoot(); ?>css/images/main/logo.png" class="navbar-brand" alt="logo">

			<ul id="top_menu_site" class="nav navbar-top-links navbar-left">
				<li><p><a style="text-decoration:none;color:black;" href="<?php echo CMSApplication::getApplicationRoot(); ?>"><?php echo CMSApplication::getApplicationName(); ?></a></p></li>
			</ul>

			<ul id="top_menu" class="nav navbar-top-links navbar-right">
				<?php if(CMSApplication::isDirectLogin()){ ?>
					<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Login/Logout"); ?>"><i class="fa fa-sign-out fa-fw"></i>ログアウト</a></li>
				<?php }else{ ?>
					<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/"); ?>"><i class="fa fa-home fa-fw"></i>CMS管理</a></li>
					&nbsp;
				<?php if(CMSApplication::checkUseSiteDb()){ ?>
					<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Site/Login/") . CMSApplication::getLoginedSiteId(); ?>"><i class="fa fa-sitemap fa-fw"></i>ログイン中のサイトへ</a></li>
				<?php }else{ ?>
					<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Site"); ?>"><i class="fa fa-sitemap fa-fw"></i>サイト一覧</a></li>
				<?php }?>
					&nbsp;
					<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Application"); ?>"><i class="fa fa-arrows-alt fa-fw"></i>アプリケーション一覧</a></li>
				<?php } ?>
			</ul>
			<!-- /.navbar-top-links -->

			<div class="navbar-default sidebar" role="navigation">
				<div class="sidebar-nav navbar-collapse">
					<?php CMSApplication::printTabs(); ?>
					<!--ul class="nav" id="side-menu">
						<li>
							<a href="/main/admin/"><i class="fa fa-home fa-fw"></i><span class="tab_active">ダッシュボード</span></a>
						</li>
						<li>
							<a href="/main/admin/index.php/Site"><i class="fa fa-sitemap fa-fw"></i><span class="tab_inactive">サイト一覧</span></a>
						</li>
						<li>
							<a href="/main/admin/index.php/Application"><i class="fa fa-arrows-alt fa-fw"></i><span class="tab_inactive">アプリケーション</span></a>
						</li>
						<li>
							<a href="/main/admin/index.php/Administrator"><i class="fa fa-users fa-fw"></i><span class="tab_inactive">管理者一覧</span></a>
						</li>

						<li class="hidden-xs">
							<a href="#" id="toggle-side-menu" class="text-right"><i class="fa fa-fw fa-angle-left"></i><span>&nbsp;</span></a>
						</li>

					</ul-->
				</div>
				<!-- /.sidebar-collapse -->
			</div>
			<!-- /.navbar-static-side -->
		</nav>

		<div id="page-wrapper" style="padding-top: 30px;">
			<?php CMSApplication::printApplication(); ?>
		</div><!-- /#page-wrapper -->

		<footer class="text-right">
			<div id="copyright" class="">SOY CMS developing. Copyright &copy; 2007-2017, Brassica, Inc.</div>
		</footer>
	</div><!-- /#wrapper -->

<!-- Bootstrap Core JavaScript -->
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/bootstrap/js/bootstrap.min.js?<?php echo $time;?>"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/metisMenu/metisMenu.min.js?<?php echo $time;?>"></script>

<!-- Morris Charts JavaScript -->
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/raphael/raphael.min.js?<?php echo $time;?>"></script>
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/morrisjs/morris.min.js?<?php echo $time;?>"></script>

<!-- Custom Theme JavaScript -->
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/dist/js/sb-admin-2.min.js?<?php echo $time;?>"></script>
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/dist/js/soycms-common.js?<?php echo $time;?>"></script>
<script src="<?php echo $soycmsDir;?>/js/lang/ja.js?<?php echo $time;?>"></script>

<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery-cookie/jquery.cookie.js?<?php echo $time;?>" type="text/javascript"></script>
<?php CMSApplication::printScript(); ?>

<script type="text/javascript">
$(function(){
	$("#toggle-side-menu").click(function(){
		if($("#side-menu li a span").is(":hidden")){
			$("#page-wrapper").css({'margin-left': '250px'});
			$("#side-menu li a span").show();
			$(".sidebar").css({'width': '250px'});
			$("#toggle-side-menu i").removeClass("fa-angle-right").addClass("fa-angle-left");
			$("#toggle-side-menu").removeClass("active").blur();
			$.cookie('admin-hide-side-menu', false);
		}else{
			$("#page-wrapper").css({'margin-left': '50px'});
			$("#side-menu li a span").hide();
			$(".sidebar").css({'width': '50px'});
			$("#toggle-side-menu i").removeClass("fa-angle-left").addClass("fa-angle-right");
			$("#toggle-side-menu").removeClass("active").blur();
			$.cookie('admin-hide-side-menu', true);
		}
	});
});
</script>
</body>
</html>
