<?php

class CustomAliasPluginFormPage extends WebPage{

	private $pluginObj;

	function __construct(){}

	function doPost(){

    	if(soy2_check_token()){
			if(isset($_POST["save"])){
				$this->pluginObj->setMode((int)$_POST["mode"]);
				if(isset($_POST["custom_alias_prefix"])) $this->pluginObj->setPrefix($_POST["custom_alias_prefix"]);
				if(isset($_POST["custom_alias_postfix"])) $this->pluginObj->setPostfix($_POST["custom_alias_postfix"]);

				if(isset($_POST["manualCfg"])){
					CustomAliasUtil::saveAdvancedConfig(CustomAliasUtil::MODE_MANUAL, $_POST["manualCfg"]);
				}

				if(isset($_POST["IdCfg"])){
					CustomAliasUtil::saveAdvancedConfig(CustomAliasUtil::MODE_ID, $_POST["IdCfg"]);
				}

				if(isset($_POST["RandomCfg"])){
					CustomAliasUtil::saveAdvancedConfig(CustomAliasUtil::MODE_RANDOM, $_POST["RandomCfg"]);
				}

				CMSPlugin::savePluginConfig($this->pluginObj->getId(),$this->pluginObj);
			}

			if(isset($_POST["all_change"]) || isset($_POST["all_restore"])){
				set_time_limit(0);

				$logic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");

				//50件ずつ記事を取得
				$offset = 0;
				$dao = SOY2DAOFactory::create("cms.EntryDAO");
				$dao->setLimit(50);

				for(;;){
					$dao->setOffset($offset++);
					try{
						$entries = $dao->get();
					}catch(Exception $e){
						break;
					}
					if(!count($entries)) break;

					foreach($entries as $entry){
						if(isset($_POST["all_change"])){
							if($entry->getId() == $entry->getAlias()) continue;
							$entry->setAlias($entry->getId());
						}else if(isset($_POST["all_restore"])){
							$alias = $logic->getUniqueAlias($entry->getId(), $entry->getTitle());
							$entry->setAlias($alias);
						}

						try{
							$dao->update($entry);
						}catch(Exception $e){
							//
						}
					}
				}
			}

			CMSPlugin::redirectConfigPage();
    	}
	}

	function execute(){
		parent::__construct();

		self::_buildConfigForm();
		self::_buildManualConfigForm();
		self::_buildIdConfigForm();
		self::_buildRandomConfigForm();
		self::_buildChangeForm();
	}

	private function _buildConfigForm(){
		$this->addForm("custom_alias_form", array());

		$this->addInput("custom_alias_prefix", array(
			"name" => "custom_alias_prefix",
			"value" => $this->pluginObj->getPrefix(),
		));
		$this->addInput("custom_alias_postfix", array(
			"name" => "custom_alias_postfix",
			"value" => $this->pluginObj->getPostfix(),
		));

		$this->addCheckBox("mode_manual", array(
			"name" => "mode",
			"value" => CustomAliasUtil::MODE_MANUAL,
			"selected" => (self::_mode() == CustomAliasUtil::MODE_MANUAL),
			"label" => "手動"
		));

		$this->addCheckBox("mode_manual", array(
			"name" => "mode",
			"value" => CustomAliasUtil::MODE_MANUAL,
			"selected" => (self::_mode() == CustomAliasUtil::MODE_MANUAL),
			"label" => "手動"
		));

		$this->addCheckBox("mode_id", array(
			"name" => "mode",
			"value" => CustomAliasUtil::MODE_ID,
			"selected" => (self::_mode() == CustomAliasUtil::MODE_ID),
			"label" => "ID"
		));

		$this->addCheckBox("mode_hash", array(
			"name" => "mode",
			"value" => CustomAliasUtil::MODE_HASH,
			"selected" => (self::_mode() == CustomAliasUtil::MODE_HASH),
			"label" => "ハッシュ値"
		));

		$this->addCheckBox("mode_random", array(
			"name" => "mode",
			"value" => CustomAliasUtil::MODE_RANDOM,
			"selected" => (self::_mode() == CustomAliasUtil::MODE_RANDOM),
			"label" => "ランダム"
		));
	}

	//互換性を持たせる
	private function _mode(){
		$mode = $this->pluginObj->getMode();
		if(is_null($mode)){
			if($this->pluginObj->getUseId()) return CustomAliasUtil::MODE_ID;
			return CustomAliasUtil::MODE_MANUAL;
		}
		return $mode;
	}

	private function _buildManualConfigForm(){
		$cfg = CustomAliasUtil::getAdvancedConfig(CustomAliasUtil::MODE_MANUAL);

		$this->addCheckBox("mode_manual_title", array(
			"name" => "manualCfg[mode]",
			"value" => CustomAliasUtil::AUTO_INSERT_TITLE,
			"selected" => ((int)$cfg["mode"] === CustomAliasUtil::AUTO_INSERT_TITLE),
			"label" => "記事名を自動挿入"
		));

		$this->addCheckBox("mode_manual_id", array(
			"name" => "manualCfg[mode]",
			"value" => CustomAliasUtil::AUTO_INSERT_ID,
			"selected" => ((int)$cfg["mode"] === CustomAliasUtil::AUTO_INSERT_ID),
			"label" => "IDモードで生成した値を自動挿入(IDモードの高度な設定の設定内容を使用します)"
		));
	}

	private function _buildIdConfigForm(){
		$cfg = CustomAliasUtil::getAdvancedConfig(CustomAliasUtil::MODE_ID);

		$this->addInput("id_cfg_prefix", array(
			"name" => "IdCfg[prefix]",
			"value" => (isset($cfg["prefix"])) ? $cfg["prefix"] : "",
			"style" =>"width:200px;"
		));

		$this->addInput("id_cfg_postfix", array(
			"name" => "IdCfg[postfix]",
			"value" => (isset($cfg["postfix"])) ? $cfg["postfix"] : "",
			"style" =>"width:200px;"
		));
	}

	private function _buildRandomConfigForm(){
		$cfg = CustomAliasUtil::getAdvancedConfig(CustomAliasUtil::MODE_RANDOM);

		$this->addInput("random_cfg_lenfth", array(
			"name" => "RandomCfg[length]",
			"value" => (isset($cfg["length"])) ? $cfg["length"] : 12,
			"style" =>"width:80px;"
		));

		foreach(array(CustomAliasUtil::INCLUDE_DIGIT => "数字", CustomAliasUtil::INCLUDE_LOWER => "小文字", CustomAliasUtil::INCLUDE_UPPER => "大文字") as $idx => $l){
			$this->addCheckBox("include_" . $idx, array(
				"name" => "RandomCfg[include][]",
				"value" => $idx,
				"selected" => (isset($cfg["include"]) && is_numeric(array_search($idx, $cfg["include"]))),
				"label" => $l
			));
		}

		//ラベル毎の設定
		$logic = SOY2Logic::createInstance("site_include.plugin.custom_alias.logic.RandomAliasLogic");
		$list = $logic->getLabelList();
		DisplayPlugin::toggle("random_labels", count($list));

		$labelCheckedList = (isset($cfg["label"]) && is_array($cfg["label"])) ? $cfg["label"] : array();

		//name="RandomCfg[label][]"でチェックボックスを作る
		$this->addLabel("random_checkbox", array(
			"html" => (count($list)) ? $logic->buildLabelCheckboxes($list, $labelCheckedList) : ""
		));
	}

	private function _buildChangeForm(){
		$this->addForm("all_change_form");
	}

	function setPluginObj($pluginObj) {
		$this->pluginObj = $pluginObj;
	}
}
