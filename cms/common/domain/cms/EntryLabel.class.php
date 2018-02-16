<?php

/**
 * @table EntryLabel
 */
class EntryLabel {

	const ORDER_MAX = 10000000;

	/**
	 * @column entry_id
	 */
    private $entryId;

    /**
     * @column label_id
     */
    private $labelId;

    /**
     * @column display_order
     */
    private $displayOrder;

    function getEntryId() {
    	return $this->entryId;
    }
    function setEntryId($entryId) {
    	$this->entryId = $entryId;
    }
    function getLabelId() {
    	return $this->labelId;
    }
    function setLabelId($labelId) {
    	$this->labelId = $labelId;
    }
    function getDisplayOrder() {
    	return $this->displayOrder;
    }
    function setDisplayOrder($displayOrder) {
    	if(((int)$displayOrder) >= EntryLabel::ORDER_MAX) return;
    	$this->displayOrder = $displayOrder;
    }
    function setMaxDisplayOrder(){
    	$this->displayOrder = EntryLabel::ORDER_MAX;
    }
}
