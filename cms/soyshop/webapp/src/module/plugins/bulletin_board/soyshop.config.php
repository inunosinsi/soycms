<?php
class BulletinBoardConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["group_id"]) && is_numeric($_GET["group_id"])){
			SOY2::import("module.plugins.bulletin_board.config.GroupDetailPage");
			$form = SOY2HTMLFactory::createInstance("GroupDetailPage");
		}else{
			SOY2::import("module.plugins.bulletin_board.config.BoardConfigPage");
			$form = SOY2HTMLFactory::createInstance("BoardConfigPage");
		}

		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		if(isset($_GET["group_id"]) && is_numeric($_GET["group_id"])){
			return SOY2Logic::createInstance("module.plugins.bulletin_board.logic.GroupLogic")->getById($_GET["group_id"])->getName() . "の編集";
		}else{
			return "SOY Board on SOY Shop";
		}
	}
}
SOYShopPlugin::extension("soyshop.config", "bulletin_board", "BulletinBoardConfig");
