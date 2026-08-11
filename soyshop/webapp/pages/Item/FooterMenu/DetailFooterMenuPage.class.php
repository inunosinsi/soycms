<?php
/**
 * @class Site.Pages.SubMenu.FreePage
 * @date 2009-11-19T22:37:03+09:00
 * @author SOY2HTMLFactory
 */
class DetailFooterMenuPage extends HTMLPage{

	private $id;

	function __construct($arg = array()){
		$this->id = (isset($arg[0])) ? (int)$arg[0] : null;
		parent::__construct();

		$hists = self::_getHistories(soyshop_get_item_object($this->id));
		DisplayPlugin::toggle("change_history", count($hists));

		$this->createAdd("history_list", "_common.Item.ChangeHistoryListComponent", array(
			"list" => $hists
		));

		SOYShopPlugin::load("soyshop.notepad");
		$this->addLabel("notepad_extension", array(
			"html" => SOYShopPlugin::invoke("soyshop.notepad", array(
				"mode" => "item",
				"id" => $this->id
			))->getHtml()
		));
	}

	//拡張ポイントのsoyshop.item.update関連の処理
	private function _getHistories(SOYShop_Item $item){
		SOYShopPlugin::load("soyshop.item.update");
		$delegate = SOYShopPlugin::invoke("soyshop.item.update", array(
			"item" => $item
		));

		$histories = array();
		if(is_array($delegate->getList()) && count($delegate->getList()) > 0){
			foreach($delegate->getList() as $key => $values){
				if(isset($values)){
					foreach($values as $value){
						$array = array("date" => $value->getCreateDate(), "content" => $value->getMemo());
						$histories[] = $array;
					}
				}
			}
		}

		return $histories;
	}
}
