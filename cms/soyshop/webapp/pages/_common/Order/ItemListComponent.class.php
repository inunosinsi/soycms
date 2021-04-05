<?php

class ItemListComponent extends HTMLList{

    private $detailLink;

    protected function populateItem($item, $idx) {

		$this->addLabel("index", array(
			"text" => $idx
		));

        $this->addLabel("item_id", array(
            "text" => $item->getId()
        ));

		$imagePath = soyshop_convert_file_path_on_admin($item->getAttribute("image_small"));
		if(!strlen($imagePath)) $imagePath = "/" . SOYSHOP_ID . "/themes/sample/noimage.jpg";
		$this->addImage("item_small_image", array(
			//"src" => "/" . SOYSHOP_ID . "/im.php?src=" . $imagePath . "&width=60",	//im.phpが使えなくなった
			"src" => $imagePath,
			"attr:style" => "width:60px;"
        ));

        $this->addLabel("item_name", array(
            "text" => $item->getOpenItemName()
        ));

        $this->addLabel("item_code", array(
            "text" => $item->getCode()
        ));

		$categories = soyshop_get_category_list(true);
		$this->addLabel("item_category", array(
            "text" => (is_numeric($item->getCategory()) && is_array($categories) && isset($categories[$item->getCategory()])) ? $categories[$item->getCategory()] : "-"
        ));

		//親商品であるか？
		$isParent = ($item instanceof SOYShop_Item && $item->getType() == SOYShop_Item::TYPE_GROUP);
        $this->addLabel("item_price", array(
            "text" => (!$isParent) ? soy2_number_format((int)$item->getPrice()) . " 円" : ""
        ));

		$this->addLabel("purchase_price", array(
			"text" => soy2_number_format($item->getPurchasePrice())
		));

        $this->addLabel("item_stock", array(
            "text" => (!$isParent) ? soy2_number_format($item->getStock()) : ""
        ));

        $this->addLink("detail_link", array(
            "link" => $this->detailLink . $item->getId()
        ));

		//在庫切れ

		//親商品の場合は追加ボタンを表示しない
		$this->addModel("show_add_button", array(
			"visible" => !$isParent
		));

		//子商品の表
		$children = ($isParent && is_numeric($item->getId())) ? self::_getChildrenByParentId($item->getId()) : array();
		$childrenCount = count($children);
		$this->addModel("show_child_table", array(
			"visible" => $childrenCount
		));

		$this->addLabel("children_table", array(
			"html" => ($childrenCount > 0) ? self::_buildChildrenTable($children) : ""
		));

		//iframeのchangeにあるindexを出力
		$this->addLabel("iframe_index", array(
			"text" => (isset($_GET["change"]) && is_numeric($_GET["change"])) ? $_GET["change"] : 0
		));
    }

	private function _getChildrenByParentId($parentId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

		try{
			return $dao->getByTypeIsOpenNoDisabled($parentId);
		}catch(Exception $e){
			return array();
		}
	}

	private function _buildChildrenTable($children){
		static $component;
		if(is_null($component)){
			include_once(dirname(dirname(dirname(__FILE__))) . "/Order/Register/component/ChildrenTableComponent.class.php");
			$component = new ChildrenTableComponent();
		}
		return $component->buildTable($children);
	}

    function setDetailLink($detailLink) {
        $this->detailLink = $detailLink;
    }
}
