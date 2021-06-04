<?php
class LabelLogic extends SOY2LogicBase{

	/**
	 * ラベルの新規作成
	 */
    function create($bean){

    	if(!self::checkDuplicateCaption($bean->getCaption())) throw new Exception("Duplicated Caption: ".$bean->getCaption());

    	$bean->setAlias(self::getUniqueAlias($bean->getCaption()));
    	$bean->setDefaultDisplayOrder();

    	$dao = self::getLabelDAO();

    	return $dao->insert($bean);
    }

    /**
     * キャプションの重複チェック
     */
    function checkDuplicateCaption($caption, $id = null){
    	$dao = self::getLabelDAO();

    	try{
    		//自分以外が取れたらNG
    		$label = $dao->getByCaption($caption);
    		if($id && $id == $label->getId()) return true;
    		return false;
    	}catch(Exception $e){
    		//取れないなら使われていないキャプションなのでOK
    		return true;
    	}
    }

    /**
     * エイリアスの重複チェック
     */
    private function checkDuplicateAlias($alias, $id = null){
    	$dao = self::getLabelDAO();

		//エイリアスが空ならIDを使うのでOK
		if(strlen($alias)==0) return true;

		try{
    		//自分以外が取れたらNG
    		$label = $dao->getByAlias($alias);
    		if($id && $id == $label->getId()) return true;
    		return false;
    	}catch(Exception $e){
    		//取れないなら使われていないエイリアスなのでOK
			return true;
    	}
    }

    /**
     * ユニークなエイリアスを取得
     */
    private function getUniqueAlias($caption, $id = null){
    	$dao = self::getLabelDAO();
		$alias = CMSUtil::sanitizeAlias($caption);

   		//[?#\/%\&]は取り除く
   		$alias = CMSUtil::sanitizeAlias($alias);

   		//数字だけの場合は_を前につける
   		if(is_numeric($alias)) $alias = "_".$alias;

		//重複したら空にしてIDを使う
		if(!self::checkDuplicateAlias($alias, $id)) $alias = null;

   		return $alias;
    }

    function get(){
    	$dao = self::getLabelDAO();
    	$labels = $dao->get();

    	foreach($labels as $key => $label){
    		$labels[$key]->setEntryCount($dao->getEntryCount($label->getId()));
    	}

    	return $labels;
    }

	/**
	 * 管理権限を考慮してラベルを取得する：記事管理者は先頭に*が付くラベルにアクセスできない
	 * @return Array
	 */
	function getWithAccessControl(){
    	$dao = self::getLabelDAO();
    	$labels = $dao->get();

		//記事管理者の場合
		if(class_exists("UserInfoUtil") && !UserInfoUtil::hasSiteAdminRole()){
			//ラベル名の先頭が*のラベルは削除する
			foreach($labels as $key => $label){
				if($label->isEditableByNormalUser()){
					// TODO 記事の数からアクセスできない記事の分を除く
					$labels[$key]->setEntryCount($dao->getEntryCount($label->getId()));
				}else{
					unset($labels[$key]);
				}
			}
		}else{
			foreach($labels as $key => $label){
				$labels[$key]->setEntryCount($dao->getEntryCount($label->getId()));
			}
		}

		return $labels;
	}

	/**
	 * アクセスできないラベルのIDのリストを取得する
	 * @return Array
	 */
	function getProhibitedLabelIds(){
		$prohibitedIds = array();

		//記事管理者の場合
		if(class_exists("UserInfoUtil") && !UserInfoUtil::hasSiteAdminRole()){
			$labels = $this->get();

			//記事管理者がアクセスできないラベルのIDだけを取る
			foreach($labels as $key => $label){
				if( ! $label->isEditableByNormalUser() ){
					$prohibitedIds[] = $key;
				}
			}
		}

		return $prohibitedIds;
	}

    function getById($id){
    	$dao = self::getLabelDAO();
    	return $dao->getById($id);
    }

    function getByCaption($caption){
    	$dao = self::getLabelDAO();
    	return $dao->getByCaption($caption);
    }

    function update($bean){

    	if(!self::checkDuplicateCaption($bean->getCaption(), $bean->getId())) throw new Exception("Duplicated Caption: ".$bean->getCaption());
    	if(!self::checkDuplicateAlias($bean->getAlias(), $bean->getId())) throw new Exception("Duplicated Alias: ".$bean->getAlias());

		if(is_null($bean->getAlias()) || !strlen($bean->getAlias())){
			$bean->setAlias(self::getUniqueAlias($bean->getCaption(), $bean->getId()));
		}

    	$dao = self::getLabelDAO();
    	$dao->update($bean);
    }

    function delete($id){
    	$dao = self::getLabelDAO();

    	$label = $dao->getById($id);

    	//ブログページに設定されているラベルの場合は削除できないようにする
    	$pageDAO= SOY2DAOFactory::create("cms.PageDAO");
    	$blogDAO = SOY2DAOFactory::create("cms.BlogPageDAO");

    	foreach($pageDAO->get() as $key => $page){
    		if($page->getPageType() != Page::PAGE_TYPE_BLOG){
    			continue;
    		}

    		$blog = $blogDAO->getById($page->getId());

    		if($blog->getBlogLabelId() == $id){
    			return false;
    		}
    	}
    	$dao->delete($id);
    	return true;
    }

    function getBlogCategoryLabelsByPageId($pageId){
		try{
			$list = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($pageId)->getCategoryLabelList();
		}catch(Exception $e){
			$list = array();
		}
    	if(!count($list)) return array();

		//SQLを一回で済ませる
		$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
		try{
			$res = $labelDAO->executeQuery("SELECT * FROM Label WHERE id IN (" . implode(",", $list) . ")");
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$ret_val = array();
		foreach($res as $v){
			$ret_val[$v["id"]] = $labelDAO->getObject($v);
		}

    	//並べ替え
    	uasort($ret_val,function($a,$b) { return $b->compare($a); });

    	return $ret_val;
    }

    function getBlogLabelByPageId($pageId){
    	$dao = SOY2DAOFactory::create("cms.BlogPageDAO");
		$labelDAO = SOY2DAOFactory::create("cms.LabelDAO");
    	$page = $dao->getById($pageId);
    	$labelId = $page->getBlogLabelId();
    	return $labelDAO->getById($labelId);
    }

    private static function &getLabelDAO(){
    	static $_dao;
    	if(is_null($_dao)) $_dao = SOY2DAOFactory::create("cms.LabelDAO");
    	return $_dao;
    }

	/**
	 * 引数で指定されたラベルIDが含まれているエントリーについているラベルを返す
	 */
	function getNarrowLabels($labelIds){
		$dao = SOY2DAOFactory::create("cms.EntryLabelDAO");

		$tmp = $dao->getNarrowLabels($labelIds);

		$result = array();
		foreach($tmp as $entryLabel){
			if(in_array($entryLabel->getLabelId(),$labelIds))continue;
			$result[] = $entryLabel->getLabelId();
		}

		return $result;
	}

	/**
	 * ブロック内で記事に紐付いているラベルを取得する
	 */
	function getLabelsByBlogPageIdAndEntryId($blogPageId, $entryId){
		static $blogPageLabels, $entryLogic;
		if(is_null($blogPageLabels)) {
			$blogPageLabels = array();
			$entryLogic = SOY2Logic::createInstance("logic.site.Entry.EntryLogic");
		}
		if(!isset($blogPageLabels[$blogPageId])){
			$blogPageLabels[$blogPageId] = self::getBlogCategoryLabelsByPageId($blogPageId);
		}

		//記事IDに紐付いているラベルを調べる
		$labelIds = $entryLogic->getLabelIdsByEntryId($entryId);
		if(!count($labelIds)) return array();

		$labelList = array();
		foreach($labelIds as $labelId){
			if(isset($blogPageLabels[$blogPageId][$labelId])) $labelList[] = $blogPageLabels[$blogPageId][$labelId];
		}

		return $labelList;
	}

}
