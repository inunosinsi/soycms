<?php
/**
 * @table soyshop_category
 */
class SOYShop_Category {

    const IS_OPEN = 1;
    const NO_OPEN = 0;

    /**
     * @id
     */
    private $id;

    /**
     * @column category_name
     */
    private $name;

    /**
     * @column category_alias
     */
    private $alias;

    /**
     * @column category_order
     */
    private $order = 0;

    /**
     * @column category_parent
     */
    private $parent;

    /**
     * @column category_config
     */
    private $config;

    /**
     * @column category_is_open
     */
    private $isOpen = 1;

    function getId() {
        return $this->id;
    }
    function setId($id) {
        $this->id = $id;
    }
    function getName() {
        return $this->name;
    }
    function setName($name) {
        $this->name = $name;
    }
    function getAlias() {
        return $this->alias;
    }
    function setAlias($alias) {
        $this->alias = $alias;
    }
    function getParent() {
        return $this->parent;
    }
    function setParent($parent) {
        $this->parent = $parent;
    }
    function getConfig() {
        return $this->config;
    }
    function setConfig($config) {
        $this->config = $config;
    }

    function getIsOpen(){
        return $this->isOpen;
    }
    function setIsOpen($isOpen){
        $this->isOpen = $isOpen;
    }

    function getOrder() {
        return $this->order;
    }
    function setOrder($order) {
        $this->order = $order;
    }


       /* 以下 便利メソッド */

       //多言語化プラグインを考慮した商品名の取得
    function getOpenCategoryName(){
        static $lngCnf;
        if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");
        if(!defined("SOYSHOP_MAIL_LANGUAGE")) define("SOYSHOP_MAIL_LANGUAGE", SOYSHOP_PUBLISH_LANGUAGE);

		if(SOYSHOP_MAIL_LANGUAGE != "jp"){
            if(is_null($lngCnf)) {
				SOY2::import("module.plugins.util_multi_language.util.UtilMultiLanguageUtil");
				$lngCnf = UtilMultiLanguageUtil::getConfig();
			}

            try{
                $name = (is_numeric($this->id)) ? soyshop_get_category_attribute_value($this->id, "category_name_" . SOYSHOP_MAIL_LANGUAGE, "string") : "";
                if(strlen($name)) return $name;
            }catch(Exception $e){
                try{
					$lng = (isset($lngCnf[SOYSHOP_MAIL_LANGUAGE]["prefix"])) ? $lngCnf[SOYSHOP_MAIL_LANGUAGE]["prefix"] : SOYSHOP_MAIL_LANGUAGE;
					$name = (is_numeric($this->id)) ? soyshop_get_category_attribute_value($this->id, "category_name_" . $lng, "string") : "";
	                if(strlen($name)) return $name;
				}catch(Exception $e){
					//
				}
            }
        }
        return (is_string($this->name)) ? $this->name : "";
    }

    function getAttachmentsPath(){
        $dir = SOYSHOP_SITE_DIRECTORY . "files/category-" . $this->getId() . "/";
        if(!file_exists($dir)) mkdir($dir);
        return $dir;
    }

    function getAttachmentsUrl(){
        return soyshop_get_site_path() . "files/category-" . $this->getId() . "/";
    }

    function getNameWithStatus(){
        if($this->getIsOpen() == self::IS_OPEN){
            return $this->getOpenCategoryName();
        }else{
            return $this->getOpenCategoryName() . "(非公開)";
        }
    }

    /**
     * 添付ファイルを取得
     */
    function getAttachments(){
        $dir = $this->getAttachmentsPath();
        $url = $this->getAttachmentsUrl();
        $files = scandir($dir);
        $res = array();

        foreach($files as $file){
            if($file[0] == ".") continue;
            $res[] = $url . $file;
        }

        return $res;
    }

    /**
     * 親カテゴリーを > でつなげたもの
     */
    function getCategoryChain(){
        return SOY2Logic::createInstance("logic.shop.CategoryLogic")->getCategoryChain($this->id);
    }
}
