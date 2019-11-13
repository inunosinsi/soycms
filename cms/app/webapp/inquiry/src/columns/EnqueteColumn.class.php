<?php

class EnqueteColumn extends SOYInquiry_ColumnBase{

	//アンケートの項目に表記する文言
	private $question;

	//項目
	private $items;

	//フォームに挿入するクラス
	private $style;	//1.0.1からclassのみ指定は廃止されるが、1.0.0以前から使用しているユーザのために残しておく

	//フォームに自由に挿入する属性
	private $attribute;

	/**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		$items = explode("\n",$this->items);
		$value = $this->getValue();

		$__attributes = $this->getAttributes();//HTML

		$html = array();
		foreach($items as $key => $item){

			$attributes = $__attributes;

			$item = trim($item);
			if(strlen($item) < 1) continue;

			$checked = "";

			if($item[0] == "*"){
				$item = substr($item, 1);
				if(!$value){
					$checked = 'checked="checked"';
				}
			}

			if($value == $item){
				$checked = 'checked="checked"';
			}

			$html[] = "<input type=\"radio\" id=\"data_".$this->getColumnId() . "_" . $key. "\" name=\"data[".$this->getColumnId()."]\" value=\"".$item."\" " . implode(" ",$attributes). " ". $checked."/>";
			$html[] = "<label for=\"data_".$this->getColumnId() . "_" . $key. "\">".$item."</label><br>";
		}

		return implode("\n",$html);
	}

	function getAttributes(){
		$attributes = array();

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

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = "アンケートの項目に表記する文言：<br>";
		$html .= '<input type="text" name="Column[config][question]" style="width:70%;" value="' . $this->question . '"><br><br>';
		$html .= "項目を1行ずつを設定して下さい：<br>";
		$html .= '<textarea type="text" name="Column[config][items]" style="height:100px;padding:0;">'.$this->items.'</textarea>';
		$html .= '<p>初期値として選択される項目がある場合、項目の前に[*]を入力して下さい。</p>';

		$html .= "<br/>";

		if(is_null($this->attribute) && isset($this->style)){
			$attribute = "class=&quot;".htmlspecialchars($this->style,ENT_QUOTES,"UTF-8")."&quot;";
		}else{
			$attribute = trim($this->attribute);
		}

		$html .= '<label for="Column[config][attribute]'.$this->getColumnId().'">属性:</label>';
		$html .= '<input  id="Column[config][attribute]'.$this->getColumnId().'" name="Column[config][attribute]" type="text" value="'.$attribute.'" style="width:90%;" /><br />';
		$html .= "※記述例：class=\"sample\" title=\"サンプル\"";

		return $html;
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->question = (isset($config["question"])) ? $config["question"] : "";
		$this->items = (isset($config["items"])) ? $config["items"] : "*項目１\n項目２\n項目３";
		$this->style = (isset($config["style"])) ? $config["style"] : null ;
		$this->attribute = (isset($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : null;
	}
	function getConfigure(){
		$config = parent::getConfigure();
		$config["question"] = $this->question;
		$config["items"] = $this->items;
		$config["style"] = $this->style;
		$config["attribute"] = $this->attribute;
		return $config;
	}

	function validate(){
		if($this->getIsRequire() && strlen($this->getValue())<1){
			$this->setErrorMessage($this->getLabel()."から1つ選んでください。");
			return false;
		}
	}

	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE		=> "連携しない",
			SOYMailConverter::SOYMAIL_GENDER	=> "性別",
			SOYMailConverter::SOYMAIL_ATTR1 => "属性A",
			SOYMailConverter::SOYMAIL_ATTR2 => "属性B",
			SOYMailConverter::SOYMAIL_ATTR3 => "属性C",
			SOYMailConverter::SOYMAIL_MEMO  => "備考"
		);
	}

	function factoryConverter() {
		return new RadioConverter();
	}
}
