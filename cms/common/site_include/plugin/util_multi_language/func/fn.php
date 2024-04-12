<?php
function multi_language_execute_common_update_process(int $id, string $mode="Entry"){
	SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguage".$mode."RelationDAO");
	$dao = SOY2DAOFactory::create("MultiLanguage".$mode."RelationDAO");

	foreach($_POST["multi_language"] as $lang => $_id){
		$idx = SOYCMSUtilMultiLanguageUtil::getLanguageIndex($lang);
		$_id = (int)$_id;
		// 登録
		if($_id > 0){
			$obj = ($mode == "Entry") ? new MultiLanguageEntryRelation() : new MultiLanguageLabelRelation();
			$obj->setParentId($id);
			$obj->setLang($idx);
			$obj->setChildId($_id);
			
			try{
				$dao->insert($obj);
			}catch(Exception $e){
				try{
					$dao->delete($id, $idx);
					$dao->insert($obj);
				}catch(Exception $e){
					//
				}
			}

		// 削除
		}else{
			try{
				$dao->delete($id, $idx);
			}catch(Exception $e){
				//
			}
		}
	}

	return true;
}

function multi_language_execute_common_remove_process(array $ids, string $mode="Entry"){
	SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguage".$mode."RelationDAO");
	$dao = SOY2DAOFactory::create("MultiLanguage".$mode."RelationDAO");

	foreach($ids as $id){
		try{
			$dao->deleteByParentId($id);
		}catch(Exception $e){
			//
		}
	}

	return true;
}