<?php
class SOYShop_DetailPage extends SOYShop_PageBase{

    const ITEM_ORDER_ASC = 0;
    const ITEM_ORDER_DESC = 1;

    private $currentItem;
    private $sortOrder = self::ITEM_ORDER_ASC;
    private $sortType = "id";

    function getTitleFormatDescription(){
        return self::_getCommonFormat();
    }

    function getKeywordFormatDescription(){
        return self::_getCommonFormat();
    }

    function getDescriptionFormatDescription(){
        return self::_getCommonFormat();
    }

    function convertPageTitle(string $title){
		$title = parent::convertPageTitle($title);
        if(!$this->currentItem instanceof SOYShop_Item) return $title;
		
        $title = str_replace("%ITEM_NAME%", $this->currentItem->getOpenItemName(), $title);
        $title = str_replace("%ITEM_CODE%", $this->currentItem->getCode(), $title);
        return str_replace("%CATEGORY_NAME%", soyshop_get_category_name((int)$this->currentItem->getCategory()), $title);
    }

	private function _getCommonFormat(){
		$tags = parent::getCommonFormat();
		$tags[] = array("label" => "商品名", "format" => "%ITEM_NAME%");
		$tags[] = array("label" => "商品コード", "format" => "%ITEM_CODE%");
		$tags[] = array("label" => "カテゴリー名", "format" => "%CATEGORY_NAME%");

		$html = array();
		$html[] = "<table style=\"margin-top:5px;\">";
		foreach($tags as $tag){
			$html[] = "<tr><td>" . $tag["label"] . "：</td><td><strong>" . $tag["format"] . "</strong></td></tr>";
		}
		$html[] = "</table>";
    	return implode("\n", $html);
	}

    function getCurrentItem() {
        return $this->currentItem;
    }
    function setCurrentItem($currentItem) {
        $this->currentItem = $currentItem;
    }

    function getSortOrder() {
        return $this->sortOrder;
    }
    function setSortOrder($sortOrder) {
        $this->sortOrder = $sortOrder;
    }

    function getSortType() {
        return $this->sortType;
    }
    function setSortType($sortType) {
        $this->sortType = $sortType;
    }
}
