<?php
/**
 * @class Site.Pages.Extra.ComplexPage
 * @date 2010-02-11T23:11:52+09:00
 * @author SOY2HTMLFactory
 */
class ComplexPage extends WebPage{

	function doPost(){

		//check
		if(!soy2_check_token())return;

		$page = $this->page;
		$obj = $page->getPageObject();


		if(isset($_POST["add"])){
			$blockId = $_POST["blockId"];
			$blockId = $obj->addBlock($blockId);
			$page->setPageObject($obj);

			$logic = $this->getPageLogic();
			$logic->updatePageObject($page);

			SOY2PageController::jump("Site.Pages.Extra.ComplexPage." . $this->id."?created&blockId=" . $blockId);
			exit;
		}

		if(isset($_POST["remove"])){
			$obj->removeBlock($_POST["remove"]);
			$page->setPageObject($obj);

			$logic = $this->getPageLogic();
			$logic->updatePageObject($page);

			SOY2PageController::jump("Site.Pages.Extra.ComplexPage." . $this->id."?deleted");
			exit;
		}

		if(isset($_POST["Block"])){
			$blocks = $obj->getBlocks();

			foreach($_POST["Block"] as $blockId => $array){
				if(!isset($blocks[$blockId]))continue;
				$block = $blocks[$blockId];

				SOY2::cast($block,(object)$array);
				$obj->setBlock($blockId,$block);
			}

			$page->setPageObject($obj);

			$logic = $this->getPageLogic();
			$logic->updatePageObject($page);

			SOY2PageController::jump("Site.Pages.Extra.ComplexPage." . $this->id."?updated&blockId=" . $blockId);
			exit;
		}

		exit;

	}

	var $id;
	var $page;

	function __construct($args){

		$this->id = $args[0];

		$logic = $this->getPageLogic();
		$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");

		try{
			$page = $dao->getById($this->id);
		}catch(Exception $e){
			SOY2PageController::jump("Site.Pages");
			exit;
		}

		$this->page = $page;

		WebPage::__construct();

		$this->createAdd("update_form","HTMLForm");
		$this->createAdd("add_form","HTMLForm");
		$this->createAdd("remove_form","HTMLForm");

		$this->createAdd("detail_page_link","HTMLLink", array(
			"link" => SOY2PageController::createLink("Site.Pages.Detail." . $this->id)
		));

		$this->buildForm();
	}

	function buildForm(){

		$obj = $this->page->getPageObject();
		$page = $this->page;

		$this->createAdd("page_name","HTMLLabel", array(
			"text" => $page->getName()
		));

		//ブロック情報の取得
		$blocks = $obj->getBlocks();

		if(count($blocks) < 1){
			DisplayPlugin::hide("has_blocks");
		}

		$selectedBlock = null;
		if(isset($_GET["blockId"])){
			$selectedBlock = $_GET["blockId"];
		}else{
			if(count($blocks)){
				$keys = array_keys($blocks);
				$selectedBlock = $keys[0];
			}
		}

		$this->createAdd("block_list","HTMLSelect", array(
			"name" => "",
			"options" => $blocks,
			"property" => "blockId",
			"selected" => $selectedBlock,
			"onchange" => "javascript:select_detail(this);"
		));

		$this->createAdd("block_detail_list","BlockDetailList", array(
			"selected" => $selectedBlock,
			"list" => $blocks
		));
	}

	function getPageLogic(){
		static $logic;
		if(!$logic)$logic = SOY2Logic::createInstance("logic.site.page.PageLogic");
		return $logic;
	}
}

class BlockDetailList extends HTMLList{

	private $selected = null;

	protected function populateItem($entity,$key){

		$block = ($entity instanceof SOYShop_ComplexPageBlock) ? $entity : new SOYShop_ComplexPageBlock();
		$blockId = $entity->getBlockId();

		$this->createAdd("block_id_text","HTMLLabel", array(
			"text" => $blockId
		));

		$this->createAdd("block_detail_row","HTMLModel", array(
			"attr:id" => "block_detail_" . $blockId,
			"style" => ($this->selected == $blockId) ? "" : "display:none;"
		));

		//カテゴリ

		$categories = $block->getCategories();

		$this->createAdd("category_cordination_list","CategoryCordinationList", array(
			"blockId" => $blockId,
			"list" => (count($categories) > 0) ? $categories : array(-1)
		));

		//カスタムフィールド
		$this->createAdd("customfield_cordination_type_and","HTMLCheckbox", array(
			"name" => "Block[" . $blockId."][isAndCustomFieldCordination]",
			"value"=>1,
			"selected"=>$block->isAndCustomFieldCordination(),
			"label"=>"全て一致",
		));
		$this->createAdd("customfield_cordination_type_or" ,"HTMLCheckbox", array(
			"name" => "Block[" . $blockId."][isAndCustomFieldCordination]",
			"value"=>0,
			"selected"=>!$block->isAndCustomFieldCordination(),
			"label"=>"いずれかと一致",
		));

		$customField = $block->getCustomFields();

		$this->createAdd("customfield_cordination_list","CustomFieldCordinationList", array(
			"blockId" => $blockId,
			"list" => (count($customField) > 0) ? array_values($customField) : array(array("",""))
		));



		//表示件数

		$this->createAdd("item_count_start","HTMLInput", array(
			"name" => "Block[" . $blockId."][countStart]",
			"value" => $block->getCountStart()
		));

		$this->createAdd("item_count_end","HTMLInput", array(
			"name" => "Block[" . $blockId."][countEnd]",
			"value" => $block->getCountEnd()
		));


		//記述例

		$this->createAdd("template_example","HTMLTextArea", array(
			"value" => 	'<!-- block:id="'.$blockId.'" -->' . "\n" .
						'<div>' . "\n".
						'<p><a cms:id="item_link"><img cms:id="item_small_image" /></a></p>' . "\n".
						'<dl>' . "\n".
						'<dt cms:id="item_name">商品名</dt>' . "\n".
						'<dd cms:id="item_price">商品の価格</dd>' . "\n".
						'</dl>' . "\n".
						'<p><a cms:id="item_cart_link">カートに入れる</a></p>' . "\n".
						'</div>' . "\n".
						'<!-- /block:id="'.$blockId.'" -->'
		));

		/* sort */
		$this->createAdd("sort_list","HTMLList", array(
			"list" => array(
				"name" => "商品名",
				"code" => "商品コード",
				"stock" => "在庫数",
				"price" => "標準価格",
				"cdate" => "作成日",
				"udate" => "更新日"
			),
			'populateItem:function($entity,$key)' =>
					'$this->createAdd("sort_input","HTMLCheckbox", array(' .
						'"name" => "Block['.$blockId.'][defaultSort]",' .
						'"value" => $key,' .
						'"label" => $entity,' .
						'"selected" => ($key == "'.$block->getDefaultSort().'")' .
					'));'
		));

		$this->createAdd("sort_custom","HTMLCheckbox", array(
			"name" => "Block[" . $blockId."][defaultSort]",
			"value" => "_custom",
			"selected" => ($block->getDefaultSort() == "_custom"),
			"label" => "カスタム項目でソート"
		));

		//ソートで使用するカスタム項目
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
		$config = SOYShop_ItemAttributeConfig::load(true);
		$indexed = SOYShop_ItemAttributeConfig::getIndexFields();
		$indexed = array_flip($indexed);
		$indexed = array_intersect_key($config,$indexed);
		$this->createAdd("sort_custom_field_list","HTMLSelect", array(
			"name" => "Block[" . $blockId."][customSort]",
			"selected" => $block->getCustomSort(),
			"options" =>$indexed,
			"property" => "label"
		));



		$this->createAdd("sort_normal","HTMLCheckbox", array(
			"name" => "Block[" . $blockId."][isReverse]",
			"selected" => (!$block->getIsReverse()),
			"value" => 0,
			"label" => "昇順",
		));

		$this->createAdd("sort_reverse","HTMLCheckbox", array(
			"name" => "Block[" . $blockId."][isReverse]",
			"selected" => ($block->getIsReverse()),
			"value" => 1,
			"label" => "降順",
		));

		//削除

		$this->createAdd("remove_link","HTMLModel", array(
			"onclick" => 'if(confirm(\'本当に削除しますか？\'))$(\'#block_remove_submit_btn\').val(\''.$blockId.'\').trigger(\'click\');'
		));
	}


	function getSelected() {
		return $this->selected;
	}
	function setSelected($selected) {
		$this->selected = $selected;
	}
}

class CategoryCordinationList extends HTMLList{

	private $blockId;

	function populateItem($entity,$key){

		$categories = CategoryCordinationList::getCategories();

		$this->createAdd("category_list","HTMLSelect", array(
			"name" => "Block[" . $this->getBlockId() . "][categories][]",
			"options" => $categories,
			"selected" => $entity,
			"property" => "name"
		));
	}

	private static function getCategories(){
		static $categories;
		if(!$categories){
			$dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");
			$categories = $dao->get();
		}

		return $categories;
	}


	function getBlockId() {
		return $this->blockId;
	}
	function setBlockId($blockId) {
		$this->blockId = $blockId;
	}
}

class CustomFieldCordinationList extends HTMLList{


	private $blockId;

	function populateItem($entity,$key){

		$customFields = CustomFieldCordinationList::getCustomFields();

		$this->createAdd("customfield_list","HTMLSelect", array(
			"name" => "Block[" . $this->getBlockId() . "][customFields][$key][fieldId]",
			"options" => $customFields,
			"selected" => @$entity["fieldId"],
			"property" => "label"
		));

		$this->createAdd("field_value","HTMLInput", array(
			"name" => "Block[" . $this->getBlockId() . "][customFields][$key][value]",
			"value" => @$entity["value"]
		));

		$this->createAdd("field_type","HTMLSelect", array(
			"name" => "Block[" . $this->getBlockId() . "][customFields][$key][type]",
			"selected" => @$entity["type"],
			"options" => SOYShop_ComplexPageBlock::getOperations()
		));
	}

	private static function getCustomFields(){
		static $customFields;
		if(!$customFields){
			$dao = SOY2DAOFactory::create("shop.SOYShop_ItemAttributeDAO");
			$customFields = SOYShop_ItemAttributeConfig::load(true);
		}

		return $customFields;
	}


	function getBlockId() {
		return $this->blockId;
	}
	function setBlockId($blockId) {
		$this->blockId = $blockId;
	}
}
