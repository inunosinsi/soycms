<?php

class DisplayLogic extends SOY2LogicBase{
		
	function DisplayLogic(){
		$this->entryLabelDao = SOY2DAOFactory::create("cms.EntryLabelDAO");
	}
	
	/**
	 * @param int entryId, object SOY2HTMLObject
	 * @return int LabelId, array LabelIds
	 */
	function checkAcceleration($entryId, $htmlObj){
		
		//HTML記述ブロックとスクリプトモジュールブロックの場合は何もしない
		if(get_class($htmlObj) == "HTMLBlockComponent_ViewPage" || get_class($htmlObj) == "ScriptModuleBlockComponent_ViewPage"){
			return array(null, array());
		}
		
		//記事ブロックの設定の場合
		if(get_class($htmlObj) == "EntryBlockComponent_ViewPage"){
			$entryLabels = $this->getEntryLabels($entryId);
			if(count($entryLabels) === 0) return array(null, array());
			
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
		if(is_null($labelIdWithBlock)){
			$labelIdWithBlock = (isset($htmlObj->blogLabelId)) ? (int)$htmlObj->blogLabelId : null;
		}
			
		//ブロックのカテゴリ設定をしているラベル
		$blogCategoryLabelList = (isset($htmlObj->categoryLabelList)) ? $htmlObj->categoryLabelList : array();
				
		return array($labelIdWithBlock, $blogCategoryLabelList);
	}
	
	function getEntryLabels($entryId){
		try{
			$entryLabels = $this->entryLabelDao->getByEntryId($entryId);
		}catch(Exception $e){
			$entryLabels = array();
		}
		
		return $entryLabels;
	}
}
?>