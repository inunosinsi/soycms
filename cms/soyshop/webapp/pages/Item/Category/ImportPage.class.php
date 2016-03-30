<?php

class ImportPage extends WebPage{
	var $dao;
	var $categories;

    function ImportPage() {
    	WebPage::WebPage();
    	$this->buildForm();

    	$this->createAdd("invalid","HTMLModel", array(
    		"visible" => (isset($_GET["invalid"]))
    	));
    	$this->createAdd("fail","HTMLModel", array(
    		"visible" => (isset($_GET["fail"]))
    	));

    	$this->createAdd("updated","HTMLModel", array(
    		"visible" => (isset($_GET["updated"]))
    	));

    	$this->createAdd("is_custom_plugin","HTMLModel", array(
			"visible" => class_exists("SOYShopPluginUtil") && (SOYShopPluginUtil::checkIsActive("common_category_customfield"))
		));

    }

    function buildForm(){
    	$this->createAdd("import_form", "HTMLForm", array(
    		 "ENCTYPE" => "multipart/form-data"
    	));
    }

    function doPost(){

    	if(!soy2_check_token() && !DEBUG_MODE){
    		SOY2PageController::jump("Item.Category.Import?invalid");
			exit;
    	}

    	set_time_limit(0);

    	$file  = $_FILES["import_file"];

		$logic = SOY2Logic::createInstance("logic.csv.ExImportLogicBase");
    	$format = $_POST["format"];
    	$item = $_POST["item"];

		$logic->setSeparator(@$format["separator"]);
		$logic->setQuote(@$format["quote"]);
		$logic->setCharset(@$format["charset"]);
		$logic->setItems($item);

		if(!$logic->checkUploadedFile($file)){
			SOY2PageController::jump("Item.Category.Import?fail");
			exit;
		}
		if(!$logic->checkFileContent($file)){
			SOY2PageController::jump("Item.Category.Import?invalid");
			exit;
		}

    	//ファイル読み込み・削除
		$fileContent = file_get_contents($file["tmp_name"]);
    	unlink($file["tmp_name"]);

		//データを行単位にばらす
		$lines = $logic->GET_CSV_LINES($fileContent);	//fix multiple lines

		//先頭行削除
    	if(isset($format["label"])) array_shift($lines);

    	//DAO
    	$categoryDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
    	$this->dao = $categoryDAO;

		//カテゴリのデータを取得
		$categoryLogic = SOY2Logic::createInstance("logic.shop.CategoryLogic");
		$categoryMap = $categoryLogic->getCategoryMap();
		//$categoryUniqueNames = $categoryLogic->getUniqueNames();

		try{
			$categoryDAO->begin();

			//データ更新
			$deletes = $inserts = $updates = array();

	    	foreach($lines as $line){
	    		if(empty($line)) continue;

				//CSV一行を配列に
	    		$obj = $logic->import($line);

				//カテゴリー名があれば操作を行う
				if(strlen($obj["name"]) > 0){

					//ルートからのカテゴリー名が一致したらそれ
					$id = array_search($obj["name"],$categoryMap);

					//もしくはカテゴリー名単体で一致するものがあればそれ
					if($id === false && strpos($obj["name"],">") === false){
						//$id = array_search($categoryLogic->unescapeCategoryName($obj["name"]),$categoryUniqueNames);
						try{
							$hit = $categoryDAO->getByName($obj["name"]);
							if(count($hit) == 1){
								$id = $hit[0]->getId();
							}
						}catch(Exception $e){
							//do nothing
						}
					}

					if($id !== false){
						//既存データ
						if($obj["id"] == "delete"){
							$deletes[] = $id;
						}else{
							$obj["id"] = $id;
							$obj["name"] = $categoryLogic->getNameFromChain($obj["name"]);

							//そのつど更新
							$categoryLogic->update($obj);
							unset($obj);
						}
					}else{
						//新規データ
						if($obj["id"] == "delete"){
							//do nothing
						}else{
							unset($obj["id"]);
							$inserts[] = $obj;
						}
					}
				}
	    	}

	    	//既存のデータが先
	    	//更新はすでにやった
	    	//削除
	    	foreach($deletes as $id){
				$categoryLogic->delete($id);
	    	}
	    	//作成は最後
	    	foreach($inserts as $obj){
				$categoryLogic->import($obj);
	    	}
			$categoryDAO->commit();

			SOY2PageController::jump("Item.Category?updated");
		}catch(Exception $e){
			$categoryDAO->rollback();
			SOY2PageController::jump("Item.Category?failed");
		}
    }
}

