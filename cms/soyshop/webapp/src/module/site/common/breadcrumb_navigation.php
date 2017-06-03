<?php
/**
 * <!-- shop:module="common.breadcrumb_navigation" -->
 *     <p id="pankuzu">
 *    <a cms:id="top_link">トップ</a>
 *    <!-- block:id="breadcrumb" -->
 *    &nbsp;&gt;&nbsp;
 *    <a cms:id="breadcrumb_link">カテゴリー名</a>
 *    <!-- /block:id="breadcrumb" -->
 *    &nbsp;&gt;&nbsp;
 *    <a cms:id="current_name_link">子のカテゴリー名</a>
 *    &nbsp;&gt;&nbsp;
 *    <!-- cms:id="current_item_name" -->
 *    商品名
 *    <!-- /cms:id="current_item_name" -->
 * </p>
 * <!-- /shop:module="common.breadcrumb_navigation" -->
 */
SOY2::import("util.SOYShopPluginUtil");
function soyshop_breadcrumb_navigation($html, $page){
    $obj = $page->create("soyshop_breadcrumb_navigation", "HTMLTemplatePage", array(
        "arguments" => array("soyshop_breadcrumb_navigation", $html)
    ));

    if(SOYShopPluginUtil::checkIsActive("common_breadcrumb")){

        $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");

        $pageObject = $page->getPageObject();
        $className = (isset($pageObject)) ? get_class($pageObject) : "";
        if($className == "SOYShop_Page"){

            $type = $pageObject->getType();
            switch($type){

                case SOYShop_Page::TYPE_LIST:
                    $current = $pageObject->getObject()->getCurrentCategory();
                    $uri = null;
                    $categories = array();
                    $alias = "";
                    if(isset($current)){
                        $uri = $pageObject->getUri();
                        $categories = $dao->getAncestry($current, false);
                        $name = $current->getOpenCategoryName();
                        $alias = $current->getAlias();
                    }else{
                        //カスタムフィールドの場合
                        if($pageObject->getObject()->getType() == "field"){
                            $itemAttributeDao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
                            $list = SOYShop_ItemAttributeConfig::load(true);
                            $object = $pageObject->getObject();
                            $name = (isset($list[$object->getFieldId()])) ? $list[$object->getFieldId()]->getLabel() : "";
                        //その他
                        }else{
                            //カスタムサーチフィールド
                            if(!is_null($pageObject->getObject()->getModuleId()) && $pageObject->getObject()->getModuleId() === "custom_search_field"){
                                $args = $page->getArguments();
                                if(isset($args[1])) $name = htmlspecialchars($args[1], ENT_QUOTES, "UTF-8");
                            }
                        }

                    }
                    break;

                case SOYShop_Page::TYPE_DETAIL:
                    $item = $page->getItem();

                    //商品グループの子商品の時
                    if(is_numeric($item->getType())){
                        $itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
                        try{
                            $parent = $itemDao->getById($item->getType());
                        }catch(Exception $e){
                            $parent = new SOYShop_Item();
                        }

                        $categoryId = $parent->getCategory();

                        $pageDao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
                        try{
                            $detailPage = $pageDao->getById($parent->getDetailPageId());
                        }catch(Exception $e){
                            $detailPage = new SOYShop_Page();
                        }

                        SOY2::import("module.plugins.common_breadcrumb.util.BreadcrumbUtil");
                        $config = BreadcrumbUtil::getConfig();

                        //パンくずに子商品まで表示させる
                        if(isset($config["displayChild"]) && $config["displayChild"] == 1){
                            $parentUrl = soyshop_get_site_url().$detailPage->getUri() . "/" . $parent->getAlias();
                            $itemName = "<a href=\"" . $parentUrl."\">" . $parent->getOpenItemName() . "</a>"."&nbsp;&gt;&nbsp;" .$item->getOpenItemName();

                        //パンくずに表示する商品を親商品までにする
                        }else{
                            $itemName = $parent->getOpenItemName();
                        }


                    //子商品以外の時
                    }else{
                        $categoryId = $item->getCategory();
                        $itemName = $item->getOpenItemName();
                    }

                    //表示中の商品名
                    $obj->addLabel("current_item_name", array(
                        "html" => $itemName,
                        "soy2prefix" => SOYSHOP_SITE_PREFIX
                    ));

                    try{
                        $current = $dao->getById($categoryId);
                    }catch(Exception $e){
                        return;
                    }

                    SOY2::imports("module.plugins.common_breadcrumb.domain.*");
                    $breadcrumbDao = SOY2DAOFactory::create("SOYShop_BreadcrumbDAO");
                    $uri = $breadcrumbDao->getPageUriByItemId($item->getId());

                    $categories = $dao->getAncestry($current, false);

                    $name = $current->getOpenCategoryName();
                    $alias = $current->getAlias();

                    break;
                case SOYShop_Page::TYPE_SEARCH:
                    $categories = array();

                    $uri = "";
                    $name = "";
                    if(isset($_GET["q"])){
                        $name = trim($_GET["q"]);
                    //カスタムサーチフィールド
                    }else if(isset($_GET["c_search"]["item_name"])){
                        $name = trim($_GET["c_search"]["item_name"]);
                    }
                    $alias = "";
                    break;
                case SOYShop_Page::TYPE_FREE:
                case SOYShop_Page::TYPE_COMPLEX:
                default:
                    $categories = array();
                    $uri = "";
                    $name = $pageObject->getName();
                    $alias = "";
                    break;
            }

        //カートページとマイページ
        }else{
            $className = get_class($page);
            if($className == "SOYShop_CartPage"){
                $name = SOYShop_DataSets::get("config.cart.cart_title", "ショッピングカート");
            //マイページ
            }else{
                //マイページのタイトルフォーマットで置換文字列を使用
                $name = MyPageLogic::getMyPage()->getTitleFormat($page->getArgs());
            }

            $categories = array();
            $uri = "";
            $alias = "";
        }

        $obj->createAdd("breadcrumb", "BreadcrumbNavigation", array(
            "list" => $categories,
            "uri" => $uri,
            "soy2prefix" => "block"
        ));

        //表示中のカテゴリ名
        $obj->addLabel("current_name", array(
            "text" => $name,
            "soy2prefix" => SOYSHOP_SITE_PREFIX,
        ));

        //表示中のカテゴリ名
        $obj->addLink("current_name_link", array(
            "text" => $name,
            "link" => soyshop_get_site_url() . $uri . "/" . $alias,
            "soy2prefix" => SOYSHOP_SITE_PREFIX,
        ));

        $obj->addLink("top_link", array(
            "link" => soyshop_get_site_url(),
            "soy2prefix" => SOYSHOP_SITE_PREFIX
        ));

        //検索ワード
        $obj->addLabel("search_word", array(
            "text" => $name,
            "soy2prefix" => SOYSHOP_SITE_PREFIX
        ));
    }

    $obj->display();
}

/**
 * パンくず
 */
if(!class_exists("BreadcrumbNavigation")){
class BreadcrumbNavigation extends HTMLList{

    private $uri;

    protected function populateItem($entity, $key){
        if(false == ($entity instanceof SOYShop_Category)){
            $entity = new SOYShop_Category();
        }

        $this->addLink("breadcrumb_link", array(
            "text" => $entity->getOpenCategoryName(),
            "link" => soyshop_get_site_url() . $this->uri . "/" . $entity->getAlias(),
            "soy2prefix" => SOYSHOP_SITE_PREFIX
        ));


        $this->addLabel("breadcrumb_name", array(
            "text" => $entity->getOpenCategoryName(),
            "soy2prefix" => SOYSHOP_SITE_PREFIX
        ));
    }

    function setUri($uri){
        $this->uri = $uri;
    }
}
}
?>
