<?php
LabelFieldPlugin::register();

class LabelFieldPlugin {

	const PLUGIN_ID = "label_field";

	private $configs = array(
		//"label" => array("label" => "ラベルフィールド", "postfix" => "label")	//postfixがIDになる
	);

	function getId(){
		return self::PLUGIN_ID;
	}

	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "ラベルフィールド",
			"description" => "カスタムフィールドアドバンスドで実現できなかったラベルフィールドをこのプラグインで実装した",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co/article/3886",
			"mail" => "tsuyoshi@saitodev.co",
			"version" => "0.5"
		));

		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			CMSPlugin::addPluginConfigPage(self::PLUGIN_ID,array(
				$this,"config_page"
			));

			if(defined("_SITE_ROOT_")){
				//
			}else{
				SOY2::import("site_include.plugin.LabelField.util.OutputLabeledEntriesUtil");
				CMSPlugin::setEvent("onPageUpdate", self::PLUGIN_ID, array($this, "onPageUpdate"));
				CMSPlugin::setEvent("onBlogPageUpdate", self::PLUGIN_ID, array($this, "onBlogPageUpdate"));

				CMSPlugin::setEvent('onEntryUpdate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', self::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCopy', self::PLUGIN_ID, array($this, "onEntryCopy"));
				CMSPlugin::setEvent('onEntryRemove', self::PLUGIN_ID, array($this, "onEntryRemove"));

				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			}

		}
	}

	function config_page(){
		SOY2::import("site_include.plugin.LabelField.config.OutputLabeledEntriesConfigPage");
		$form = SOY2HTMLFactory::createInstance("OutputLabeledEntriesConfigPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	//モジュールを作成
	function onPageUpdate($arg){
		if(!is_array($this->configs) || !count($this->configs)) return;

		$new = $arg["new_page"];
		self::_generateLabelFieldModule($new->getTemplate());
	}

	function onBlogPageUpdate($arg){
		if(!is_array($this->configs) || !count($this->configs)) return;

		$new = $arg["new_page"];
		//テンプレートを精査して、cms:moduleを作成する必要があるか？調べる
		self::_generateLabelFieldModule($new->getTopTemplate());
		self::_generateLabelFieldModule($new->getArchiveTemplate());
		self::_generateLabelFieldModule($new->getEntryTemplate());
	}

	private function _generateLabelFieldModule($template){
		preg_match_all('/cms:module=\"labelfield\.(.*?)\"/', $template, $tmp);
		if(!isset($tmp[1]) || !count($tmp[1])) return;

		$arr = array_unique($tmp[1]);
		$keys = array_keys($this->configs);


		$modDir = self::_moduleDir();


		foreach($arr as $fieldId){
			$hit = false;
			foreach($keys as $key){
				if($fieldId == $key){
					$hit = true;
				}else{	//cms:module="labelfield.{fieldId}_数字"に対応
					preg_match('/^' . $key . '_\d*?/', $fieldId, $tmp);
					if(isset($tmp[0])) $hit = true;
				}
			}
			if(!$hit) continue;

			//モジュールを生成する
			$file = file_get_contents(dirname(__FILE__) . "/module/template.txt");
			$file = str_replace("##field_id##", $fieldId, $file);
			file_put_contents($modDir . $fieldId . ".php", $file);
		}
	}

	private function _moduleDir(){
		$dir = UserInfoUtil::getSiteDirectory() . ".module/";
		if(!file_exists($dir)) mkdir($dir);
		$dir .= "labelfield/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir;
	}

	/**
	 * 記事投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
		return self::_buildFormOnEntryPage($entryId);
	}

	/**
	 * ブログ記事 投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;
		return self::_buildFormOnEntryPage($entryId);
	}

	// いずれはフィールドの複数対応をしたい
	private function _buildFormOnEntryPage($entryId){
		$html = array();
		//ラベル一覧
		$labels = OutputLabeledEntriesUtil::getLabels();
		if(count($labels) && count($this->configs)){
			foreach($this->configs as $cnf){
				if(!isset($cnf["postfix"]) && !strlen($cnf["postfix"])) continue;
				if(!isset($cnf["label"]) && !strlen($cnf["label"])) continue;

				$postfix = trim($cnf["postfix"]);
				$label = trim($cnf["label"]);

				$selectedLabelId = (is_numeric($entryId)) ? OutputLabeledEntriesUtil::getSelectedLabelId($entryId, $postfix) : 0;
				if(is_numeric($selectedLabelId) && $selectedLabelId > 0){
					$displayCount = OutputLabeledEntriesUtil::getDisplayCount($entryId, $postfix);
					$displaySort = OutputLabeledEntriesUtil::getDisplaySort($entryId, $postfix);
				}else{
					$displayCount = OutputLabeledEntriesUtil::DISPLAY_COUNT;
					$displaySort = OutputLabeledEntriesUtil::SORT_ASC;
				}

				$html[] = '<div class="section custom_field form-inline">';
				$html[] = "<label>" . $label . "(cms:module=\"labelfield." . $postfix . "\")</label><br>";
				$html[] = "\t<select name=\"" . OutputLabeledEntriesUtil::FIELD_ID . "_" . $postfix . "\">";
				$html[] = "\t\t<option></option>";
				foreach($labels as $labelId => $caption){
					if($selectedLabelId > 0 && $selectedLabelId === (int)$labelId){
						$html[] = "\t\t<option value=\"" . $labelId . "\" selected>" . $caption . "</option>";
					}else{
						$html[] = "\t\t<option value=\"" . $labelId . "\">" . $caption . "</option>";
					}
				}
				$html[] = "\t</select> ";
				$html[] = "<input type=\"number\" class=\"form-control\" name=\"" . OutputLabeledEntriesUtil::FIELD_ID . "_" . $postfix . "_config[displayCount]\" style=\"width:70px;\" value=\"" . $displayCount . "\">件  ";
				$html[] = "<select name=\"" . OutputLabeledEntriesUtil::FIELD_ID . "_" . $postfix . "_config[sort]\">";
				foreach(OutputLabeledEntriesUtil::getSortTypes() as $key => $label){
					if($key == $displaySort){
						$html[] = "<option value=\"" . $key . "\" selected=\"selected\">" . $label . "</option>";
					}else{
						$html[] = "<option value=\"" . $key . "\">" . $label . "</option>";
					}
				}
				$html[] = "</select>";
				$html[] = '</div>';
			}
		}

		return implode("\n", $html);
	}

	/**
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($arg){
		if(count($this->configs)){
			$arg = SOY2PageController::getArguments();
			$entryId = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : null;
			foreach($this->configs as $cnf){
				if(!isset($cnf["postfix"]) || !strlen($cnf["postfix"])) continue;
				$fieldId = OutputLabeledEntriesUtil::FIELD_ID . "_" . $cnf["postfix"];
				OutputLabeledEntriesUtil::save($entryId, $fieldId, $_POST[$fieldId]);

				//フィールド毎の設定
				$cnfFieldId = $fieldId . "_config";
				$v = null;
				if(isset($_POST[$cnfFieldId]) && is_array($_POST[$cnfFieldId]) && self::_checkIsValueOnArray($_POST[$cnfFieldId])){
					$v = soy2_serialize($_POST[$cnfFieldId]);
				}
				OutputLabeledEntriesUtil::save($entryId, $cnfFieldId, $v);
			}
		}

		return true;
	}

	private function _checkIsValueOnArray($arr){
		if(!count($arr)) return false;

		foreach($arr as $v){
			$v = trim($v);
			if(strlen($v)) return true;
		}

		return false;
	}

	/**
	 * 記事複製時
	 */
	function onEntryCopy($args){
		if(count($this->configs)){
			list($old, $new) = $args;
			foreach($this->configs as $cnf){
				if(!isset($cnf["postfix"]) || !strlen($cnf["postfix"])) continue;
				$fieldId = OutputLabeledEntriesUtil::FIELD_ID . "_" . $cnf["postfix"];
				OutputLabeledEntriesUtil::save($new, $fieldId, OutputLabeledEntriesUtil::getSelectedLabelId($old, $cnf["postfix"]));
			}
		}
		return true;
	}

	/**
	 * 記事削除時
	 * @param array $args エントリーID
	 */
	function onEntryRemove($args){
		if(count($this->configs)){
			foreach($args as $entryId){
				foreach($this->configs as $cnf){
					if(!isset($cnf["postfix"]) || !strlen($cnf["postfix"])) continue;
					$fieldId = OutputLabeledEntriesUtil::FIELD_ID . "_" . $cnf["postfix"];
					OutputLabeledEntriesUtil::save($entryId, $fieldId, "");
				}
			}
		}
		return true;
	}

	function getConfigs(){
		return $this->configs;
	}
	function setConfigs($configs){
		$this->configs = $configs;
	}

	public static function register(){
		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(!$obj) $obj = new LabelFieldPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
