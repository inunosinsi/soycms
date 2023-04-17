<?php

CustomFieldPluginAdvanced::register();

class CustomFieldPluginAdvanced{

	const PLUGIN_ID = "CustomFieldAdvanced";

	function getId(){
		return self::PLUGIN_ID;
	}

	//カスタムフィールドの項目設定
	var $customFields = array();

	//アドバンス用のカスタムフィールドの項目設定
	private $advancedCustomFields = array();

	//アドバンス用のフィールドで高度な設定のラベル紐づけを考慮したフィールドリストを毎回取得しないようにするフラグ
	private $prevLabelId;
	private $prevFieldIds = array();

	//表示の高速化
	private $acceleration = 0;

	private $displayLogic;

	//設定
	var $displayTitle = 0;//「カスタムフィールド」を表示する
	var $displayID = 0;//IDを表示する

	//フィールド種別事に設定されている属性
	private $properties = array();

	function init(){
		CMSPlugin::addPluginMenu(CustomFieldPluginAdvanced::PLUGIN_ID, array(
			"name" => "カスタムフィールド アドバンスド",
			"type" => Plugin::TYPE_ENTRY,
			"description" => "エントリーにカスタムフィールドを追加します。<br>Entryテーブルのカラムではなく、EntryAttributeテーブルにデータを保持します。<br />このプラグインは、SOY CMS 1.6.0よりご利用頂けます。",
			"author" => "日本情報化農業研究所",
			"url" => "http://www.n-i-agroinformatics.com/",
			"mail" => "soycms@soycms.net",
			"version"=>"1.20.4"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(CustomFieldPluginAdvanced::PLUGIN_ID)){
			SOY2::import("site_include.plugin.CustomFieldAdvanced.util.CustomfieldAdvancedUtil");
			include_once(SOY2::RootDir() . "site_include/plugin/CustomFieldAdvanced/func/func.php");	//便利な関数

			//管理側
			if(!defined("_SITE_ROOT_")){

				CMSPlugin::addPluginConfigPage(CustomFieldPluginAdvanced::PLUGIN_ID, array(
					$this,"config_page"
				));

				CMSPlugin::setEvent('onEntryUpdate', CustomFieldPluginAdvanced::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCreate', CustomFieldPluginAdvanced::PLUGIN_ID, array($this, "onEntryUpdate"));
				CMSPlugin::setEvent('onEntryCopy', CustomFieldPluginAdvanced::PLUGIN_ID, array($this, "onEntryCopy"));
				CMSPlugin::setEvent('onEntryRemove', CustomFieldPluginAdvanced::PLUGIN_ID, array($this, "onEntryRemove"));

				CMSPlugin::addCustomFieldFunction(CustomFieldPluginAdvanced::PLUGIN_ID, "Entry.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(CustomFieldPluginAdvanced::PLUGIN_ID, "Blog.Entry", array($this, "onCallCustomField_inBlog"));

			//公開側
			}else{
				CMSPlugin::setEvent('onEntryListBeforeOutput', CustomFieldPluginAdvanced::PLUGIN_ID, array($this, "onEntryListBeforeOutput"));
				CMSPlugin::setEvent('onEntryOutput', CustomFieldPluginAdvanced::PLUGIN_ID, array($this, "onEntryOutput"));
				CMSPlugin::setEvent('onPageOutput',CustomFieldPluginAdvanced::PLUGIN_ID,array($this,"onPageOutput"));
			}

		}else{
			CMSPlugin::setEvent('onActive', CustomFieldPluginAdvanced::PLUGIN_ID, array($this, "createTable"));
		}
	}

	/**
	 * onEntryListBeforeOutput
	 */
	function onEntryListBeforeOutput($arg){
		$entries = &$arg["entries"];
		$entryIds = soycms_get_entry_id_by_entries($entries);
		
		// カスタムフィールドの値を一気に取得
		$fieldIds = array_keys($this->customFields);
		if(count($entryIds)) CustomfieldAdvancedUtil::setValuesByEntryIdsAndFieldIds($entryIds, $fieldIds);
	}

	/**
	 * onEntryOutput
	 */
	function onEntryOutput($arg){

		$entryId = (int)$arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		//高速化
		if($this->acceleration == 1){
			if(!$this->displayLogic) $this->displayLogic = SOY2Logic::createInstance("site_include.plugin.CustomFieldAdvanced.logic.DisplayLogic");
			list($labelIdWithBlock, $blogCategoryLabelList) = $this->displayLogic->checkAcceleration($entryId, $htmlObj);
			$fieldValues = self::_getFieldValues($entryId, (int)$labelIdWithBlock, $blogCategoryLabelList);
			$customFields = (isset($this->advancedCustomFields)) ? $this->advancedCustomFields : $this->customFields;
		}else{
			$fieldValues = self::_getFieldValues($entryId);
			$customFields = $this->customFields;
		}

		if(count($fieldValues)){
			//設定内に記事フィールドはあるか？
			$isEntryField = CustomfieldAdvancedUtil::checkIsEntryField($customFields);
			$isLabelField = CustomfieldAdvancedUtil::checkIsLabelField($customFields);	// @ToDo メモリをたくさん食うからブログ一覧やラベルブロックでは禁止にしたい → 別のプラグインで対応
			$isListField = CustomfieldAdvancedUtil::checkIsListField($customFields);	//
			$isDlListField = CustomfieldAdvancedUtil::checkIsDlListField($customFields);	//

			foreach($fieldValues as $fieldId => $fieldValueArr){
				//設定を取得
				$master = (isset($customFields[$fieldId])) ? $customFields[$fieldId] : null;
				$fieldValue = (isset($fieldValueArr["value"])) ? $fieldValueArr["value"] : null;
				$fieldExtraValues = (isset($fieldValueArr["extraValues"]) && is_array($fieldValueArr["extraValues"])) ? $fieldValueArr["extraValues"] : array();

				$class = "CMSLabel";
				$attr = array(
					"html"	   => $fieldValue,
					"soy2prefix" => "cms",
				);

				//カスタムフィールドの設定が取れるときの動作（たとえば同じサイト内の場合）
				if($master instanceof CustomField){

					//$attr["html"]に改めて値を入れ直す時に使用するフラグ
					$resetFlag = true;

					//値が設定されていないなら初期値を使う
					if(is_null($fieldValue)) $fieldValue = $master->getDefaultValue();

					//空の時の動作
					if(is_string($fieldValue) && strlen($fieldValue) == 0 ){
						if($master->getHideIfEmpty()){
							//空の時は表示しない
							$attr["visible"] = false;
						}else{
							$fieldValue = $master->getEmptyValue(); //空の時の値
						}
					}

					//タイプがリンクの場合はここで上書き
					if($master->getType() == "link"){
						$class = "HTMLLink";
						$attr["link"] = $fieldValue;
						unset($attr["html"]);
						$resetFlag = false;

					//画像の場合
					}else if($master->getType() == "image"){
						$class = "HTMLImage";
						$attr["src"] = $fieldValue;
						unset($attr["html"]);
						$resetFlag = false;

						$imgProps = self::_getImgProps("image");
						if(count($imgProps)){
							foreach($imgProps as $imgProp){
								$attr[$imgProp] = "";
							}
						}
					}

					//リンク、もしくは画像の場合、パスを表示するためのcms:id
					if($master->getType() == "link" || $master->getType() == "image"){
						$htmlObj->addLabel($fieldId . "_text", array(
							"soy2prefix" => "cms",
							"text" => $fieldValue
						));
					}

					//複数行テキストの場合は\n\rを<br>に変換するタグを追加
					if($master->getType() == "textarea"){
						$htmlObj->addLabel($fieldId . "_raw", array(
							"soy2prefix" => "cms",
							"html" => $fieldValue
						));
						$attr["html"] = nl2br($fieldValue);
						$htmlObj->addLabel($fieldId . "_br_mode", array(
							"soy2prefix" => "cms",
							"html" => $attr["html"]
						));
						$resetFlag = false;
					}

					//上で空の時の値が入るかも知れず、下でunsetされる可能性があるのでここで設定し直す。
					if($resetFlag) $attr["html"] = $fieldValue;

					//記事フィールド
					if($isEntryField){

						$entry = new Entry();
						$labelId = 0;
						if($master->getType() == "entry"){
							if(!class_exists("EntryFieldUtil")) SOY2::import("site_include.plugin.CustomFieldAdvanced.util.EntryFieldUtil");
							list($selectedSiteId, $labelId, $entryEntryId) = EntryFieldUtil::divideIds((string)$fieldValue);

							//$selectedSiteIdとcurrentSiteIdが異なる場合はDSNの切り替え
							$old = ($selectedSiteId > 0 && $selectedSiteId !== CMSUtil::getCurrentSiteId()) ? CMSUtil::switchOtherSite($selectedSiteId) : array();

							$entry = EntryFieldUtil::getEntryObjectById($entryEntryId);
							$attr["html"] = $entry->getContent();

							if(count($old)) CMSUtil::resetOtherSite($old);
						}

						/**
						 * @記事フィールドの隠しモード
						 * cms:id="***_title"で記事名を出力
						 * cms:id="***_create_date"で記事の作成時刻を出力
						 **/
						SOY2::import("site_include.plugin.CustomFieldAdvanced.component.EntryFieldComponent");
						EntryFieldComponent::addTags($htmlObj, $entry, $fieldId);
						EntryFieldComponent::addOrderPartsTags($htmlObj, $labelId, $fieldId);

						//サムネイルプラグイン
						if(CMSPlugin::activeCheck("soycms_thumbnail")){
							SOY2::import("site_include.plugin.CustomFieldAdvanced.component.ThumbnailPluginComponent");
							ThumbnailPluginComponent::addTags($htmlObj, (int)$entry->getId(), $fieldId);
						}
						/** 記事フィールドの隠しモードここまで **/
					}

					//ラベルフィールド
					if($isLabelField){	//メモリをたくさん食うので別の方法で実装するが、一応コードは残しておく
						// $entries = array();
						// $selectedLabelId = ($master->getType() == "label" && is_numeric($fieldValue)) ? (int)$fieldValue : null;
						// if(isset($selectedLabelId)){
						// 	// @ToDo 一覧の取得条件
						//
						// }

						//if(!class_exists("EntryListComponent")) SOY2::import("site_include.blog.component.EntryListComponent");
						// $htmlObj->createAdd($fieldId . "_entry_list", "EntryListComponent", array(
						// 	"soy2prefix" => "cms",
						// 	"list" => $entries
						// ));
					}

					//リストフィールド
					if($isListField){
						if(!class_exists("ListFieldListComponent")) SOY2::import("site_include.plugin.CustomFieldAdvanced.component.ListFieldListComponent");
						$htmlObj->createAdd($fieldId . "_list", "ListFieldListComponent", array(
							"soy2prefix" => "cms",
							"list" => ($master->getType() == "list" && is_string($attr["html"])) ? soy2_unserialize($attr["html"]) : array()
						));
					}

					//定義型リストフィールド
					if($isDlListField){
						if(!class_exists("DlListFieldListComponent")) SOY2::import("site_include.plugin.CustomFieldAdvanced.component.DlListFieldListComponent");
						$htmlObj->createAdd($fieldId . "_dl_list", "DlListFieldListComponent", array(
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
						foreach($extraOutputs as $extraOutput){
							$extraOutput = trim($extraOutput);
							$attr[$extraOutput] = (isset($fieldExtraValues[$extraOutput])) ? $fieldExtraValues[$extraOutput] : "";
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

								$htmlObj->addModel($fieldId . "_pair_" . ($idx + 1) . "_visible", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) > 0)
								));

								$htmlObj->addModel($fieldId . "_pair_" . ($idx + 1) . "_is_not_empty", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) > 0)
								));

								$htmlObj->addModel($fieldId . "_pair_" . ($idx + 1) . "_is_empty", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) === 0)
								));

								$htmlObj->addLabel($fieldId . "_pair_" . ($idx + 1), array(
									"soy2prefix" => "cms",
									"html" => $pairValue
								));
							}
						}
					}
				}

				$fieldValueLength = (is_string($fieldValue)) ? strlen($fieldValue) : 0;
				$htmlObj->addModel($fieldId . "_visible", array(
					"soy2prefix" => "cms",
					"visible" => ($fieldValueLength > 0)
				));

				$htmlObj->addModel($fieldId . "_is_not_empty", array(
					"soy2prefix" => "cms",
					"visible" => ($fieldValueLength > 0)
				));

				$htmlObj->addModel($fieldId."_is_empty", array(
					"soy2prefix" => "cms",
					"visible" => ($fieldValueLength === 0)
				));

				//SOY2HTMLのデフォルトの _visibleがあるので、$fieldId."_visible"より後にこれをやらないと表示されなくなる
				$htmlObj->createAdd($fieldId, $class, $attr);
			}
		}
	}

	//画像フィールドの属性の設定を取得
	private function _getImgProps(string $type="image"){
		if(!is_array($this->customFields) || !count($this->customFields)) return array();
		if(isset($this->properties[$type])) return $this->properties[$type];
		$this->properties[$type] = array();

		foreach($this->customFields as $field){
			if($field->getType() == $type){
				$extraOutputs = (is_string($field->getExtraOutputs()) && strlen($field->getExtraOutputs())) ? explode("\n", $field->getExtraOutputs()) : array();
				if(!count($extraOutputs)) continue;
				foreach($extraOutputs as $output){
					$output = trim($output);
					if(!strlen($output) || is_numeric(array_search($output, $this->properties[$type]))) continue;
					$this->properties[$type][] = $output;
				}
			}
		}
		return $this->properties[$type];
	}

	function onPageOutput($obj){
		$entryId = (get_class($obj) == "CMSBlogPage" && isset($obj->entry)) ? $obj->entry->getId() : 0;

		// b_blockタグ設定しているフィールドのみを取得
		$fieldIds = array();
		if($entryId > 0 && is_array($this->customFields) && count($this->customFields)){
			foreach($this->customFields as $fieldId => $field){
				if($field->getType() != "checkbox" || !$field->getAddTagOutsideBlock()) continue;
				$fieldIds[] = $fieldId;
			}
		}

		if(count($fieldIds)){
			foreach($fieldIds as $fieldId){
				$v = soycms_get_entry_attribute_value($entryId, $fieldId);
				$checked = (is_string($v) && strlen(trim($v)));
				
				$obj->addModel("is_". $fieldId, array(
					"soy2prefix" => "b_block",
					"visible" => $checked
				));

				$obj->addModel("no_". $fieldId, array(
					"soy2prefix" => "b_block",
					"visible" => !$checked
				));
			}
		}
	}

	/**
	 * プラグイン管理画面の表示
	 */
	function config_page($message){
		//$this->importFields();
		SOY2::import("site_include.plugin.CustomFieldAdvanced.config.CustomFieldAdvancedPluginFormPage");
		$form = SOY2HTMLFactory::createInstance("CustomFieldAdvancedPluginFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * 記事作成時、記事更新時
	 */
	function onEntryUpdate($arg){
		$entry = $arg["entry"];

		//$arg = SOY2PageController::getArguments();
		//$entryId = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : null;
		$postFields = (isset($_POST["custom_field"]) && is_array($_POST["custom_field"])) ? $_POST["custom_field"] : array();
		$extraFields = (isset($_POST["custom_field_extra"]) && is_array($_POST["custom_field_extra"])) ? $_POST["custom_field_extra"] : null;

		foreach($this->customFields as $key => $field){

			$value = (isset($postFields[$key])) ? $postFields[$key] : null;
			$extra = (isset($extraFields[$key])) ? soy2_serialize($extraFields[$key]) : null;
			
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
			$attr = soycms_get_entry_attribute_object($entry->getId(), $field->getId());
			$attr->setValue($value);
			$attr->setExtraValues($extra);
			soycms_save_entry_attribute_object($attr);
		}
		
		return true;
	}

	/**
	 * 記事複製時
	 */
	function onEntryCopy($args){
		list($old, $new) = $args;
		$arr = self::_getFieldValues($old);
		if(!count($arr)) return true;

		foreach($arr as $fieldId => $v){
			$attr = soycms_get_entry_attribute_object($new, $fieldId);
			$attr->setValue($v["value"]);
			if(is_array($v["extraValues"])){
				$attr->setExtraValuesArray($v["extraValues"]);
			}else{
				$attr->setExtraValues($v["extraValues"]);
			}
			soycms_save_entry_attribute_object($attr);
		}

		return true;
	}

	/**
	 * 記事削除時
	 * @param array $args エントリーID
	 */
	function onEntryRemove($args){
		foreach($args as $entryId){
			try{
				soycms_get_hash_table_dao("entry_attribute")->deleteByEntryId($entryId);
			}catch(Exception $e){

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
			CMSPlugin::savePluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID,$this);
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
			CMSPlugin::savePluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID, $this);
		}
	}

	/**
	 * プラグイン管理画面 カスタムフィールド 高度な設定の更新
	 */
	function updateAdvance($id, $obj){
		if(isset($this->customFields[$id])){
			SOY2::cast($this->customFields[$id], $obj);
			CMSPlugin::savePluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID, $this);
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


			CMSPlugin::savePluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID,$this);
		}
	}

	/**
	 * プラグイン管理画面
	 */
	function insertField(CustomField $_field){
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
		CMSPlugin::savePluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID, $this);
	}

	/**
	 * 記事投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? (int)$arg[0] : 0;
		return self::buildFormOnEntryPage($entryId);
	}

	/**
	 * ブログ記事 投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : 0;
		return self::buildFormOnEntryPage($entryId);
	}

	private function buildFormOnEntryPage(int $entryId){
		$html = $this->getScripts();
		$html .= '<div class="section custom_field">' . "\n";
		$v_arr = ($entryId > 0) ? self::_getFieldValues($entryId) : array();
		
		$db_values = array();
		$db_extra_values = array();
		if(count($v_arr)){
			foreach($v_arr as $fieldId => $fieldValueArr){
				$db_values[$fieldId] = (string)$fieldValueArr["value"];	//値がnullの場合は空文字を入れる
				$db_extra_values[$fieldId] = (isset($fieldValueArr["extraValues"])) ? $fieldValueArr["extraValues"] : null;
			}
		}

		$isEntryField = false;	//記事フィールドがあるか？
		$isListField = false;	//リストフィールドがあるか？
		$isDlListField = false;	//定義型リストフィールドがあるか？
		
		if(count($this->customFields)){
			foreach($this->customFields as $fieldId => $fieldObj){
				if(!$isEntryField && $fieldObj->getType() == "entry") $isEntryField = true;
				if(!$isListField && $fieldObj->getType() == "list") $isListField = true;
				if(!$isDlListField && $fieldObj->getType() == "dllist") $isDlListField = true;
				$v = (isset($db_values[$fieldId])) ? $db_values[$fieldId] : null;
				$extra = (isset($db_extra_values[$fieldId])) ? $db_extra_values[$fieldId] : null;
				$html .= $fieldObj->getForm($this, $v, $extra);
			}
		}

		$html .= '</div>';
		if($isEntryField) $html .= "<script>\n" . file_get_contents(SOY2::RootDir() . "site_include/plugin/CustomField/js/entry.js") . "\n</script>\n";
		if($isListField) {
			$html .= "<script>\n" . file_get_contents(SOY2::RootDir() . "site_include/plugin/CustomField/js/list.js");
			$html .= "\nfunction open_listfield_filemanager(id){\n";
			$html .= "	common_to_layer(\"" . SOY2PageController::createLink("Page.Editor.FileUpload") . "?\"+id);\n";
			$html .= "}\n";
			$html .= "\n</script>\n";
		}
		if($isDlListField) {
			$html .= "<script>\n" . file_get_contents(SOY2::RootDir() . "site_include/plugin/CustomField/js/dllist.js");
			$html .= "\nfunction open_dllistfield_filemanager(id){\n";
			$html .= "	common_to_layer(\"" . SOY2PageController::createLink("Page.Editor.FileUpload") . "?\"+id);\n";
			$html .= "}\n";
			$html .= "\n</script>\n";
		}

		return $html;
	}

	/**
	 * 記事投稿画面でのJavaScriptファイル
	 * @return string
	 */
	function getScripts(){
		$script = '<script type="text/javascript">';
		$script .= file_get_contents(dirname(__FILE__) . "/custom_field.js");
		$script .= '</script>';
		$script = str_replace("#FILE_UPLOAD_LINK#", SOY2PageController::createLink("Page.Editor.FileUpload"), $script);
		$script = str_replace("#PUBLIC_URL#", UserInfoUtil::getSiteURLBySiteId(""), $script);
		$script = str_replace("#SITE_URL#", UserInfoUtil::getSiteURL(), $script);
		$script = str_replace("#SITE_ID#", UserInfoUtil::getSite()->getSiteId(), $script);

		return $script;
	}

	/**
	 * 特定の記事のカスタムフィールドの値を返す
	 * @param int entryId 記事のID
	 * @return array 
	 * array(
	 * 	fieldId => array("value" => "", "extraValues" => "")
	 * 	...
	 * )
	 */
	private function _getFieldValues(int $entryId, int $labelIdWithBlock=0,  array $blogCategoryLabelList=array()){
		$fieldIds = array();
		if($labelIdWithBlock === 0){
			$customFields = $this->customFields;
			if(!count($customFields)) return array();
			foreach($customFields as $fieldId => $_dust){
				$fieldIds[] = $fieldId;
			}
		}else{
			if($labelIdWithBlock != $this->prevLabelId){
				$customFields = $this->customFields;
				foreach($this->customFields as $customField){
					$labelId = (int)$customField->getLabelId();

					//ラベルと紐づけを行っているフィールドの場合、指定されているラベルのIDと一致していなかった場合は配列から除く
					if(CustomfieldAdvancedUtil::checkLabelConfigOnBlock($labelId, $labelIdWithBlock)){

						//ブログのカテゴリ設定分を確認する。存在している場合はcontinue
						if(count($blogCategoryLabelList) > 0 && in_array($labelId, $blogCategoryLabelList)) continue;
						unset($customFields[$customField->getId()]);
					//検索対象として、fieldIdsに入れておく
					}else{
						$fieldIds[] = $customFields[$customField->getId()]->getId();
					}
				}
				$this->advancedCustomFields = $customFields;
				$this->prevFieldIds = $fieldIds;
			}else{
				$fieldIds = $this->prevFieldIds;
				$customFields = $this->advancedCustomFields;
			}
			$this->prevLabelId = $labelIdWithBlock;
		}

		if(!count($fieldIds)) return array();

		//公開側のデータの取得とページの表示速度の高速化の為にSQLを発行する
		$fieldValues = ($entryId > 0) ? CustomfieldAdvancedUtil::getValuesByFieldIds($entryId, $fieldIds) : array();
		
		//記事にないカスタムフィールドの設定内容を入れておく ← @ToDo この処理は無駄かも
		//（HTMLListやカスタムフィールドを追加したときの既存の記事のため）
		$attrValues = array();
		foreach($customFields as $fieldId => $fieldValue){
			// データがない場合、初回など。初期値を入れる
			$attrValues[$fieldId] = (isset($fieldValues[$fieldId])) ? $fieldValues[$fieldId]: array("value" => $fieldValue->getDefaultValue(), "extraValues" => null);
		}

		return $attrValues;
	}

	/**
	 * csvファイルをインポートする
	 */
	function importFields(){
		SOY2Logic::createInstance("site_include.plugin.CustomFieldAdvanced.logic.ExImportLogic", array("pluginObj" => $this))->importFile();
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
	 * エクスポート
	 */
	function exportFields(){
		SOY2Logic::createInstance("site_include.plugin.CustomFieldAdvanced.logic.ExImportLogic", array("pluginObj" => $this))->exportFile($this->customFields);
		exit;
	}

	/**
	 * プラグイン管理画面 設定の保存
	 */
	function updateDisplayConfig($config){
		//表示設定
		$this->displayTitle = ( $config["display_title"] >0 ) ? 1 : 0 ;
		$this->displayID = ( $config["display_id"] >0 ) ? 1 : 0 ;
		$this->acceleration = ( $config["acceleration"] >0 ) ? 1 : 0 ;

		CMSPlugin::savePluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID,$this);
	}

	function getAcceleration(){
		return $this->acceleration;
	}

	/**
	 * プラグイン アクティブ 初回テーブル作成
	 */
	function createTable(){
		$dao = new SOY2DAO();

		try{
			$exist = $dao->executeQuery("SELECT * FROM EntryAttribute", array());
			return;//テーブル作成済み
		}catch(Exception $e){

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
		if(!class_exists("CustomField")) include(dirname(__FILE__)."/entity.php");
		if(!class_exists("CustomFieldAdvancedPluginFormPage")) include(dirname(__FILE__)."/form.php");

		$obj = CMSPlugin::loadPluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID);
		if(is_null($obj)) $obj = new CustomFieldPluginAdvanced();
		CMSPlugin::addPlugin(CustomFieldPluginAdvanced::PLUGIN_ID, array($obj, "init"));
	}
}
