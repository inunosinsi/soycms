<?php
SOY2::import("module.site.common.output_item", ".php");

class SOYShop_DetailPageBase extends SOYShopPageBase{

	private $item;
	private $nextItem;
	private $prevItem;
	private $currentIndex = 1;
	private $totalItemCount = 0;

	function build($args){

		$page = $this->getPageObject();
		$obj = $page->getPageObject();

		$alias = implode("/", $args);

		$itemDAO = soyshop_get_hash_table_dao("item");
		try{
			$item = $itemDAO->getByAlias($alias);
		}catch(Exception $e){
			throw new Exception("The specified product cannot be found.");
		}

		$forAdminOnly = self::getForAdminOnly($item);

		//非公開(非公開プレビューモードは除く) && 削除フラグのチェック
		if((!$forAdminOnly && $item->getIsOpen() != SOYShop_Item::IS_OPEN)  || $item->getIsDisabled() == SOYShop_Item::IS_DISABLED){
			throw new Exception("The specified product does not have publishing authority.");
		}

		//子商品だった場合は、親商品の詳細ページにリダイレクト
		if(is_numeric($item->getType()) && (int)SOYShop_ShopConfig::load()->getDisplayChildItem() == 0){
			header("Location: " . soyshop_get_page_url($page->getUri(),soyshop_get_item_object($item->getType())->getAlias()));
		}

		$this->setItem($item);

		//現在の商品を保存
		$obj->setCurrentItem($item);

		soyshop_convert_item_detail_page_id($item, $page);

		if(strlen($item->getDetailPageId()) > 0 && $item->getDetailPageId() != $page->getId()){
			throw new Exception("The specified product does not have publishing authority.");
		}

		$logic = SOY2Logic::createInstance("logic.shop.item.SearchItemUtil");
		list($items, $total) = $logic->getByCategoryId($item->getCategory());
		$this->setTotalItemCount($total);

		$counter = 1;
		$prev_item = false;
		$next_item = false;
		foreach($items as $tmp){
			if($tmp->getId() == $item->getId()){
				$next_item = true;
				continue;
			}

			if($next_item){
				$next_item = $tmp;
				break;
			}
			$prev_item = $tmp;
			$counter++;
		}

		$this->setCurrentIndex($counter);
		$this->setPrevItem($prev_item);
		if($next_item && $next_item !== true) $this->setNextItem($next_item);

		//keywords
		$keywords = $item->getAttribute("keywords");
		if(is_string($keywords) && strlen($keywords)) $this->getHeadElement()->insertMeta("keywords", $keywords . ",");

		//description
		$description = $item->getAttribute("description");
		if(is_string($description) && strlen($description)) $this->getHeadElement()->insertMeta("description", $description . " ");

		if(!defined("SOYSHOP_PAGE_TYPE")) define("SOYSHOP_PAGE_TYPE", get_class($obj));

		//item
		$this->createAdd("item", "SOYShop_ItemListComponent", array(
			"list" => array($item),
			"obj" => $obj,
			"soy2prefix" => "block",
			"forAdminOnly" => $forAdminOnly //商品詳細ページ確認モード
		));
	}

	/**
	 * 商品が非公開で商品詳細ページ確認モードのフラグが立っている場合はページを表示するか調べる
	 * @param object SOYShop_Item
	 * @return boolean or null ← どうにかしたい
	 */
	function getForAdminOnly(SOYShop_Item $item){

		if(!$item->isPublished() && isset($_GET["foradminonly"])){
			$session = SOY2ActionSession::getUserSession();
			$forAdminOnly = (!is_null($session->getAttribute("loginid")));
		}else{
			$forAdminOnly = null;
		}
		return $forAdminOnly;
	}

	function getNextItem() {
		return $this->nextItem;
	}
	function setNextItem($nextItem) {
		$this->nextItem = $nextItem;
	}
	function getPrevItem() {
		return $this->prevItem;
	}
	function setPrevItem($prevItem) {
		$this->prevItem = $prevItem;
	}
	function getCurrentIndex() {
		return $this->currentIndex;
	}
	function setCurrentIndex($currentIndex) {
		$this->currentIndex = $currentIndex;
	}

	function getTotalItemCount() {
		return $this->totalItemCount;
	}
	function setTotalItemCount($totalItemCount) {
		$this->totalItemCount = $totalItemCount;
	}

	function getItem() {
		return $this->item;
	}
	function setItem($item) {
		$this->item = $item;
	}

	function getPager(){
		return new SOYShop_DetailPagePager($this);
	}
}

class SOYShop_DetailPagePager extends SOYShop_PagerBase{

	private $page;

	function __construct(SOYShop_DetailPageBase $page){
		$this->page = $page;
	}

	function getCurrentPage(){
		return $this->page->getCurrentIndex();
	}

	function getTotalPage(){
		return $this->page->getTotalItemCount();
	}

	function getLimit(){
		return 1;	//detail page's limiy is always 1;
	}

	private $_pagerUrl;

	function getPagerUrl(){
		if(!$this->_pagerUrl){
			$url = $this->page->getPageUrl();
			if($url[strlen($url) - 1] == "/") $url = substr($url, 0, strlen($url) - 1);
			$this->_pagerUrl = $url;
		}
		return $this->_pagerUrl;
	}

	function getNextPageUrl(){
		$nextItem = $this->page->getNextItem();
		if(!$nextItem instanceof SOYShop_Item) return "-";

		if($this->page->getPageObject()->getId() != $nextItem->getDetailPageId()){
			$url = soyshop_get_page_url(soyshop_get_page_object($nextItem->getDetailPageId())->getUri());
		}else{
			$url = $this->getPagerUrl();
		}
		return $url . "/" . ($nextItem->getAlias());
	}

	function getPrevPageUrl(){
		$prevItem = $this->page->getPrevItem();
		if(!$prevItem instanceof SOYShop_Item) return "-";

		if($this->page->getPageObject()->getId() != $prevItem->getDetailPageId()){
			$url = soyshop_get_page_url(soyshop_get_page_object($prevItem->getDetailPageId())->getUri());
		}else{
			$url = $this->getPagerUrl();
		}
		return $url . "/" . ($prevItem->getAlias());
	}

	function hasNext(){
		return ($this->page->getNextItem());
	}

	function hasPrev(){
		return ($this->page->getPrevItem());
	}
}
