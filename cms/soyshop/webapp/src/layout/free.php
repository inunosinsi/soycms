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
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery/jquery.min.js?<?php echo $time; ?>" type="text/JavaScript" charset="utf-8"></script>
<script src="<?php echo $soycmsDir;?>/webapp/pages/files/vendor/jquery-ui/jquery-ui.min.js?<?php echo $time; ?>" type="text/JavaScript" charset="utf-8"></script>
<?php if(false){ ?>
<script type="text/javascript" src="./js/main.pack.js?<?php echo $time; ?>" charset="utf-8"></script>
<?php } ?>
<?php
foreach($css as $link){
	echo '<link rel="stylesheet" href="' . htmlspecialchars($link,ENT_QUOTES,"UTF-8"). '?' . $time . '" charset="utf-8">';
	echo "\n";
}
foreach($scripts as $script){
	//$script = str_replace(".pack","",$script);
	echo '<script type="text/javascript" src="' . htmlspecialchars($script,ENT_QUOTES,"UTF-8"). '?' . $time . '" charset="utf-8"></script>';
	echo "\n";
}
?>
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
		<?php echo $html; ?>
	</div>
</div>
</body>
