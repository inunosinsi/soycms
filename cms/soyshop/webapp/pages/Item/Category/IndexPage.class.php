<?php

/**
 * @class Item.IndexPage
 * @date 2009-11-25T19:36:32+09:00
 * @author SOY2HTMLFactory
 */
class IndexPage extends WebPage{

    function doPost(){
        if(!soy2_check_token())return;

        if(isset($_POST["create"])){

            $name = $_POST["name"];

            $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
            $obj = new SOYShop_Category();
            $obj->setName($name);

            if(isset($_POST["parent"])){
                $obj->setParent($_POST["parent"]);
            }

            try{
                $id = $dao->insert($obj);
            }catch(SOY2DAOException $e){
                //echo $e->getPDOExceptionMessage();
            }

            SOY2PageController::jump("Item.Category?updated=created&id=" . $id);
        }

        if(isset($_POST["update"])){

            $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
            $obj = soyshop_get_category_object($_POST["Category"]["id"]);
            SOY2::cast($obj,(object)$_POST["Category"]);

            //設定する親カテゴリが自身だった場合
            if($obj->getId() == $obj->getParent()){
                $obj->setParent(null);
            }

            if(!strlen($obj->getParent()) > 0){
                $obj->setParent(null);
            }

            try{
                $dao->update($obj);
            }catch(Exception $e){

            }

            SOYShopPlugin::load("soyshop.category.customfield");
            SOYShopPlugin::invoke("soyshop.category.customfield", array(
                    "category" => $obj
            ));


            SOY2PageController::jump("Item.Category?updated&id=" . $obj->getId());
        }

        if(isset($_POST["upload"])){
            $urls = $this->uploadImage($_POST["upload"]);

            echo "<html><head>";
            echo "<script type=\"text/javascript\">";
            if($urls !== false){
                foreach($urls as $url){
                    echo 'window.parent.ImageSelect.notifyUpload("'.$url.'");';
                }
            }else{
                echo 'alert("failed");';
            }
            echo "</script></head><body></body></html>";
            exit;
        }

    }

    function __construct(){
        parent::__construct();

        $this->addForm("create_form");

        $this->createAdd("category_tree","MyTree", array(
            "list" => soyshop_get_category_objects()
        ));
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("カテゴリ管理", array("Item" => "商品管理"));
	}

	function getFooterMenu(){
		try{
			return SOY2HTMLFactory::createInstance("Item.FooterMenu.CategoryFooterMenuPage")->getObject();
		}catch(Exception $e){
			//
			return null;
		}
	}

    function getScripts(){
        $root = SOY2PageController::createRelativeLink("./js/");
        return array(
            $root . "ImageSelect.js",
            $root . "jquery/treeview/jquery.treeview.pack.js",
            $root . "tree.js",
        );
    }

    function getCSS(){
        $root = SOY2PageController::createRelativeLink("./js/");
        return array(
            $root . "jquery/treeview/jquery.treeview.css",
            $root . "tree.css",
        );
    }

    /**
     * 画像のアップロード
     *
     * @return url
     * 失敗時には false
     */
    function uploadImage($id){
        $category = soyshop_get_category_object($id);

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
                    $filepath = $category->getAttachmentsPath() . $name;


                    if(!file_exists($filepath)){
                        break;
                    }
                    $counter++;
                }

                //一回でも失敗した場合はfalseを返して終了（rollbackは無し）
                $result = move_uploaded_file($upload["tmp_name"][$key],$filepath);
                @chmod($filepath,0604);

                if($result){
                    $url = $category->getAttachmentsUrl() . $name;
                    $urls[] = $url;
                }else{
                    return false;
                }
            }
        }

        return $urls;
    }
}
SOY2HTMLFactory::importWebPage("_base.TreeComponent");
class MyTree extends TreeComponent{

    function getClass($id){
        return "";
    }

    function getHref($id){
        return SOY2PageController::createLink("Item.Category.Detail.").$id;
    }
}
