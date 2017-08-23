<?php
/**
 * @class Site.Pages.SubMenu.FreePage
 * @date 2009-11-19T22:37:03+09:00
 * @author SOY2HTMLFactory
 */
class DetailMenuPage extends HTMLPage{

	var $id;

	function __construct($arg = array()){
		$this->id = $arg[0];
		parent::__construct();

		$this->createAdd("detail_page_detail_link","HTMLLink", array(
			"link" => SOY2PageController::createLink("Site.Pages.Extra.Detail." . $this->id)
		));

		$this->createAdd("item_list","DetailMenuPage_ItemList", array(
			"list" => $this->getItemList($this->id),
			"detailLink" => SOY2PageController::createLink("Item.Detail.")
		));

	}

	function getItemList($id){
		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$itemDAO->setLimit(10);
		return $itemDAO->getByDetailPageIdIsPublished($id);
	}
}

class DetailMenuPage_ItemList extends HTMLList{

	private $detailLink;

	protected function populateItem($entity,$key){

		$this->createAdd("item_detail_link","HTMLLink", array(
			"link" => $this->detailLink . $entity->getId(),
			"text" => $entity->getName()
		));

	}


	function getDetailLink() {
		return $this->detailLink;
	}
	function setDetailLink($detailLink) {
		$this->detailLink = $detailLink;
	}
}
?>