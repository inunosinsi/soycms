<?php
SOY2::import("domain.config.SOYShop_ShopConfig");
class DetailPage extends WebPage{

	private $id;
	private $parent;
	private $config;

	function doPost(){

		//カテゴリー情報の更新
		if(isset($_POST["update"]) && soy2_check_token()){

			$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
			$categoryId = $_POST["Category"]["id"];
			$obj = soyshop_get_category_object($categoryId);
			if(is_null($obj->getId())) SOY2PageController::jump("Item.Category.Detail." . $categoryId . "?failed");

			SOY2::cast($obj, (object)$_POST["Category"]);

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
				SOY2PageController::jump("Item.Category.Detail." . $categoryId . "?failed");
			}

			SOYShopPlugin::load("soyshop.category.customfield");
			SOYShopPlugin::invoke("soyshop.category.customfield", array(
					"category" => $obj
			));

			SOYShopPlugin::load("soyshop.category.name");
			SOYShopPlugin::invoke("soyshop.category.name", array(
				"category" => $obj
			));

			SOY2PageController::jump("Item.Category.Detail." . $categoryId . "?updated");
			exit;
		}

		//カテゴリーの追加
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

			SOY2PageController::jump("Item.Category.Detail." . $id . "?updated=created");
			exit;
		}

		//画像のアップロード
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

    function __construct($args) {
		if(!AUTH_CONFIG) SOY2PageController::jump("Item");

    	//IDがない場合はカテゴリのトップページに飛ばす
    	if(!isset($args[0])){
    		SOY2PageController::jump("Item.Category");
    	}

    	//ショップの基本設定を取得
    	$this->config = SOYShop_ShopConfig::load();

    	$this->id = (int)$args[0];
    	$id = $this->id;

    	parent::__construct();

    	$this->addForm("create_form");

		$category = soyshop_get_category_object($id);
		if(is_null($category->getId())) SOY2PageController::jump("Item.Category?failed");

		$categories = soyshop_get_category_objects();

		$this->createAdd("category_tree","MyTree", array(
			"list" => $categories,
		));

		self::buildForm($category, $categories);

		SOYShopPlugin::load("soyshop.notepad");
		$this->addLabel("notepad_extension", array(
			"html" => SOYShopPlugin::invoke("soyshop.notepad", array(
				"mode" => "category",
				"id" => $category->getId()
			))->getHtml()
		));
    }

    private function buildForm($entity, $parents){

    	$id = $entity->getId();

		$this->addModel("list_row", array(
			"attr:id"=> "category_detail_" . $id,
			"style" => (isset($_GET["id"]) && $_GET["id"] == $id) ? "" : "display:none;"
		));

		$this->addLabel("category_name", array(
			"text" => $entity->getNameWithStatus()
		));

		SOYShopPlugin::load("soyshop.category.name");
		$nameForm = SOYShopPlugin::display("soyshop.category.name", array(
			"category" => $entity
		));

		$this->addLabel("extension_category_name_input", array(
			"html" => $nameForm
		));

		$this->addForm("child_create_form", array(
			"attr:id" => "child_create_form_" . $id
		));

		$this->addInput("category_new_name", array(
			"attr:id" => "category_new_name_" . $id . ""
		));

		$this->addModel("category_new_name_error", array(
			"attr:id" => "category_new_name_" . $id . "_error"
		));

		$this->addInput("parent", array(
			"name" => "parent",
			"value" => $id
		));

		$this->addModel("create_submit_btn", array(
			"onclick" => '$(\'#child_create_form_'.$id.'\').trigger(\'submit\');'
		));

		$this->addActionLink("remove_link", array(
			"link" => SOY2PageController::createLink("Item.Category.Remove." . $id)
		));

		/* 更新フォーム */

		$this->addForm("child_update_form", array(
			"attr:id" => "update_form_" . $id
		));

		$this->addInput("category_id_input", array(
			"name" => "Category[id]",
			"value" => $entity->getId()
		));

		$this->addInput("category_id_upload", array(
			"name" => "upload",
			"value" => $entity->getId()
		));

		$this->addInput("category_name_input", array(
			"name" => "Category[name]",
			"value" => $entity->getName()
		));

		$this->addInput("category_alias_input", array(
			"name" => "Category[alias]",
			"value" => $entity->getAlias()
		));

		$this->addInput("category_order_input", array(
			"name" => "Category[order]",
			"value" => $entity->getOrder()
		));

		unset($parents[$entity->getId()]);

		$this->createAdd("category_parent","_common.CategorySelectComponent", array(
			"name" => "Category[parent]",
			"domId" => "category_parent_" . $entity->getId(),
			"selected" => $entity->getParent(),
			"label" => (isset($parents[$entity->getParent()])) ? $parents[$entity->getParent()]->getName() : "---------"
		));

		SOYShopPlugin::load("soyshop.category.customfield");
		$this->addLabel("category_custom_field", array(
			"html" => SOYShopPlugin::display("soyshop.category.customfield", array("category" => $entity))
		));

		$this->addCheckBox("category_is_open", array(
			"name" => "Category[isOpen]",
			"value" => SOYShop_Category::IS_OPEN,
			"selected" => ($entity->getIsOpen() == SOYShop_Category::IS_OPEN),
			"label" => "公開"
		));

		$this->addCheckBox("category_no_open", array(
			"name" => "Category[isOpen]",
			"value" => SOYShop_Category::NO_OPEN,
			"selected" => ($entity->getIsOpen() == SOYShop_Category::NO_OPEN),
			"label" => "非公開"
		));

		$this->addModel("update_submit_btn", array(
			"onclick" => '$(\'#update_form_'.$id.'\').trigger(\'submit\');'
		));

		$this->addForm("upload_form", array(
			"enctype" => "multipart/form-data",
			"attr:id" => "upload_form",
			"attr:target" => "upload_target_frame",
		));

		$this->createAdd("image_list","_common.Category.ImageListComponent", array(
			"list" => $this->getAttachments($entity)
		));
    }


    /**
     * 添付ファイル取得
     */
    function getAttachments($category){
    	return $category->getAttachments();
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("カテゴリ詳細", array("Item" => "商品管理", "Item.Category" => "カテゴリ管理"));
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
