<?php

class IndexPage extends CMSWebPageBase {

	function doPost(){
		if(soy2_check_token()){
			if(!isset($_POST["site_id"]) || !is_numeric($_POST["site_id"])) self::_error();

			$selectedSiteId = (int)$_POST["site_id"];

			$mode = (isset($_POST["mode"])) ? (string)$_POST["mode"] : "label";
			switch($mode){
				case "site":
					$old = ($selectedSiteId !== CMSUtil::getCurrentSiteId()) ? CMSUtil::switchOtherSite($selectedSiteId) : array();
					try{
						$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
					}catch(Exception $e){
						$labels = array();
					}

					$list = array();
					if(count($labels)){
						foreach($labels as $label){
							$list[] = array("id" => $label->getId(), "caption" => $label->getCaption());
						}
					}
					if(count($old)) CMSUtil::resetOtherSite($old);
					echo json_encode(array("soy2_token" => soy2_get_token(), "result" => 1, "list" => $list));
					exit;
				default:
				case "label":
					if(!isset($_POST["label_id"]) || !is_numeric($_POST["label_id"])) self::_error();

					$lim = (isset($_POST["entry_field_count"]) && is_numeric($_POST["entry_field_count"])) ? (int)$_POST["entry_field_count"] : 20;

					$old = ($selectedSiteId !== CMSUtil::getCurrentSiteId()) ? CMSUtil::switchOtherSite($selectedSiteId) : array();
					$res = SOY2Logic::createInstance("site_include.plugin.CustomField.logic.EntryFieldLogic")->getEntriesByLabelId($_POST["label_id"], $lim);
					if(count($old)) CMSUtil::resetOtherSite($old);
					echo json_encode(array("soy2_token" => soy2_get_token(), "result" => 1, "list" => $res));
					exit;
			}
		}
	}

	private function _error(){
		echo json_encode(array("soy2_token" => soy2_get_token(), "result" => 0));
		exit;
	}
}
