<?php

class SelectBoxColumn extends SOYInquiry_ColumnBase{

	//項目
	private $items;

	//セレクトの高さ
	private $size;

	//空のオプション
	private $emptyOption = 0;
	private $emptyOptionText = "選択してください";

	//フォームに挿入するクラス
	private $style;	//1.0.1からclassのみ指定は廃止されるが、1.0.0以前から使用しているユーザのために残しておく

	//フォームに自由に挿入する属性
	private $attribute;

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;


    /**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		$items = explode("\n",$this->items);
		$value = $this->getValue();
		if(!is_array($value))$value=array();

		$attributes = $this->getAttributes();
		$required = $this->getRequiredProp();

		foreach($attr as $key => $attr_value){
			$attributes[] = htmlspecialchars($key, ENT_QUOTES) . "=\"".htmlspecialchars($attr_value, ENT_QUOTES)."\"";
		}

		$html = array();
		$html[] = "<select name=\"data[".$this->getColumnId()."][]\" " . implode(" ",$attributes) . "" . $required . ">";
		//先頭に空のオプションを追加する
		if($this->emptyOption == 1){
			$html[] = "<option value=\"\">".$this->emptyOptionText."</option>";
		}
		foreach($items as $key => $item){
			$item = trim($item);
			if(strlen($item)<1)continue;

			$checked = "";

			if($item[0] == "*"){
				$item = substr($item,1);
				if(empty($value)){
					$checked = 'selected="selected"';
				}
			}

			if(in_array($item,$value)){
				$checked = 'selected="selected"';
			}

			$html[] = "<option ".$checked.">".$item."</option>";

		}
		$html[] = "</select>";

		return implode("\n",$html);
	}

	function getAttributes(){
		$attributes = array();
		if($this->size)$attributes[] = "size=\"".$this->size."\"";

		//1.0.0以前のバージョンに対応
		if(is_null($this->attribute) && isset($this->style)){
			$attributes[] = "class=\"".htmlspecialchars($this->style,ENT_QUOTES,"UTF-8")."\"";
		}

		//設定したattributeを挿入
		if(isset($this->attribute) && strlen($this->attribute) > 0){
			$attribute = str_replace("&quot;","\"",$this->attribute);	//"が消えてしまうから、htmlspecialcharsができない
			$attributes[] = trim($attribute);
		}

		return $attributes;
	}

	function getRequiredProp(){
		return (!SOYINQUIRY_FORM_DESIGN_PAGE && $this->requiredProp) ? " required" : "";
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = "項目を1行ずつを設定して下さい：<br>";
		$html.= '<textarea type="text" name="Column[config][items]" style="height:100px;padding:0;">'.htmlspecialchars($this->items).'</textarea>';
		$html.= '<p>初期値として選択される項目がある場合、項目の前に <strong>*</strong> を入力して下さい。</p>';

		$html .= "<p>";
		if($this->emptyOption == 1){
			$html .= '<input type="checkbox" name="Column[config][emptyOption]" value="1" checked="checked" />';
		}else{
			$html .= '<input type="checkbox" name="Column[config][emptyOption]" value="1" />';
		}
		$html .= "先頭に値が空の項目を追加する。<br/>";
		$html .= '文言: <input type="text" name="Column[config][emptyOptionText]" value="'.htmlspecialchars($this->emptyOptionText,ENT_QUOTES,"UTF-8").'" style="width:90%;" />';
		$html .= "</p>";

		if(is_null($this->attribute) && isset($this->style)){
			$attribute = "class=&quot;".htmlspecialchars($this->style,ENT_QUOTES,"UTF-8")."&quot;";
		}else{
			$attribute = trim($this->attribute);
		}

		$html .= '<label for="Column[config][attribute]'.$this->getColumnId().'">属性:</label>';
		$html .= '<input  id="Column[config][attribute]'.$this->getColumnId().'" name="Column[config][attribute]" type="text" value="'.$attribute.'" style="width:90%;" /><br />';
		$html .= "※記述例：class=\"sample\" title=\"サンプル\"<br>";

		$html .= '<label><input type="checkbox" name="Column[config][requiredProp]" value="1"';
		if($this->requiredProp){
			$html .= ' checked';
		}
		$html .= '>required属性を利用する</label>';

		return $html;
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->items = (isset($config["items"])) ? $config["items"] : "*項目１\n項目２\n項目３";
		$this->emptyOption = (isset($config["emptyOption"]) && $config["emptyOption"] == 1) ? 1 : 0;
		$this->emptyOptionText = (isset($config["emptyOptionText"])) ? $config["emptyOptionText"] : null;
		$this->style = (isset($config["style"])) ? $config["style"] : null ;
		$this->attribute = (isset($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : null;
		$this->requiredProp = (isset($config["requiredProp"])) ? $config["requiredProp"] : null;
	}
	function getConfigure(){
		$config = parent::getConfigure();
		$config["items"] = $this->items;
		$config["emptyOption"] = $this->emptyOption;
		$config["emptyOptionText"] = $this->emptyOptionText;
		$config["style"] = $this->style;
		$config["attribute"] = $this->attribute;
		$config["requiredProp"] = $this->requiredProp;
		return $config;
	}

	/**
	 * 確認画面で呼び出す
	 */
	function getView(){
		$value = $this->getValue();
		if(!is_array($value))$value = array();
		return htmlspecialchars(implode(", ",$value), ENT_QUOTES, "UTF-8");
	}

	function validate(){
		$values =  $this->getValue();
		if($this->getIsRequire() && (isset($values[0]) && strlen($values[0]) == 0)){
			$this->setErrorMessage($this->getLabel()."から1つ選んでください。");
			return false;
		}
	}

	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE  => "連携しない",
			SOYMailConverter::SOYMAIL_ATTR1 => "属性A",
			SOYMailConverter::SOYMAIL_ATTR2 => "属性B",
			SOYMailConverter::SOYMAIL_ATTR3 => "属性C",
			SOYMailConverter::SOYMAIL_MEMO  => "備考"
		);
	}

	function factoryConverter() {
		return new CheckConverter();
	}
}
