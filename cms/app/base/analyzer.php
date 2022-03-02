<?php
include_once(dirname(dirname(__FILE__)) . "/analyzer/layout/MainLayoutPage.class.php");
$page = SOY2HTMLFactory::createInstance("MainLayoutPage");
$page->display();
