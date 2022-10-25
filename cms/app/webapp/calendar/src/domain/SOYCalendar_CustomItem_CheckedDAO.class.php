<?php
/**
 * @entity SOYCalendar_CustomItem_Checked
 */
abstract class SOYCalendar_CustomItem_CheckedDAO extends SOY2DAO {

    abstract function insert(SOYCalendar_CustomItem_Checked $bean);

    abstract function getByItemId($itemId);

    abstract function deleteByItemId($itemId);

    /**
     * @final
     * @param array
     * @return array
     */
    function getCheckedListByItemIds(array $itemIds){
        if(!count($itemIds)) return array();
        try{
            $res = $this->executeQuery("SELECT * FROM soycalendar_custom_item_checked WHERE item_id IN (" . implode(",", $itemIds) . ")");
        }catch(Exception $e){
            $res = array();
        }
		if(!count($res)) return array();
        
		$customIds = array();
		foreach($res as $v){
			if(!isset($customIds[$v["item_id"]])) $customIds[$v["item_id"]] = array();
			$customIds[$v["item_id"]][] = $v["custom_id"];
		}

        return $customIds;
    }
}