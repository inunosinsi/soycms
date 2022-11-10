<?php
function soycms_soyinquiry_display($html, $page){
	$obj = $page->create("soycms_soyinquiry_display", "HTMLTemplatePage", array(
		"arguments" => array("soycms_soyinquiry_display", $html)
	));

    SOY2::import("site_include.component.CMSAppContainer");
    $obj->createAdd("apps","CMSAppContainer",array(
        "page" => $obj,
        "soy2prefix" => "cms"
    ));

	$obj->display();
}
