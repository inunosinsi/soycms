<?php

class CustomConfigFormPage extends WebPage{

    private $configObj;

    private $attrDao;
    private $fieldTable = array();
    private $optionTable = array();
    private $customTable = array(); //カスタムサーチフィールド用
    private $itemId;
    private $lang;
    private $options = array();

    function __construct(){
        SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
        SOY2::import("util.SOYShopPluginUtil");
        $this->attrDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
    }

    function doPost(){

        if(soy2_check_token()){
            //カスタムフィールド、カスタムオプション
            if(isset($_POST["LanguageConfig"])){
                $indexes = array("LanguageConfig", "custom_field");
                foreach($indexes as $index){
                    foreach($_POST[$index] as $key => $value) {
                        if(strlen($value)){
                            $attr = new SOYShop_ItemAttribute();
                            $attr->setItemId($this->itemId);
                            $attr->setFieldId($key);
                            $attr->setValue($value);

                            try{
                                $this->attrDao->insert($attr);
                            }catch(Exception $e){
                                try{
                                    $this->attrDao->update($attr);
                                }catch(Exception $e){
                                    //
                                }
                            }
                        }else{	//削除
							try{
								$this->attrDao->delete($this->itemId, $key);
							}catch(Exception $e){
								//
							}
						}
                    }
                }
            }

            //カスタムサーチフィールド
            if(isset($_POST["custom_search"]) && count($_POST["custom_search"])){
                SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic")->save($this->itemId, $_POST["custom_search"], $this->lang);
            }

            SOY2PageController::jump("Config.Detail?plugin=util_multi_language&item_id=" . $this->itemId . "&language=" . $this->lang . "&updated");
        }

        if(isset($_POST["upload"])){
            $urls = $this->uploadImage();

            echo "<html><head>";
            echo "<script type=\"text/javascript\">";
            if($urls !== false){
                foreach($urls as $url){
                    echo 'window.parent.ImageSelect.notifyUpload("' . $url . '");';
                }
            }else{
                echo 'alert("failed");';
            }
            echo "</script></head><body></body></html>";
            exit;
        }

        SOY2PageController::jump("Config.Detail?plugin=util_multi_language&item_id=" . $this->itemId . "&language=" . $this->lang . "&failed");
    }

    function execute(){

        //多言語化のプレフィックスを取得できない場合
        if(!isset($_GET["item_id"])) SOY2PageController::jump("Item");
        if(!isset($_GET["language"])) SOY2PageController::jump("Item.Detail." . $_GET["item_id"]);

        $this->itemId = $_GET["item_id"];
        $this->lang = $_GET["language"];

        //商品を取得
        $item = self::getById($this->itemId);

        //商品情報を取得できない場合は商品一覧に遷移
        if(is_null($item->getId())) SOY2PageController::jump("Item");

        //日本語用のものだけ集める
        self::setLangFieldList();

        parent::__construct();

        $this->addLink("item_name_link", array(
            "text" => $item->getName(),
            "link" => SOY2PageController::createLink("Item.Detail." . $this->itemId)
        ));

        $this->addLink("confirm_link", array(
            "link" => self::getConfirmLink(),
            "target" => "_blank"
        ));

        $this->addLabel("item_name_with_lang", array(
            "text" => $item->getName() . " - " . UtilMultiLanguageUtil::getLanguageText($this->lang)
        ));

        $this->addForm("form");

        DisplayPlugin::toggle("customfield", count($this->fieldTable));
        $this->addLabel("customfield", array(
            "html" => (count($this->fieldTable)) ? self::buildForm() : ""
        ));

        $options = SOYShop_DataSets::get("item_option", null);
        if(isset($options)) $this->options = soy2_unserialize($options);

        DisplayPlugin::toggle("option", count($this->options));
        $this->addLabel("option", array(
            "html" => (count($this->options)) ? self::buildOptionForm() : ""
        ));

        DisplayPlugin::toggle("customsearch", count($this->customTable));
        $this->addLabel("customsearch", array(
            "html" => (count($this->customTable)) ? self::buildCustomSearchForm() : ""
        ));

        //upload
        $this->addForm("upload_form");

        $this->createAdd("image_list","_common.Item.ItemImageListComponent", array(
            "list" => $item->getAttachments()
        ));
    }

    private function setLangFieldList(){
        foreach(SOYShop_ItemAttributeConfig::load() as $field){
            $accord = false;

            foreach(UtilMultiLanguageUtil::getConfig() as $conf){
                if(isset($conf["prefix"]) && strlen($conf["prefix"])){
                    if(strpos($field->getFieldId(), "_" . $conf["prefix"]) !== false) $accord = true;
                }
            }
            //一致してないfieldIdを集める
            if(!$accord){
                $this->fieldTable[] = $field;
            }
        }

        //カスタムサーチフィールド
        if(SOYShopPluginUtil::checkIsActive("custom_search_field")){
            SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
            $this->customTable = CustomSearchFieldUtil::getConfig();
        }
    }

    private function getById($itemId){
        $itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
        try{
            return $itemDao->getById($itemId);
        }catch(Exception $e){
            return new SOYShop_Item();
        }
    }

    private function getConfirmLink(){
        try{
            $item = SOY2DAOFactory::create("shop.SOYShop_ItemDAO")->getById($this->itemId);
        }catch(Exception $e){
            return null;
        }

        try{
            $page = SOY2DAOFactory::create("site.SOYShop_PageDAO")->getById($item->getDetailPageId());
        }catch(Exception $e){
            return null;
        }

        return soyshop_get_page_url($page->getUri(), $item->getAlias()) . "?language=" . $this->lang;
    }

    private function buildForm(){
        $html = array();

        $key = "item_name_" . $this->lang;

        //先頭に商品名(多言語)のフォームを追加
        $html[] = "<dt>商品名(" . $this->lang . ")</dt>";
        $html[] = "<dd>";
        try{
            $field = $this->attrDao->get($this->itemId, $key);
        }catch(Exception $e){
            $field = new SOYShop_ItemAttribute();
        }
        $html[] = "<input name=\"LanguageConfig[" . $key . "]\" value=\"" . $field->getValue() . "\" type=\"text\" class=\"form-control\">";
        $html[] = "</dd>";

        $config = UtilMultiLanguageUtil::getConfig();
        $langConf = $config[$this->lang];

        if(count($this->fieldTable) > 0 && strlen($langConf["prefix"])){
            foreach($this->fieldTable as $field){
                try{
                    $obj = $this->attrDao->get($this->itemId, $field->getFieldId() . "_" . $langConf["prefix"]);
                }catch(Exception $e){
                    $obj = new SOYShop_ItemAttribute();
                }

                if(is_null($obj->getFieldId()) || strlen($obj->getValue()) === 0){
                    try{
                        $obj = $this->attrDao->get($this->itemId, $field->getFieldId() . "_" . $this->lang);
                    }catch(Exception $e){
                        $obj = new SOYShop_ItemAttribute();
                    }
                }

                $field->setFieldId($field->getFieldId() . "_" . $this->lang);
                $html[] = $field->getForm($obj->getValue(), $obj->getExtraValues());
            }
        }

        return implode("\n", $html);
    }

    private function buildOptionForm(){
        $html = array();

        $accord = false;

        foreach($this->options as $key => $option){
            foreach(UtilMultiLanguageUtil::getConfig() as $conf){
                if(isset($conf["prefix"]) && strlen($conf["prefix"])){
                    if(strpos($key, "_" . $conf["prefix"]) !== false) $accord = true;
                }
            }
            //一致してないoptionIdを集める
            if(!$accord){
                $option["type"] = (isset($option["type"])) ? $option["type"] : "select";
                $this->optionTable[$key] = $option;
            }
        }

        $config = UtilMultiLanguageUtil::getConfig();
        $langConf = $config[$this->lang];
        if(strlen($langConf["prefix"])){

            $prefix = "item_option_";

            foreach($this->optionTable as $key => $option){
                try{
                    $obj = $this->attrDao->get($this->itemId, $prefix . $key . "_" . $langConf["prefix"]);
                }catch(Exception $e){
                    $obj = new SOYShop_ItemAttribute();
                }

                if(is_null($obj->getFieldId()) || strlen($obj->getValue()) === 0){
                    try{
                        $obj = $this->attrDao->get($this->itemId, $prefix . $key . "_" . $this->lang);
                    }catch(Exception $e){
                        $obj = new SOYShop_ItemAttribute();
                    }
                }
                $obj->setFieldId($prefix . $key . "_" . $langConf["prefix"]);
                $html[] = self::buildTextArea($obj, $key);
            }
        }

        return implode("\n", $html);
    }

    private function buildTextArea($obj, $key){

        //古いバージョンから使用していて、typeの値がない場合はselectにする
//        $type = (isset($value["type"])) ? $value["type"] : "select";

        $type = ($this->optionTable[$key]["type"] == "select") ? "セレクトボックス" : "ラジオ";
        $optionName = (isset($this->optionTable[$key]["name_" . $this->lang])) ? $this->optionTable[$key]["name_" . $this->lang] : $this->optionTable[$key]["name"];

        $html = array();

        $html[] = "<dt>";
        $html[] = "<label for=\"" . $obj->getFieldId() . "\">オプション名：" . $optionName . "&nbsp;&nbsp;タイプ：" . $type . "</label>";
        $html[] = "</dt>";
        $html[] = "<dd>";
        $html[] = "<textarea name=\"custom_field[" . $obj->getFieldId() . "]\">" . $obj->getValue() . "</textarea>";
        $html[] = "</dd>";

        return implode("\n", $html);
    }

    private function buildCustomSearchForm(){
        $html = array();

        if(count($this->customTable)){
            //登録されている値を取得
            SOY2::import("module.plugins.custom_search_field.component.FieldFormComponent");
            $values = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic")->getByItemId($this->itemId, $this->lang);

            // @ToDo HTMLを組み立てる
            foreach($this->customTable as $key => $field){
                $html[] = "<dt>" . $field["label"] . " (" . CustomSearchFieldUtil::PLUGIN_PREFIX . ":id=\"" . $key . "\")</dt>";
                $html[] = "<dd>";

                $value = (isset($values[$key])) ? $values[$key] : null;
                $html[] = FieldFormComponent::buildForm($key, $field, $value, $this->lang);
                $html[] = "</dd>";
            }
        }

        return implode("\n", $html);
    }

    function setConfigObj($configObj){
        $this->configObj = $configObj;
    }

    /**
     * 画像のアップロード
     *
     * @return url
     * 失敗時には false
     */
    function uploadImage(){
        $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
        $item = $dao->getById($this->itemId);

        $urls = array();

        foreach($_FILES as $upload){
            foreach($upload["name"] as $key => $value){
                //replace invalid filename
                $upload["name"][$key] = strtolower(str_replace("%","",rawurlencode($upload["name"][$key])));

                $pathinfo = pathinfo($upload["name"][$key]);
                if(!isset($pathinfo["filename"]))$pathinfo["filename"] = str_replace("." . $pathinfo["extension"], $pathinfo["basename"]);

                //get unique file name
                $counter = 0;
                $filepath = "";
                $name = "";
                while(true){
                    $name = ($counter > 0) ? $pathinfo["filename"] . "_" . $counter . "." . $pathinfo["extension"] : $pathinfo["filename"] . "." . $pathinfo["extension"];
                    $filepath = $item->getAttachmentsPath() . $name;

                    if(!file_exists($filepath)){
                        break;
                    }
                    $counter++;
                }

                //一回でも失敗した場合はfalseを返して終了（rollbackは無し）
                $result = move_uploaded_file($upload["tmp_name"][$key], $filepath);
                @chmod($filepath,0604);

                if($result){
                    $url = $item->getAttachmentsUrl() . $name;
                    $urls[] = $url;
                }else{
                    return false;
                }
            }
        }

        return $urls;
    }
}
