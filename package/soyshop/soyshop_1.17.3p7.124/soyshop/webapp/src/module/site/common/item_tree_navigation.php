<?php
/**
 * 20091126 新規作成
 *
 */
function soyshop_item_tree_navigation(){

	$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
	$logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil", array(
		
	));
	
	$array = $dao->get();
	
	$tree = array();
	$root = array();

	foreach($array as $obj){
		if($obj->getParent()){
			$parent = $obj->getParent();
			if(!isset($tree[$parent])) $tree[$parent] = array();
			$tree[$parent][] = $obj;
		}else{
			$root[] = $obj;
		}
	}
	
	//URLマッピングの取得
	$config = SOYShop_DataSets::get("common.category_navigation", array());
	$urls = SOYShop_DataSets::get("site.url_mapping", array());
	
	return soyshop_item_tree_navigation_build_html($root, $tree, $config, $urls, $logic);
}
	
if(!function_exists("soyshop_item_tree_navigation_build_html")){
function soyshop_item_tree_navigation_build_html($root, $tree, $config, $urls, $logic){
	//ディフォルトの一覧ページ
	$defaultUrl = "";
	$defaultDetailUrl = "";
	foreach($urls as $map){
		if($map["type"] == "list"){
			$defaultUrl = $map["uri"];
		}
	}
	foreach($urls as $map){
		if($map["type"] == "detail"){
			$defaultDetailUrl = $map["uri"];
		}
	}
	
	//HTMLの構築
	$html = array();
	
	foreach($root as $category){
		
		$id = (isset($config[$category->getId()])) ? $config[$category->getId()]["id"] : null;
		$parameter = (isset($config[$category->getId()])) ? $config[$category->getId()]["parameter"] : null;

		$url = (!isset($urls[$id])) ? $defaultUrl : $urls[$id]["uri"];
		$href = soyshop_get_page_url($url, $category->getAlias());
		if(strlen($parameter)){
			$href .= "?" . $parameter;
		}
		
		try{
			list($items, $total) = $logic->getByCategoryIds($category->getId());
		}catch(Exception $e){
			//var_dump($e);
			$items = array();
		}
		$html[] = "<li><a href=\"$href\">" . $category->getName() . "</a>";
		
		//子カテゴリーがある場合は再帰的に追加
		if(isset($tree[$category->getId()])){
			$html[] = "\t<ul>";
			$html[] = soyshop_item_tree_navigation_build_html($tree[$category->getId()], $tree, $config, $urls, $logic);
			$html[] = "\t</ul>";
		}
		
		if(count($items) > 0){
			$html[] = "\t<ul>";
			foreach($items as $item){
				$url = (isset($urls[$item->getDetailPageId()])) ? $urls[$item->getDetailPageId()]["uri"] : $defaultDetailUrl;
				$itemLink = soyshop_get_page_url($url, $item->getAlias());
				$itemName = $item->getName();
				
				$html[] = "\t\t<li><a href=\"$itemLink\">$itemName</a></li>";
			}
			$html[] = "\t</ul>";
		}
		$html[] = "</li>";
	}
	
	return implode("\n", $html);
}
}