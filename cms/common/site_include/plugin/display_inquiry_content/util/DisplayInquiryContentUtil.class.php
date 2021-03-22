<?php

class DisplayInquiryContentUtil {

	public static function getInquiryFormList(){
		$forms = self::_getInquiryForms();

		if(!count($forms)) return array();

		$list = array();
		foreach($forms as $form){
			$list[(int)$form->getId()] = $form->getName() . "(" . $form->getFormId() . ")";
		}

		return $list;
	}

	public static function getColumnsByFormId($formId){
		$forms = self::_getInquiryForms();
		if(!isset($forms[$formId])) return array();

		$columns = self::_getColumnsByFormId($formId);
		if(!count($columns)) return array();

		$list = array();
		$list["tracking_number"] = "お問い合わせ番号";
		$list["create_date"] = "お問い合わせ日時";

		foreach($columns as $col){
			$list[(int)$col->getId()] = $col->getLabel();
		}

		return $list;
	}

	public static function getCustomFieldConfig(){
		SOY2::import("site_include.plugin.CustomFieldAdvanced.CustomFieldAdvanced");

		$obj = CMSPlugin::loadPluginConfig(CustomFieldPluginAdvanced::PLUGIN_ID);
		if(is_null($obj)) $obj = new CustomFieldPluginAdvanced();

		if(!count($obj->customFields)) return array();

		$list = array();
		foreach($obj->customFields as $fieldId => $field){
			switch($field->getType()){
				case "input":	//許可するフィールド種別
				case "textarea":
				case "select":
				case "image":
					$list[$fieldId] = $field->getLabel();
					break;
				default:
					//
			}

		}

		return $list;
	}

	public static function getLabelList(){
		try{
			$labels = SOY2DAOFactory::create("cms.LabelDAO")->get();
		}catch(Exception $e){
			return array();
		}

		if(!count($labels)) return array();

		$list = array();
		foreach($labels as $label){
			$list[$label->getId()] = $label->getCaption();
		}

		return $list;
	}

	private static function _getInquiryForms(){
		static $forms;
		if(is_null($forms)){
			$old = SOYAppUtil::switchAppMode("inquiry");

			try{
				$forms = SOY2DAOFactory::create("SOYInquiry_FormDAO")->get();
			}catch(Exception $e){
				$forms = array();
			}

			SOYAppUtil::resetAppMode($old);
		}

		return $forms;
	}

	private static function _getColumnsByFormId($formId){
		$old = SOYAppUtil::switchAppMode("inquiry");
		try{
			$columns = SOY2DAOFactory::create("SOYInquiry_ColumnDAO")->getOrderedColumnsByFormId($formId);
		}catch(Exception $e){
			$columns = array();
		}

		SOYAppUtil::resetAppMode($old);

		return $columns;
	}

	public static function getIdByFormId($formId){
		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAppMode("inquiry");

		try{
			$id = (int)SOY2DAOFactory::create("SOYInquiry_FormDAO")->getByFormId($formId)->getId();
		}catch(Exception $e){
			$id = null;
		}
		SOYAppUtil::resetAppMode($old);

		return $id;
	}

	public static function getLastInquiryTime($formId){
		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAppMode("inquiry");
		$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");

		$sql = "SELECT create_date FROM soyinquiry_inquiry WHERE form_id = :formId  AND flag != " . SOYInquiry_Inquiry::FLAG_DELETED . " ORDER BY create_date DESC LIMIT 1";
		try{
			$results = $dao->executeQuery($sql, array(":formId" => $formId));
		}catch(Exception $e){
			$results = array();
		}

		SOYAppUtil::resetAppMode($old);

		return (isset($results[0]["create_date"]) && is_numeric($results[0]["create_date"])) ? (int)$results[0]["create_date"] : 0;
	}

	public static function getInquiryContentsAndDateByFormIdAfterSpecifiedTime($formId, $time){
		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAppMode("inquiry");
		$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");

		$sql = "SELECT tracking_number, content, data, create_date FROM soyinquiry_inquiry WHERE form_id = :formId AND create_date > :t AND flag != " . SOYInquiry_Inquiry::FLAG_DELETED;
		try{
			$results = $dao->executeQuery($sql, array(":formId" => $formId, ":t" => $time));
		}catch(Exception $e){
			$results = array();
		}

		$trackingNumbers = array();
		$contents = array();
		$datas = array();
		$dates = array();
		if(count($results)){
			foreach($results as $v){
				if(!isset($v["data"]) || !strlen($v["data"])) continue;
				$data = soy2_unserialize($v["data"]);
				if(!count($data)) continue;
				$trackingNumbers[] = $v["tracking_number"];
				$contents[] = $v["content"];
				$datas[] = $data;
				$dates[] = $v["create_date"];
			}
		}

		SOYAppUtil::resetAppMode($old);

		return array($trackingNumbers, $contents, $datas, $dates);
	}

	public static function defineInquiryDsn(){
		SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAppMode("inquiry");

		//後に使用する定数の設定
		if(!defined("DISPLAY_INQUIRY_CONTENT_DSN")) define("DISPLAY_INQUIRY_CONTENT_DSN", SOY2DAOConfig::dsn());
		if(!defined("DISPLAY_INQUIRY_CONTENT_USER")) define("DISPLAY_INQUIRY_CONTENT_USER", SOY2DAOConfig::user());
		if(!defined("DISPLAY_INQUIRY_CONTENT_PASS")) define("DISPLAY_INQUIRY_CONTENT_PASS", SOY2DAOConfig::pass());

		SOYAppUtil::resetAppMode($old);
	}
}
