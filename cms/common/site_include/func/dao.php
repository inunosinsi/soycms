<?php

use function Complex\ln;

/** 共通且つ効率化する関数群 **/
//ハッシュテーブル用のハッシュ値を作成する
function soycms_generate_hash_value(string $str, int $length=12){
	$hash = md5($str);
	return substr($hash, 0, $length);
}

function soycms_get_hash_table_types(){
	static $types;
	if(is_null($types)) $types = array("entry", "entry_attribute", "entry_label", "label", "label_attribute", "page", "blog_page");
	return $types;
}

function soycms_get_hash_table_mode(string $fnName){
	if(is_bool(strpos($fnName, "soycms_"))) return $fnName;
	$fnName = str_replace(array("soycms_get_", "soycms_save_", "_objects", "_object"), "", $fnName);
	if(is_numeric(strpos($fnName, "_by_"))) $fnName = substr($fnName, 0, strpos($fnName, "_by_"));
	return $fnName;
}

//ハッシュ値を記録したテーブルを用いてインデックスを検索する
function soycms_get_hash_index(string $str, string $fnName){
	static $tables;
	if(is_null($tables)) $tables = array_fill(0, count(soycms_get_hash_table_types()), array());

	$idx = array_search(soycms_get_hash_table_mode($fnName), soycms_get_hash_table_types());
	$hash = soycms_generate_hash_value($str);
	if(!count($tables[$idx]) || !is_numeric(array_search($hash, $tables[$idx]))) $tables[$idx][] = $hash;
	return array_search($hash, $tables[$idx]);
}

function soycms_get_hash_table_dao(string $fnName){
	static $daos;
	if(is_null($daos)) $daos = array_fill(0, count(soycms_get_hash_table_types()), null);

	$idx = array_search(soycms_get_hash_table_mode($fnName), soycms_get_hash_table_types());
	if(!is_null($daos[$idx])) return $daos[$idx];

	switch($idx){
		case 0:	//entry
			$path = "cms.EntryDAO";
			break;
		case 1:	//entry_attribute
			$path = "cms.EntryAttributeDAO";
			break;
		case 2:	//entry_label
			$path = "cms.EntryLabelDAO";
			break;
		case 3:	//label
			$path = "cms.LabelDAO";
			break;
		case 4:	//label_attribute
			$path = "cms.LabelAttributeDAO";
			break;
		case 5:	//page
			$path = "cms.PageDAO";
			break;
		case 6:
			$path = "cms.BlogPageDAO";
			break;
	}
	$daos[$idx] = SOY2DAOFactory::create($path);
	return $daos[$idx];
}

/**
 * 配列から指定の値を除き、間を詰める
 */
function soycms_remove_value_on_array(string $fieldId, array $fieldIds){
	if(!count($fieldIds)) return array($fieldIds);
	$idx = array_search($fieldId, $fieldIds);
	if(!is_numeric($idx)) return $fieldIds;

	unset($fieldIds[$idx]);
	return array_values($fieldIds);
}

/** 各種オブジェクトを取得する関数群 **/
/**
 * 各attribute値を$dataTypeに従って変換する
 * dataType : string, int, bool
 */
function soycms_get_attribute_value($v=null, $dataType=""){
	switch($dataType){
		case "string":
			return (is_string($v)) ? $v : "";
			break;
		case "int":
			if(is_numeric($v)) return (int)$v;
			break;
		case "bool":
			if(is_null($v) || (is_string($v) && !strlen($v))) return false;
			if(is_string($v) && strlen($v)) return true;
			if(is_numeric($v) && (int)$v === 1) return true;
			return false;
			break;
	}
	return $v;
}

/** 記事IDから記事オブジェクト **/
function soycms_get_entry_object(int $entryId){
	$dao = soycms_get_hash_table_dao(__FUNCTION__);
	if((int)$entryId <= 0) return new Entry();

	$idx = soycms_get_hash_index((string)$entryId, __FUNCTION__);
	if(isset($GLOBALS["soycms_entry_hash_table"][$idx])) return $GLOBALS["soycms_entry_hash_table"][$idx];

	try{
        $GLOBALS["soycms_entry_hash_table"][$idx] = $dao->getById($entryId);
    }catch(Exception $e){
        $GLOBALS["soycms_entry_hash_table"][$idx] = new Entry();
    }
	return $GLOBALS["soycms_entry_hash_table"][$idx];
}

function soycms_get_entry_object_by_alias(string $alias){
	$dao = soycms_get_hash_table_dao(__FUNCTION__);
	if(!strlen($alias)) return new Entry();

	try{
		$entry = $dao->getByAlias($alias);
	}catch(Exception $e){
		return new Entry();
	}

	$idx = soycms_get_hash_index((string)$entry->getId(), __FUNCTION__);
	if(!isset($GLOBALS["soycms_entry_hash_table"][$idx])) $GLOBALS["soycms_entry_hash_table"][$idx] = $entry;
	return $entry;
}

/** 商品IDとカスタムフィールドのIDから商品属性のオブジェクトを取得する **/
function soycms_get_entry_attribute_object(int $entryId, string $fieldId){
	$dao = soycms_get_hash_table_dao(__FUNCTION__);
	if((int)$entryId <= 0 || !strlen($fieldId)) return new EntryAttribute();

	$idx = soycms_get_hash_index(((string)$entryId . $fieldId), __FUNCTION__);
	if(isset($GLOBALS["soycms_entry_attribute_hash_table"][$idx])) return $GLOBALS["soycms_entry_attribute_hash_table"][$idx];

	try{
		$GLOBALS["soycms_entry_attribute_hash_table"][$idx] = $dao->get($entryId, $fieldId);
	}catch(Exception $e){
		$attr = new EntryAttribute();
		$attr->setEntryId($entryId);
		$attr->setFieldId($fieldId);
		$GLOBALS["soycms_entry_attribute_hash_table"][$idx] = $attr;
	}

	return $GLOBALS["soycms_entry_attribute_hash_table"][$idx];
}

/**
function soycms_get_entry_attribute_objects(int $entryId, array $fieldIds){
	$dao = soycms_get_hash_table_dao(__FUNCTION__);
	if(!count($fieldIds)) return array();

	$attrs = array();
	foreach($fieldIds as $fieldId){
		$idx = soycms_get_hash_index(((string)$entryId . $fieldId), __FUNCTION__);
		if(!isset($GLOBALS["soycms_entry_attribute_hash_table"][$idx])) continue;
		$attrs[$fieldId] = $GLOBALS["soycms_entry_attribute_hash_table"][$idx];
	}
	if(count($attrs)){
		foreach($attrs as $fieldId => $_dust){
			$fieldIds = soyshop_remove_value_on_array($fieldId, $fieldIds);
		}
		unset($_dust);
	}
	if(!count($fieldIds)) return $attrs;

	$attrs = $dao->getByEntryIdAndFieldIds($entryId, $fieldIds);
	foreach($attrs as $fieldId => $attr){
		$idx = soycms_get_hash_index(((string)$entryId . $fieldId), __FUNCTION__);
		if(!isset($GLOBALS["soycms_entry_attribute_hash_table"][$idx])) $GLOBALS["soycms_entry_attribute_hash_table"][$idx] = $attr;
	}
	return $attrs;
}
**/
function soycms_get_entry_attribute_value(int $entryId, string $fieldId, string $dataType=""){
	return soycms_get_attribute_value(soycms_get_entry_attribute_object($entryId, $fieldId)->getValue(), $dataType);
}

function soycms_save_entry_attribute_object(EntryAttribute $attr){
	$dao = soycms_get_hash_table_dao(__FUNCTION__);

	if(is_string($attr->getValue())) $attr->setValue(trim($attr->getValue()));
	if(is_string($attr->getValue()) && !strlen($attr->getValue())) $attr->setValue(null);

	if(!is_null($attr->getValue()) || !is_null($attr->getExtraValues())){
		try{
			$dao->insert($attr);
		}catch(Exception $e){
			try{
				$dao->update($attr);
			}catch(Exception $e){
				//
			}
		}
	}else{
		try{
			$dao->delete($attr->getEntryId(), $attr->getFieldId());
		}catch(Exception $e){
			//
		}
	}
}

/** ラベルIDからラベルオブジェクト **/
function soycms_get_label_object(int $labelId){
	$dao = soycms_get_hash_table_dao(__FUNCTION__);
	if((int)$labelId <= 0) return new Label();

	$idx = soycms_get_hash_index((string)$labelId, __FUNCTION__);
	if(isset($GLOBALS["soycms_label_hash_table"][$idx])) return $GLOBALS["soycms_label_hash_table"][$idx];

	try{
        $GLOBALS["soycms_label_hash_table"][$idx] = $dao->getById($labelId);
    }catch(Exception $e){
        $GLOBALS["soycms_label_hash_table"][$idx] = new Label();
    }
	return $GLOBALS["soycms_label_hash_table"][$idx];
}

/** 
 * ページIDからページオブジェクト 
 * isPriorityBlogPageModeをtrueにすると、ブログページを優先的に検索する
 * @param int pageId, bool isPriorityBlogPageMode
 * @return Page()|BlogPage()
 **/
function soycms_get_page_object(int $pageId, bool $isPriorityBlogPageMode=true){
	$dao = soycms_get_hash_table_dao(__FUNCTION__);
	if((int)$pageId <= 0) return new Page();

	$idx = soycms_get_hash_index((string)$pageId, __FUNCTION__);
	if(isset($GLOBALS["soycms_page_hash_table"][$idx])) return $GLOBALS["soycms_page_hash_table"][$idx];

	//ブログページの方の取得を優先する
	if($isPriorityBlogPageMode){
		try{
			$blogPage = soycms_get_hash_table_dao("blog_page")->getById($pageId);
			if(is_numeric($blogPage->getId())) $GLOBALS["soycms_page_hash_table"][$idx] = $blogPage;
		}catch(Exception $e){
			//
		}
		if(isset($GLOBALS["soycms_page_hash_table"][$idx])) return $GLOBALS["soycms_page_hash_table"][$idx];
	}

	try{
		$GLOBALS["soycms_page_hash_table"][$idx] = $dao->getById($pageId);
	}catch(Exception $e){
		$GLOBALS["soycms_page_hash_table"][$idx] = new Page();
	}
	return $GLOBALS["soycms_page_hash_table"][$idx];
}

/** Pageオブジェクトのラッパー関数 */
function soycms_get_blog_page_object(int $pageId){
	$page = soycms_get_page_object($pageId, true);
	if(!class_exists("BlogPage")) SOY2::import("domain.cms.BlogPage");
	return ($page instanceof BlogPage) ? $page : new BlogPage();
}