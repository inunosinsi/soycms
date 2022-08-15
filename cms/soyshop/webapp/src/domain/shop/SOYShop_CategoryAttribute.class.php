<?php
/**
 * @table soyshop_category_attribute
 */
class SOYShop_CategoryAttribute {

    public static function getTableName(){
        return "soyshop_category_attribute";
    }

    /**
     * @column category_id
     */
    private $categoryId;

    /**
     * @column category_field_id
     */
    private $fieldId;

    /**
     * @column category_value
     */
    private $value;

    /**
     * @column category_value2
     */
    private $value2;

    function getCategoryId() {
        return (is_numeric($this->categoryId)) ? (int)$this->categoryId : 0;
    }
    function setCategoryId($categoryId) {
        $this->categoryId = $categoryId;
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
    function getValue2(){
        return $this->value2;
    }
    function setValue2($value2){
        $this->value2 = $value2;
    }
}

class SOYShop_CategoryAttributeConfig{

    const DATASETS_KEY = "config.category.attributes";
    const DATASETS_INDEX = "config.category.indexed_attributes";

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
    public static function load(bool $flag=false){
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
        $dao = SOY2DAOFactory::create("shop.SOYShop_CategoryDAO");

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
//            "file" => "ファイル",
            "richtext" => "リッチテキスト",
            "link" => "リンク"
        );

    }

    private $fieldId;

    private $label;

    private $type;

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

    function getOutput() {
        return (isset($this->config["output"])) ? (string)$this->config["output"] : "";
    }
    function setOutput($output) {
        $this->config["output"] = $output;
    }
    function getDescription(){
        return (isset($this->config["description"])) ? (string)$this->config["description"] : "";
    }
    function setDescription($description){
        $this->config["description"] = $description;
    }
    function getDefaultValue() {
        return (isset($this->config["defaultValue"])) ? (string)$this->config["defaultValue"] : "";
    }
    function setDefaultValue($defaultValue) {
        $this->config["defaultValue"] = $defaultValue;
    }
    function getEmptyValue() {
        return (isset($this->config["emptyValue"])) ? (string)$this->config["emptyValue"] : "";
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
    function getOption() {
        return (isset($this->config["option"])) ? (string)$this->config["option"] : "";
    }
    function setOption($option) {
        $this->config["option"] = $option;
    }
    function hasOption(){
        return (boolean)($this->getType() == "checkboxes" || $this->getType() == "radio" || $this->getType() == "select");
    }

    function getFormName(){
        return 'custom_field['.$this->getFieldId().']';
    }
    function getFormId(){
        return 'custom_field_'.$this->getFieldId();
    }
    function isIndex(){
        return (isset($this->config["isIndex"])) ? (boolean)$this->config["isIndex"] : false;
    }

    function getForm(string $value="", string $value2=""){

        $h_formName = htmlspecialchars($this->getFormName(), ENT_QUOTES, "UTF-8");
        $h_formNameOption = str_replace("]","_option]",$h_formName);
        $h_formID = htmlspecialchars($this->getFormId(), ENT_QUOTES, "UTF-8");

        $title = '<label for="">'
                 .''
                 .htmlspecialchars($this->getLabel(), ENT_QUOTES, "UTF-8");
        $title .= (is_string($this->getDescription()) && strlen($this->getDescription())) ? "<span class=\"option\">(" . $this->getDescription() . ")</span>" : "";
        $title .= '</label><br>';

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
				if(is_string($value) && strlen($value)){
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

                $body = '<select class="cstom_field_select" name="'.$h_formName.'" id="'.$h_formID.'">';
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
                $h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
                $body = '<textarea class="custom_field_textarea" style="width:100%;"'
                        .' id="'.$h_formID.'"'
                        .' name="'.$h_formName.'"'
                        .'>'
                        .$h_value.'</textarea>';
                break;
            case "richtext":
                $h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
                $body = '<textarea class="custom_field_textarea mceEditor" style="width:100%;"'
                        .' id="'.$h_formID.'"'
                        .' name="'.$h_formName.'"'
                        .'>'
                        .$h_value.'</textarea>';
                break;
            case "image":
                $h_value = (is_string($value)) ? soyshop_convert_file_path_on_admin(htmlspecialchars($value, ENT_QUOTES, "UTF-8")) : "";
                $h_value2 = (is_string($value2)) ? htmlspecialchars($value2, ENT_QUOTES, "UTF-8") : "";

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

                $html[] = '<div>';
                $html[] = '<h4>alt</h4>';
                $html[] = '<input type="text" name="'.$h_formNameOption.'" value="'.$h_value2.'" />';
                $html[] = '</div>';

                $body = implode("",$html);

                break;
            case "link":
                $h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
                $body = '<input type="text" class="custom_field_input" style="width:70%"'
                       .' id="'.$h_formID.'"'
                       .' name="'.$h_formName.'"'
                       .' value="'.$h_value.'"'
                       .' />';
                if(strlen($h_value)){
                    $body .= "&nbsp;<a href=\"" . $h_value . "\" target=\"_blank\">確認</a>";
                }
                break;
            case "file":
            case "input":
            default:
                if(!is_string($value)) $value = "";
                $h_value = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
                $body = '<input type="text" class="custom_field_input" style="width:100%"'
                       .' id="'.$h_formID.'"'
                       .' name="'.$h_formName.'"'
                       .' value="'.$h_value.'"'
                       .' />';
                break;
        }

        return "<div class=\"form-group\">" . $title .  $body . "</div>\n";
    }
}
