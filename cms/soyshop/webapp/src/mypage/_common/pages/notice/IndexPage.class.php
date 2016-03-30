<?php

class IndexPage extends MainMyPagePageBase{
	
	private $noticeDao;
	
	function IndexPage(){
		//お気に入り登録プラグインがアクティブでない場合はトップページに飛ばす
		if(!SOYShopPluginUtil::checkIsActive("common_notice_arrival")){
			$this->jumpToTop();
		}
		
		$mypage = MyPageLogic::getMyPage();
    	
    	//ログインしていない場合はログイン画面に飛ばす
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}
		
		WebPage::WebPage();
		
		SOY2::imports("module.plugins.common_notice_arrival.domain.*");
		$this->noticeDao = SOY2DAOFactory::create("SOYShop_NoticeArrivalDAO");
		
		$user = $this->getUser();
		
		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));
		
		$items = $this->noticeDao->getItems($user->getId(), null, SOYShop_NoticeArrival::NOT_CHECKED);
		
		$this->addModel("no_notice", array(
			"visible" => (count($items) === 0)
		));
		
		$this->addModel("is_notice", array(
			"visible" => (count($items) > 0)
		));
		
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
?>