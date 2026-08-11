<?php
/**
 * @entity shop.SOYShop_CategoryAttribute
 */
abstract class SOYShop_CategoryAttributeDAO extends SOY2DAO{

    abstract function insert(SOYShop_CategoryAttribute $bean);

    /**
     * @query #categoryId# = :categoryId AND #fieldId# = :fieldId
     */
    abstract function update(SOYShop_CategoryAttribute $bean);

    /**
     * @index fieldId
     */
    abstract function getByCategoryId($categoryId);

    /**
     * @return object
     * @query #categoryId# = :categoryId AND #fieldId# = :fieldId
     */
    abstract function get($categoryId,$fieldId);

    /**
	 * @final
	 * isBackwardMatchにすることでfieldId_多言語化のpostfixも検索対象にする
	 * @param int categoryId, array $fieldIds, bool $isBackwardMatch
	 * @return array
	 */
	function getByCategoryIdAndFieldIds(int $categoryId, array $fieldIds, bool $isBackwardMatch=true){
		if(!count($fieldIds)) return array();

		if($isBackwardMatch){
			$sql = "SELECT * ".
					"FROM soyshop_category_attribute ".
					"WHERE category_id = :categoryId ";
			$q = array();
			foreach($fieldIds as $fieldId){
				$q[] = "category_field_id LIKE '" . htmlspecialchars($fieldId, ENT_QUOTES, "UTF-8") . "%'";
			}
			$sql .=	"AND (" . implode(" OR ", $q) . ")";
		}else{
			$sql = "SELECT * ".
					"FROM soyshop_category_attribute ".
					"WHERE category_id = :categoryId ".
					"AND category_field_id IN (\"" . implode("\",\"", $fieldIds) . "\")";
		}

		try{
			$res = $this->executeQuery($sql, array(":categoryId" => $categoryId));
		}catch(Exception $e){
			$res = array();
		}

		$list = array();
		if(count($res)){
			foreach($res as $v){
				$list[$v["category_field_id"]] = $this->getObject($v);
			}
		}

		if($isBackwardMatch) return $list;	//後方一致モードの場合は値がない場合の穴埋めはしない

		foreach($fieldIds as $fieldId){
			if(!isset($list[$fieldId])){
				$list[$fieldId] = new SOYShop_CategoryAttribute();
			}
		}

		return $list;
	}

	/**
	 * @final
	 */
	function getAll($limit=null, $offset=null){
		$sql = "SELECT * FROM soyshop_category_attribute";
		if(isset($limit) && is_numeric($limit)) $sql .= " LIMIT " . $limit;
		if(isset($offset) && is_numeric($offset)) $sql .= " OFFSET " . $offset;
		try{
			$res = $this->executeQuery($sql);
		}catch(Exception $e){
			return array();
		}
		if(!count($res)) return array();

		$list = array();
		foreach($res as $v){
			$list[] = $this->getObject($v);
		}
		return $list;
	}

    abstract function deleteByCategoryId($categoryId);

    /**
     * @query #categoryId# = :categoryId AND #fieldId# = :fieldId
     */
    abstract function delete($categoryId,$fieldId);

    abstract function deleteByFieldId($fieldId);
}
