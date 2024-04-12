<?php

class BuildEntryCustomFieldFormComponent {

	private $pluginObj;

	/**
	 * @paran int
	 * @return string
	 */
	function buildForm(int $entryId){
		$langs = SOYCMSUtilMultiLanguageUtil::getLanguageList($this->pluginObj);
		if(!count($langs)) return "";
		if(count($langs) === 1 && $langs[0] === SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP) return "";

		$html = array();
		$html[] = "<br><div class=\"alert alert-info\">多言語化プラグインの設定</div>";

		$entryLabels = ($entryId > 0) ? soycms_get_hash_table_dao("entry_label")->getByEntryId($entryId) : array();
		if(!count($entryLabels)){
			$html[] = "<div class=\"alert alert-warning\">ラベルにチェックを入れ、記事の作成 or 更新を行うと多言語の紐付けの項目が表示されます。</div>";
		}else{
			// 多言語用のラベルとして登録されているか？を調べる
			$parentId = self::_getParentIdByChildId($entryId);
			if($parentId){
				$parentEntry = soycms_get_entry_object($parentId);
				$html[] = "<label>日本語用記事：</label><a href=\"".SOY2PageController::createLink("Entry.Detail.".$parentEntry->getId())."\" class=\"btn btn-outline btn-primary\">".$parentEntry->getTitle()."</a>";
			// 多言語設定
			}else{
				$list = self::_dao()->getRelationListByParentId($entryId);

				$labelIds = array();
				foreach($entryLabels as $entryLabel){
					$labelIds[] = $entryLabel->getLabelId();
				}
				
				foreach($langs as $lang){
					if($lang === SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP) continue;

					$langIdx = SOYCMSUtilMultiLanguageUtil::getLanguageIndex($lang);
					$selected = (isset($list[$langIdx])) ? $list[$langIdx] : null;

					$html[] = "<div class=\"form-group form-inline\">";
					$html[] = "<label>".SOYCMSUtilMultiLanguageUtil::getLanguageLabel($lang)."：</label>";
					$html[] = "<select name=\"multi_language[".$lang."]\" class=\"form-control\">";
					$html[] = "<option></option>";

					$isHit = false;
					foreach(self::_getEntryList($labelIds, $lang) as $_entryId => $title){
						if($_entryId == $entryId) continue;
						if(is_numeric($selected) && $selected === (int)$_entryId){
							$html[] = "<option value=\"".$_entryId."\" selected>".$title."</option>";
							$isHit = true;
						}else{
							$html[] = "<option value=\"".$_entryId."\">".$title."</option>";
						}
					}
					
					// 多言語記事(子記事)を選択を選択しているのに、セレクトボックスの項目として出力されていない時の対応
					if(!$isHit && is_numeric($selected)){
						$selectedEntry = soycms_get_entry_object($selected);
						if(is_numeric($selectedEntry->getId())){
							$html[] = "<option value=\"".$selectedEntry->getId()."\" selected>".$selectedEntry->getTitle()."</option>";
						}
					}
					$html[] = "</select>";
					if(is_numeric($selected)) $html[] = "<a href=\"".SOY2PageController::createLink("Entry.Detail.".$selected)."\" class=\"btn btn-outline btn-primary\">詳細</a>";
					$html[] = "</div>";
				}
			}
		}

		return implode("\n", $html);
	}

	/**
	 * @param int
	 * @return int
	 */
	private function _getParentIdByChildId(int $childId){
		try{
			$parentId = (int)self::_dao()->getByChildId($childId)->getParentId();
		}catch(Exception $e){
			$parentId = 0;
		}
		return $parentId;
	}

	/**
	 * @param array
	 * @return array
	 */
	private function _getEntryList(array $labelIds, string $lang){
		if(!count($labelIds)) return array();

		// 念の為、多言語化したラベルID分を取得しておく
		$labelIds = self::_labelDao()->sortOutParentLabelIds($labelIds);
		if(!count($labelIds)) return array();

		// 多言語ラベル用に変換
		$childLabelIds = self::_labelDao()->getRelationListByParentIdsAndLang($labelIds, $lang);
		if(!count($childLabelIds) || count($labelIds) != count($childLabelIds)) return array();
		
		$_childIds = array();
		foreach($childLabelIds as $_labId){
			$_childIds[] = $_labId;
		}
		
		try{
			$res = soycms_get_hash_table_dao("entry_label")->executeQuery(
				"SELECT entry_id, label_id FROM EntryLabel ".
				"WHERE label_id IN (".implode(",", $_childIds).")"
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$table = array();
		foreach($res as $v){
			$entryId = (int)$v["entry_id"];
			if(!isset($table[$entryId])) $table[$entryId] = array();
			$table[$entryId][] = (int)$v["label_id"]; 
		}
		
		$_entryIds = array();
		foreach($table as $entryId => $_labelIds){
			// 配列の並び順が異なる場合があるのでソートしておく
			sort($_childIds);
			sort($_labelIds);
			if($_childIds === $_labelIds) $_entryIds[] = $entryId;
		}
		
		try{
			$res = soycms_get_hash_table_dao("entry")->executeQuery(
				"SELECT id, title FROM Entry ".
				"WHERE id IN (".implode(",", $_entryIds).")"
			);
		}catch(Exception $e){
			$res = array();
		}
		if(!count($res)) return array();

		$_arr = array();
		foreach($res as $v){
			$_arr[(int)$v["id"]] = $v["title"];
		}
		
		return $_arr;
	}

	private function _dao(){
		static $d;
		if(is_null($d)){
			SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageEntryRelationDAO");
			$d = SOY2DAOFactory::create("MultiLanguageEntryRelationDAO");
		}
		return $d;
	}

	private function _labelDao(){
		static $d;
		if(is_null($d)){
			SOY2::import("site_include.plugin.util_multi_language.domain.MultiLanguageLabelRelationDAO");
			$d = SOY2DAOFactory::create("MultiLanguageLabelRelationDAO");
		}
		return $d;
	}

	function setPluginObj($pluginObj){
		$this->pluginObj = $pluginObj;
	}
}