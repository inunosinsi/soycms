<?php
/**
 * @table PageAttribute
 */
class PageAttribute {

	/**
	 * @column page_id
	 */
	private $pageId;

	/**
	 * @column page_field_id
	 */
	private $fieldId;

	/**
	 * @column page_value
	 */
	private $value;

	/**
	 * soy2_serialize対象
	 * @column page_extra_values
	 */
	private $extraValues;

	/**
	 * @no_persistent
	 */
	private $customFieldObject;


	function getPageId() {
		return $this->pageId;
	}
	function setPageId($pageId) {
		$this->pageId = $pageId;
	}
	function getFieldId() {
		return $this->fieldId;
	}
	function setFieldId($fieldId) {
		$this->fieldId = $fieldId;
	}
	function getValue() {
		return $this->value;
	}
	function setValue($value) {
		$this->value = $value;
	}

	public function getExtraValues() {
		return $this->extraValues;
	}
	public function setExtraValues($extraValues) {
		$this->extraValues = $extraValues;
	}

	public function getExtraValuesArray() {
		$res = soy2_unserialize((string)$this->extraValues);
		return (is_array($res)) ? $res : array();
	}
	public function setExtraValuesArray($extraValues) {
		if(is_array($extraValues)){
			$this->extraValues = soy2_serialize($extraValues);
		}else{
			$this->extraValues = soy2_serialize(array());
		}
	}

	public function getCustomFieldObject() {
		return $this->customFieldObject;
	}
	public function setCustomFieldObject($customFieldObject) {
		$this->customFieldObject = $customFieldObject;
	}

	public function getCustomFieldObjectArray() {
		$res = soy2_unserialize((string)$this->customFieldObject);
		return ($res instanceof PageCustomField) ? $res : new PageCustomField();
	}
	public function setCustomFieldObjectArray($customFieldObject) {
		if($customFieldObject instanceof CustomField){
			$this->customFieldObject = soy2_serialize($customFieldObject);
		}else{
			$this->customFieldObject = soy2_serialize(new CustomField());
		}
	}

}
