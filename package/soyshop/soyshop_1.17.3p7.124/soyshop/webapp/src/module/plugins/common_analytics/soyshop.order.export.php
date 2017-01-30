<?php
/*
 */
SOY2::import("module.plugins.common_analytics.util.AnalyticsPluginUtil");
class CommonAnalyticsExport extends SOYShopOrderExportBase{
	
	private $csvLogic;

	/**
	 * 検索結果一覧に表示するメニューの表示文言
	 */
	function getMenuTitle(){
		return "統計";
	}

	/**
	 * 検索結果一覧に表示するメニューの説明
	 */
	function getMenuDescription(){
		include_once(dirname(__FILE__) . "/form/AnalyticsPluginFormPage.class.php");
		$form = SOY2HTMLFactory::createInstance("AnalyticsPluginFormPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * export エクスポート実行
	 */
	function export($orders){
		
		$mode = (isset($_POST["AnalyticsPlugin"]["type"])) ? $_POST["AnalyticsPlugin"]["type"] : "month";
		$class = "Analytics_" . ucfirst($mode) . "Page";
		
		include_once(dirname(__FILE__) . "/template/_common.php");
		$html = file_get_contents(dirname(__FILE__) . "/template/" . $mode . ".html");
		include_once(dirname(__FILE__) . "/template/" . $mode . ".php");
		
		$page = SOY2HTMLFactory::createInstance($class, array(
			"arguments" => array("build_print", $html),
		));
		
		$page->setTitle(AnalyticsPluginUtil::getTitle());
		$page->build_print();
		return $page->display();
	}

}

SOYShopPlugin::extension("soyshop.order.export","common_analytics","CommonAnalyticsExport");
?>