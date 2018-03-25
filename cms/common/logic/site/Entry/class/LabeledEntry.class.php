<?php
SOY2::import("domain.cms.Entry");

/**
 * @table Entry inner join EntryLabel on(Entry.id = EntryLabel.entry_id)
 */
class LabeledEntry extends Entry{

	const ENTRY_ACTIVE = 1;
	const ENTRY_OUTOFDATE = -1;
	const ENTRY_NOTPUBLIC = -2;
	const ORDER_MAX = 10000000;

	/**
	 * @column label_id
	 */
	private $labelId;

	/**
	 * @column display_order
	 */
	private $displayOrder;

	/**
	 * @no_persistent
	 */
	private $labels;

	/**
	 * @no_persistent
	 */
	private $trackbackCount;

	/**
	 * @no_persistent
	 */
	private $commentCount;

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
		if(((int)$displayOrder) >= LabeledEntry::ORDER_MAX)return;
		$this->displayOrder = $displayOrder;
	}
	function setMaxDisplayOrder(){
		$this->displayOrder = LabeledEntry::ORDER_MAX;
	}
	function getLabels() {
		return $this->labels;
	}
	function setLabels($labels) {
		$this->labels = $labels;
	}

	function getTrackbackCount() {
		return $this->trackbackCount;
	}
	function setTrackbackCount($trackbackCount) {
		$this->trackbackCount = $trackbackCount;
	}
	function getCommentCount() {
		return $this->commentCount;
	}
	function setCommentCount($commentCount) {
		$this->commentCount = $commentCount;
	}
}
