<?php

class IndexPage extends WebPage{

    function doPost(){

        if(soy2_check_token()){

            $categoryDao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");

            if(count($_POST["categories"])){
                $catIds = $_POST["categories"];

                foreach($catIds as $catId){
					$category = soyshop_get_category_object($catId);
                    if(is_null($category->getId())) continue;

                    //公開かどうか
                    if(isset($_POST["open_change"])){
                        $category->setIsOpen((int)$_POST["Open"]);
                    }else if(isset($_POST["parent_change"])){
                        $parentId = (isset($_POST["Parent"]) && (int)$_POST["Parent"] > 0) ? (int)$_POST["Parent"] : null;
                        $category->setParent($parentId);
                    }

                    try{
                        $categoryDao->updateImpl($category);
                    }catch(Exception $e){
                        //
                    }
                }

            }else if(isset($_POST["order_change"])){
                foreach($_POST["Order"] as $catId => $o){
					$category = soyshop_get_category_object($catId);
                    if(is_null($category->getId())) continue;

                    if(isset($o) && is_numeric($o) && $o > 0){
                        $category->setOrder($o);
                    }else {
                        $category->setOrder(null);
                    }

                    try{
                        $categoryDao->updateImpl($category);
                    }catch(Exception $e){
                        //
                    }
                }
            }

            //カテゴリーツリーの再構築
            unset($category);
            $categoryDao->buildMapping();
            SOY2PageController::jump("Item.Category.Setting?successed");
        }

        SOY2PageController::jump("Item.Category.Setting?failed");
    }

    function __construct(){
        parent::__construct();

        $this->addForm("form");

        $selectParent = self::getParameter("parent");

        $this->addSelect("parent_select", array(
            "options" => soyshop_get_category_list(),
            "selected" => $selectParent,
            "onchange" => "redirectAfterSelect(this);"
        ));

        $this->createAdd("category_list", "_common.Category.CategoryListComponent", array(
            "list" => self::getCategories($selectParent),
            "categoryDao" => SOY2DAOFactory::create("shop.SOYShop_CategoryDAO")
        ));

        $this->addSelect("open_select", array(
            "name" => "Open",
            "options" => array(SOYShop_Category::IS_OPEN => "公開", SOYShop_Category::NO_OPEN => "非公開")
        ));

        $this->addSelect("parent_change_select", array(
            "name" => "Parent",
            "options" => soyshop_get_category_list()
        ));
    }

    private function getCategories($selectParent){
        $categoryDao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
        $sql = "SELECT * FROM soyshop_category ";
        $binds = array();

        if(is_null($selectParent) || (int)$selectParent < 0){
            //
        }else{
            $sql .= "WHERE category_parent = :parent ";
            $binds[":parent"] = $selectParent;
        }

        $sql .= "ORDER BY category_order ASC";

        try{
            $res = $categoryDao->executeQuery($sql, $binds);
        }catch(Exception $e){
            return array();
        }

        if(!count($res)) return array();

        $list = array();
        foreach($res as $values){
            $list[] = $categoryDao->getObject($values);
        }

        return $list;
    }

    private function getParameter($key){
        if(array_key_exists($key, $_GET)){
            $value = $_GET[$key];
            $this->setParameter($key,$value);
        }else{
            $value = SOY2ActionSession::getUserSession()->getAttribute("Item.Category.Setting.Search:" . $key);
        }
        return $value;
    }
    private function setParameter($key,$value){
        SOY2ActionSession::getUserSession()->setAttribute("Item.Category.Setting.Search:" . $key, $value);
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("カテゴリ一括設定", array("Item" => "商品管理", "Item.Category" => "カテゴリ管理"));
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
