<?php
if(!function_exists("soycms_customfield_check_is_field")) include_once(SOY2::RootDir() . "site_include/plugin/CustomFieldAdvanced/func/func.php");

class PageCustomfieldUtil {

	public static function checkIsEntryField(array $fields){
		return soycms_customfield_check_is_field($fields, "entry", 2);
	}

	public static function checkIsLabelField(array $fields){
		return soycms_customfield_check_is_field($fields, "label", 2);
	}

	public static function checkIsListField(array $fields){
		return soycms_customfield_check_is_field($fields, "list", 2);
	}

	public static function checkIsDlListField(array $fields){
		return soycms_customfield_check_is_field($fields, "dllist", 2);
	}

	/**
	 * 特定のラベルのカスタムフィールドの値を返す
	 * @param int, array
	 * @return Array <PageCustomField>
	 */
	public static function getCustomFields(int $pageId, array $customfields){
		$fieldIds = soycms_get_field_id_list($customfields);
		if(!count($fieldIds)) return array();

		$dao = soycms_get_hash_table_dao("page_attribute");

		try{
			$attrs = $dao->getByPageIdCustom($pageId, $fieldIds);
		}catch(Exception $e){
			$attrs = array();
		}
	   
		//値がない場合は満たす
		foreach($fieldIds as $fieldId){
			if(isset($attrs[$fieldId])) continue;
			$attr = new PageCustomField();
			$attr->setId($fieldId);
			$attr->setPageId($pageId);
			$attrs[$fieldId] = $attr;
		}
	   
		/*
		 * 注意！
		 * $customfieldsは連想配列（カスタムフィールドのID => カスタムフィールドのオブジェクト）
		 * $db_arryはただの配列（連番 => カスタムフィールドのオブジェクト（IDと値だけが入っている、高度な設定などは空））
		 */


		//ラベルにないカスタムフィールドの設定内容を入れておく
		//（HTMLListやカスタムフィールドを追加したときの既存の記事のため）
		$list = array();
		foreach($customfields as $fieldId => $fieldObj){
			$added = new PageCustomField();
			$added->setId($fieldId);
			
			//カスタムフィールドのデータがある場合
			if(isset($attrs[$fieldId]) && $attrs[$fieldId] instanceof PageAttribute){
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
	* @param string, string
	* @return float 0.1〜1.0
    */
	public static function calcOpacity(string $formId, string $fieldValue){
		static $pageCount, $results;
		if(is_null($pageCount)) {
			$c = soycms_get_hash_table_dao("page")->getTotalPageCount()	;
			// 404NotFoundのページ分だけ引いておく
			$pageCount = (isset($c["count"]) && is_numeric($c["count"])) ? (int)$c["count"] - 1 : 0;
			
		}
		$fieldId = str_replace("custom_field_", "", $formId);

		if(is_null($results)) $results = array();
		if(!isset($results[$fieldId])){
			try{
				$results[$fieldId] = soycms_get_hash_table_dao("page_attribute")->executeQuery("SELECT page_id, page_value FROM PageAttribute WHERE page_field_id = :fieldId", array(":fieldId" => $fieldId));
			}catch(Exception $e){
				$results[$fieldId] = array();
			}			
		}
		
		if(!count($results[$fieldId])) return 1.0;

		$hit = 0;
		foreach($results[$fieldId] as $res){
			if(!isset($res["page_value"]) || !is_string($res["page_value"]) || !strlen($res["page_value"])) continue;
			$arr = @soy2_unserialize($res["page_value"]);
			if(!is_array($arr)) continue;
			if(is_numeric(array_search($fieldValue, $arr))){
				$hit++;
			}
		}

		if($hit === 0) return 1.0;

		$opacity = 1.0 - round($hit/$pageCount, 1);
		if($opacity <= 0.3) $opacity = 0.3;

		return $opacity;
	}
}
