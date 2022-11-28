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
