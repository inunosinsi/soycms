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

	private $dao;

	private $displayLogic;

	//設定
	var $displayTitle = 0;//「カスタムフィールド」を表示する
	var $displayID = 0;//IDを表示する

	//フィールド種別事に設定されている属性
	private $properties = array();

	function init(){
		CMSPlugin::addPluginMenu(CustomFieldPluginAdvanced::PLUGIN_ID,array(
			"name" => "カスタムフィールド アドバンスド",
			"description" => "エントリーにカスタムフィールドを追加します。<br>Entryテーブルのカラムではなく、EntryAttributeテーブルにデータを保持します。<br />このプラグインは、SOY CMS 1.6.0よりご利用頂けます。",
			"author" => "日本情報化農業研究所",
			"url" => "http://www.n-i-agroinformatics.com/",
			"mail" => "soycms@soycms.net",
			"version"=>"1.9.8"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(CustomFieldPluginAdvanced::PLUGIN_ID)){
			$this->dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");
			SOY2::import("site_include.plugin.CustomFieldAdvanced.util.CustomfieldAdvancedUtil");

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
				CMSPlugin::setEvent('onEntryOutput', CustomFieldPluginAdvanced::PLUGIN_ID, array($this, "display"));
			}

		}else{
			CMSPlugin::setEvent('onActive', CustomFieldPluginAdvanced::PLUGIN_ID, array($this, "createTable"));
		}
	}

	/**
	 * onEntryOutput
	 */
	function display($arg){

		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];

		//高速化
		if($this->acceleration == 1){
			if(!$this->displayLogic) $this->displayLogic = SOY2Logic::createInstance("site_include.plugin.CustomFieldAdvanced.logic.DisplayLogic");
			list($labelIdWithBlock, $blogCategoryLabelList) = $this->displayLogic->checkAcceleration($entryId, $htmlObj);
			$fields = $this->getCustomFields($entryId, $labelIdWithBlock, $blogCategoryLabelList);
			$customFields = (isset($this->advancedCustomFields)) ? $this->advancedCustomFields : $this->customFields;
		}else{
			$fields = $this->getCustomFields($entryId);
			$customFields = $this->customFields;
		}

		if(count($fields)){
			//設定内に記事フィールドはあるか？
			$isEntryField = CustomfieldAdvancedUtil::checkIsEntryField($customFields);
			$isLabelField = CustomfieldAdvancedUtil::checkIsLabelField($customFields);	// @ToDo メモリをたくさん食うからブログ一覧やラベルブロックでは禁止にしたい

			foreach($fields as $field){
				//設定を取得
				$master = (isset($customFields[$field->getId()])) ? $customFields[$field->getId()] : null;

				$class = "CMSLabel";
				$attr = array(
					"html"	   => $field->getValue(),
					"soy2prefix" => "cms",
				);

				//カスタムフィールドの設定が取れるときの動作（たとえば同じサイト内の場合）
				if($master){

					//$attr["html"]に改めて値を入れ直す時に使用するフラグ
					$resetFlag = true;

					//値が設定されていないなら初期値を使う
					if(is_null($field->getValue())){
						$field->setValue($master->getDefaultValue());
					}

					//空の時の動作
					if(strlen($field->getValue()) == 0 ){
						if($master->getHideIfEmpty()){
							//空の時は表示しない
							$attr["visible"] = false;
						}else{
							//空の時の値
							$field->setValue($master->getEmptyValue());
						}
					}

					//タイプがリンクの場合はここで上書き
					if($master->getType() == "link"){
						$class = "HTMLLink";
						$attr["link"] = (strlen($field->getValue()) > 0) ? $field->getValue() : null;
						unset($attr["html"]);
						$resetFlag = false;

					//画像の場合
					}else if($master->getType() == "image"){
						$class = "HTMLImage";
						$attr["src"] = (strlen($field->getValue()) > 0) ? $field->getValue() : null;
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
						$htmlObj->addLabel($field->getId() . "_text", array(
							"soy2prefix" => "cms",
							"text" => $field->getValue()
						));
					}

					//複数行テキストの場合は\n\rを<br>に変換するタグを追加
					if($master->getType() == "textarea"){
						$htmlObj->addLabel($field->getId() . "_br_mode", array(
							"soy2prefix" => "cms",
							"html" => nl2br($field->getValue())
						));
					}

					//上で空の時の値が入るかも知れず、下でunsetされる可能性があるのでここで設定し直す。
					if($resetFlag){
						$attr["html"] = $field->getValue();
					}

					//記事フィールド
					if($isEntryField){
						$entry = new Entry();
						if($master->getType() == "entry" && strlen($field->getValue()) && strpos($field->getValue(), "-")){
							$v = explode("-", $field->getValue());
							$selectedEntryId = (isset($v[1]) && is_numeric($v[1])) ? (int)$v[1] : null;
							if($selectedEntryId){
								$entry = SOY2Logic::createInstance("site_include.plugin.CustomField.logic.EntryFieldLogic")->getTitleAndContentByEntryId($selectedEntryId);
								$attr["html"] = $entry->getContent();
							}
						}

						/**
						 * @記事フィールドの隠しモード
						 * cms:id="***_title"で記事名を出力
						 * cms:id="***_create_date"で記事の作成時刻を出力
						 **/
						$htmlObj->addLabel($field->getId() . "_id", array(
	 						"text" => $entry->getId(),
	 						"soy2prefix"=>"cms"
	 					));
	 					$htmlObj->addLabel($field->getId() . "_title", array(
	 						"text" => $entry->getTitle(),
	 						"soy2prefix"=>"cms"
	 					));
	 					$htmlObj->createAdd($field->getId() . "_content", "CMSLabel", array(
	 						"html" => $entry->getContent(),
	 						"soy2prefix"=>"cms"
	 					));
	 					$htmlObj->createAdd($field->getId() . "_more", "CMSLabel", array(
	 						"html" => $entry->getMore(),
	 						"soy2prefix"=>"cms"
	 					));
	 					$htmlObj->createAdd($field->getId() . "_create_date", "DateLabel", array(
	 						"text" => $entry->getCdate(),
	 						"soy2prefix"=>"cms"
	 					));
						/** 記事フィールドの隠しモードここまで **/
					}

					//ラベルフィールド
					if($isLabelField){	//メモリをたくさん食うので別の方法で実装するが、一応コードは残しておく
						// $entries = array();
						// $selectedLabelId = ($master->getType() == "label" && is_numeric($field->getValue())) ? (int)$field->getValue() : null;
						// if(isset($selectedLabelId)){
						// 	// @ToDo 一覧の取得条件
						//
						// }

						//if(!class_exists("EntryListComponent")) SOY2::import("site_include.blog.component.EntryListComponent");
						// $htmlObj->createAdd($field->getId() . "_entry_list", "EntryListComponent", array(
						// 	"soy2prefix" => "cms",
						// 	"list" => $entries
						// ));
					}

					//属性に出力
					if(strlen($master->getOutput()) > 0){

						//リンクタイプ以外でhrefを使う場合
						if($master->getOutput() == "href" && $master->getType() != "link"){
							$class = "HTMLLink";
							$attr["link"] = (strlen($field->getValue()) > 0) ? $field->getValue() : null;

						//下方互換
						}else if($master->getType() == "image" && $master->getOutput() == "src"){
							//上で処理をしているため何もしない

						//その他
						}else{
							$class = "HTMLModel";
							$attr[$master->getOutput()] = $field->getValue();
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
					if(strlen($master->getExtraOutputs()) > 0){
						$extraOutputs = explode("\n", str_replace(array("\r\n", "\r"), "\n", $master->getExtraOutputs()));
						$extraValues = $field->getExtraValues();
						foreach($extraOutputs as $key => $extraOutput){
							$extraOutput = trim($extraOutput);
							$attr[$extraOutput] = is_array($extraValues) && isset($extraValues[$extraOutput]) ? $extraValues[$extraOutput] : "";
						}

						unset($attr["html"]);//HTMLModelなのでunsetしなくても出力されないはず
					}

					//ペアフィールド
					if($master->getType() == "pair" && strlen($master->getExtraValues())){
						$extraValues = soy2_unserialize($master->getExtraValues());

						//後方互換
						if(isset($extraValues["pair"]) && is_array($extraValues["pair"])) $extraValues = $extraValues["pair"];

						if(count($extraValues)){
							foreach($extraValues as $idx => $pairValues){
								$_hash = (strlen($field->getValue())) ? CustomfieldAdvancedUtil::createHash($field->getValue()) : null;
								$pairValue = (isset($_hash) && isset($pairValues[$_hash])) ? $pairValues[$_hash] : "";

								$htmlObj->addLabel($field->getId() . "_pair_" . ($idx + 1) . "_visible", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) > 0)
								));

								$htmlObj->addLabel($field->getId() . "_pair_" . ($idx + 1) . "_is_not_empty", array(
									"soy2prefix" => "cms",
									"visible" => (strlen($pairValue) > 0)
								));

								$htmlObj->addLabel($field->getId() . "_pair_" . ($idx + 1) . "_is_empty", array(
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
					"visible" => (strlen($field->getValue()) > 0)
				));

				$htmlObj->addModel($field->getId() . "_is_not_empty", array(
					"soy2prefix" => "cms",
					"visible" => (strlen($field->getValue()) > 0)
				));

				$htmlObj->addModel($field->getId()."_is_empty", array(
					"soy2prefix" => "cms",
					"visible" => (strlen($field->getValue()) === 0)
				));

				//SOY2HTMLのデフォルトの _visibleがあるので、$field->getId()."_visible"より後にこれをやらないと表示されなくなる
				$htmlObj->createAdd($field->getId(), $class, $attr);
			}
		}
	}

	//画像フィールドの属性の設定を取得
	private function _getImgProps($type="image"){
		if(!is_array($this->customFields) || !count($this->customFields)) return array();
		if(isset($this->properties[$type])) return $this->properties[$type];
		$this->properties[$type] = array();

		foreach($this->customFields as $fieldId => $field){
			if($field->getType() == $type){
				$extraOutputs = explode("\n", $field->getExtraOutputs());
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
		 $dao = $this->dao;

		$entry = $arg["entry"];

		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0]) && is_numeric($arg[0])) ? (int)$arg[0] : null;
		$postFields = (isset($_POST["custom_field"]) && is_array($_POST["custom_field"])) ? $_POST["custom_field"] : array();
		$extraFields = (isset($_POST["custom_field_extra"]) && is_array($_POST["custom_field_extra"])) ? $_POST["custom_field_extra"] : array();

		foreach($this->customFields as $key => $field){

			$value = (isset($postFields[$key])) ? $postFields[$key] : "";
			$extra = (isset($extraFields[$key]))? $extraFields[$key]: array();

			//更新の場合
			try{
				$obj = $dao->get($entry->getId(), $field->getId());
				$obj->setValue($value);
				$obj->setExtraValuesArray($extra);
				$dao->update($obj);
				continue;
			}catch(Exception $e){
				//新規作成の場合
				try{
					$obj = new EntryAttribute();
					$obj->setEntryId($entry->getId());
					$obj->setFieldId($key);
					$obj->setValue($value);
					$obj->setExtraValuesArray($extra);
					$dao->insert($obj);
				}catch(Exception $e){
					//
				}

			}
		}

		return true;
	}

	/**
	 * 記事複製時
	 */
	function onEntryCopy($args){
		list($old, $new) = $args;
		$list = $this->getCustomFields($old);

		$dao = $this->dao;

		foreach($list as $custom){
			try{
				$obj = new EntryAttribute();
				$obj->setEntryId($new);
				$obj->setFieldId($custom->getId());
				$obj->setValue($custom->getValue());
				$obj->setExtraValuesArray($custom->getExtraValues());
				$dao->insert($obj);
			}catch(Exception $e){

			}
		}

		return true;
	}

	/**
	 * 記事削除時
	 * @param array $args エントリーID
	 */
	function onEntryRemove($args){
		$dao = $this->dao;
		foreach($args as $entryId){
			try{
				$dao->deleteByEntryId($entryId);
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
		$entryId = (isset($arg[0])) ? (int)$arg[0] : null;
		return self::buildFormOnEntryPage($entryId);
	}

	/**
	 * ブログ記事 投稿画面
	 * @return string HTMLコード
	 */
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? (int)$arg[1] : null;
		return self::buildFormOnEntryPage($entryId);
	}

	private function buildFormOnEntryPage($entryId){
		$html = $this->getScripts();
		$html .= '<div class="section custom_field">';
		$db_arr = $this->getCustomFields($entryId);

		$db_values = array();
		foreach($db_arr as $field){
			$db_values[$field->getId()] = $field->getValue();
		}

		$isEntryField = false;	//記事フィールドがあるか？
		$db_extra_values = array();
		foreach($db_arr as $field){
			$db_extra_values[$field->getId()] = $field->getExtraValues();
		}

		foreach($this->customFields as $fieldId => $fieldObj){
			if($fieldObj->getType() == "entry") $isEntryField = true;
			$v = (isset($db_values[$fieldId])) ? $db_values[$fieldId] : null;
			$extra = (isset($db_extra_values[$fieldId])) ? $db_extra_values[$fieldId] : null;
			$html .= $fieldObj->getForm($this, $v, $extra);
		}

		$html .= '</div>';
		if($isEntryField){
			$html .= "<script>\n" . file_get_contents(SOY2::RootDir() . "site_include/plugin/CustomField/js/entry.js") . "\n</script>\n";
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
	 * @return Array <CustomField>
	 */
	function getCustomFields($entryId, $labelIdWithBlock = null, $blogCategoryLabelList = array()){

		$dao = $this->dao;

		if(is_null($labelIdWithBlock)){
			$customFields = $this->customFields;
			if(!count($customFields)) return array();
			$fieldIds = array();
			foreach($customFields as $fieldId => $field){
				$fieldIds[] = $fieldId;
			}

			try{
				$attrs = $dao->getByEntryIdCustom($entryId, $fieldIds);
			}catch(Exception $e){
				$attrs = array();
			}

		}else{
			$fieldIds = $this->prevFieldIds;

			if($labelIdWithBlock != $this->prevLabelId){
				$fieldIds = array();
				$customFields = $this->customFields;
				foreach($this->customFields as $customField){
					$labelId = (int)$customField->getLabelId();

					//ラベルと紐づけを行っているフィールドの場合、指定されているラベルのIDと一致していなかった場合は配列から除く
					if($this->checkLabelConfigOnBlock($labelId, $labelIdWithBlock)){

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
				$customFields = $this->advancedCustomFields;
			}

			$attrs = $dao->getByEntryIdCustom($entryId, $fieldIds);
			$this->prevLabelId = $labelIdWithBlock;
		}

		//値がない場合は満たす
		foreach($fieldIds as $fieldId){
			if(!isset($attrs[$fieldId])) {
				$attr = new EntryAttribute();
				$attr->setFieldId($fieldId);
				$attrs[$fieldId] = $attr;
			}
		}

		/*
		 * 注意！
		 * $this->customFieldsは連想配列（カスタムフィールドのID => カスタムフィールドのオブジェクト）
		 * $db_arryはただの配列（連番 => カスタムフィールドのオブジェクト（IDと値だけが入っている、高度な設定などは空））
		 */


		//記事にないカスタムフィールドの設定内容を入れておく
		//（HTMLListやカスタムフィールドを追加したときの既存の記事のため）
		$list = array();
		foreach($customFields as $fieldId => $fieldValue){
			$added = new CustomField();
			$added->setId($fieldId);

			//カスタムフィールドのデータがある場合
			if(isset($attrs[$fieldId]) && $attrs[$fieldId] instanceof EntryAttribute){
				//do nothing
				$attr = $attrs[$fieldId];
				$added->setValue($attr->getValue());
				$added->setExtraValues($attr->getExtraValuesArray());
				$list[] = $added;

			//データがない場合。初回など。
			}else{
				$added->setValue($fieldValue->getDefaultValue());
				$list[] = $added;
			}
		}

		return $list;
	}

	/**
	 * ブロックに紐づいたラベルIDと高度な設定で設定したラベルIDが同じでなければtrue
	 */
	function checkLabelConfigOnBlock($labelId, $labelIdWithBlock){
		return (!is_null($labelId) && $labelId > 0 && $labelId != $labelIdWithBlock);
	}

	/**
	 * csvファイルをインポートする
	 */
	function importFields(){
		$csvLogic = SOY2Logic::createInstance("site_include.plugin.CustomFieldAdvanced.logic.ExImportLogic", array("pluginObj" => $this));
		$csvLogic->importFile();
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
		$csvLogic = SOY2Logic::createInstance("site_include.plugin.CustomFieldAdvanced.logic.ExImportLogic", array("pluginObj" => $this));
		$csvLogic->exportFile($this->customFields);
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
		if(!class_exists("CustomField")){
			include(dirname(__FILE__)."/entity.php");
		}

		if(!class_exists("CustomFieldAdvancedPluginFormPage")){
			include(dirname(__FILE__)."/form.php");
		}

		$obj = CMSPlugin::loadPluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID);
		if(is_null($obj)) $obj = new CustomFieldPluginAdvanced();
		CMSPlugin::addPlugin(CustomFieldPluginAdvanced::PLUGIN_ID, array($obj, "init"));
	}
}
