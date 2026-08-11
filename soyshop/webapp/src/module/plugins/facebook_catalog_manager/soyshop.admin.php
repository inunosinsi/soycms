<?php
class FacebookCatalogManagerAdmin extends SOYShopAdminBase{

	function execute(){
		//小カテゴリのリストを取得
		if(isset($_GET["facebook_catalog_manager"])){
			if(isset($_POST["hierarchy"]) && isset($_POST["key"])){
				$list = SOY2Logic::createInstance("module.plugins.facebook_catalog_manager.logic.TaxonomyLogic")->getTaxonomy($_POST["hierarchy"], $_POST["key"]);
				echo json_encode($list);
				exit;
			}
		}
	}
}
SOYShopPlugin::extension("soyshop.admin", "facebook_catalog_manager", "FacebookCatalogManagerAdmin");
