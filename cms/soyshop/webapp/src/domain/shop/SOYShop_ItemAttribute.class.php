<?php
/**
 * @table soyshop_item_attribute
 */
class SOYShop_ItemAttribute {

	public static function getTableName(){
		return "soyshop_item_attribute";
	}

	/**
	 * @column item_id
	 */
	private $itemId;

	/**
	 * @column item_field_id
	 */
	private $fieldId;

	/**
	 * @column item_value
	 */
	private $value;

	/**
	 * 画像<img>でのみ使われる追加の属性に設定された値
	 * @column item_extra_values
	 */
	private $extraValues;

	function getItemId() {
		return $this->itemId;
	}
	function setItemId($itemId) {
		$this->itemId = $itemId;
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

class SOYShop_ItemAttributeConfig{

	const DATASETS_KEY = "config.item.attributes";
	const DATASETS_INDEX = "config.item.indexed_attributes";

	public static function save($array){
		$array = array_values($array);

		$list = array();
		$indexed = array();
		foreach($array as $key => $config){
			if(strlen($config->getFieldId()) < 1){
				$config->setFieldId("customfield_" . $key);
			}

			if($config->isIndex()){
				$indexed[] = $config->getFieldId();
			}

			$list[$config->getFieldId()] = $config;
		}

		$array = array_values($list);
		SOYShop_DataSets::put(self::DATASETS_KEY,$array);
		$old = self::getIndexFields();

		self::updateIndexFields($indexed,$old);
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
	private static function updateIndexFields($new,$old){
		$dao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");

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
		$create = array_diff($new,$old);

		foreach($create as $name){
			try{
				$dao->createSortColumn($name);
			}catch(Exception $e){
				//
			}
		}

		$new = array_values($new);
		SOYShop_DataSets::put(self::DATASETS_INDEX,$new);

	}

	public static function getTypes(){

		return array(
			"input" => "一行テキスト",
			"textarea" => "複数行テキスト",
			"checkbox" => "チェックボックス",
			"checkboxes" => "チェックボックス(複数)",
			"radio" => "ラジオボタン",
			"select" => "セレクトボックス",
			"image" => "画像",
			"file" => "ファイル",
			"richtext" => "リッチテキスト",
			"link" => "リンク"
		);

	}

	private $fieldId;

	private $label;

	private $type;

	//カテゴリとの関連付け
	private $showInput;

	//初期値
	private $defaultValue;

	//空の時の値
	private $emptyValue;

	//追加の属性（<img>でのみ有効）
	private $extraOutputs;

	//間違って追加されたのでは？
	private $extraValues;

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

	function getOutput() {
		return (isset($this->config["output"])) ? $this->config["output"] : null;
	}
	function setOutput($output) {
		$this->config["output"] = $output;
	}
	function getDescription(){
		return (isset($this->config["description"])) ? $this->config["description"] : null;
	}
	function setDescription($description){
		$this->config["description"] = $description;
	}
	function getShowInput(){
		return (isset($this->config["showInput"])) ? $this->config["showInput"] : null;
	}
	function setShowInput($showInput){
		$this->config["showInput"] = $showInput;
	}
	function getDefaultValue() {
		return (isset($this->config["defaultValue"])) ? $this->config["defaultValue"] : null;
	}
	function setDefaultValue($defaultValue) {
		$this->config["defaultValue"] = $defaultValue;
	}
	function getEmptyValue() {
		return (isset($this->config["emptyValue"])) ? $this->config["emptyValue"] : null;
	}
	function setEmptyValue($emptyValue) {
		$this->config["emptyValue"] = $emptyValue;
	}
	function getHideIfEmpty() {
		return (isset($this->config["hideIfEmpty"])) ? $this->config["hideIfEmpty"] : null;
	}
	function setHideIfEmpty($hideIfEmpty) {
		$this->config["hideIfEmpty"] = $hideIfEmpty;
	}
	function getExtraOutputs() {
		return (isset($this->config["extraOutputs"])) ? $this->config["extraOutputs"] : null;
	}
	function setExtraOutputs($extraOutputs) {
		$this->config["extraOutputs"] = $extraOutputs;
	}
	function getExtraValues() {
		return $this->extraValues;
	}
	function setExtraValues($extraValues) {
		$this->extraValues = $extraValues;
	}
	function getOption() {
		return (isset($this->config["option"])) ? $this->config["option"] : null;
	}
	function setOption($option) {
		$this->config["option"] = $option;
	}
	function hasOption(){
		return (boolean)($this->getType() == "checkboxes" || $this->getType() == "radio" || $this->getType() == "select");
	}
	function hasExtra(){
		return (boolean)($this->getType() == "image");
	}

	function getFormName(){
		return 'custom_field['.$this->getFieldId().']';
	}
	function getFormId(){
		return 'custom_field_'.$this->getFieldId();
	}
	function getExtraFormName($extraOutput) {
		return "custom_field_extra[" . $this->getFieldId() . "][" . $extraOutput . "]";
	}
	function getExtraFormId($extraOutput) {
		return "custom_field_" .$this->getFieldId() . "_extra_" . $extraOutput;
	}
	function isIndex(){
		return (isset($this->config["isIndex"])) ? (boolean)$this->config["isIndex"] : false;
	}

	function getForm($value, $extraValues = null){

		//AUTH_OPERATEがfalseの場合は、在庫以外の項目をreadOnlyにする
		$readOnly = (!AUTH_OPERATE);

		$h_formName = htmlspecialchars($this->getFormName(), ENT_QUOTES, "UTF-8");
		$h_formID = htmlspecialchars($this->getFormId(), ENT_QUOTES, "UTF-8");

		$title = '<label for="">'
		         .''
		         .htmlspecialchars($this->getLabel(), ENT_QUOTES, "UTF-8")
		         //.' ('.htmlspecialchars($this->getFieldId(), ENT_QUOTES, "UTF-8").')'
						 .' (cms:id="' . htmlspecialchars($this->getFieldId(), ENT_QUOTES, "UTF-8") . '")';

		$title .= (strlen($this->getDescription())) ? "<span class=\"option\">(" . $this->getDescription() . ")</span><br>" : "";
		$title .= '</label><br>' . "\n";

		switch($this->getType()){
			case "checkbox":
				//DefaultValueがあればそれを使う
				$checkbox_value = (strlen($this->getDefaultValue()) > 0) ? $this->getDefaultValue() : $this->getLabel() ;
				$h_checkbox_value = htmlspecialchars($checkbox_value, ENT_QUOTES, "UTF-8");
				$body = '<input type="checkbox" class="custom_field_checkbox"'
				       .' id="'.$h_formID.'"'
				       .' name="'.$h_formName.'"'
				       .' value="'.$h_checkbox_value.'"'
				       .( ($value == $checkbox_value) ? ' checked="checked"' : ""  )
				       .' />';

				break;
			case "checkboxes":
				$options = explode("\n",str_replace(array("\r\n","\r"),"\n",$this->getOption()));
				//$value = (is_null($value)) ? $this->getDefaultValue() : $value ;
				if(isset($value) && strlen($value)){
					$values = explode(",", $value);
				}else{
					//カンマ区切りの初期値
					$values = strpos($this->getDefaultValue(), ",") ? array($this->getDefaultValue()) : explode(",", $this->getDefaultValue());
				}

				$body = "";
				foreach($options as $key => $option){
					$option = trim($option);
					if(strlen($option) > 0){
						$h_option = htmlspecialchars($option, ENT_QUOTES, "UTF-8");
						$id = 'custom_field_radio_'.$this->getFieldId().'_'.$key;

						$body .= '<input type="checkbox" class="custom_field_radio"' .
								 ' name="'.$h_formName.'[]"' .
								 ' id="'.$id.'"'.
								 ' value="'.$h_option.'"' .
								 ((!is_bool(array_search($option, $values))) ? ' checked="checked"' : "") .
								 ' />';
						$body .= '<label for="'.$id.'">'.$h_option.'</label>';
					}
				}
				break;
			case "radio":
				$options = explode("\n",str_replace(array("\r\n","\r"),"\n",$this->getOption()));
				$value = (is_null($value)) ? $this->getDefaultValue() : $value ;

				$body = "";
				foreach($options as $key => $option){
					$option = trim($option);
					if(strlen($option) > 0){
						$h_option = htmlspecialchars($option, ENT_QUOTES, "UTF-8");
						$id = 'custom_field_radio_'.$this->getFieldId().'_'.$key;

						$body .= '<input type="radio" class="custom_field_radio"' .
								 ' name="'.$h_formName.'"' .
								 ' id="'.$id.'"'.
								 ' value="'.$h_option.'"' .
								 (($option == $value) ? ' checked="checked"' : "") .
								 ' />';
						$body .= '<label for="'.$id.'">'.$h_option.'</label>';
					}
				}

				break;
			case "select":
				$options = explode("\n",str_replace(array("\r\n","\r"),"\n",$this->getOption()));
				$value = (is_null($value)) ? $this->getDefaultValue() : $value ;

				$body = '<select class="custom_field_select" name="'.$h_formName.'" id="'.$h_formID.'">';
				$body .= '<option value="">----</option>';
				foreach($options as $option){
					$option = trim($option);
					if(strlen($option) > 0){
						$h_option = htmlspecialchars($option, ENT_QUOTES, "UTF-8");
						$body .= '<option value="'.$h_option.'" ' .
								 (($option == $value) ? 'selected="selected"' : "") .
								 '>' . $h_option . '</option>' . "\n";
					}
				}
				$body .= '</select>';

				break;
			case "textarea":
				$value = (is_null($value)) ? $this->getDefaultValue() : $value;
				$h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
				$body = '<textarea class="custom_field_textarea" style="width:100%;"'
				        .' id="'.$h_formID.'"'
				        .' name="'.$h_formName.'"';
				if($readOnly){
					$body .= ' readonly="readonly"';
				}
		        $body .= '>'
						.$h_value.'</textarea>';
				break;
			case "richtext":
				$value = (is_null($value)) ? $this->getDefaultValue() : $value;
				$h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
				$body = '<textarea class="custom_field_textarea mceEditor" style="width:100%;"'
				        .' id="'.$h_formID.'"'
				        .' name="'.$h_formName.'"'
				        .'>'
						.$h_value.'</textarea>';
				break;
			case "file":
				$value = (is_null($value)) ? $this->getDefaultValue() : $value ;
				$h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");

				$html[] = '<div><input type="file" id="'.$h_formID.'_upload"'
				       .' name="'.$h_formName.'"'
				       .' value="" /></div>';
				$html[] = '<p><a class="btn btn-default" href="javascript:void(0);" onclick="return doFileUpload(\''.$h_formID.'_upload\',\''.$h_formID.'\');">Upload</a></p>';

				$html[] = '<p>';
				$html[] = '<input type="text" id="'.$h_formID.'"'
				       .' name="'.$h_formName.'"'
				       .' value="'.$h_value.'" size="50" style="'.(((strlen($h_value) > 0)) ? "" : "display:none;").'" />';
				if(strlen($h_value) > 0){
					$html[] = ' <a href="' . $h_value . '" target="_blank">確認</a>';
					$html[] = ' <a class="btn btn-default" href="javascript:void(0);" onclick="$(\'#'.$h_formID.'\').val(\'\');">Clear</a>';
				}
				$html[] = '</p>';

				$body = implode("",$html);
				break;
			case "image":
				$value = (is_null($value)) ? $this->getDefaultValue() : $value ;
				$h_value = soyshop_convert_file_path_on_admin(htmlspecialchars($value, ENT_QUOTES, "UTF-8"));

				$style = (strlen($h_value) > 0) ? "" : "display:none;";

				$html = array();
				$html[] = '<div class="image_select" id="image_select_wrapper_'.$h_formID.'">';

				//選択ボタン
				$html[] = '<a class="btn btn-default" href="javascript:void(0);" onclick="return ImageSelect.popup(\''.$h_formID.'\');">Select</a>';

				//クリアボタン
				$html[] = '<a class="btn btn-default" href="javascript:void(0);" onclick="return ImageSelect.clear(\''.$h_formID.'\');">Clear</a>';

				//プレビュー画像
				$html[] = '<a id="image_select_preview_link_'.$h_formID.'" href="'.$h_value.'" onclick="return common_click_image_to_layer(this);" target="_blank">';
				$html[] = '<img class="image_select_preview" id="image_select_preview_'.$h_formID.'" src="'.$h_value.'"  style="'.$style.'" />';
				$html[] = '</a>';

				$html[] = '</div>';
				$html[] = '<input type="hidden" id="'.$h_formID.'"'
				       .' name="'.$h_formName.'"'
				       .' value="'.$h_value.'" />';

				$extraOutputs = explode("\n", str_replace(array("\r\n", "\r"), "\n", $this->getExtraOutputs()));

				foreach($extraOutputs as $key => $extraOutput){
					$extraOutput = trim($extraOutput);
					if(strlen($extraOutput) > 0){
						$h_extraformName = htmlspecialchars($this->getExtraFormName($extraOutput), ENT_QUOTES, "UTF-8");
						$h_extraformID = htmlspecialchars($this->getExtraFormId($extraOutput), ENT_QUOTES, "UTF-8");
						$h_extraOutput = htmlspecialchars($extraOutput, ENT_QUOTES, "UTF-8");
						$extraValue = is_array($extraValues) && isset($extraValues[$h_extraOutput]) ? $extraValues[$h_extraOutput] : "";
						$h_extraValue = htmlspecialchars($extraValue, ENT_QUOTES, "UTF-8");

						$html[] = '<br />' . $h_extraOutput . '&nbsp;<input type="text" class="custom_field_input" style="width:50%"' .
							' id="'.$h_extraformID.'"'.
							' name="'.$h_extraformName.'"' .
							' value="'.$h_extraValue.'"' .
							' />';
					}
				}

				$body = implode("",$html);

				break;
			case "link":
				$value = (is_null($value)) ? $this->getDefaultValue() : $value;
				$h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
				$body = '<input type="text" class="custom_field_input" style="width:70%"'
				       .' id="'.$h_formID.'"'
				       .' name="'.$h_formName.'"'
				       .' value="'.$h_value.'"';
				if($readOnly){
					$body .= ' readonly="readonly"';
				}
				$body .= ' />';
				if(strlen($h_value)){
					$body .= "&nbsp;<a href=\"" . $h_value . "\" target=\"_blank\">確認</a>";
				}
				break;
			case "input":
			default:
				$value = (is_null($value)) ? $this->getDefaultValue() : $value;
				$h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
				$body = '<input type="text" class="custom_field_input" style="width:100%"'
				       .' id="'.$h_formID.'"'
				       .' name="'.$h_formName.'"'
				       .' value="'.$h_value.'"';
				if($readOnly){
					$body .= ' readonly="readonly"';
				}
				$body .= ' />';
				break;
		}

		return "<div class=\"form-group\" id=\"" . $h_formID ."_group\">" . $title . $body . "</div>\n";
	}
}
