<?php
/**
 * @table soyboard_group_attribute
 */
class SOYBoard_GroupAttribute {

	/**
	 * @column group_id
	 */
	private $groupId;

	/**
	 * @column group_field_id
	 */
	private $fieldId;

	/**
	 * @column group_value
	 */
	private $value;

	/**
	 * 画像<img>でのみ使われる追加の属性に設定された値
	 * @column group_extra_values
	 */
	private $extraValues;

	function getGroupId() {
		return $this->groupId;
	}
	function setGroupId($groupId) {
		$this->groupId = $groupId;
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

	function getExtraValues() {
		return $this->extraValues;
	}
	function setExtraValues($extraValues) {
		$this->extraValues = $extraValues;
	}

	function getExtraValuesArray() {
		$res = soy2_unserialize($this->extraValues);
		if(is_array($res)){
			return $res;
		}else{
			return array();
		}
	}
	function setExtraValuesArray($extraValues) {
		if(is_array($extraValues)){
			$this->extraValues = soy2_serialize($extraValues);
		}else{
			$this->extraValues = soy2_serialize(array());
		}
	}
}
