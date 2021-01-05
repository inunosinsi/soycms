<?php
class BulletinBoardConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		SOY2::import("module.plugins.bulletin_board.config.BoardConfigPage");
		$form = SOY2HTMLFactory::createInstance("BoardConfigPage");
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 */
	function getConfigPageTitle(){
		return "SOY Board on SOY Shop";
	}
}
SOYShopPlugin::extension("soyshop.config", "bulletin_board", "BulletinBoardConfig");
