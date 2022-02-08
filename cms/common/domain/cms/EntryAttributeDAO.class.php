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


    function getByEntryIdCustom(int $entryId, array $fieldIds){
    	if(!is_numeric($entryId) || count($fieldIds) === 0) return array();

    	$sql = "SELECT * FROM EntryAttribute ".
    			"WHERE entry_id = :entryId ".
    			"AND entry_field_id IN (\"" . implode("\",\"", $fieldIds) . "\")";
    	$binds = array(":entryId" => (int)$entryId);

    	try{
    		$res = $this->executeQuery($sql, $binds);
    	}catch(Exception $e){
    		$res = array();
    	}

    	$attrs = array();
		foreach($res as $v){
    		if(!isset($v["entry_field_id"])) continue;
    		$attrs[$v["entry_field_id"]] = $this->getObject($v);
    	}

		foreach($fieldIds as $fieldId){
			if(!isset($attrs[$fieldId])) $attrs[$fieldId] = new EntryAttribute();
		}

    	return $attrs;
    }

    abstract function deleteByEntryId($entryId);

    /**
     * @query #entryId# = :entryId AND #fieldId# = :fieldId
     */
    abstract function delete($entryId, $fieldId);

    abstract function deleteByFieldId($fieldId);
}
