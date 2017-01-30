<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class CustomSearchFieldBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){
		
		//カートとマイページで動作しない様にする
		if(is_null($page->getPageObject())) return;		
		
		//一覧で動作する
		if($page->getPageObject()->getType() == SOYShop_Page::TYPE_LIST){
			
			//_homeでもソートボタン設置プラグインを使用できるようにする
			if($page->getPageObject()->getUri() == SOYShop_Page::URI_HOME){
				$pageUrl = soyshop_get_page_url(null);
			}else{
				$pageUrl = soyshop_get_page_url($page->getPageObject()->getUri());
			}
			
			$query = "";
			
			$args = $page->getArguments();
			for($i = 0; $i < count($args); $i++){
				if(isset($args[$i]) && strlen($args[$i])){
					$pageUrl .= "/" . htmlspecialChars($args[$i], ENT_QUOTES, "UTF-8");
				}
			}
			
			SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
			foreach(CustomSearchFieldUtil::getConfig() as $fieldId => $values){
				$page->addLink("custom_search_sort_" . $fieldId . "_desc", array(
					"soy2prefix" => "css",
					"link" => $pageUrl . "?custom_search_sort=" . $fieldId . "&r=1" . $query
				));
				
				$page->addLink("custom_search_sort_" . $fieldId . "_asc", array(
					"soy2prefix" => "css",
					"link" => $pageUrl . "?custom_search_sort=" . $fieldId . "&r=0" . $query
				));
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "custom_search_field", "CustomSearchFieldBeforeOutput");
?>