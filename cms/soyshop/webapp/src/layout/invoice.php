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
	echo '<link rel="stylesheet" href="'.htmlspecialchars($link,ENT_QUOTES,"UTF-8").'?'.SOYSHOP_BUILD_TIME.'" />';
	echo "\n";
}
foreach($scripts as $script){
	echo '<script type="text/javascript" src="' . htmlspecialchars($script,ENT_QUOTES,"UTF-8"). '?'.SOYSHOP_BUILD_TIME.'" charset="utf-8"></script>';
	echo "\n";
}
?>
<title><?php echo htmlspecialchars($title,ENT_QUOTES,"UTF-8"); ?></title>
</head>
<body class="<?php echo htmlspecialchars($layout,ENT_QUOTES,"UTF-8"); ?>">


	<div id="main">
		<?php echo $html; ?>
	</div>


</div>
</body>
