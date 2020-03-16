<?php
class CustomField{

	public static $TYPES = array(
		"input" => "一行テキスト",
		"textarea" => "複数行テキスト",
		"checkbox" => "チェックボックス",
		"radio" => "ラジオボタン",
		"select" => "セレクトボックス",
		"image" => "画像",
		"file" => "ファイル",
		"richtext" => "リッチテキスト",
		"link" => "リンク"
	);

	private $id;
	private $label;
	private $type;
	private $value;

	/* 以下、高度な設定 */

	//radio,selectのオプション(string)
	private $option;

	//入力欄の表示/非表示
	private $showInput = true;

	//ラベルIDとの関連付け
	private $labelId;

	//どの属性値に出力するかの設定
	private $output;

	//フィールドの説明文
	private $description;

	//初期値設定
	private $defaultValue;

	//空の時の表示しない
	private $hideIfEmpty = 0;

	//空の時の値
	private $emptyValue;

	//追加属性値
	private $extraOutputs;

	//追加属性値の値
	private $extraValues;

	function __construct($array = array()){
		$obj = (object)$array;
		SOY2::cast($this,$obj);
	}

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getLabel() {
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}
	function getValue() {
		return $this->value;
	}
	function setValue($value) {
		$this->value = $value;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getOption() {
		return $this->option;
	}
	function setOption($option) {
		$this->option = $option;
	}
	function getLabelId() {
		return $this->labelId;
	}
	function setLabelId($labelId) {
		$this->labelId = $labelId;
	}
	function getOutput() {
		return $this->output;
	}
	function setOutput($output) {
		$this->output = $output;
	}
	function getDescription(){
		return $this->description;
	}
	function setDescription($description){
		$this->description = $description;
	}
	function getFormName(){
		return 'custom_field['.$this->getId().']';
	}
	function getFormId(){
		return 'custom_field_'.$this->getId();
	}
	function getExtraFormName($extraOutput) {
		return "custom_field_extra[" . $this->getId() . "][" . $extraOutput . "]";
	}
	function getExtraFormId($extraOutput) {
		return "custom_field_" .$this->getId() . "_extra_" . $extraOutput;
	}

	function hasOption(){
		return (boolean)($this->getType() == "radio" OR $this->getType() == "select");
	}

	function hasExtra(){
		return (boolean)($this->getType() == "image");
	}

	/**
	 * 1.2.0でcheckboxを追加
	 */
	function getForm($pluginObj, $fieldValue, $extraValues=null){

		//表示しないとき
		if(!$this->showInput){
			return "";
		}

		$h_formName = htmlspecialchars($this->getFormName(),ENT_QUOTES,"UTF-8");
		$h_formID = htmlspecialchars($this->getFormId(),ENT_QUOTES,"UTF-8");

		$title = '<label for="'.$h_formID.'">'
		         .( ($pluginObj->displayTitle) ? 'カスタムフィールド：' : '' )
		         .htmlspecialchars($this->getLabel(),ENT_QUOTES,"UTF-8")
		         .( ($pluginObj->displayID) ? ' ('.htmlspecialchars($this->getId(),ENT_QUOTES,"UTF-8").')' : '' )
		         .'</label>'
		         .'';
		$title .= (strlen($this->getDescription())) ? '<br /><span>' . $this->getDescription() . '</span>' : "";

		switch($this->getType()){
			case "checkbox":
				//DefaultValueがあればそれを使う
				$checkbox_value = (strlen($this->getDefaultValue()) >0) ? $this->getDefaultValue() : $this->getLabel() ;
				$h_checkbox_value = htmlspecialchars($checkbox_value,ENT_QUOTES,"UTF-8");
				$body = '<input type="checkbox" class="custom_field_checkbox"'
				       .' id="'.$h_formID.'"'
				       .' name="'.$h_formName.'"'
				       .' value="'.$h_checkbox_value.'"'
				       .( ($fieldValue == $checkbox_value) ? ' checked="checked"' : ""  )
				       .' />';

				break;
			case "radio":
				$options = explode("\n",str_replace(array("\r\n","\r"),"\n",$this->option));
				$value = (is_null($fieldValue)) ? $this->getDefaultValue() : $fieldValue ;

				$body = "";
				foreach($options as $key => $option){
					$option = trim($option);
					if(strlen($option)>0){
						$h_option = htmlspecialchars($option,ENT_QUOTES,"UTF-8");
						$id = 'custom_field_radio_'.$this->getId().'_'.$key;

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
				$options = explode("\n",str_replace(array("\r\n","\r"),"\n",$this->option));
				$value = (is_null($fieldValue)) ? $this->getDefaultValue() : $fieldValue ;

				$body = '<select class="cstom_field_select" name="'.$h_formName.'" id="'.$h_formID.'">';
				$body .= '<option value="">----</option>';
				foreach($options as $option){
					$option = trim($option);
					if(strlen($option)>0){
						$h_option = htmlspecialchars($option,ENT_QUOTES,"UTF-8");
						$body .= '<option value="'.$h_option.'" ' .
								 (($option == $value) ? 'selected="selected"' : "") .
								 '>' . $h_option . '</option>' . "\n";
					}
				}
				$body .= '</select>';

				break;
			case "textarea":
				$h_value = htmlspecialchars($fieldValue,ENT_QUOTES,"UTF-8");
				$body = '<textarea class="custom_field_textarea" style="width:100%;"'
				        .' id="'.$h_formID.'"'
				        .' name="'.$h_formName.'"'
				        .'>'
						.$h_value.'</textarea>';
				break;
			case "richtext":
				$h_value = htmlspecialchars($fieldValue,ENT_QUOTES,"UTF-8");
				$body = '<textarea class="custom_field_textarea mceEditor" style="width:100%;"'
				        .' id="'.$h_formID.'"'
				        .' name="'.$h_formName.'"'
				        .'>'
						.$h_value.'</textarea>';
				break;
			case "image":
			case "file":
				$h_value = htmlspecialchars($fieldValue,ENT_QUOTES,"UTF-8");
				$body = '<input type="text" class="custom_field_input" style="width:50%"'
				       .' id="'.$h_formID.'"'
				       .' name="'.$h_formName.'"'
				       .' value="'.$h_value.'"'
				       .' />'
				       .'<button type="button" onclick="open_customfield_filemanager($(\'#'.$h_formID.'\'));" style="margin-right:10px;">ファイルを指定する</button>';

				if($h_value){
					if($this->getType() == "image"){
						$body .= '<a href="#" onclick="return preview_customfield($(\'#'.$h_formID.'\'));">Preview</a>';
					}
					if($this->getType() == "file"){
						$body .= '<a href="'.$h_value.'">'.basename($h_value).'</a>';
					}
				}

				$extraOutputs = explode("\n", str_replace(array("\r\n", "\r"), "\n", $this->extraOutputs));

				foreach($extraOutputs as $key => $extraOutput){
					$extraOutput = trim($extraOutput);
					if(strlen($extraOutput) > 0){
						$h_extraformName = htmlspecialchars($this->getExtraFormName($extraOutput), ENT_QUOTES, "UTF-8");
						$h_extraformID = htmlspecialchars($this->getExtraFormId($extraOutput), ENT_QUOTES, "UTF-8");
						$h_extraOutput = htmlspecialchars($extraOutput, ENT_QUOTES, "UTF-8");
						$extraValue = is_array($extraValues) && isset($extraValues[$h_extraOutput]) ? $extraValues[$h_extraOutput] : "";
						$h_extraValue = htmlspecialchars($extraValue, ENT_QUOTES, "UTF-8");

						$body .= '<br />' . $h_extraOutput . '&nbsp;<input type="text" class="custom_field_input" style="width:50%"' .
							' id="'.$h_extraformID.'"'.
							' name="'.$h_extraformName.'"' .
							' value="'.$h_extraValue.'"' .
							' />';
					}
				}

				break;
			case "link":
				$h_value = htmlspecialchars($fieldValue,ENT_QUOTES,"UTF-8");
				$body = '<input type="text" class="custom_field_input" style="width:70%"'
				       .' id="'.$h_formID.'"'
				       .' name="'.$h_formName.'"'
				       .' value="'.$h_value.'"'
				       .' />';
				if(strlen($h_value)){
					$body .= "&nbsp;<a href=\"" . $h_value . "\" target=\"_blank\">確認</a>";
				}
				break;
			case "input":
			default:
				$h_value = htmlspecialchars($fieldValue,ENT_QUOTES,"UTF-8");
				$body = '<input type="text" class="custom_field_input" style="width:100%"'
				       .' id="'.$h_formID.'"'
				       .' name="'.$h_formName.'"'
				       .' value="'.$h_value.'"'
				       .' />';
				break;
		}

		switch($this->type){
			case "checkbox":
				$return = '<p class="sub">'
				       .$title
				       .$body
				       .'</p>';
				break;
			case "textarea":
			case "input":
			default:
				$return = '<p class="sub">'
				       .$title
				       .'</p>'
				       .'<div style="margin:-0.5ex 0px 0.5ex 1em;">'.$body.'</div>';
				break;
		}

		if($this->labelId){
			return '<div class="toggled_by_label_'.$this->labelId.'" style="display:none;">' . $return . '</div>' . "\n";
		}else{
			return '<div class="toggled_by_label_'.$this->labelId.'">' . $return . '</div>' . "\n";
		}

	}

	function getDefaultValue() {
		return $this->defaultValue;
	}
	function setDefaultValue($defaultValue) {
		$this->defaultValue = $defaultValue;
	}
	function getEmptyValue() {
		return $this->emptyValue;
	}
	function setEmptyValue($emptyValue) {
		$this->emptyValue = $emptyValue;
	}
	function getHideIfEmpty() {
		return $this->hideIfEmpty;
	}
	function setHideIfEmpty($hideIfEmpty) {
		$this->hideIfEmpty = $hideIfEmpty;
	}
	function getShowInput() {
		return $this->showInput;
	}
	function setShowInput($showInput) {
		$this->showInput = $showInput;
	}

	function getExtraOutputs() {
		return $this->extraOutputs;
	}
	function setExtraOutputs($extraOutputs) {
		$this->extraOutputs = $extraOutputs;
	}
	function getExtraValues() {
		return $this->extraValues;
	}
	function setExtraValues($extraValues) {
		$this->extraValues = $extraValues;
	}
}
