<?php

PageCustomFieldPlugin::register();

class PageCustomFieldPlugin{

	const PLUGIN_ID = "PageCustomFieldPlugin";

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
			"name" => "ページカスタムフィールド",
			"description" => "",
			"author" => "齋藤毅",
			"url" => "",
			"mail" => "info@saitodev.co",
			"version"=>"0.1"
		));

		//プラグイン アクティブ
		if(CMSPlugin::activeCheck(self::PLUGIN_ID)){
			//管理側
			if(!defined("_SITE_ROOT_")){

				CMSPlugin::addPluginConfigPage(self::PLUGIN_ID, array(
					$this,"config_page"
				));

				CMSPlugin::setEvent('onPageUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
				CMSPlugin::setEvent('onBlogPageConfigUpdate', self::PLUGIN_ID, array($this, "onPageUpdate"));
				CMSPlugin::setEvent('onPageRemove', self::PLUGIN_ID, array($this, "onPageRemove"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Page.Detail", array($this, "onCallCustomField"));
				CMSPlugin::addCustomFieldFunction(self::PLUGIN_ID, "Blog.Config", array($this, "onCallCustomField"));

			//公開側
			}else{
				// 高速化の為にcms:moduleの方で行う
				//CMSPlugin::setEvent('onPageOutput', self::PLUGIN_ID, array($this, "onPageOutput"));
			}

		}else{
			CMSPlugin::setEvent('onActive', self::PLUGIN_ID, array($this, "createTable"));
		}
	}

	/**
	 * onPageOutput
	 */
	function onPageOutput($htmlObj){}

	/**
	 * プラグイン管理画面の表示
	 */
	function config_page($message){
		//$this->importFields();
		SOY2::import("site_include.plugin.PageCustomField.config.PageCustomFieldFormPage");
		$form = SOY2HTMLFactory::createInstance("PageCustomFieldFormPage");
		$form->setPluginObj($this);
		$form->execute();
		return $form->getObject();
	}

	/**
	 * ラベル更新時
	 */
	function onPageUpdate($arg){
		if(!isset($arg["new_page"])) return;
		$pageId = (int)$arg["new_page"]->getId();
		if($pageId === 0) return;

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

			$attr = soycms_get_page_attribute_object($pageId, $field->getId());
			$attr->setValue($value);
			$attr->setExtraValuesArray($extra);
			soycms_save_page_attribute_object($attr);
		}

		return true;
	}

	/**
	 * ラベル削除時
	 * @param array $args ラベルID
	 */
	function onPageRemove($args){
		foreach($args as $pageId){
			try{
				soycms_get_hash_table_dao("page_attribute")->deleteByPageId($pageId);
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
	function insertField(PageCustomField $_field){
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
		$pageId = (isset($arg[0])) ? (int)$arg[0] : 0;
		return self::buildFormOnLabelPage($pageId);
	}

	private function buildFormOnLabelPage(int $pageId){
		$html = self::_getScripts();
		SOY2::import("site_include.plugin.PageCustomField.util.PageCustomfieldUtil");
		$db_arr = ($pageId > 0 && count($this->customFields)) ? PageCustomfieldUtil::getCustomFields($pageId, $this->customFields) : array();
		
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
			$exist = $dao->executeQuery("SELECT * FROM PageAttribute", array());
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
		if(!class_exists("PageCustomField")) include(dirname(__FILE__)."/entity.php");

		$obj = CMSPlugin::loadPluginConfig(self::PLUGIN_ID);
		if(is_null($obj)) $obj = new PageCustomFieldPlugin();
		CMSPlugin::addPlugin(self::PLUGIN_ID, array($obj, "init"));
	}
}
