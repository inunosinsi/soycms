<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<!-- Framework CSS -->
<link rel="stylesheet" href="<?php echo CMSApplication::getRoot(); ?>css/blueprint/screen.css" type="text/css" media="screen, projection">
<link rel="stylesheet" href="<?php echo CMSApplication::getRoot(); ?>css/blueprint/print.css" type="text/css" media="print">
<!--[if IE]><link rel="stylesheet" href="<?php echo CMSApplication::getRoot(); ?>css/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->

<link rel="stylesheet" href="<?php echo CMSApplication::getRoot(); ?>css/styles.css" />

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

<div><?php CMSApplication::printApplication(); ?></div>

</body>
</html>

