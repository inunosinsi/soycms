<!DOCTYPE html>
<html>
<head>
<base href="<?php echo SOYSHOP_BASE_URL; ?>">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" href="./css/soy2/style.css?<?php echo SOYSHOP_BUILD_TIME; ?>">
<link rel="stylesheet" href="./css/admin/style.css?<?php echo SOYSHOP_BUILD_TIME; ?>">
<link rel="stylesheet" href="./css/jquery-ui/themes/base/jquery-ui.css?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8">
<script type="text/javascript" src="./js/jquery.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<script type="text/javascript" src="./js/jquery-ui.min.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<script type="text/javascript" src="./js/main.pack.js?<?php echo SOYSHOP_BUILD_TIME; ?>" charset="utf-8"></script>
<?php
foreach($css as $link){
	echo '<link rel="stylesheet" href="'.$link.'?'.SOYSHOP_BUILD_TIME.'" />';
	echo "\n";
}
foreach($scripts as $script){
	echo '<script type="text/javascript" src="' . $script . '?'.SOYSHOP_BUILD_TIME.'" charset="utf-8"></script>';
	echo "\n";
}
?>
<style type="text/css">
#main{
	margin:0 !important;
	padding:10px;
}
.block {
	margin:0;
}
</style>
<title><?php echo $title; ?></title>
</head>
<body class="layout_full">
<div id="wrapper" class="">

	<div id="main">
		<?php echo $html; ?>
	</div>
	
	<div id="account_form_el" class="popup" style="display:none;">
		<iframe src="<?php echo SOYCMS_ADMIN_URL; ?>/index.php/Account"></iframe>
		<p class="close"></p>
	</div>
</div>
</body>