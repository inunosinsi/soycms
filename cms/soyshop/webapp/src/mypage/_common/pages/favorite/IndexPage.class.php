<?php

class IndexPage extends MainMyPagePageBase{
	
	private $favoriteDao;
	
	function doPost(){

	}
	
	function IndexPage(){
		//お気に入り登録プラグインがアクティブでない場合はトップページに飛ばす
		if(!SOYShopPluginUtil::checkIsActive("common_favorite_item")){
			$this->jumpToTop();
		}
		
		$mypage = MyPageLogic::getMyPage();
		
		//ログインしていない場合はログイン画面に飛ばす
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}
		
		SOY2::imports("module.plugins.common_favorite_item.domain.*");
		$this->favoriteDao = SOY2DAOFactory::create("SOYShop_FavoriteItemDAO");
		
		WebPage::WebPage();
		
		$user = $this->getUser();
		
		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));
		
		$items = $this->favoriteDao->getFavoriteItems($user->getId());
		
		$this->addModel("no_favorite", array(
			"visible" => (count($items) === 0)
		));
		
		$this->addModel("is_favorite", array(
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