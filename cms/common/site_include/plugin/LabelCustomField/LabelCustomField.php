<?php

LabelCustomFieldPlugin::register();

class LabelCustomFieldPlugin{

	const PLUGIN_ID = "LabelCustomFieldPlugin";

	function getId(){
		return self::PLUGIN_ID;
	}

	//カスタムフィールドの項目設定
	var $customFields = array();

	//アドバンス用のフィールドで高度な設定のラベル紐づけを考慮したフィールドリストを毎回取得しないようにするフラグ
	private $prevLabelId;
	private $prevFieldIds = array();

	private $displayLogic;


	function init(){
		CMSPlugin::addPluginMenu(self::PLUGIN_ID, array(
			"name" => "ラベルカスタムフィールド",
			"description" => "",
			"author" => "齋藤毅",
			"url" => "https://saitodev.co/article/3532",
			"mail" => "info@saitodev.co",
			"version"=>"0.7"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			SOY2::import("site_include.plugin.LabelCustomField.util.LabelCustomfieldUtil");
			include_once(SOY2::RootDir() . "site_include/plugin/CustomFieldAdvanced/func/func.php");	//便利な関数

			//管理側
			if(!defined("_SITE_ROOT_")){

				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
					$this,"config_page"
				));

				CMSPlugin::setEvent('onLabelUpdate', self::PLUGIN_ID, array($this, "onLabelUpdate"));
				CMSPlugin::setEvent('onLabelCreate', self::PLUGIN_ID, array($this, "onLabelUpdate"));
				CMSPlugin::setEvent('onLabelRemove', self::PLUGIN_ID, array($this, "onLabelRemove"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Label.Detail", array($this, "onCallCustomField"));

			//公開側
			}else{
				CMSPlugin::setEvent('onLabelOutput', self::PLUGIN_ID, array($this, "onLabelOutput"));
			}

		}else{
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	/**
	 * onLabelOutput
	 */
	function onLabelOutput($arg){

		$labelId = $arg["labelId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		$fields = $this->getCustomFields($labelId);
	
		if(count($fields)){
			$customFields = $this->customFields;
			
			//設定内に記事フィールドはあるか？
			//$isEntryField = LabelCustomfieldUtil::checkIsEntryField($customFields);
			$isListField = LabelCustomfieldUtil::checkIsListField($customFields);	//
			$isDlListField = LabelCustomfieldUtil::checkIsDlListField($customFields);	//

			foreach($fields as $field){

				//設定を取得
				$master = (isset($customFields[$field->getId()])) ? $customFields[$field->getId()] : null;

				$class = "CMSLabel";
				$attr = array(
					"html"	   => $field->getValue(),
					"soy2prefix" => "cms",
				);

				//カスタムフィールドの設定が取れるときの動作（たとえば同じサイト内の場合）
				if($master instanceof LabelCustomField){
					//$attr["html"]に改めて値を入れ直す時に使用するフラグ
					$resetFlag = true;

					//値が設定されていないなら初期値を使う
					if(is_null($field->getValue())) $field->setValue($master->getDefaultValue());
					$fieldValue = (is_string($field->getValue())) ? $field->getValue() : "";

					//空の時の動作
					if(!strlen($fieldValue)){
						if($master->getHideIfEmpty()){
							//空の時は表示しない
							$attr["visible"] = false;
						}else{
							//空の時の値
							if(is_string($master->getEmptyValue())) $field->setValue($master->getEmptyValue());
						}
					}

					//タイプがリンクの場合はここで上書き
					if($master->getType() == "link"){
						$class = "HTMLLink";
						$attr["link"] = (strlen($fieldValue)) ? $fieldValue : null;
						unset($attr["html"]);
						$resetFlag = false;

					//画像の場合
					}else if($master->getType() == "image"){
						$class = "HTMLImage";
						$attr["src"] = (strlen($fieldValue)) ? $fieldValue : null;
						unset($attr["html"]);
						$resetFlag = false;
					}

					//リンク、もしくは画像の場合、パスを表示するためのcms:id
					if($master->getType() == "link" || $master->getType() == "image"){
						$htmlObj->addLabel($field->getId() . "_text", array(
							"soy2prefix" => "cms",
							"text" => $fieldValue
						));
					}

					//複数行テキストの場合は\n\rを<br>に変換するタグを追加
					if($master->getType() == "textarea"){
						$htmlObj->addLabel($field->getId() . "_raw", array(
							"soy2prefix" => "cms",
							"html" => $fieldValue
						));
						$attr["html"] = nl2br($fieldValue);
						$htmlObj->addLabel($field->getId() . "_br_mode", array(
							"soy2prefix" => "cms",
							"html" => $attr["html"]
						));
						$resetFlag = false;
					}

					//上で空の時の値が入るかも知れず、下でunsetされる可能性があるのでここで設定し直す。
					if($resetFlag) $attr["html"] = $fieldValue;

					//リストフィールド listcomponentはCustomfieldAdvancedのファイルを流用
					if($isListField){
						if(!class_exists("ListFieldListComponent")) SOY2::import("site_include.plugin.CustomFieldAdvanced.component.ListFieldListComponent");
						$htmlObj->createAdd($field->getId() . "_list", "ListFieldListComponent", array(
							"soy2prefix" => "cms",
							"list" => ($master->getType() == "list" && is_string($attr["html"])) ? soy2_unserialize($attr["html"]) : array()
						));
					}

					//定義型リストフィールド listcomponentはCustomfieldAdvancedのファイルを流用
					if($isDlListField){
						if(!class_exists("DlListFieldListComponent")) SOY2::import("site_include.plugin.CustomFieldAdvanced.component.DlListFieldListComponent");
						$htmlObj->createAdd($field->getId() . "_dl_list", "DlListFieldListComponent", array(
							"soy2prefix" => "cms",
							"list" => ($master->getType() == "dllist" && is_string($attr["html"])) ? soy2_unserialize($attr["html"]) : array()
						));
					}

					//属性に出力
					if(is_string($master->getOutput()) && strlen($master->getOutput()) > 0){

						//リンクタイプ以外でhrefを使う場合
						if($master->getOutput() == "href" && $master->getType() != "link"){
							$class = "HTMLLink";
							$attr["link"] = $fieldValue;

						//下方互換
						}else if($master->getType() == "image" && $master->getOutput() == "src"){
							//上で処理をしているため何もしない

						//その他
						}else{
							$class = "HTMLModel";
							$attr[$master->getOutput()] = $fieldValue;
						}

						/*
						if(strlen($master->getExtraOutputs()) > 0 && is_array($field->getExtraValues())){
							foreach($field->getExtraValues() as $key => $value){
								$attr["attr:" . $key] = $value;
							}
						}
						*/
						unset($attr["html"]);//HTMLModelなのでunsetしなくても出力されないはず
					}

					//追加属性を出力
					if(is_string($master->getExtraOutputs()) && strlen($master->getExtraOutputs()) > 0){
						$extraOutputs = explode("\n", str_replace(array("\r\n", "\r"), "\n", $master->getExtraOutputs()));
						$extraValues = $field->getExtraValues();
						foreach($extraOutputs as $key => $extraOutput){
							$extraOutput = trim($extraOutput);
							$attr[$extraOutput] = (is_array($extraValues) && isset($extraValues[$extraOutput])) ? $extraValues[$extraOutput] : "";
						}

						unset($attr["html"]);//HTMLModelなのでunsetしなくても出力されないはず
					}

					//ペアフィールド
					if($master->getType() == "pair" && is_string($master->getExtraValues())){
						$extraValues = soy2_unserialize($master->getExtraValues());

						//後方互換
						if(isset($extraValues["pair"]) && is_array($extraValues["pair"])) $extraValues = $extraValues["pair"];

						if(count($extraValues)){
							foreach($extraValues as $idx => $pairValues){
								$_hash = (is_string($fieldValue) && strlen($fieldValue)) ? CustomfieldAdvancedUtil::createHash($fieldValue) : null;
								$pairValue = (isset($_hash) && isset($pairValues[$_hash])) ? $pairValues[$_hash] : "";

								$htmlObj->addModel($field->getId() . "_pair_" . ($idx + 1) . "_visible", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) > 0)
								));

								$htmlObj->addModel($field->getId() . "_pair_" . ($idx + 1) . "_is_not_empty", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) > 0)
								));

								$htmlObj->addModel($field->getId() . "_pair_" . ($idx + 1) . "_is_empty", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) === 0)
								));

								$htmlObj->addLabel($field->getId() . "_pair_" . ($idx + 1), array(
									"soy2prefix" => "cms",
									"html" => $pairValue
								));
							}
						}
					}
				}

				$htmlObj->addModel($field->getId() . "_visible", array(
					"soy2prefix" => "cms",
					"visible" => (strlen($fieldValue))
				));

				$htmlObj->addModel($field->getId() . "_is_not_empty", array(
					"soy2prefix" => "cms",
					"visible" => (strlen($fieldValue))
				));

				$htmlObj->addModel($field->getId()."_is_empty", array(
					"soy2prefix" => "cms",
					"visible" => (!strlen($fieldValue))
				));

				//SOY2HTMLのデフォルトの _visibleがあるので、$field->getId()."_visible"より後にこれをやらないと表示されなくなる
				$htmlObj->createAdd($field->getId(), $class, $attr);
			}
		}
	}

	/**
	 * プラグイン管理画面の表示
	 */
	function config_page($message){
		//$this->importFields();
		SOY2::import("site_include.plugin.LabelCustomField.config.LabelCustomFieldFormPage");
		$form = SOY2HTMLFactory::createInstance("LabelCustomFieldFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * ラベル更新時
	 */
	function onLabelUpdate($arg){
		if(!isset($arg["label"])) return;
		
		$labelId = (int)$arg["label"]->getId();
		if($labelId === 0) return 0;

		$dao = soycms_get_hash_table_dao("label_attribute");

		$postFields = (isset($_POST["custom_field"]) && is_array($_POST["custom_field"])) ? $_POST["custom_field"] : array();
		$extraFields = (isset($_POST["custom_field_extra"]) && is_array($_POST["custom_field_extra"])) ? $_POST["custom_field_extra"] : array();

		foreach($this->customFields as $key => $field){

			$value = (isset($postFields[$key])) ? $postFields[$key] : "";
			$extra = (isset($extraFields[$key]))? $extraFields[$key]: array();

			//リストフィールド
			if($field->getType() == "list" && is_array($value)){
				//空の値を除く
				$values = array();
				if(count($value)){
					foreach($value as $v){
						$v = trim($v);
						if(!strlen($v)) continue;
						$values[] = $v;
					}
				}
				$value = (count($values)) ? soy2_serialize($values) : null;
			}
			//定義型リストフィールド
			if($field->getType() == "dllist" && is_array($value)){
				//空の値を除く
				$values = array();
				if(isset($value["label"]) && isset($value["value"])){	//array("label" => array(), "value" => array())の形の値がくる
					foreach($value["label"] as $idx => $lab){
						if(!isset($value["value"][$idx])) continue;
						$lab = trim($lab);
						$val = trim($value["value"][$idx]);
						if(!strlen($lab) || !strlen($val)) continue;
						$values[] = array("label" => $lab, "value" => $val);
					}
				}
				$value = (count($values)) ? soy2_serialize($values) : null;
			}

			$attr = soycms_get_label_attribute_object($labelId, $field->getId());
			$attr->setValue($value);
			$attr->setExtraValuesArray($extra);
			soycms_save_label_attribute_object($attr);
		}

		return true;
	}

	/**
	 * ラベル削除時
	 * @param array $args ラベルID
	 */
	function onLabelRemove(array $args){
		foreach($args as $labelId){
			try{
				soycms_get_hash_table_dao("label_attribute")->deleteByLabelId($labelId);
			}catch(Exception $e){
				//
			}
		}
		return true;
	}


	/**
	 * プラグイン管理画面 カスタムフィールドの削除
	 */
	function deleteField($id){
		if(isset($this->customFields[$id])){
			unset($this->customFields[$id]);
			CMSPlugin::savePluginConfig(self::PLUGIN_ID,$this);
		}
	}

	/**
	 * プラグイン管理画面 カスタムフィールド  通常の更新
	 * ラベルと種別のみ更新
	 */
	function update($id, $value, $type){
		if(isset($this->customFields[$id])){
			$this->customFields[$id]->setLabel($value);
			$this->customFields[$id]->setType($type);
			CMSPlugin::savePluginConfig(self::PLUGIN_ID, $this);
		}
	}

	/**
	 * プラグイン管理画面 カスタムフィールド 高度な設定の更新
	 */
	function updateAdvance($id, $obj){
		if(isset($this->customFields[$id])){
			SOY2::cast($this->customFields[$id], $obj);
			CMSPlugin::savePluginConfig(self::PLUGIN_ID, $this);
		}
	}

	/**
	 * プラグイン管理画面 表示順の変更
	 */
	function moveField($id, $diff){
		if(isset($this->customFields[$id])){

			$keys = array_keys($this->customFields);
			$currentKey = array_search($id,$keys);
			$swap = ($diff > 0) ? $currentKey+1 :$currentKey-1;

			if($swap >= 0 && $swap < count($keys)){
				$tmp = $keys[$currentKey];
				$keys[$currentKey] = $keys[$swap];
				$keys[$swap] = $tmp;

				$tmpArray = array();
				foreach($keys as $index => $value){
					$field = $this->customFields[$value];
					$tmpArray[$field->getId()] = $field;
				}

				$this->customFields = $tmpArray;
			}


			CMSPlugin::savePluginConfig(self::PLUGIN_ID,$this);
		}
	}

	/**
	 * プラグイン管理画面
	 */
	function insertField(LabelCustomField $_field){
		if(isset($this->customFields[$_field->getId()])){
			return false;
		}

		$id_blacklist = array(
			"title", "content", "more", "id", "create_date",
		);

		if(in_array($_field->getId(),$id_blacklist)){
			return false;
		}

		if(preg_match('/_visible$/i',$_field->getId())){
			return false;
		}

		$this->customFields[$_field->getId()] = $_field;
		CMSPlugin::savePluginConfig(self::PLUGIN_ID, $this);
	}

	/**
	 * ラベル編集画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$labelId = (isset($arg[0])) ? (int)$arg[0] : 0;
		return self::buildFormOnLabelPage($labelId);
	}

	private function buildFormOnLabelPage(int $labelId){
		$html = self::_getScripts();
		$db_arr = ($labelId > 0) ? $this->getCustomFields($labelId) : array();

		$db_values = array();
		foreach($db_arr as $field){
			$db_values[$field->getId()] = $field->getValue();
		}

		//$isEntryField = false;	//記事フィールドがあるか？
		$isListField = false;	//リストフィールドがあるか？
		$isDlListField = false;	//定義型リストフィールドがあるか？


		$db_extra_values = array();
		foreach($db_arr as $field){
			$db_extra_values[$field->getId()] = $field->getExtraValues();
		}

		if(count($this->customFields)){
			foreach($this->customFields as $fieldId => $fieldObj){
				//if($fieldObj->getType() == "entry") $isEntryField = true;
				if(!$isListField && $fieldObj->getType() == "list") $isListField = true;
				if(!$isDlListField && $fieldObj->getType() == "dllist") $isDlListField = true;
				$v = (isset($db_values[$fieldId])) ? $db_values[$fieldId] : null;
				$extra = (isset($db_extra_values[$fieldId])) ? $db_extra_values[$fieldId] : null;
				$html .= $fieldObj->getForm($this, $v, $extra);
			}
		}
		
		// CustomFieldのjsファイルを流用
		if($isListField) $html .= "<script>\n" . file_get_contents(SOY2::RootDir() . "site_include/plugin/CustomField/js/list.js") . "\n</script>\n";
		if($isDlListField) $html .= "<script>\n" . file_get_contents(SOY2::RootDir() . "site_include/plugin/CustomField/js/dllist.js") . "\n</script>\n";

		return $html;
	}

	/**
	 * ラベル詳細画面でのJavaScriptファイル
	 * @return string
	 */
	private function _getScripts(){

		$script = '<script type="text/javascript">';
		$script .= file_get_contents(dirname(dirname(__FILE__)) . "/CustomFieldAdvanced/custom_field.js");
		$script .= '</script>';
		$script = str_replace("#FILE_UPLOAD_LINK#", SOY2PageController::createLink("Page.Editor.FileUpload"), $script);
		$script = str_replace("#PUBLIC_URL#", UserInfoUtil::getSiteURLBySiteId(""), $script);
		$script = str_replace("#SITE_URL#", UserInfoUtil::getSiteURL(), $script);
		$script = str_replace("#SITE_ID#", UserInfoUtil::getSite()->getSiteId(), $script);

		return $script;
	}

	/**
	 * 特定のラベルのカスタムフィールドの値を返す
	 * @param int labelId 記事のID
	 * @return Array <LabelCustomField>
	 */
	function getCustomFields(int $labelId){
		$fieldIds = soycms_get_field_id_list($this->customFields);
		if(!count($fieldIds)) return array();

		$dao = soycms_get_hash_table_dao("label_attribute");

		try{
			$attrs = $dao->getByLabelIdCustom($labelId, $fieldIds);
		}catch(Exception $e){
			$attrs = array();
		}

		//値がない場合は満たす
		foreach($fieldIds as $fieldId){
			if(!isset($attrs[$fieldId])) {
				$attr = soycms_get_label_attribute_object(0, $fieldId);
				$attr->setFieldId($fieldId);
				$attrs[$fieldId] = $attr;
			}
		}

		/*
		 * 注意！
		 * $this->customFieldsは連想配列（カスタムフィールドのID => カスタムフィールドのオブジェクト）
		 * $db_arryはただの配列（連番 => カスタムフィールドのオブジェクト（IDと値だけが入っている、高度な設定などは空））
		 */


		//ラベルにないカスタムフィールドの設定内容を入れておく
		//（HTMLListやカスタムフィールドを追加したときの既存の記事のため）
		$list = array();
		foreach($this->customFields as $fieldId => $fieldObj){
			$added = new LabelCustomField();
			$added->setId($fieldId);

			//カスタムフィールドのデータがある場合
			if(isset($attrs[$fieldId]) && $attrs[$fieldId] instanceof LabelAttribute){
				//do nothing
				$attr = $attrs[$fieldId];
				$added->setValue($attr->getValue());
				$added->setExtraValues($attr->getExtraValuesArray());
				$list[] = $added;

			//データがない場合。初回など。
			}else{
				$added->setValue($fieldObj->getDefaultValue());
				$list[] = $added;
			}
		}

		return $list;
	}

	/**
	 * プラグイン管理画面 カスタムフィールド設定の削除
	 */
	function deleteAllFields(){
		foreach($this->customFields as $field){
			$this->deleteField($field->getId());
		}
	}

	/**
	 * プラグイン管理画面 設定の保存
	 */
	function updateDisplayConfig($config){
		//表示設定
		// $this->displayTitle = ( $config["display_title"] >0 ) ? 1 : 0 ;
		// $this->displayID = ( $config["display_id"] >0 ) ? 1 : 0 ;

		CMSPlugin::savePluginConfig(self::PLUGIN_ID,$this);
	}

	/**
	 * プラグイン アクティブ 初回テーブル作成
	 */
	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM LabelAttribute", array());
			return;//テーブル作成済み
		}catch(Exception $e){
			//
		}

		$file = file_get_contents(dirname(__FILE__) . "/sql/init_".SOYCMS_DB_TYPE.".sql");
		$sqls = preg_split('/create/', $file, -1, PREG_SPLIT_NO_EMPTY) ;

		foreach($sqls as $sql){
			$sql = trim("create" . $sql);
			try{
				$dao->executeUpdateQuery($sql, array());
			}catch(Exception $e){
				//
			}
		}

		return;
	}

	public static function register(){
		if(!class_exists("LabelCustomField")) include(dirname(__FILE__)."/entity.php");

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new LabelCustomFieldPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
