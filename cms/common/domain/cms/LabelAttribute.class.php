<?php
/**
 * @table LabelAttribute
 */
class LabelAttribute {

	/**
	 * @column label_id
	 */
	private $labelId;

	/**
	 * @column label_field_id
	 */
	private $fieldId;

	/**
	 * @column label_value
	 */
	private $value;

	/**
	 * soy2_serialize対象
	 * @column label_extra_values
	 */
	private $extraValues;

	/**
	 * @no_persistent
	 */
	private $customFieldObject;


	function getLabelId() {
		return $this->labelId;
	}
	function setLabelId($labelId) {
		$this->labelId = $labelId;
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
		$res = soy2_unserialize($this->extraValues);
		if(is_array($res)){
			return $res;
		}else{
			return array();
		}
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
		$res = soy2_unserialize($this->customFieldObject);
		if($res instanceof CustomField){
			return $res;
		}else{
			return new CustomField();
		}
	}
	public function setCustomFieldObjectArray($customFieldObject) {
		if($customFieldObject instanceof CustomField){
			$this->customFieldObject = soy2_serialize($customFieldObject);
		}else{
			$this->customFieldObject = soy2_serialize(new CustomField());
		}
	}

}
