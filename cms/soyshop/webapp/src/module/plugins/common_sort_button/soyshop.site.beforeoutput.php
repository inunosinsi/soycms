<?php
/*
 * soyshop.site.beforeoutput.php
 * Created: 2010/03/11
 */

class SOYShopSortButtonBeforeOutput extends SOYShopSiteBeforeOutputAction{

	function beforeOutput($page){

		//カートとマイページで動作しない様にする
		if(is_null($page->getPageObject())) return;

		//一覧と検索結果ページで動作する (コンプレックスページの商品ブロックにも対応)
		$pageType = $page->getPageObject()->getType();
		if($pageType == SOYShop_Page::TYPE_LIST || $pageType == SOYShop_Page::TYPE_SEARCH ||  $pageType == SOYShop_Page::TYPE_COMPLEX){

			//_homeでもソートボタン設置プラグインを使用できるようにする
			if($page->getPageObject()->getUri() == SOYShop_Page::URI_HOME){
				$pageUrl = soyshop_get_page_url(null);
			}else{
				$pageUrl = soyshop_get_page_url($page->getPageObject()->getUri());
			}

			$query = "";

			//商品一覧ページで使う場合
			if($pageType == SOYShop_Page::TYPE_LIST){
				$args = $page->getArguments();
				for($i = 0; $i < count($args); $i++){
					if(isset($args[$i]) && strlen($args[$i])){
						$pageUrl .= "/" . htmlspecialChars($args[$i], ENT_QUOTES, "UTF-8");
					}
				}
			}else if($pageType == SOYShop_Page::TYPE_COMPLEX){
				//今のところ、なにもしない
			}else{
				SOY2::import("util.SOYShopPluginUtil");
				if(SOYShopPluginUtil::checkIsActive("custom_search_field") && isset($_GET["c_search"]) && count($_GET["c_search"])){
					foreach($_GET["c_search"] as $key => $val){
						//配列の場合
						if(is_array($val)){
							foreach($val as $v){
								if(strlen($v)){
									$query .= "&c_search[" . $key . "][]=" . htmlspecialchars($v, ENT_QUOTES, "UTF-8");
								}
							}
						//文字列の場合
						}else{
							if(strlen($val)){
								$query .= "&c_search[" . $key . "]=" . htmlspecialchars($val, ENT_QUOTES, "UTF-8");
							}
						}
					}
				}else{
					//検索ページで使う場合
					$query = (isset($_GET["type"]) && isset($_GET["q"])) ? "&type=" . trim($_GET["type"]) . "&q=" . trim($_GET["q"]) : "";
				}

			}


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
