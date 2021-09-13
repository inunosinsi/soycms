<?php
class TagCloudConfig extends SOYShopConfigPageBase{

	/**
	 * @return string
	 */
	function getConfigPage(){
		if(isset($_GET["category"])){
			$pageClassName = "TCCategoryPage";
		}else{
			$pageClassName = "TCConfigPage";
		}
		SOY2::import("module.plugins.tag_cloud.config." . $pageClassName);
		$form = SOY2HTMLFactory::createInstance($pageClassName);
		$form->setConfigObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * @return string
	 * 拡張設定に表示されたモジュールのタイトルを表示する
	 */
	function getConfigPageTitle(){
		if(isset($_GET["category"])){
			return "タグのカテゴリ分け";
		}else{
			return "タグクラウドの設定";
		}
	}

}
SOYShopPlugin::extension("soyshop.config", "tag_cloud", "TagCloudConfig");
