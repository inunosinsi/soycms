<?php
class ExportPage extends WebPage{

	var $logic;

	function __construct() {
		WebPage::__construct();
		$this->buildForm();
	}

	function buildForm(){
		$this->addForm("export_form");

		//カスタムフィールドリストを表示する
		$this->createAdd("customfield_list","_common.Item.CustomFieldImExportListComponent", array(
			"list" => $this->getCustomFieldList()
		));
		
		//カスタムサーチフィールドリストを表示する
		$this->createAdd("custom_search_field_list", "_common.Item.CustomSearchFieldImExportListComponent", array(
			"list" => $this->getCustomSearchFieldList()
		));
		
		//商品オプションリストを表示する
		$this->createAdd("item_option_list", "_common.Item.ItemOptionImExportListComponent", array(
			"list" => $this->getItemOptionList()
		));

		SOYShopPlugin::load("soyshop.item.csv");
		$delegate = SOYShopPlugin::invoke("soyshop.item.csv");

		$this->createAdd("plugin_list", "_common.Item.PluginCSVListComponent", array(
			"list" => $delegate->getModules()
		));

		//カテゴリ
		$this->createAdd("category_tree", "_base.MyTreeComponent", array(
			"list" => SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")->get(),
		));

		$this->addModel("retry", array("visible" => (isset($_GET["retry"]))));

	}

	function getLabels(){
		return array(
			"id" => "id",

			"isOpen" => "公開状態",

			"name" => "商品名",
			"alias" => "URL",
			"code" => "商品コード",

			"config[list_price]" => "定価",
			"price" => "通常価格",
			"salePrice" => "セール価格",
			"saleFlag" => "セール中",

			"stock" => "在庫",
			"category" => "カテゴリ",
			"type" => "商品タイプ",
			"detailPageId" => "商品詳細ページID",
			"config[keywords]" => "キーワード",
			"config[description]" => "説明",
			"config[image_small]" => "商品画像（小）",
			"config[image_large]" => "商品画像（大）",
			
			"orderPeriodStart" => "販売開始日",
			"orderPeriodEnd" => "販売終了日",
			"openPeriodStart" => "公開開始日",
			"openPeriodEnd" => "公開終了日",
		);
	}

	function getCustomFieldList($flag = false){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$config = SOYShop_ItemAttributeConfig::load($flag);
		return $config;
	}
	
	function getCustomSearchFieldList(){
		SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
		return CustomSearchFieldUtil::getConfig();
	}
	
	function getItemOptionList(){
		return SOY2Logic::createInstance("module.plugins.common_item_option.logic.ItemOptionLogic")->getOptions();
	}

	function doPost(){
    	if(!soy2_check_token()){
    		SOY2PageController::jump("Item.Export?retry");
			exit;
    	}

		set_time_limit(0);

		//準備
		$logic = SOY2Logic::createInstance("logic.shop.item.ExImportLogic");
		$this->logic = $logic;

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

		//パラメータ
		$category_id = $_POST["category"];

		$format = $_POST["format"];
		$item = $_POST["item"];

		$displayLabel = @$format["label"];
		$logic->setSeparator(@$format["separator"]);
		$logic->setQuote(@$format["quote"]);
		$logic->setCharset(@$format["charset"]);

		//出力する項目にセット
		$logic->setItems($item);
		$logic->setLabels($this->getLabels());
		$logic->setCustomFields($this->getCustomFieldList(true));
		$logic->setCustomSearchFields($this->getCustomSearchFieldList());
		$logic->setItemOptions($this->getItemOptionList());

		//Plugin soyshop.item.csv
		SOYShopPlugin::load("soyshop.item.csv");
		$delegate = SOYShopPlugin::invoke("soyshop.item.csv", array("mode" => "export"));
		$logic->setModules($delegate->getModules());

		//カテゴリの親子取得
		$categoryDao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
		$mappings = $categoryDao->getMapping();

		//DAO: 2000ずつ取得
		$limit = 2000;//16MB弱を消費
		$step = 0;
		$dao->setLimit($limit);

		do{
			if(connection_aborted())exit;

			$dao->setOffset($step * $limit);
			$step++;

			//データ取得
			try{
				if(strlen($category_id) > 0 && isset($mappings[$category_id])){
					$items = $dao->getByCategories($mappings[$category_id]);
				}else{
					$items = $dao->get();
				}
			}catch(Exception $e){
				$items = array();
			}

			//CSV(TSV)に変換
			$lines = $this->itemToCSV($items);

			//出力
			$this->outputFile($lines, $displayLabel);			

		}while(count($items) >= $limit);

		exit;
	}

	/**
	 * 商品データをCSVに変換する
	 * カテゴリーは">"でつないだ文字列にする。
	 */
	function itemToCSV($items){
		static $categories;

		if(!$categories){
			//カテゴリ全部取得
			$categoryDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
			$categories = $categoryDAO->get();
		}

		$lines = array();
		foreach($items as $item){
			//CSVにはカテゴリは文字列で出力
			$category = $item->getCategory();
			
			if(strlen($category) > 0){
				$categoryChain = (isset($categories[$category])) ? $categories[$category]->getCategoryChain() : "";
				$item->setCategory($categoryChain);
			}
			
			//販売日の変換
			$item->setOrderPeriodStart(soyshop_convert_date_string($item->getOrderPeriodStart()));
			$item->setOrderPeriodEnd(soyshop_convert_date_string($item->getOrderPeriodEnd()));
			
			//公開日
			$item->setOpenPeriodStart(soyshop_convert_date_string($item->getOpenPeriodStart()));
			$item->setOpenPeriodEnd(soyshop_convert_date_string($item->getOpenPeriodEnd()));

			//CSVに変換
			$lines[] = $this->logic->export($item);
		}

		return $lines;
	}

	/**
	 * ファイル出力：改行コードはCRLF
	 */
	function outputFile($lines, $displayLabel){
		static $headerSent = false;
		if(!$headerSent){
			$headerSent = true;
			header("Cache-Control: no-cache");
			header("Pragma: no-cache");
			header("Content-Disposition: attachment; filename=soyshop_items-".date("Ymd").".csv");
			header("Content-Type: text/csv; charset=" . $this->logic->getCharset() . ";");

			//ラベル：logic->export()の後で呼び出さないとカスタムフィールドのタイトルが入らない
			if($displayLabel){
				echo $this->logic->getHeader() . "\r\n";
			}
		}

		echo implode("\r\n", $lines) . "\r\n";
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.pack.js",
		);
	}

	function getCSS(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.css",
			$root . "tree.css",
		);
	}
}
?>