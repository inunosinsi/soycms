<?php

class SettingPage extends WebPage{

    private $configObj;
    private $fieldId;

    private $config;
    private $dbLogic;

    private $categories = array();

    function __construct(){
        $this->fieldId = (isset($_GET["field_id"])) ? $_GET["field_id"] : null;
        SOY2::import("module.plugins.custom_search_field.util.CustomSearchFieldUtil");
        $this->config = CustomSearchFieldUtil::getConfig();
        $this->dbLogic = SOY2Logic::createInstance("module.plugins.custom_search_field.logic.DataBaseLogic");
        $this->categories = soyshop_get_category_objects();
        SOY2::import("domain.shop.SOYShop_Item");

        //言語設定
        if(!defined("SOYSHOP_PUBLISH_LANGUAGE")){
            $lang = self::getParameter("language_condition");
            if(is_null($lang)) $lang = "jp";
            define("SOYSHOP_PUBLISH_LANGUAGE", $lang);
        }
    }

    function doPost(){

        if(soy2_check_token()){

            //言語切り替え
            if(isset($_POST["language"])){
                self::setParameter("language_condition", $_POST["language"]);
                $this->configObj->redirect("collective&field_id=" . $this->fieldId);
            }

            if(isset($_POST["set"])){

                if(count($_POST["items"])){
                    foreach($_POST["items"] as $itemId){
                        $values = (isset($_POST["custom_search"]) && count($_POST["custom_search"])) ? $_POST["custom_search"] : null;
                        $customs = $this->dbLogic->getByItemId($itemId);
                        foreach($values as $key => $v){
                            $customs[$key] = $v;
                        }
                        $this->dbLogic->save($itemId, $customs, SOYSHOP_PUBLISH_LANGUAGE);
                    }
                }

                $this->configObj->redirect("collective&field_id=" . $this->fieldId . "&updated");
            }
        }

    }

    function execute(){

        MessageManager::addMessagePath("admin");

        parent::__construct();

		$this->addLabel("nav", array(
			"html" => LinkNaviAreaComponent::build()
		));

        self::buildLanguageForm();
        self::buildSearchForm();

        $this->addForm("form");

        $field = $this->config[$this->fieldId];
        $this->addLabel("field_label", array(
            "text" => (isset($field["label"])) ? $field["label"] : ""
        ));

        $this->addLabel("prefix", array(
            "text" => CustomSearchFieldUtil::PLUGIN_PREFIX
        ));

        $this->addLabel("field_id", array(
            "text" => $this->fieldId
        ));

        $this->addLabel("csf_form", array(
            "html" => self::buildForm($field)
        ));

        $this->createAdd("item_list", "_common.Item.ItemListComponent", array(
            "list" => self::getItems(),
            "detailLink" => SOY2PageController::createLink("Item.Detail.")
        ));
    }

    private function buildLanguageForm(){
        $this->addForm("lang_form");

        $this->addSelect("language", array(
            "name" => "language",
            "options" => self::getLanguageList(),
            "selected" => SOYSHOP_PUBLISH_LANGUAGE
        ));
    }

    private function getLanguageList(){
        SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
        if(SOYShopPluginUtil::checkIsActive("util_multi_language")){
            return UtilMultiLanguageUtil::allowLanguages();
        }else{
            return array(UtilMultiLanguageUtil::LANGUAGE_JP => "日本語");
        }
    }

    private function buildSearchForm(){

        //リセット
        if(isset($_POST["search_condition"])){
            foreach($_POST["search_condition"] as $key => $value){
                if(is_array($value)){
                    //
                }else{
                    if(!strlen($value)){
                        unset($_POST["search_condition"][$key]);
                    }
                }
            }
        } else if(isset($_GET["reset"])) {
            self::setParameter("search_condition", null);
            $cnd = array();
        }

        if(isset($_POST["search"]) && !isset($_POST["search_condition"])){
            self::setParameter("search_condition", null);
            $cnd = array();
        }else{
            $cnd = self::getParameter("search_condition");
        }
        //リセットここまで

        $this->addModel("search_area", array(
            "style" => (isset($cnd) && count($cnd)) ? "display:inline;" : "display:none;"
        ));

        $this->addForm("search_form");

        $this->addLabel("csf_label", array(
            "text" => $this->config[$this->fieldId]["label"]
        ));

        $this->addCheckBox("nothing_check", array(
            "name" => "search_condition[nothing]",
            "value" => 1,
            "selected" => (isset($cnd["nothing"])),
            "label" => "値の設定なし"
        ));

        $this->addLabel("csf_cnd_form", array(
            "html" => self::buildSearchConditionForm($this->config[$this->fieldId], $cnd)
        ));

        foreach(array("item_name", "item_code") as $t){
            $this->addInput("search_" . $t, array(
                "name" => "search_condition[" . $t . "]",
                "value" => (isset($cnd[$t])) ? $cnd[$t] : ""
            ));
        }

        $opts = array();
        foreach($this->categories as $cat){
            $opts[$cat->getId()] = $cat->getOpenCategoryName();
        }
        $this->addSelect("search_item_category", array(
            "name" => "search_condition[item_category]",
            "options" => $opts,
            "selected" => (isset($cnd["item_category"])) ? $cnd["item_category"] : null
        ));

        $this->addCheckBox("search_item_is_open", array(
            "name" => "search_condition[item_is_open][]",
            "value" => SOYShop_Item::IS_OPEN,
            "selected" => (isset($cnd["item_is_open"]) && in_array(SOYShop_Item::IS_OPEN, $cnd["item_is_open"])),
            "label" => "公開"
        ));

        $this->addCheckBox("search_item_no_open", array(
            "name" => "search_condition[item_is_open][]",
            "value" => SOYShop_Item::NO_OPEN,
            "selected" => (isset($cnd["item_is_open"]) && in_array(SOYShop_Item::NO_OPEN, $cnd["item_is_open"])),
            "label" => "非公開"
        ));
    }

    private function buildForm($field){
        SOY2::import("module.plugins." . $this->configObj->getModuleId() . ".component.FieldFormComponent");
        $h = array();
        $h[] = FieldFormComponent::buildForm($this->fieldId, $field, null, SOYSHOP_PUBLISH_LANGUAGE);
        return implode("\n", $h);
    }

    private function buildSearchConditionForm($field, $cnd){
        SOY2::import("module.plugins." . $this->configObj->getModuleId() . ".component.FieldFormComponent");
        $h = array();
        $h[] = FieldFormComponent::buildSearchConditionForm($this->fieldId, $field, $cnd, SOYSHOP_PUBLISH_LANGUAGE);
        return implode("\n", $h);
    }

    private function getItems(){
        $searchLogic = SOY2Logic::createInstance("module.plugins." . $this->configObj->getModuleId() . ".logic.admin.SearchLogic", array("fieldId" => $this->fieldId));
        $searchLogic->setLimit(50);    //仮
        $searchLogic->setCondition(self::getParameter("search_condition"));
        return $searchLogic->get();
    }

    private function getParameter($key){
        if(array_key_exists($key, $_POST)){
            $value = $_POST[$key];
            self::setParameter($key,$value);
        }else{
            $value = SOY2ActionSession::getUserSession()->getAttribute("Custom.Search:" . $key);
        }
        return $value;
    }
    private function setParameter($key,$value){
        SOY2ActionSession::getUserSession()->setAttribute("Custom.Search:" . $key, $value);
    }

    function setConfigObj($configObj){
        $this->configObj = $configObj;
    }
}
