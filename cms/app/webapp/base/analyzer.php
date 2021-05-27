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
<style>.navbar-static-top{background: linear-gradient(#<?php echo $backgroundColor; ?>,#ffffff);}</style>
<?php CMSApplication::printLink(); ?>
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery/jquery.min.js?1510124446" type="text/JavaScript" charset="utf-8"></script>
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery-ui/jquery-ui.min.js?1510124446" type="text/JavaScript" charset="utf-8"></script>
</head>

<?php if($hideSideMenu) { ?>
<style type="text/css">
@media (min-width: 768px) {
	#page-wrapper{
		margin-left: 50px;
	}
}
</style>
<?php } ?>


<body>
	<div id="wrapper">
		<!-- Navigation -->
		<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0;">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			 </div><!-- /.navbar-header -->

			<img src="<?php echo $logoPath; ?>" class="navbar-brand" alt="logo">

			<ul id="top_menu_site" class="nav navbar-top-links navbar-left">
				<li><p><a style="text-decoration:none;color:white;" href="<?php echo CMSApplication::getApplicationRoot(); ?>"><?php echo CMSApplication::getApplicationName(); ?></a><?php echo CMSApplication::getApplicationNameAdding(); ?></p></li>
			</ul>

			<?php $enMode = AnalyzerUtil::getIsNecessaryTranslatePlan();?>

			<ul id="top_menu" class="nav navbar-top-links navbar-right">
				<?php if(CMSApplication::isDirectLogin()){ ?>
					<?php if(CMSApplication::getDisplayAccountEditPanelConfig()) {?><li><a href="javascript:void(0);" data-toggle="modal" data-target="#accountModal"><i class="fa fa-user fa-fw"></i><?php echo ($enMode) ? "User Info" : "ユーザ情報";?></a></li><?php }?>
					<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/index.php/Login/Logout"); ?>"><i class="fa fa-sign-out fa-fw"></i><?php echo ($enMode) ? "Logout" : "ログアウト"; ?></a></li>
				<?php }else{ ?>
					<?php if(CMSApplication::checkAuthWithSiteOnly()){?>
						<li><a href="<?php echo SOY2PageController::createRelativeLink("../admin/"); ?>"><i class="fa fa-home fa-fw"></i>ダッシュボード</a></li>&nbsp;
					<?php } ?>
				<?php if(CMSApplication::checkUseSiteDb()){ ?>
				<?php }else{ ?>
				<?php }?>
					<?php if(CMSApplication::checkAuthWithSiteOnly()){?>
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

		<div id="page-wrapper">
			<?php CMSApplication::printApplication(); ?>
		</div><!-- /#page-wrapper -->

		<footer class="text-right">
			<div id="copyright">Copyright © <?php echo (defined("SOYCMS_DEVELOPER_NAME")) ? SOYCMS_DEVELOPER_NAME : "Brassica, Inc."?> All Rights Reserved.</div>
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

<!-- モーダル -->
<?php if(CMSApplication::getDisplayAccountEditPanelConfig()) {?>
<div class="modal fade" id="accountModal" tabindex="-1" role="dialog" aria-labelledby="AccountLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body">
				<iframe src="<?php echo dirname(CMSApplication::getRoot()); ?>/admin/index.php/Account" style="width:100%;height:460px;"></iframe>
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

			//soyapp_iframeがある場合は、soyapp_iframeの高さを変更
			if($("#soyapp_iframe")){
				$("#soyapp_iframe").css("height", "44px");
			}
		}
	});
});
</script>
</body>
</html>
