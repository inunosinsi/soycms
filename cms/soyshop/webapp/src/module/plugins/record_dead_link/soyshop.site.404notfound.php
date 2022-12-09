<?php

class RecordDeadLink404NotFoundModule extends SOYShopSite404NotFoundAction{

	function execute(){
		if(isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], $_SERVER["HTTP_HOST"]) == false){
			SOY2::imports("module.plugins.record_dead_link.domain.*");
			$dao = SOY2DAOFactory::create("SOYShop_RecordDeadLinkDAO");
			
			$obj = new SOYShop_RecordDeadLink();
			$obj->setReferer($_SERVER["HTTP_REFERER"]);
			$obj->setUrl($_SERVER["REQUEST_URI"]);
			
			try{
				$dao->insert($obj);
			}catch(Exception $e){
				var_dump($e);
			}
		}
	}
}

SOYShopPlugin::extension("soyshop.site.404notfound", "record_dead_link", "RecordDeadLink404NotFoundModule");
?>