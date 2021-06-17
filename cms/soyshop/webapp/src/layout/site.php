<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="robots" content="noindex">
	<base href="<?php echo SOYSHOP_BASE_URL; ?>">
	<title><?php echo htmlspecialchars($title,ENT_QUOTES,"UTF-8"); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

<?php
	$soycmsDir = rtrim(dirname(SOY2PageController::createRelativeLink("./")), "/") . "/soycms";
	$hideSideMenu = ( isset($_COOKIE["soyshop-hide-side-menu"]) && $_COOKIE["soyshop-hide-side-menu"] == "true" );
	$time = SOYSHOP_BUILD_TIME;
	$isSubMenu = (strlen($subMenu) > 0);
?>

<link rel="stylesheet" type="text/css" href="<?php echo $soycmsDir;?>/css/dashboard.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/bootstrap/css/bootstrap.min.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/metisMenu/metisMenu.min.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/dist/css/sb-admin-2.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/dist/css/soycms_cp.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/morrisjs/morris.css?<?php echo $time;?>">
<link type="text/css" rel="stylesheet" href="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/font-awesome/css/font-awesome.min.css?<?php echo $time;?>">
<link rel="stylesheet" type="text/css" href="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery-ui/jquery-ui.min.css?<?php echo $time;?>">
<style>.navbar-static-top{background: linear-gradient(#cdcdcd,#ffffff);}</style>
<?php
foreach($css as $link){
	echo '<link rel="stylesheet" href="' . htmlspecialchars($link,ENT_QUOTES,"UTF-8"). '?' . $time . '" charset="utf-8">';
	echo "\n";
}
foreach($scripts as $script){
	$script = str_replace(".pack","",$script);
	echo '<script type="text/javascript" src="' . htmlspecialchars($script,ENT_QUOTES,"UTF-8"). '?' . $time . '" charset="utf-8"></script>';
	echo "\n";
}
?>
<?php if($path != "Site.File"){ ?>
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery/jquery.min.js?<?php echo $time; ?>" type="text/JavaScript" charset="utf-8"></script>
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery-ui/jquery-ui.min.js?<?php echo $time; ?>" type="text/JavaScript" charset="utf-8"></script>
<?php }?>
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
		<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0;">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
			</div>
			<!-- /.navbar-header -->

			<a href="<?php echo SOYSHOP_ADMIN_URL; ?>"><img src="<?php echo $appLogoPath; ?>" class="navbar-brand" alt="logo"></a>

			<ul id="top_menu_site" class="nav navbar-top-links navbar-left">
				<li>
					<p>
						<a style="text-decoration:none;color:black;" href="<?php echo soyshop_get_site_url(true); ?>" target="_blank" rel="noopener">
							<?php $adminTitle = ($appName != $shopName) ? $appName . " - " . htmlspecialchars($shopName,ENT_QUOTES,"UTF-8") : $appName;?>
							<?php echo $adminTitle; ?>
						</a> - サイト管理
					</p>
				</li>
			</ul>

			<ul id="top_menu" class="nav navbar-top-links navbar-right">
				<?php if(AUTH_OPERATE){?>
					<li><a href="<?php echo $_SERVER["REQUEST_URI"]; ?>?clear_cache"><i class="fa fa-refresh fa-fw"></i>キャッシュ削除</a></li>
				<?php }?>
				<?php if(AUTH_SITE){?>
				<li class="shop">
					<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Navigation" id="shop_manager_link"><i class="fa fa-list fa-fw"></i><?php echo SHOP_MANAGER_LABEL;?>管理</a>
				</li>
				<li class="site">
					<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site" id="site_manager_link"><i class="fa fa-file fa-fw"></i>サイト管理</a>
				</li>
				<li class="user">
					<a href="javascript:void(0);" data-toggle="modal" data-target="#accountModal"><i class="fa fa-user fa-fw"></i>ユーザー情報</a>
				</li>
				<?php }?>
				<li>
				<?php if(SHOW_LOGOUT_LINK){ ?>
					<a href="<?php echo SOYCMS_ADMIN_URL; ?>index.php/Login/Logout"><i class="fa fa-sign-out fa-fw"></i>ログアウト</a>
				<?php }else{ ?>
					<a href="<?php echo SOYCMS_ADMIN_URL; ?>"><i class="fa fa-home fa-fw"></i>CMS管理</a>
				<?php } ?>
				</li>
			</ul>
			<!-- /.navbar-top-links -->

			<?php if($hideSideMenu) { ?>
			<div class="navbar-default sidebar sidebar-narrow" role="navigation">
			<?php }else{ ?>
			<div class="navbar-default sidebar" role="navigation">
			<?php } ?>

				<div class="sidebar-nav navbar-collapse">
					<ul class="nav" id="side-menu">
						<li class="site_pages">
							<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site/Pages"><i class="fa fa-file fa-fw"></i>ページ設定</a>
						</li>
						<li class="site_template">
							<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site/Template"><i class="fa fa-html5 fa-fw"></i>テンプレート管理</a>
						</li>
						<li class="site_file">
							<a href="<?php echo SOYSHOP_ADMIN_URL; ?>/Site/File"><i class="fa fa-folder-open fa-fw"></i><span>ファイル管理</span></a>
						</li>
						<?php if(AUTH_IFRAME){?>
						<li class="abstract">
							<iframe id="soyshop_iframe" src="<?php echo SOY2PageController::createLink("Abstract")?>"></iframe>
						</li>
						<?php }?>
						<li class="hidden-xs">
							<?php if($hideSideMenu) { ?>
							<a href="#" id="toggle-side-menu" class="text-right"><i class="fa fa-fw fa-angle-right"></i><span>&nbsp;</span></a>
							<?php }else{ ?>
							<a href="#" id="toggle-side-menu" class="text-right"><i class="fa fa-fw fa-angle-left"></i><span>&nbsp;</span></a>
							<?php }?>
						</li>
					</ul>
				</div>
				<!-- /.sidebar-collapse -->
			</div>
			<!-- /.navbar-static-side -->
		</nav>

		<div id="page-wrapper" style="padding-top: 30px;">
			<?php echo $breadcrumb; ?>

			<?php echo $html; ?>

			<?php if($isSubMenu){ ?>
				<div class="col-lg-3">
					<div class="panel panel-default">
						<div class="panel-heading">その他</div>
						<div class="panel-body">
							<?php echo $subMenu; ?>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>

			<?php echo $footerMenu; ?>
		</div>

		<footer class="text-right">
			<div id="copyright" class=""><?php echo (defined("SOYCMS_CMS_NAME")) ? SOYCMS_CMS_NAME : "SOY CMS";?> developing. Copyright &copy; 2007-2017, <?php echo (defined("SOYCMS_DEVELOPER_NAME")) ? SOYCMS_DEVELOPER_NAME : "Brassica, Inc."?></div>
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

<!-- widget -->
<?php
SOY2::import("component.Widget.MemoWidgetComponent");
$widget = new MemoWidgetComponent();
echo $widget->buildWidget();
?>

<!-- モーダル -->
<div class="modal fade" id="accountModal" tabindex="-1" role="dialog" aria-labelledby="AccountLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">
	<div class="modal-body">
		<iframe src="<?php echo SOYCMS_ADMIN_URL; ?>index.php/Account" style="width:100%;height:460px;"></iframe>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	</div>
</div>
</div>
</div>

<style>
/** 仮 **/
dt{
	margin-top:5px;
}
dt:before{
	content:"・";
}
dd:before{
	content:"　";
}
#soyshop_iframe {
	width:100%;
	height:600px;
	border:1px solid #FFFFFF;
}
</style>

<script type="text/javascript">
$(function(){
$("#toggle-side-menu").click(function(){
if($("#side-menu li a span").is(":hidden")){
	$("#page-wrapper").css({'margin-left': '250px'});
	$("#side-menu li a span").show();
	$(".sidebar").css({'width': '250px'});
	$("#toggle-side-menu i").removeClass("fa-angle-right").addClass("fa-angle-left");
	$("#toggle-side-menu").removeClass("active").blur();
	$.cookie('soyshop-hide-side-menu', false);

	//soyshop_iframeがある場合は、soyshop_iframeの高さを変更
	if($("#soyshop_iframe")){
		$("#soyshop_iframe").css("height", "600px");
	}
}else{
	$("#page-wrapper").css({'margin-left': '50px'});
	$("#side-menu li a span").hide();
	$(".sidebar").css({'width': '50px'});
	$("#toggle-side-menu i").removeClass("fa-angle-left").addClass("fa-angle-right");
	$("#toggle-side-menu").removeClass("active").blur();
	$.cookie('soyshop-hide-side-menu', true);

	//soyshop_iframeがある場合は、soyshop_iframeの高さを変更
	if($("#soyshop_iframe")){
		$("#soyshop_iframe").css("height", "44px");
	}
}
});
});
</script>
</body>
