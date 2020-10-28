<?php
class CustomFieldAdvancedPluginFormPage extends WebPage{

	private $pluginObj;

	const SHOW_INPUT_YES   = 1;
	const SHOW_INPUT_NO    = 0;
	const SHOW_INPUT_LABEL = 2;


	function __construct(){}

	function doPost(){

		//カスタムフィールドから設定をインポート
		if(soy2_check_token() && isset($_POST["import"])){
			$migrateLogic = SOY2Logic::createInstance("site_include.plugin.CustomFieldAdvanced.logic.MigrateConfigLogic", array("pluginObj" => $this->pluginObj));
			$migrateLogic->import();
		}

		//CSVエクスポート
		if(isset($_POST["csv"])){
			$this->pluginObj->exportFields();
		}

		//CSVインポート
		if(isset($_POST["upload"])){
			$this->pluginObj->importFields();
		}

		if(isset($_POST["display_config"])){
			$this->pluginObj->updateDisplayConfig($_POST["display_config"]);
		}elseif(isset($_POST["delete_submit"])){
			$this->pluginObj->deleteField($_POST["delete_submit"]);
		}else if(isset($_POST["update_submit"]) && isset($_POST["label"]) && isset($_POST["type"])){
			$this->pluginObj->update($_POST["update_submit"],$_POST["label"],$_POST["type"]);
		}else if(isset($_POST["move_up"]) && isset($_POST["field_id"])){
			$this->pluginObj->moveField($_POST["field_id"],-1);
		}else if(isset($_POST["move_down"]) && isset($_POST["field_id"])){
			$this->pluginObj->moveField($_POST["field_id"],1);
		}else if(isset($_POST["update_advance"]) && isset($_POST["config"])){

			//入力欄の表示・非表示設定
			if(isset($_POST["config"]["showInput"])){
				if($_POST["config"]["showInput"] == self::SHOW_INPUT_YES){
					$_POST["config"]["showInput"] = true;
					//常に表示を選んだときはラベル設定をクリアする
					$_POST["config"]["labelId"] = "";
				}elseif($_POST["config"]["showInput"] == self::SHOW_INPUT_LABEL){
					$_POST["config"]["showInput"] = true;
				}elseif($_POST["config"]["showInput"] == self::SHOW_INPUT_NO){
					$_POST["config"]["showInput"] = false;
				}
			}

			//pairの値は配列からシリアライズした文字列にしてExtraValuesに格納する
			if(isset($_POST["pair"]) && is_array($_POST["pair"]) && count($_POST["pair"])){
				$pairConf = array();
				$pairConf["count"] = (isset($_POST["pair_count"]) && (int)$_POST["pair_count"] > 0) ? (int)$_POST["pair_count"] : 1;
				$pairConf["pair"] = $_POST["pair"];
				$_POST["config"]["extraValues"] = soy2_serialize($pairConf);
			}

			$this->pluginObj->updateAdvance($_POST["update_advance"],(object)$_POST["config"]);

		}else{
			$data = new CustomField($_POST);
			if(strlen($data->getId())>0){
				$this->pluginObj->insertField($data);
			}
		}

		CMSUtil::notifyUpdate();
		CMSPlugin::redirectConfigPage();

	}

	function execute(){
		parent::__construct();

		self::buildCreateForm();

		//$this->pluginObj->importFields();
		//$this->pluginObj->deleteAllFields();

		DisplayPlugin::toggle("field_table", count($this->pluginObj->customFields));
		DisplayPlugin::toggle("no_field", !count($this->pluginObj->customFields));
		DisplayPlugin::toggle("add_field", (count($this->pluginObj->customFields) < 1));

		//カスタムフィールドから設定をインポート
		$this->addForm("import_form");

		//ラベルの取得
		$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();

		SOY2::import("site_include.plugin.CustomFieldAdvanced.component.CustomFieldListComponent");
		$this->createAdd("field_list","CustomFieldListComponent",array(
			"list"=>$this->pluginObj->customFields,
			"labels" => $labels
		));

		/* カスタムフィールド全体の設定変更用 */
		$this->createAdd("config_display_title","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "display_config[display_title]",
			"value"    => 1,
			"selected" => $this->pluginObj->displayTitle,
			"isBoolean"=> true,
			"elementId"=> "config_display_title",
			"label"    => "「カスタムフィールド」を表示する",
			"onclick"  => "update_display_sample()"
		));
		$this->createAdd("config_display_id","HTMLCheckBox",array(
			"type"     => "checkbox",
			"name"     => "display_config[display_id]",
			"value"    => 1,
			"selected" => $this->pluginObj->displayID,
			"isBoolean" => true,
			"elementId"=> "config_display_id",
			"label"    => "IDを表示する",
			"onclick"  => "update_display_sample()"
		));

		$this->addCheckBox("acceleration", array(
			"type" => "checkbox",
			"name" => "display_config[acceleration]",
			"value" => 1,
			"selected" => ($this->pluginObj->getAcceleration() == 1),
			"label" => "表示の高速化"
		));

		$this->addForm("csv_export_form");
	}

	private function buildCreateForm(){
        $this->addForm("create_form");

        $this->addSelect("custom_type_select", array(
            "name" => "type",
            "options" => CustomField::$TYPES
        ));
    }

	function getPluginObj() {
		return $this->pluginObj;
	}
	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
