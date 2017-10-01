<?php

class UserGroupDetailPage extends WebPage{

	private $configObj;
	private $detailId;

	function __construct(){
		SOY2::imports("module.plugins.user_group.domain.*");
		SOY2::import("module.plugins.user_group.util.UserGroupCustomSearchFieldUtil");
	}

	function doPost(){
		if(soy2_check_token()){
			$old = self::getGroupById($this->detailId);
			$group = SOY2::cast($old, $_POST["Group"]);

			//緯度経度のカラムがある場合
			if(isset($_POST["map_lat"]) && strlen($_POST["map_lat"])){
				$group->setLat($_POST["map_lat"]);
				$group->setLng($_POST["map_lng"]);
			}

			//新規登録
			if(is_null($group->getId())){
				try{
					$this->detailId = self::groupDao()->insert($group);
				}catch(Exception $e){
					var_dump($e);
				}
			//更新
			}else{
				try{
					self::groupDao()->update($group);
				}catch(Exception $e){
					var_dump($e);
				}
			}

			//カスタムサーチフィールド
			if(isset($_POST["user_group_custom"]) && count($_POST["user_group_custom"])){
				SOY2Logic::createInstance("module.plugins.user_group.logic.UserGroupDataBaseLogic")->save($this->detailId, $_POST["user_group_custom"]);
			}

			SOY2PageController::jump("Extension.Detail.user_group." . $this->detailId . "?updated");
		}
		SOY2PageController::jump("Extension.Detail.user_group." . $this->detailId . "?error");
	}

	function execute(){
		parent::__construct();

		DisplayPlugin::toggle("error", isset($_GET["error"]));

		self::buildForm();
	}

	private function buildForm(){

		$group = self::getGroupById($this->detailId);

		$this->addForm("form");

		$this->addInput("name", array(
			"name" => "Group[name]",
			"value" => $group->getName()
		));

		$this->addInput("code", array(
			"name" => "Group[code]",
			"value" => $group->getCode()
		));

		//カスタムサーチフィールド
		$html = array();
		SOY2::import("module.plugins.user_group.component.GroupFieldFormComponent");
		$configs = UserGroupCustomSearchFieldUtil::getConfig();
		if(count($configs)){
			$isMap = "false";	//地図付き住所のカラムがあるか？
			$mapApiKey = null;

			$values = SOY2Logic::createInstance("module.plugins.user_group.logic.UserGroupDataBaseLogic")->getByGroupId($this->detailId);
			foreach($configs as $key => $field){
				if(!isset($field["label"]) || !strlen($field["label"])) continue;

				//地図付き住所のカラムがあるか？
				if($field["type"] === UserGroupCustomSearchFieldUtil :: TYPE_MAP){
					$isMap = true;
					if(isset($field["mapKey"])) $mapApiKey = trim($field["mapKey"]);
				}

				$value = (isset($values[$key])) ? $values[$key] : null;

				$html[] = "<dt>" . htmlspecialchars($field["label"], ENT_QUOTES, "UTF-8") . "</dt>\n" .
							"<dd>" . GroupFieldFormComponent::buildForm($key, $field, $value, false, false, $group->getLat(), $group->getLng()) . "</dd>";
			}
		}

		$this->addLabel("build_form", array(
			"html" => implode("\n", $html)
		));

		//地図付き住所の場合
		DisplayPlugin::toggle("map_script", $isMap);
		$this->addModel("google_map_src", array(
			"src" => "https://maps.googleapis.com/maps/api/js?key=" . $mapApiKey . "&callback=initMap"
		));

		$this->addLabel("map_js", array(
			"html" => file_get_contents(dirname(dirname(__FILE__)) . "/js/map.js")
		));
	}

	private function getGroupById($groupId){
		try{
			return self::groupDao()->getById($groupId);
		}catch(Exception $e){
			return new SOYShop_UserGroup();
		}
	}

	private function groupDao(){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("SOYShop_UserGroupDAO");
		return $dao;
	}

	function setConfigObj($configObj){
        $this->configObj = $configObj;
	}

	function setDetailId($detailId){
    	$this->detailId = $detailId;
    }
}
