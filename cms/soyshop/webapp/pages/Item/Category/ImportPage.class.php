<?php

class ImportPage extends WebPage{
    var $dao;
    var $categories;

    function __construct() {
        parent::__construct();

        DisplayPlugin::toggle("fail", isset($_GET["fail"]));
        DisplayPlugin::toggle("invalid", isset($_GET["invalid"]));

        self::buildForm();
    }

    private function buildForm(){
        $this->addForm("import_form", array(
             "ENCTYPE" => "multipart/form-data"
        ));

        //多言語化
        $this->createAdd("multi_language_item_name_list", "_common.Category.MultiLanguageCategoryNameListComponent", array(
            "list" => self::getLanguageList()
        ));

        $this->createAdd("customfield_list", "_common.Item.CustomFieldImExportListComponent", array(
            "list" => self::getCustomFieldList()
        ));
    }

    function doPost(){

        if(!soy2_check_token() && !DEBUG_MODE){
            SOY2PageController::jump("Item.Category.Import?invalid");
            exit;
        }

        set_time_limit(0);

        $file  = $_FILES["import_file"];

        $logic = SOY2Logic::createInstance("logic.shop.category.ExImportLogic");
        $format = $_POST["format"];
        $item = $_POST["item"];

        $logic->setSeparator(@$format["separator"]);
        $logic->setQuote(@$format["quote"]);
        $logic->setCharset(@$format["charset"]);
        $logic->setItems($item);
        $logic->setCustomFields($this->getCustomFieldList(true));
        $logic->setLanguageItems(self::getLanguageList());

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
        $lines = $logic->GET_CSV_LINES($fileContent);    //fix multiple lines

        //先頭行削除
        if(isset($format["label"])) array_shift($lines);

        //DAO
        $categoryDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
        $this->dao = $categoryDAO;
        $attributeDAO = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");

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
                list($obj, $attributes) = $logic->import($line);

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

                    foreach($attributes as $key => $value){
                        try{
                            $attributeDAO->delete($id, $key);
                        }catch(Exception $e){
                            //
                        }

                        $attr = new SOYShop_CategoryAttribute();
                        $attr->setCategoryId($id);
                        $attr->setFieldId($key);
                        $attr->setValue($value);
                        try{
                            $attributeDAO->insert($attr);
                        }catch(Exception $e){
                            //var_dump($e);
                        }
                        /** @ToDoカスタムフィールドのソートは必要になったら **/
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

            SOY2PageController::jump("Item.Category.Import?updated");
        }catch(Exception $e){
            $categoryDAO->rollback();
            SOY2PageController::jump("Item.Category.Import?failed");
        }
    }

    private function getLanguageList(){
        if(!SOYShopPluginUtil::checkIsActive("util_multi_language")) return array();
        SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
        return UtilMultiLanguageUtil::allowLanguages();
    }

    function getCustomFieldList($flag = false){
        $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryAttributeDAO");
        $config = SOYShop_CategoryAttributeConfig::load($flag);
        return $config;
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("カテゴリCSVインポート", array("Item" => "商品管理", "Item.Category" => "カテゴリ管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Item.FooterMenu.CategoryFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}
}
