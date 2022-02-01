<?php

class DisplayLogic extends SOY2LogicBase{
		
	function __construct(){}
	
	/**
	 * @param int entryId, object SOY2HTMLObject
	 * @return int LabelId, array LabelIds
	 */
	function checkAcceleration(int $entryId, $htmlObj){
		
		//HTML記述ブロックとスクリプトモジュールブロックの場合は何もしない
		if(get_class($htmlObj) == "HTMLBlockComponent_ViewPage" || get_class($htmlObj) == "ScriptModuleBlockComponent_ViewPage") return array(0, array());
		
		//記事ブロックの設定の場合
		if(get_class($htmlObj) == "EntryBlockComponent_ViewPage"){
			$entryLabels = self::_getEntryLabels($entryId);
			if(count($entryLabels) === 0) return array(0, array());
			
			$labelIdWithBlock =  array_shift($entryLabels)->getLabelId();
			$array = array();
			foreach($entryLabels as $entryLabel){
				$array[] = $entryLabel->getLabelId();
			}
			$blogCategoryLabelList = $array;
			return array($labelIdWithBlock, $blogCategoryLabelList);
		}
		
		
		//ブロックに紐づいているラベルIDを取りにいく、取得できなかった場合はブログブロックに紐づいているラベルIDを取りにいく
		$labelIdWithBlock = (isset($htmlObj->labelId)) ? (int)$htmlObj->labelId : null;
		if(is_null($labelIdWithBlock)) $labelIdWithBlock = (isset($htmlObj->blogLabelId)) ? (int)$htmlObj->blogLabelId : 0;
			
		//ブロックのカテゴリ設定をしているラベル
		$blogCategoryLabelList = (isset($htmlObj->categoryLabelList)) ? $htmlObj->categoryLabelList : array();
				
		return array($labelIdWithBlock, $blogCategoryLabelList);
	}
	
	private function _getEntryLabels(int $entryId){
		static $dao;
		if(is_null($dao)) $dao = SOY2DAOFactory::create("cms.EntryLabelDAO");
		try{
			return $dao->getByEntryId($entryId);
		}catch(Exception $e){
			return array();
		}
	}
}
