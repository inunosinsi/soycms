<?php
/**
 * @entity cms.LabelAttribute
 */
abstract class LabelAttributeDAO extends SOY2DAO{

    abstract function insert(LabelAttribute $bean);

	/**
     * @query #labelId# = :labelId AND #fieldId# = :fieldId
     */
    abstract function update(LabelAttribute $bean);

    /**
     * @index fieldId
     */
    abstract function getByLabelId($labelId);

	/**
	 * @return object
	 * @query #labelId# = :labelId AND #fieldId# = :fieldId
	 */
    abstract function get($labelId, $fieldId);

	/**
	 * @final
	 */
	function getAll(){
		try{
			$res = $this->executeQuery("SELECT * FROM LabelAttribute");
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


    function getByLabelIdCustom(int $labelId, array $fields){
    	$sql = "SELECT * FROM LabelAttribute ".
    			"WHERE label_id = :labelId ".
    			"AND label_field_id IN (\"" . implode("\",\"", $fields) . "\")";
    	$binds = array(":labelId" => (int)$labelId);

    	try{
    		$results = $this->executeQuery($sql, $binds);
    	}catch(Exception $e){
    		return array();
    	}

    	$attributes = array();
    	foreach($results as $result){
    		if(!isset($result["label_field_id"])) continue;
    		$attributes[$result["label_field_id"]] = $this->getObject($result);
    	}

    	return $attributes;
    }

    abstract function deleteByLabelId($labelId);

    /**
     * @query #labelId# = :labelId AND #fieldId# = :fieldId
     */
    abstract function delete($labelId, $fieldId);

    abstract function deleteByFieldId($fieldId);
}
