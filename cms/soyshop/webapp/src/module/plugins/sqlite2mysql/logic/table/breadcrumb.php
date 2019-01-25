<?php

function register_breadcrumb($stmt){
	SOY2::import("module.plugins.common_breadcrumb.domain.SOYShop_BreadcrumbDAO");
	$dao = SOY2DAOFactory::create("SOYShop_BreadcrumbDAO");

	try{
		$breads = $dao->get();
		if(!count($breads)) return;
	}catch(Exception $e){
		return;
	}

	foreach($breads as $bread){
		$stmt->execute(array(
			":item_id" => $bread->getItemId(),
			":page_id" => $bread->getPageId(),
		));
	}
}
