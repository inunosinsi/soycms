<?php

class EnqueteFreeColumn extends SOYInquiry_ColumnBase{

	private $question;
    private $cols;
    private $rows;

	//フォームに挿入するクラス
	private $style;	//1.0.1からclassのみ指定は廃止されるが、1.0.0以前から使用しているユーザのために残しておく

	//フォームに自由に挿入する属性
	private $attribute;

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;

    function getCols(){
    	return $this->cols;
    }
    function setCols($cols){
    	$this->cols = $cols;
    }
    function getRows(){
    	return $this->rows;
    }
	function setRows($rows){
		$this->rows = $rows;
	}

     /**
	 * ユーザに表示するようのフォーム
	 */
    function getForm($attr = array()){

		$attributes = $this->getAttributes();
		$required = $this->getRequiredProp();

		foreach($attr as $key => $value){
			$attributes[] = htmlspecialchars($key,ENT_QUOTES,"UTF-8") . "=\"".htmlspecialchars($value,ENT_QUOTES,"UTF-8")."\"";
		}

		$html = array();
		//<textarea>直後の改行は必須
		$html[] = "<textarea name=\"data[".$this->getColumnId()."]\" " . implode(" ",$attributes) . $required . ">\n".htmlspecialchars($this->getValue(), ENT_QUOTES, "UTF-8")."</textarea>";

		return implode("\n",$html);
	}

	function getAttributes(){
		$attributes = array();
		if($this->cols)$attributes[] = "cols=\"".$this->cols."\"";
		if($this->rows)$attributes[] = "rows=\"".$this->rows."\"";

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
	 * 確認画面での表示
	 */
	function getView(){
		$html = htmlspecialchars((string)$this->getValue(), ENT_QUOTES, "UTF-8");
		return nl2br($html);
	}

	function getContent(){
		return htmlspecialchars((string)$this->getValue(), ENT_QUOTES, "UTF-8");
	}

    /**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = "アンケートの項目に表記する文言：<br>";
		$html .= '<input type="text" name="Column[config][question]" style="width:70%;" value="' . $this->question . '"><br><br>';
		$html .= '幅:<input type="text" name="Column[config][cols]" value="'.$this->cols.'" size="3"/>&nbsp;';
		$html .= '高さ:<input type="text" name="Column[config][rows]" value="'.$this->rows.'" size="3" />';

		$html .= "<br/>";

		if(is_null($this->attribute) && isset($this->style)){
			$attribute = "class=&quot;".htmlspecialchars($this->style,ENT_QUOTES,"UTF-8")."&quot;";
		}else{
			$attribute = trim($this->attribute);
		}

		$html .= '<label for="Column[config][attribute]'.$this->getColumnId().'">属性:</label>';
		$html .= '<input  id="Column[config][attribute]'.$this->getColumnId().'" name="Column[config][attribute]" type="text" value="'.$attribute.'" style="width:90%;" /><br />';
		$html .= "※記述例：class=\"sample\" title=\"サンプル\" placeholder=\"お問い合わせ内容を入力してください。\" pattern=\"\"<br>";

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
		$this->question = (isset($config["question"])) ? $config["question"] : "";
		$this->cols = (isset($config["cols"]) && is_numeric($config["cols"])) ? (int)$config["cols"] : null;
		$this->rows = (isset($config["rows"]) && is_numeric($config["rows"])) ? (int)$config["rows"] : null;
		$this->style = (isset($config["style"])) ? $config["style"] : null ;
		$this->attribute = (isset($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : null;
		$this->requiredProp = (isset($config["requiredProp"])) ? $config["requiredProp"] : null;
	}
	function getConfigure(){
		$config = parent::getConfigure();
		$config["question"] = $this->question;
		$config["cols"] = $this->cols;
		$config["rows"] = $this->rows;
		$config["style"] = $this->style;
		$config["attribute"] = $this->attribute;
		$config["requiredProp"] = $this->requiredProp;
		return $config;
	}

	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE  => "連携しない",
			SOYMailConverter::SOYMAIL_MEMO  => "備考"
		);
	}

	function getLinkagesSOYShopFrom() {
		return array(
			SOYShopConnector::SOYSHOP_NONE  => "連携しない",
			SOYShopConnector::SOYSHOP_MEMO  => "備考"
		);
	}
}
