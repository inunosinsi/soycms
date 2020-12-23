<?php

class SOYShopSitemapBase implements SOY2PluginAction{

	/**
	 * @return array
	 * array(
	 *	array("loc" => ページのuri(必須), "priority" => 0.8, "lastmod" => タイムスタンプ)
	 * )
	 * 上記の形式で返す
	 */
	function items(){
		return array();
	}
}

class SOYShopSitemapDeletageAction implements SOY2PluginDelegateAction{

	private $items = array();

	function run($extetensionId, $moduleId, SOY2PluginAction $action){
		$items = $action->items();
		if(is_array($items) && count($items)){
			foreach($items as $item){
				if(!isset($item["loc"])) continue;	// locは必須
				$this->items[] = $item;
			}
		}
	}

	function getItems(){
		return $this->items;
	}
}
SOYShopPlugin::registerExtension("soyshop.sitemap","SOYShopSitemapDeletageAction");
