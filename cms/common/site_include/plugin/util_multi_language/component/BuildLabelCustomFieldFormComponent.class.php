<?php

class BuildLabelCustomFieldFormComponent {

	private $pluginObj;

	/**
	 * @paran int
	 * @return string
	 */
	function buildForm(int $labelId){
		$langs = SOYCMSUtilMultiLanguageUtil::getLanguageList($this->pluginObj);
		if(!count($langs)) return "";
		if(count($langs) === 1 && $langs[0] === SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP) return "";
		
		$html = array();
		$html[] = "<br><div class=\"alert alert-info\">多言語化プラグインの設定</div>";

		// 多言語用のラベルとして登録されているか？を調べる
		$parentId = self::_getParentIdByChildId($labelId);
		if($parentId){
			$parentLabel = soycms_get_label_object($parentId);
			$html[] = "<label>日本語用ラベル：</label><a href=\"".SOY2PageController::createLink("Label.Detail.".$parentLabel->getId())."\" class=\"btn btn-outline btn-primary\">".$parentLabel->getCaption()."</a>";
		// 多言語設定
		}else{
			$list = self::_dao()->getRelationListByParentId($labelId);
			
			foreach($langs as $lang){
				if($lang === SOYCMSUtilMultiLanguageUtil::LANGUAGE_JP) continue;

				$langIdx = SOYCMSUtilMultiLanguageUtil::getLanguageIndex($lang);
				$selected = (isset($list[$langIdx])) ? $list[$langIdx] : null;

				$html[] = "<div class=\"form-group form-inline\">";
				$html[] = "<label>".SOYCMSUtilMultiLanguageUtil::getLanguageLabel($lang)."：</label>";
				$html[] = "<select name=\"multi_language[".$lang."]\" class=\"form-control\">";
				$html[] = "<option></option>";
				foreach(self::_getLabelList() as $_labelId => $caption){
					if($_labelId == $labelId) continue;
					if(is_numeric($selected) && $selected === (int)$_labelId){
						$html[] = "<option value=\"".$_labelId."\" selected>".$caption."</option>";
					}else{
						$html[] = "<option value=\"".$_labelId."\">".$caption."</option>";
					}
				}
				$html[] = "</select>";
				if(is_numeric($selected)) $html[] = "<a href=\"".SOY2PageController::createLink("Label.Detail.".$selected)."\" class=\"btn btn-outline btn-primary\">詳細</a>";
				$html[] = "</div>";
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

	private function _getLabelList(){
		static $_arr;
		if(is_array($_arr)) return $_arr;

		$_arr = array();
		$labels = soycms_get_hash_table_dao("label")->get();
		if(!count($labels)) return $_arr;

		foreach($labels as $label){
			$_arr[$label->getId()] = $label->getCaption();
		}

		return $_arr;
	}

	private function _dao(){
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