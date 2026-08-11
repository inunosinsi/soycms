<?php
class CommonNotepad extends SOYShopNotepadBase {

	function __construct(){
		SOY2::import("module.plugins.common_notepad.component.NotepadListAreaComponent");
	}

	function buildItemNotepad(int $itemId){
		if($itemId === 0) return "";
		$component = new NotepadListAreaComponent();
		$component->setItem(soyshop_get_item_object($itemId));
		$component->setPluginId($this->getPluginId());

		return $component->buildBlock();
	}

	function buildCategoryNotepad(int $categoryId){
		if($categoryId === 0) return "";
		$component = new NotepadListAreaComponent();
		$component->setCategory(soyshop_get_category_object($categoryId));
		$component->setPluginId($this->getPluginId());

		return $component->buildBlock();
	}

	function buildUserNotepad(int $userId){
		if($userId === 0) return "";
		$component = new NotepadListAreaComponent();
		$component->setUser(soyshop_get_user_object($userId));
		$component->setPluginId($this->getPluginId());

		return $component->buildBlock();
	}
}
SOYShopPlugin::extension("soyshop.notepad", "common_notepad", "CommonNotepad");
