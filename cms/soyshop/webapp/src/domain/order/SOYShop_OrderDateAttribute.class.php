<?php

/**
 * @table soyshop_order_date_attribute
 */
class SOYShop_OrderDateAttribute {

	const CUSTOMFIELD_TYPE_DATE = "date";		//日付
	const CUSTOMFIELD_TYPE_PERIOD = "period";	//期間

	//フォームの設置箇所
	const DISPLAY_ALL = 0;
	const DISPLAY_ADMIN_ONLY = 1;

	public static function getTableName(){
		return "soyshop_order_date_attribute";
	}

	/**
	 * @column order_id
	 */
	private $orderId;

	/**
	 * @column order_field_id
	 */
	private $fieldId;

	/**
	 * @column order_value_1
	 */
	private $value1;

	/**
	 * @column order_value_2
	 */
	private $value2;

	function getOrderId() {
		return $this->orderId;
	}
	function setOrderId($orderId) {
		$this->orderId = $orderId;
	}
	function getFieldId() {
		return $this->fieldId;
	}
	function setFieldId($fieldId) {
		$this->fieldId = $fieldId;
	}
	function getValue1() {
		return $this->value1;
	}
	function setValue1($value1) {
		$this->value1 = $value1;
	}
	function getValue2(){
		return $this->value2;
	}
	function setValue2($value2){
		$this->value2 = $value2;
	}
}

class SOYShop_OrderDateAttributeConfig{

	const DATASETS_KEY = "config.order.date.attributes";
	const DATASETS_INDEX = "config.order.date.indexed_attributes";

	public static function save($array){
		$array = array_values($array);

		$list = array();
		foreach($array as $key => $config){
			if(strlen($config->getFieldId()) < 1){
				$config->setFieldId("customfield_" . $key);
			}

			$list[$config->getFieldId()] = $config;
		}

		$array = array_values($list);
		SOYShop_DataSets::put(self::DATASETS_KEY, $array);
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
		$dao = SOY2DAOFactory::create("order.SOYShop_OrderDAO");

		//drop
		$drop = array_diff($old, $new);
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
			"date" => "日付",
			"period" => "期間"
		);
	}

	private $fieldId;
	private $label;
	private $type;

	private $attributeName;
	private $attributeDescription;
	private $attributeYearStart;
	private $attributeYearEnd;

	private $defaultValue;
	private $emptyValue;
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

	function getAttributeName(){
		return (isset($this->config["attributeName"])) ? $this->config["attributeName"] : null;
	}
	function setAttributeName($attributeName){
		$this->attributeName = $attributeName;
	}
	function getAttributeDescription(){
		return $this->config["attributeDescription"];
	}
	function setAttributeDescription($attributeDescription){
		$this->attributeDescription = $attributeDescription;
	}
	function getAttributeYearStart(){
		return $this->config["attributeYearStart"];
	}
	function setAttributeYearStart($attributeYearStart){
		$this->attributeYearStart = $attributeYearStart;
	}
	function getAttributeYearEnd(){
		return $this->config["attributeYearEnd"];
	}
	function setAttributeYearEnd($attributeYearEnd){
		$this->attributeYearEnd = $attributeYearEnd;
	}
	function getFormName(){
		return 'customfield_module[' . $this->getFieldId() . ']';
	}
	function getFormId(){
		return 'custom_field_' . $this->getFieldId();
	}

	function getForm($values){

		$h_formName = htmlspecialchars($this->getFormName(), ENT_QUOTES, "UTF-8");
		$h_formID = htmlspecialchars($this->getFormId(), ENT_QUOTES, "UTF-8");

		switch($this->getType()){
			case "date":
				$date = $values["date"];

				$body = '<select'
					   .' id="' . $h_formID . '"'
					   .' name="' . $h_formName . '[date][year]"'
					   .'>' . "\n"
					   .$this->getYearForm($date["year"]) . "\n"
					   .'</select>年' . "\n"
					   .'<select'
					   .' id="' . $h_formID . '"'
					   .' name="' . $h_formName . '[date][month]"'
					   .'>' . "\n"
					   .$this->getMonthForm($date["month"]) . "\n"
					   .'</select>月' . "\n"
					   .'<select'
					   .' id="' . $h_formID . '"'
					   .' name="' . $h_formName . '[date][day]"'
					   .'>' . "\n"
					   .$this->getDayForm($date["day"]) . "\n"
					   .'</select>日' . "\n";

				break;

			case "period":
				$start = $values["start"];
				$end = $values["end"];

				$body = '<select'
					   .' id="' . $h_formID . '"'
					   .' name="' . $h_formName . '[start][year]"'
					   .'>' . "\n"
					   .$this->getYearForm($start["year"]) . "\n"
					   .'</select>年' . "\n"
					   .'<select'
					   .' id="' . $h_formID . '"'
					   .' name="' . $h_formName . '[start][month]"'
					   .'>' . "\n"
					   .$this->getMonthForm($start["month"]) . "\n"
					   .'</select>月' . "\n"
					   .'<select'
					   .' id="' . $h_formID . '"'
					   .' name="' . $h_formName . '[start][day]"'
					   .'>' . "\n"
					   .$this->getDayForm($start["day"]) . "\n"
					   .'</select>日' . "\n"
					   .'～'
					   .'<select'
					   .' id="' . $h_formID . '"'
					   .' name="' . $h_formName . '[end][year]"'
					   .'>' . "\n"
					   .$this->getYearForm($end["year"]) . "\n"
					   .'</select>年' . "\n"
					   .'<select'
					   .' id="' . $h_formID . '"'
					   .' name="' . $h_formName . '[end][month]"'
					   .'>' . "\n"
					   .$this->getMonthForm($end["month"]) . "\n"
					   .'</select>月' . "\n"
					   .'<select'
					   .' id="' . $h_formID . '"'
					   .' name="' . $h_formName . '[end][day]"'
					   .'>' . "\n"
					   .$this->getDayForm($end["day"]) . "\n"
					   .'</select>日' . "\n";
				break;
			default:
				$checkbox_value = "";
				$h_checkbox_value = "";
				$body = "none";
				break;
		}

		$return = $body . "\n";

		return $return;
	}

	function getYearForm($value){

		if(!is_null($this->getAttributeYearStart()) && strlen($this->getAttributeYearStart()) > 0){
			$start = $this->getAttributeYearStart();
		}else{
			$start = date("Y", time());
		}

		if(!is_null($this->getAttributeYearEnd()) && strlen($this->getAttributeYearEnd()) > 0){
			$end = $this->getAttributeYearEnd() + 1;
		}else{
			$end = $start + 5;
		}

		$count = $end - $start;

		$value = (isset($value)) ? $value : date("Y", time());

		$html = array();
		for($i=0; $i < $count; $i++){
			$year = $start + $i;
			if($year == $value){
				$html[] = '<option value="' . $year . '" selected="selected">' . $year . '</option>';
			}else{
				$html[] = '<option value="' . $year.'">' . $year . '</option>';
			}
		}

		return implode("\n", $html);
	}

	function getMonthForm($value){

		$value = (isset($value)) ? $value : date("n", time());

		$html = array();
		for($i=1; $i < 13; $i++){
			if($i == $value){
				$html[] = '<option value="' . $i . '" selected="selected">' . $i . '</option>';
			}else{
				$html[] = '<option value="' . $i . '">' . $i . '</option>';
			}
		}

		return implode("\n", $html);
	}

	function getDayForm($value){

		$value = (isset($value)) ? $value : date("j", time());

		$html = array();
		for($i=1; $i < 32; $i++){
			if($i == $value){
				$html[] = '<option value="' . $i . '" selected="selected">' . $i . '</option>';
			}else{
				$html[] = '<option value="' . $i . '">' . $i . '</option>';
			}
		}

		return implode("\n", $html);
	}

	function getIsAdminOnly(){
		return (isset($this->config["isAdminOnly"])) ? $this->config["isAdminOnly"] : 0;
	}
	function setIsAdminOnly($isAdminOnly){
		$this->config["isAdminOnly"] = $isAdminOnly;
	}
}
