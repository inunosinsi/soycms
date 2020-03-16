<?php
class CustomFieldPluginAdvanced{

	const PLUGIN_ID = "CustomFieldAdvanced";

	function getId(){
		return self::PLUGIN_ID;
	}

	//カスタムフィールドの項目設定
	public $customFields = array();

		/**
	 * 特定の記事のカスタムフィールドの値を返す
	 * @param int entryId 記事のID
	 * @return Array <CustomField>
	 */
	function getCustomFields($entryId, $labelIdWithBlock = null, $blogCategoryLabelList = array()){

		$dao = SOY2DAOFactory::create("cms.EntryAttributeDAO");

		$customFields = $this->customFields;
		try{
			$entryAttributes = $dao->getByEntryId($entryId);
		}catch(Exception $e){
			return array();
		}

		/*
		 * 注意！
		 * $this->customFieldsは連想配列（カスタムフィールドのID => カスタムフィールドのオブジェクト）
		 * $db_arryはただの配列（連番 => カスタムフィールドのオブジェクト（IDと値だけが入っている、高度な設定などは空））
		 */

		SOY2::import("module.plugins.parts_entry_import.class.CustomField");


		//記事にないカスタムフィールドの設定内容を入れておく
		//（HTMLListやカスタムフィールドを追加したときの既存の記事のため）
		$list = array();
		foreach($customFields as $fieldId => $fieldValue){
			$added = new CustomField();
			$added->setId($fieldId);

			//カスタムフィールドのデータがある場合
			if(isset($entryAttributes[$fieldId])
			&& $entryAttributes[$fieldId] instanceof EntryAttribute){
				//do nothing
				$attr = $entryAttributes[$fieldId];
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
}
