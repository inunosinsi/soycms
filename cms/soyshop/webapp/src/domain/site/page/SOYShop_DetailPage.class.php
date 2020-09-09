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

    function convertPageTitle($title){
        if($this->currentItem){
            $title = str_replace("%ITEM_NAME%", $this->currentItem->getOpenItemName(), $title);
            $title = str_replace("%ITEM_CODE%", $this->currentItem->getCode(), $title);
            return str_replace("%CATEGORY_NAME%", soyshop_get_category_name($this->currentItem->getCategory()), $title);
        }
        return $title;
    }

	/**
	 * フォーマットが共通の時
	 */
	private function _getCommonFormat(){
		$html = array();
		$html[] = "<table style=\"margin-top:5px;\">";
    	$html[] = "<tr><td>ショップ名：</td><td><strong>%SHOP_NAME%</strong></td></tr>";
    	$html[] = "<tr><td>ページ名：</td><td><strong>%PAGE_NAME%</strong></td></tr>";
		$html[] = "<tr><td>商品名：</td><td><strong>%ITEM_NAME%</strong></td></tr>";
        $html[] = "<tr><td>商品コード：</td><td><strong>%ITEM_CODE%</strong></td></tr>";
		$html[] = "<tr><td>カテゴリー名：</td><td><strong>%CATEGORY_NAME%</strong></td></tr>";
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
