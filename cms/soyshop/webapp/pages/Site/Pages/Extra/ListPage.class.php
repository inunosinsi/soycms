<?php
/**
 * @class Site.Pages.Extra.ListPage
 * @date 2009-12-04T02:01:25+09:00
 * @author SOY2HTMLFactory
 */
class ListPage extends WebPage{

	function doPost(){
		if(soy2_check_token()){

			$array = $_POST["Page"];

			$logic = SOY2Logic::createInstance("logic.site.page.PageLogic");
			$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

			try{
				$page = $dao->getById($this->id);
			}catch(Exception $e){
				SOY2PageController::jump("Site.Pages");
				exit;
			}

			$obj = $page->getPageObject();
			SOY2::cast($obj,(object)$array);

			$page->setPageObject($obj);
			$logic->updatePageObject($page);

			SOYShopPlugin::load("soyshop.item.list");
			SOYShopPlugin::invoke("soyshop.item.list", array(
				"mode" => "post",
				"obj" => $obj
			));

			SOY2PageController::jump("Site.Pages.Extra.List." . $this->id . "?updated");
			exit;
		}
	}

	function __construct($args){
		$this->id = (isset($args[0])) ? (int)$args[0] : null;

		parent::__construct();

		$this->addLink("detail_page_link", array(
			"link" => SOY2PageController::createLink("Site.Pages.Detail." . $this->id)
		));

		$this->addForm("update_form");

		$this->buildForm();
	}

	function buildForm(){
		$page = soyshop_get_page_object($this->id);
		if(is_null($page->getId())) SOY2PageController::jump("Site.Pages");

		$obj = $page->getPageObject();
		$this->page = $page;

		$this->addLabel("page_name", array(
			"text" => $page->getName()
		));

		$this->addSelect("limit", array(
			"name" => "Page[limit]",
			"options" => range(1,100),
			"selected" => $obj->getLimit()
		));

		$this->addCheckBox("radio_use_category", array(
			"name" => "Page[type]",
			"value" => "category",
			"label" => "カテゴリで商品を選択",
			"selected" => ($obj->getType() == SOYShop_ListPage::TYPE_CATEGORY),
			"onclick" => "swap_config('category');"
		));

		$this->addCheckBox("radio_use_field", array(
			"name" => "Page[type]",
			"value" => "field",
			"label" => "カスタムフィールドで商品を選択",
			"selected" => ($obj->getType() == SOYShop_ListPage::TYPE_FIELD),
			"onclick" => "swap_config('field');"
		));

		$this->addCheckBox("radio_use_custom", array(
			"name" => "Page[type]",
			"value" => "custom",
			"label" => "その他",
			"selected" => ($obj->getType() == SOYShop_ListPage::TYPE_CUSTOM),
			"onclick" => "swap_config('custom');"
		));

		/* category */
		$this->addModel("config_type_category", array(
			"style" => ($obj->getType() == "category") ? "" : "display:none;",
			"attr:id" => "config_type_category"
		));

    	$categories = soyshop_get_category_objects();

		$this->createAdd("category_tree","_base.MyTreeComponent", array(
			"list" => $categories,
			"selected" => $obj->getCategories(),
		));

		$text = array();
		foreach($obj->getCategories() as $id){
			if(!isset($categories[$id])) continue;
			$text[] = $categories[$id]->getNameWithStatus();
		}

		$this->addLabel("categories_choice", array(
			"text" => implode(",", $text),
			"attr:id" => "categories_text"
		));
		$this->addInput("categories", array(
			"name" => "Page[categories]",
			"value" => implode(",", $obj->getCategories()),
			"attr:id" => "categories_input"
		));

		$this->createAdd("default_category_tree", "_base.MyTreeComponent", array(
			"list" => $categories,
			"selected" => array($obj->getDefaultCategory()),
			"func" => "onClickDefaultLeaf"
		));

		$this->addLabel("default_categories_choice", array(
			"text" => (isset($categories[$obj->getDefaultCategory()])) ? $categories[$obj->getDefaultCategory()]->getNameWithStatus() : "",
			"attr:id" => "default_categories_text"
		));
		$this->addInput("default_categories", array(
			"name" => "Page[defaultCategory]",
			"value" => $obj->getDefaultCategory(),
			"attr:id" => "default_categories_input"
		));

		/* field */
		$this->addModel("config_type_field", array(
			"style" => ($obj->getType() == "field") ? "" : "display:none;",
			"attr:id" => "config_type_field"
		));

		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$config = SOYShop_ItemAttributeConfig::load(true);
		$this->addSelect("custom_field_list", array(
			"name" => "Page[fieldId]",
			"selected" => $obj->getFieldId(),
			"options" => $config,
			"property" => "label"
		));

		$this->addInput("field_value", array(
			"name" => "Page[fieldValue]",
			"value" => $obj->getFieldValue()
		));

		$this->addCheckBox("use_parameter", array(
			"name" => "Page[useParameter]",
			"value" => 1,
			"selected" => $obj->isUseParameter()
		));

		$this->addCheckBox("not_use_parameter", array(
			"name" => "Page[useParameter]",
			"value" => 0,
			"selected" => !$obj->isUseParameter()
		));

		$this->addCheckBox("use_parameter", array(
			"name" => "Page[useParameter]",
			"value" => 1,
			"selected" => $obj->isUseParameter(),
			"label" => "引数と一致する商品一覧"
		));

		/* custom */
		$this->addModel("config_type_custom", array(
			"style" => ($obj->getType() == SOYShop_ListPage::TYPE_CUSTOM) ? "" : "display:none;",
			"attr:id" => "config_type_custom"
		));


		SOYShopPlugin::load("soyshop.item.list");
		$delegetor = SOYShopPlugin::invoke("soyshop.item.list", array(
			"mode" => "list",
			"obj" => $obj
		));

		$this->addSelect("module_name", array(
			"name" => "Page[moduleId]",
			"options" => $delegetor->getList(),
			"selected" => $obj->getModuleId()
		));

		$this->addLabel("module_config", array(
			"html" => (is_array($delegetor->getForm())) ? implode("",$delegetor->getForm()) : ""
		));

		/* sort */
		$this->createAdd("sort_list", "HTMLList", array(
			"list" => array(
				"name" => "商品名",
				"code" => "商品コード",
				"stock" => "在庫数",
				"price" => "販売価格",
				"cdate" => "作成日",
				"udate" => "更新日"
			),
			'populateItem:function($entity,$key)' =>
					'$this->createAdd("sort_input","HTMLCheckbox", array(' .
						'"name" => "Page[defaultSort]",' .
						'"value" => $key,' .
						'"label" => $entity,' .
						'"selected" => ($key == "'.$obj->getDefaultSort().'")' .
					'));'
		));

		$this->addCheckBox("sort_custom", array(
			"name" => "Page[defaultSort]",
			"value" => "_custom",
			"selected" => ($obj->getDefaultSort() == "_custom"),
			"label" => "カスタム項目でソート"
		));

		//ソートで使用するカスタム項目
		$indexed = SOYShop_ItemAttributeConfig::getIndexFields();
		$indexed = array_flip($indexed);
		$indexed = array_intersect_key($config, $indexed);
		$this->addSelect("sort_custom_field_list", array(
			"name" => "Page[customSort]",
			"selected" => $obj->getCustomSort(),
			"options" =>$indexed,
			"property" => "label"
		));

		$this->addCheckBox("sort_normal", array(
			"name" => "Page[isReverse]",
			"selected" => (!$obj->getIsReverse()),
			"value" => 0,
			"label" => "昇順",
		));

		$this->addCheckBox("sort_reverse", array(
			"name" => "Page[isReverse]",
			"selected" => ($obj->getIsReverse()),
			"value" => 1,
			"label" => "降順",
		));
	}

	function getBreadcrumb(){
		return BreadcrumbComponent::build("商品一覧ページ設定", array("Site.Pages" => "ページ管理", "Site.Pages.Detail." . $this->id => "ページ設定"));
	}

	function getSubMenu(){
		$key = "Site.Pages.SubMenu.SubMenuPage";

		try{
			$subMenuPage = SOY2HTMLFactory::createInstance($key, array(
				"arguments" => array($this->id, $this->page)
			));
			return $subMenuPage->getObject();
		}catch(Exception $e){
			return null;
		}
	}

	function getScripts(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.pack.js",
		);
	}

	function getCSS(){
		$root = SOY2PageController::createRelativeLink("./js/");
		return array(
			$root . "jquery/treeview/jquery.treeview.css",
			$root . "tree.css",
		);
	}
}
