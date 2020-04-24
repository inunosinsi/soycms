<?php

class LabelConfigPage extends WebPage{

	private $config;
	private $itemId;

	function __construct(){
		SOY2::imports("module.plugins.reserve_calendar.domain.*");
		SOY2::imports("module.plugins.reserve_calendar.component.label.*");
	}

	function doPost(){
		if(soy2_check_token()){
			$dao = SOY2DAOFactory::create("SOYShopReserveCalendar_LabelDAO");

			$isUpdated = false;

			//登録
			if(isset($_POST["new_label"]) && strlen($_POST["new_label"])){
				$obj = new SOYShopReserveCalendar_Label();
				$obj->setLabel($_POST["new_label"]);
				$obj->setItemId($this->itemId);

				if(isset($_POST["new_display_order"]) && (int)$_POST["new_display_order"] > 0){
					$obj->setDisplayOrder($_POST["new_display_order"]);
				}

				try{
					$dao->insert($obj);
					$isUpdated = true;
				}catch(Exception $e){
					var_dump($e);
				}
			}

			//更新
			if(isset($_POST["Label"])){
				foreach($_POST["Label"]["label"] as $id => $values){
					try{
						$obj = $dao->getById($id);
					}catch(Exception $e){
						continue;
					}

					$obj->setLabel($_POST["Label"]["label"][$id]);
					$obj->setDisplayOrder($_POST["Label"]["displayOrder"][$id]);

					try{
						$dao->update($obj);
						$isUpdated = true;
					}catch(Exception $e){
						var_dump($e);
					}
				}
			}

			if($isUpdated) $this->config->redirect("updated&label&item_id=" . $this->itemId);
		}

		$this->config->redirect("error&label&item_id=" . $this->itemId);
	}

	function execute(){

		//削除
		if(isset($_GET["remove"])){
			if(soy2_check_token()){
				self::removeLabel($_GET["remove"]);
				$this->config->redirect("removed&label&item_id=" . $this->itemId);
			}
		}

		parent::__construct();

		DisplayPlugin::toggle("removed", (isset($_GET["removed"])));
		DisplayPlugin::toggle("error", (isset($_GET["error"])));

		$this->addLink("back_link", array(
			"link" => SOY2PageController::createLink("Config.Detail?plugin=reserve_calendar&calendar&item_id=" . $this->itemId),
			"text" => soyshop_get_item_object($this->itemId)->getName() . "の詳細ページに戻る"
		));

		$this->addForm("form");

		$this->createAdd("label_list", "ScheduleLabelListComponent", array(
			"list" => SOY2Logic::createInstance("module.plugins.reserve_calendar.logic.Calendar.LabelLogic")->getLabelsByItemId($this->itemId)
		));
	}

	private function removeLabel($labelId){
		try{
			SOY2DAOFactory::create("SOYShopReserveCalendar_LabelDAO")->deleteById($labelId);
		}catch(Exception $e){
			var_dump($e);
		}
	}

	function setConfigObj($obj) {
		$this->config = $obj;
	}
	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
