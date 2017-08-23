<?php

/**
 * @entity cms.Label
 */
abstract class LabelDAO extends SOY2DAO{

	/**
	 * @return id
	 */
	abstract function insert(Label $bean);
	
	/**
	 * @trigger onUpdate
	 */
	abstract function update(Label $bean);
	
	/**
	 * @trigger onDelete
	 */
	abstract function delete($id);
	
	/**
	 * @return object
	 */
	abstract function getById($id);
	
	/**
	 * @order id
	 * @return object
	 */
	abstract function getByAlias($alias);
	
	/**
	 * @index id
	 * @order #displayOrder#, id
	 */
	abstract function get();
	
	/**
	 * @return column_label_count
	 * @columns count(id) as label_count
	 */
	abstract function countLabel();
	
	/**
	 * @return object
	 */
	abstract function getByCaption($caption);
	
	/**
	 * @final
	 */
	function onDelete($query,$binds){
		$dao = SOY2DAOFactory::create("cms.EntryLabelDAO");
		$dao->deleteByLabelId($binds[":id"]);
		
		return array($query,$binds);
	}
	
	/**
	 * @final
	 */
	function onUpdate($query,$binds){
		if(strlen(@$binds[":displayOrder"]) ==0){
			$binds[":displayOrder"] = Label::ORDER_MAX;
		}
		return array($query,$binds);
	}
	
	/**
	 * @sql select count(entry_id) as entry_count from EntryLabel where label_id = :id
	 */
	function getEntryCount($id){
		$result = $this->executeQuery($this->getQuery(),$this->getBinds());
		return (int)@$result[0]["entry_count"];
	}
	
	/**
	 * @columns #displayOrder#
	 * @query #id# = :id
	 */
	abstract function updateDisplayOrder($id,$displayOrder);
	 
}
?>