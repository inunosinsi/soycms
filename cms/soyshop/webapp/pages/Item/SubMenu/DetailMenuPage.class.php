<?php
/**
 * @class Site.Pages.SubMenu.FreePage
 * @date 2009-11-19T22:37:03+09:00
 * @author SOY2HTMLFactory
 */
class DetailMenuPage extends HTMLPage{

	private $id;

	function __construct($arg = array()){
		$this->id = (isset($arg[0])) ? (int)$arg[0] : null;
		parent::__construct();

		$item = soyshop_get_item_object($this->id);

		$page = soyshop_get_page_object($item->getDetailPageId());
		if(!is_null($page->getId())){
			$url = soyshop_get_page_url($page->getUri(), $item->getAlias());
		}else{
			$url = null;
		}

		//
		DisplayPlugin::toggle("is_open", $item->isPublished());
		DisplayPlugin::toggle("no_open", !$item->isPublished());

		//確認ページ
		$this->addLink("item_site_link", array(
			"link" => $url,
		));

		//確認ページ
		$this->addLink("item_confirm_link", array(
			"link" => $url . "?foradminonly",
		));

		//削除リンク
		$this->addActionLink("item_remove_link", array(
			"link" => SOY2PageController::createLink("Item.Remove." . $this->id)
		));

		//画像ファイルの管理
		$this->addLink("item_attachment_link", array(
			"link" => SOY2PageController::createLink("Item.Attachment." . $this->id)
		));

		//注文リンク
		$this->addLink("item_order_link", array(
			"link" => SOY2PageController::createLink("Order.Register.Item." . $this->id)
		));

		DisplayPlugin::toggle("can_copy", ($item->getType() == SOYShop_Item::TYPE_SINGLE || $item->getType() == SOYShop_Item::TYPE_DOWNLOAD));

		$this->addActionLink("copy_link", array(
			"link" => SOY2PageController::createLink("Item.Copy." . $this->id)
		));


		/* next before */
		$detailLink = SOY2PageController::createLink("Item.Detail");
		$nextItem = null;
		$prevItem = null;

		$itemDAO = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		$itemDAO->setLimit(1);

		$sql = "select id from " . SOYShop_Item::getTableName() . " where id > :id and item_category = :category order by id";
		$res = $itemDAO->executeQuery($sql, array(
			":id" => $this->id,
			":category" => $item->getCategory()
		));

		if(count($res) > 0){
			$nextItem = soyshop_get_item_object($res[0]["id"]);
		}

		$this->addLink("next_item_link", array(
			"link" => ($nextItem && $nextItem->getIsDisabled()!=1) ? $detailLink ."/". $nextItem->getId() : "javascript:void(0)",
			"text" => ($nextItem && $nextItem->getIsDisabled()!=1) ? $nextItem->getName() : "-"
		));

		$sql = "select id from " . SOYShop_Item::getTableName() . " where id < :id and item_category = :category order by id desc";
		$res = $itemDAO->executeQuery($sql, array(
			":id" => $this->id,
			":category" => $item->getCategory()
		));

		if(count($res) > 0) $prevItem = soyshop_get_item_object($res[0]["id"]);

		$this->addLink("prev_item_link", array(
			"link" => ($prevItem && $prevItem->getIsDisabled()!=1) ? $detailLink ."/". $prevItem->getId() : "javascript:void(0)",
			"text" => ($prevItem && $prevItem->getIsDisabled()!=1) ? $prevItem->getName() : "-"
		));
	}
}
