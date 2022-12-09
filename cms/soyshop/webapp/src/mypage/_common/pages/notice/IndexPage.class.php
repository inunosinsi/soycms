<?php

class IndexPage extends MainMyPagePageBase{

	function __construct(){
		$this->checkIsLoggedIn(); //ログインチェック

		//お気に入り登録プラグインがアクティブでない場合はトップページに飛ばす
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("common_notice_arrival")) $this->jumpToTop();

		parent::__construct();

		$this->addLabel("user_name", array(
			"text" => $this->getUser()->getName()
		));

		SOY2::import("module.plugins.common_notice_arrival.domain.SOYShop_NoticeArrivalDAO");
		$items = SOY2DAOFactory::create("SOYShop_NoticeArrivalDAO")->getItems($this->getUser()->getId(), -1, SOYShop_NoticeArrival::NOT_CHECKED);

		DisplayPlugin::toggle("no_notice", !count($items));
		DisplayPlugin::toggle("is_notice", count($items));

		//注.SOYShop_ItemListComponentで出力するタグはすべてcms:idになります。
		SOY2::import("base.site.classes.SOYShop_ItemListComponent");
		$this->createAdd("item_list", "SOYShop_ItemListComponent", array(
			"list" => $items
		));

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_top_url()
		));
	}
}
