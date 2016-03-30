<?php
class ExportPage extends WebPage{

	private $logic;

    function ExportPage() {
    	WebPage::WebPage();
    	$this->buildForm();

    	$this->createAdd("is_custom_plugin","HTMLModel", array(
			"visible" => class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_category_customfield"))
		));

		$this->createAdd("retry","HTMLModel", array("visible" => (isset($_GET["retry"]))));
    }

    function buildForm(){
    	$this->createAdd("export_form", "HTMLForm");
    }

    function getLabels(){
    	return array(
    		"id" => "id",

			"name" => "カテゴリ名",
			"alias" => "カテゴリID",

			"order" => "表示順",

    	);
    }

    function getCustomFieldList($flag = false){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$config = SOYShop_ItemAttributeConfig::load($flag);
		return $config;
    }

    function doPost(){
    	if(!soy2_check_token()){
    		SOY2PageController::jump("Item.Category.Export?retry");
			exit;
    	}

    	set_time_limit(0);

    	$this->logic = SOY2Logic::createInstance("logic.csv.ExImportLogicBase");
    	$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");

    	$format = $_POST["format"];
    	$item = $_POST["item"];

		$displayLabel = @$format["label"];
		$this->logic->setSeparator(@$format["separator"]);
		$this->logic->setQuote(@$format["quote"]);
		$this->logic->setCharset(@$format["charset"]);

		//出力する項目にセット
		$this->logic->setItems($item);
		$this->logic->setLabels($this->getLabels());

		//DAO: 2000ずつ取得
		$limit = 2000;//16MB弱を消費（商品データの場合）
		$step = 0;
		$dao->setLimit($limit);

		do{
			if(connection_aborted())exit;

			$dao->setOffset($step * $limit);
			$step++;

			//データ取得
			try{
		    	$categories = $dao->get();
			}catch(Exception $e){
				$categories = array();
			}

			foreach($categories as $category){
				//CSVにはカテゴリは文字列で出力
				$category->setName($category->getCategoryChain());

				//CSV(TSV)に変換
				$text = $this->logic->export($category);
				$lines[] = $text;
			}

			//出力
			$this->outputFile($lines, $displayLabel);

		}while(count($categories) >= $limit);

		exit;


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
			header("Content-Disposition: attachment; filename=soyshop_categories-".date("Ymd").".csv");
			header("Content-Type: text/csv; charset=" . $this->logic->getCharset() . ";");

			//ラベル：logic->export()の後で呼び出さないとカスタムフィールドのタイトルが入らない
			if($displayLabel){
				echo $this->logic->getHeader();
				echo "\r\n";
			}
		}
		$this->outputData($lines);
	}

	/**
	 * データ出力：改行コードはCRLF
	 */
	function outputData($lines){
		if(count($lines) > 0){
			echo implode("\r\n", $lines);
			echo "\r\n";
		}
	}



}



?>