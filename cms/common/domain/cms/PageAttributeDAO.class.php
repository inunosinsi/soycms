<?php
/**
 * @entity cms.PageAttribute
 */
abstract class PageAttributeDAO extends SOY2DAO{

    abstract function insert(PageAttribute $bean);

	/**
     * @query #pageId# = :pageId AND #fieldId# = :fieldId
     */
    abstract function update(PageAttribute $bean);

    /**
     * @index fieldId
     */
    abstract function getByPageId($pageId);

	/**
	 * @return object
	 * @query #pageId# = :pageId AND #fieldId# = :fieldId
	 */
    abstract function get($pageId, $fieldId);

	/**
	 * @final
	 */
	function getAll(){
		try{
			$res = $this->executeQuery("SELECT * FROM PageAttribute");
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


    function getByPageIdCustom(int $pageId, array $fields){
    	$sql = "SELECT * FROM PageAttribute ".
    			"WHERE page_id = :pageId ".
    			"AND page_field_id IN (\"" . implode("\",\"", $fields) . "\")";
    	$binds = array(":pageId" => (int)$pageId);

    	try{
    		$results = $this->executeQuery($sql, $binds);
    	}catch(Exception $e){
    		return array();
    	}

    	$attributes = array();
    	foreach($results as $result){
    		if(!isset($result["page_field_id"])) continue;
    		$attributes[$result["page_field_id"]] = $this->getObject($result);
    	}

    	return $attributes;
    }

    abstract function deleteByPageId($pageId);

    /**
     * @query #pageId# = :pageId AND #fieldId# = :fieldId
     */
    abstract function delete($pageId, $fieldId);

    abstract function deleteByFieldId($fieldId);
}
