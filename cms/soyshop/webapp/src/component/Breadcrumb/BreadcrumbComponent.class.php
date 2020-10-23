<?php

class BreadcrumbComponent {

	const MODE_SHOP = "shop";
	const MODE_SITE = "site";

	/**
	 * @param label string ページ名, parents array Uri=>ページ名の形式
	 */
	public static function build($pageName, $parents=array()){
		$html = array();
		$html[] = "<h2>" . $pageName . "</h2>";
		$html[] = "<nav>";
		$html[] = "<ol class=\"breadcrumb\">";
		switch(ADMIN_PAGE_TYPE){
			case self::MODE_SITE:
				$html[] = "<li><a href=\"" . SOY2PageController::createLink("Site") . "\">サイト管理</a></li>";
				break;
			case self::MODE_SHOP:
			default:
				$html[] = "<li><a href=\"" . SOY2PageController::createLink("") . "\">" . SHOP_MANAGER_LABEL . "管理</a></li>";
				break;
		}

		if(is_array($parents) && count($parents)){
			foreach($parents as $uri => $name){
				$html[] = "<li><a href=\"" . SOY2PageController::createLink($uri) . "\">" . $name . "</a></li>";
			}
		}

		$html[] = "<li class=\"active\">" . $pageName . "</li>";
		$html[] = "</ol>";
		$html[] = "</nav>";

		return implode("\n", $html);
	}
}
