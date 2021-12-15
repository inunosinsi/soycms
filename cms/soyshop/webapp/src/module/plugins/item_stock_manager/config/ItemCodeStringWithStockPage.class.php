<?php

class ItemCodeStringWithStockPage extends WebPage{

	private $configObj;

	function __construct(){
	}

	function doPost(){
		if(soy2_check_token()){
			set_time_limit(0);
	        $file  = $_FILES["import_file"];

			$logic = SOY2Logic::createInstance("logic.shop.item.ExImportLogic");

			//ファイル読み込み・削除
	        $fileContent = file_get_contents($file["tmp_name"]);
	        unlink($file["tmp_name"]);

	        //データを行単位にばらす
	        $lines = $logic->GET_CSV_LINES($fileContent);    //fix multiple lines

	        //先頭行削除
	        //array_shift($lines);

			//データ更新
	        foreach($lines as $line){
	            if(empty($line)) continue;

				$v = explode(",", $line);
				if(count($v) < 2) continue;

				$stock = str_replace(",", "", $v[1]);
				if(!is_numeric($stock)) continue;

				$code = trim($v[0]);
				$item = soyshop_get_item_object_by_code($code);
				if(is_null($item->getId())) continue;

				//登録されている在庫数と同じ場合は次へ
				if($item->getStock() == $stock) continue;

				$item->setStock($stock);
				try{
					self::dao()->update($item);
				}catch(Exception $e){
					var_dump($e);
				}
			}
		}
	}

	function execute(){
		parent::__construct();

		$this->addForm("form", array(
			"ENCTYPE" => "multipart/form-data"
		));
	}


	private function dao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		return $dao;
	}

	function setConfigObj($configObj){
		$this->configObj = $configObj;
	}
}
