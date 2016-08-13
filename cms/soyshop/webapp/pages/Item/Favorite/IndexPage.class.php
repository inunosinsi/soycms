<?php

class IndexPage extends WebPage{
	
	private $itemId;
	
	function __construct($args){
		
		$this->itemId = (isset($args[0])) ? (int)$args[0] : null;
		
		$favoriteLogic = SOY2Logic::createInstance("module.plugins.common_favorite_item.logic.FavoriteLogic");
		$users = $favoriteLogic->getUsersByFavoriteItemId($this->itemId);
		
		WebPage::__construct();
		
		$this->createAdd("user_list", "_common.User.UserListComponent", array(
			"list" => $users
		));
	}
}
?>