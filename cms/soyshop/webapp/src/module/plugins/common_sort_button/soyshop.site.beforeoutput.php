<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class SOYShopSortButtonBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){
		
		//カートとマイページで動作しない様にする
		if(is_null($page->getPageObject())) return;		
		
		//一覧と検索結果ページで動作する
		$pageType = $page->getPageObject()->getType();
		if($pageType == SOYShop_Page::TYPE_LIST || $pageType == SOYShop_Page::TYPE_SEARCH){
			
			//_homeでもソートボタン設置プラグインを使用できるようにする
			if($page->getPageObject()->getUri() == SOYShop_Page::URI_HOME){
				$pageUrl = soyshop_get_page_url(null);
			}else{
				$pageUrl = soyshop_get_page_url($page->getPageObject()->getUri());
			}
			
								
			//検索ページで使う場合
			$query = (isset($_GET["type"]) && isset($_GET["q"])) ? "&type=" . trim($_GET["type"]) . "&q=" . trim($_GET["q"]) : "";

			SOY2::import("module.plugins.common_sort_button.util.SortButtonUtil");
			foreach(SortButtonUtil::getColumnList() as $key => $column){
				
				$page->addLink("sort_" . $key . "_desc", array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"link" => $pageUrl . "?sort=" . $key . "&r=1" . $query
				));
				
				$page->addLink("sort_" . $key . "_asc", array(
					"soy2prefix" => SOYSHOP_SITE_PREFIX,
					"link" => $pageUrl . "?sort=" . $key . "&r=0" . $query
				));				
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.site.beforeoutput", "common_sort_button", "SOYShopSortButtonBeforeOutput");
?>