<?php
/**
 * @entity cms.EntryAttribute
 */
abstract class EntryAttributeDAO extends SOY2DAO{

    abstract function insert(EntryAttribute $bean);

	/**
     * @query #entryId# = :entryId AND #fieldId# = :fieldId
     */
    abstract function update(EntryAttribute $bean);

    /**
     * @index fieldId
     */
    abstract function getByEntryId($entryId);

	/**
	 * @return object
	 * @query #entryId# = :entryId AND #fieldId# = :fieldId
	 */
    abstract function get($entryId, $fieldId);

	/**
	 * @final
	 */
	function getAll(){
		try{
			$res = $this->executeQuery("SELECT * FROM EntryAttribute");
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


    function getByEntryIdCustom($entryId, $fields){
    	if(!is_numeric($entryId) || count($fields) === 0) return array();

    	$sql = "SELECT * FROM EntryAttribute ".
    			"WHERE entry_id = :entryId ".
    			"AND entry_field_id IN (\"" . implode("\",\"", $fields) . "\")";
    	$binds = array(":entryId" => (int)$entryId);

    	try{
    		$results = $this->executeQuery($sql, $binds);
    	}catch(Exception $e){
    		return array();
    	}

    	$attributes = array();
    	foreach($results as $result){
    		if(!isset($result["entry_field_id"])) continue;
    		$attributes[$result["entry_field_id"]] = $this->getObject($result);
    	}

    	return $attributes;
    }

    abstract function deleteByEntryId($entryId);

    /**
     * @query #entryId# = :entryId AND #fieldId# = :fieldId
     */
    abstract function delete($entryId, $fieldId);

    abstract function deleteByFieldId($fieldId);
}
