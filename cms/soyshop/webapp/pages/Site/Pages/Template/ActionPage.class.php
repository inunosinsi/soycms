<?php

class ActionPage extends WebPage{

    function ActionPage($args) {
    	$id = $args[0];

    	$dao = SOY2DAOFactory::create("site.SOYShop_PageDAO");
    	$obj = $dao->getById($id);

    	if(isset($_GET["generate"])){

			$old = $obj->getTemplateFilePath();
			$custom = $obj->getCustomTemplateFilePath();

			if(!file_exists(dirname($custom))){
				mkdir(dirname($custom));
			}

			$old = file_get_contents($old);

			//一回保存しなおした奴
			$resotre = dirname($custom) . "/" . "_" . basename($custom);
			if(file_exists($resotre)){
				$old = file_get_contents($resotre);
			}

			file_put_contents($custom,$old);

    	}

    	if(isset($_GET["generate_css"])){

			$pageLogic = SOY2Logic::createInstance("logic.site.page.PageLogic");
			$pageLogic->generateCSSFile($obj);

    	}

    	if(isset($_GET["restore"])){

			$custom = $obj->getCustomTemplateFilePath();
			$new = dirname($custom) . "/" . "_" . basename($custom);

			rename($custom,$new);

    	}

    	SOY2PageController::jump("Site.Pages.Detail." . $obj->getId() ."?updated");
    	exit;
    }
}
?>