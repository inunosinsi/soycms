<?php
/**
 * @table soycalendar_custom_item_checked
 */
class SOYCalendar_CustomItem_Checked {

    /**
     * @column item_id
     */
    private $itemId;

    /**
     * @column custom_id
     */
    private $customId;

    function getItemId(){
        return $this->itemId;
    }
    function setItemId($itemId){
        $this->itemId = $itemId;
    }

    function getCustomId(){
        return $this->customId;
    }
    function setCustomId($customId){
        $this->customId = $customId;
    }
}