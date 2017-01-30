<?php
/**
 * @table soyshop_user_attribute
 */
class SOYShop_UserAttribute {

	public static function getTableName(){
		return "soyshop_user_attribute";
	}
	
	const CUSTOMFIELD_TYPE_INPUT = "input";			//一行テキスト
	const CUSTOMFIELD_TYPE_TEXTAREA = "textarea";	//複数行テキスト
	const CUSTOMFIELD_TYPE_CHECKBOX = "checkbox";	//チェックボックス
	const CUSTOMFIELD_TYPE_RADIO = "radio";			//ラジオ
	const CUSTOMFIELD_TYPE_SELECT = "select";		//セレクトボックス
	
	//必須項目
	const IS_REQUIRED = 1;
	const NO_REQUIRED = 0;

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column user_field_id
	 */
	private $fieldId;

	/**
	 * @column user_value
	 */
	private $value;

	function getUserId() {
		return $this->userId;
	}
	function setUserId($userId) {
		$this->userId = $userId;
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
}

class SOYShop_UserAttributeConfig{

	const DATASETS_KEY = "config.user.attributes";
	const DATASETS_INDEX = "config.user.indexed_attributes";

	public static function save($array){
		$array = array_values($array);

		$list = array();
		$indexed = array();
		foreach($array as $key => $config){
			if(strlen($config->getFieldId()) < 1){
				$config->setFieldId("customfield_" . $key);
			}

			$list[$config->getFieldId()] = $config;
		}

		$array = array_values($list);
		SOYShop_DataSets::put(self::DATASETS_KEY,$array);
	}

	/**
	 * @return array
	 * @param boolean is map
	 */
	public static function load($flag = false){
		$array = SOYShop_DataSets::get(self::DATASETS_KEY, array());

		if(!$flag)return $array;

		$map = array();
		foreach($array as $config){
			$map[$config->getFieldId()] = $config;
		}

		return $map;
	}

	/**
	 * index
	 */
	public static function getIndexFields(){
		$array = SOYShop_DataSets::get(self::DATASETS_INDEX, array());
		return $array;
	}

	/**
	 * update ndex field
	 */
	private static function updateIndexFields($new, $old){
		$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

		//drop
		$drop = array_diff($old,$new);
		foreach($drop as $name){
			try{
				$dao->dropSortColumn($name);
			}catch(Exception $e){
				//
			}
		}

		//create
		$create = array_diff($new, $old);

		foreach($create as $name){
			try{
				$dao->createSortColumn($name);
			}catch(Exception $e){
				//
			}
		}

		$new = array_values($new);
		SOYShop_DataSets::put(self::DATASETS_INDEX, $new);

	}

	public static function getTypes(){

		return array(
			"input" => "一行テキスト",
			"textarea" => "複数行テキスト",
			"checkbox" => "チェックボックス",
			"radio" => "ラジオボタン",
			"select" => "セレクトボックス",
		);
	}

	private $fieldId;
	private $label;
	private $type;
	
	private $attributeDescription;
	private $attributeOther;
	private $attributeOtherText;
	
	private $defaultValue;
	private $emptyValue;
	
	//必須項目であるか
	private $isRequired;
	private $config;

	function getFieldId() {
		return $this->fieldId;
	}
	function setFieldId($fieldId) {
		$this->fieldId = $fieldId;
	}
	function getLabel() {
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getConfig() {
		return $this->config;
	}
	function setConfig($config) {
		$this->config = $config;
	}

	/* config method */
	function getAttributeDescription(){
		return @$this->config["attributeDescription"];
	}
	function setAttributeDescription($attributeDescription){
		$this->attributeDescription = $attributeDescription;
	}
	function getAttributeOther(){
		return @$this->config["attributeOther"];
	}
	function setAttributeOther($attributeOther){
		$this->attributeOther = $attributeOther;
	}
	function getAttributeOtherText(){
		return @$this->config["attributeOtherText"];
	}
	function setAttributeOtherText($attributeOtherText){
		$this->attributeOtherText = $attributeOtherText;
	}
	function getOption() {
		return (isset($this->config["option"])) ? $this->config["option"] : "";
	}
	function setOption($option) {
		$this->config["option"] = $option;
	}
	function hasOption(){
		return (boolean)($this->getType() == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO || $this->getType() == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_CHECKBOX || $this->getType() == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_SELECT);
	}
	function hasRadioOption(){
		return (boolean)($this->getType() == SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO);
	}

	function getFormName(){
		return 'user_customfield['.$this->getFieldId().']';
	}
	function getFormId(){
		return 'user_customfield_'.$this->getFieldId();
	}
	function isIndex(){
		return (boolean)$this->config["isIndex"];
	}

	function getForm($value){
		
		//おまじない
		$readOnly = false;

		$h_formName = htmlspecialchars($this->getFormName(), ENT_QUOTES, "UTF-8");
		$h_formID = htmlspecialchars($this->getFormId(), ENT_QUOTES, "UTF-8");

		switch($this->getType()){
			case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_CHECKBOX:
				$checkbox_value = (!defined("SOYSHOP_ADMIN_PAGE") && is_null($value) && strlen($this->getDefaultValue()) > 0) ? (explode(",", $this->getDefaultValue())) : explode(",", $value);
				$options = explode("\n", str_replace(array("\r\n", "\r"), "\n", $this->getOption()));
				if(count($options) && strlen($options[0])){
					$body = "";
					foreach($options as $key => $option){
						$body .= '<input type="checkbox" class="custom_field_checkbox"'
						       .' id="' . $h_formID . '_' . $key . '"'
						       .' name="' . $h_formName . '[]"'
						       .' value="' . htmlspecialchars($option, ENT_QUOTES, "UTF-8") . '"'
						       .( (in_array($option, $checkbox_value)) ? ' checked="checked"' : ""  )
						       .' />';
						$body .= '<label for="' . $h_formID . '_' . $key . '">' . $option . '</label>';
						$body .= "\n";
					}
				}
				break;
			case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_RADIO:
				$options = explode("\n",str_replace(array("\r\n","\r"),"\n",$this->getOption()));
				if(!defined("SOYSHOP_ADMIN_PAGE") && is_null($value)){
					/**
					 * radioの場合、valueは配列で["value"]と["other"]の値がある
					 */
					$value["value"] = (strlen($this->getDefaultValue())) ? $this->getDefaultValue() : null;
					$value["other"] = null;
				}
				
				$body = "";
				foreach($options as $key => $option){
					$option = trim($option);
					if(strlen($option) > 0){
						$h_option = htmlspecialchars($option, ENT_QUOTES, "UTF-8");
						$id = 'user_customfield_radio_' . $this->getFieldId() . '_' . $key;

						$body .= '<input type="radio" class="custom_field_radio"' .
								 ' name="' . $h_formName . '"' .
								 ' id="' . $id . '"'.
								 ' value="' . $h_option . '"' .
								 (($option == $value["value"]) ? ' checked="checked"' : "") .
								 ' />';
						$body .= '<label for="' . $id . '">' . $h_option . '</label>';
						$body .= "\n";
					}
				}
				break;
				
			case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_SELECT:
				$options = explode("\n", str_replace(array("\r\n", "\r"), "\n", $this->getOption()));
				$value = (!defined("SOYSHOP_ADMIN_PAGE") && is_null($value) && strlen($this->getDefaultValue()) > 0) ? $this->getDefaultValue() : $value ;

				$body = '<select class="custom_field_select" name="' . $h_formName . '" id="' . $h_formID . '">';
				$body .= '<option value="">----</option>';
				foreach($options as $option){
					$option = trim($option);
					if(strlen($option) > 0){
						$h_option = htmlspecialchars($option, ENT_QUOTES, "UTF-8");
						$body .= '<option value="' . $h_option . '" ' .
								 (($option == $value) ? 'selected="selected"' : "") .
								 '>' . $h_option . '</option>' . "\n";
					}
				}
				$body .= '</select>';

				break;
			case SOYShop_UserAttribute::CUSTOMFIELD_TYPE_TEXTAREA:
				$value = (!defined("SOYSHOP_ADMIN_PAGE") && is_null($value) && strlen($this->getDefaultValue())) ? $this->getDefaultValue() : $value;
				$h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
				$body = '<textarea class="custom_field_textarea" style="width:100%;"'
				        .' id="' . $h_formID . '"'
				        .' name="' . $h_formName . '"';
				if($readOnly){
					$body .= ' readonly="readonly"';
				}
		        $body .= '>'
						.$h_value . '</textarea>';
				break;
			default:
				$value = (!defined("SOYSHOP_ADMIN_PAGE") && is_null($value) && strlen($this->getDefaultValue())) ? $this->getDefaultValue() : $value;
				$h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
				$body = '<input type="text" class="custom_field_input" style="width:100%"'
				       .' id="' . $h_formID . '"'
				       .' name="' . $h_formName . '"'
				       .' value="' . $h_value . '"';
				if($readOnly){
					$body .= ' readonly="readonly"';
				}
				$body .= ' />';
				break;
		}

		return $body;
	}
	
	function getDefaultValue() {
		return $this->config["defaultValue"];
	}
	function setDefaultValue($defaultValue) {
		$this->config["defaultValue"] = $defaultValue;
	}
	function getEmptyValue() {
		return $this->config["emptyValue"];
	}
	function setEmptyValue($emptyValue) {
		$this->config["emptyValue"] = $emptyValue;
	}
	function getIsRequired(){
		return (isset($this->config["isRequired"])) ? $this->config["isRequired"] : 0;
	}
	function setIsRequired($isRequired){
		$this->config["isRequired"] = $isRequired;
	}
}
?>