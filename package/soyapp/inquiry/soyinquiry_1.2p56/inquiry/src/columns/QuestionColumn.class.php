<?php
/**
 * ユーザに質問を表示して応えさせます。
 */
class QuestionColumn extends SOYInquiry_ColumnBase{

	private $question = "今年は西暦何年？";
	private $answer = "";
	private $maxLength;
	private $size;
	protected $noPersistent = true;

    /**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		$attributes = array();
		if($this->maxLength)$attributes[] = "maxlength=\"".$this->maxLength."\"";
		if($this->size)$attributes[] = "size=\"".$this->size."\"";

		foreach($attr as $key => $value){
			$attributes[] = htmlspecialchars($key,ENT_QUOTES,"UTF-8") . "=\"".htmlspecialchars($value,ENT_QUOTES,"UTF-8")."\"";
		}

		$html = array();
		$html[] = $this->question;
		$html[] = "<input type=\"text\" name=\"data[".$this->getColumnId()."]\" value=\"".$this->getValue()."\"" . implode(" ",$attributes) . " />";

		return implode("\n",$html);

	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html  = '最大文字数:<input type="text" name="Column[config][maxLength]" value="'.$this->maxLength.'" size="3"/>&nbsp;';
		$html .= '幅:<input type="text" name="Column[config][size]" value="'.$this->size.'" size="3" /><br>';
		$html .= "質問：<br>";
		$html .= '<textarea type="text" name="Column[config][question]" style="height:100px;padding:0;">'.$this->question.'</textarea><br />';
		$html .= '回答:<input type="text" name="Column[config][answer]" value="'.$this->answer.'" /><br />';
		return $html;
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->maxLength = (isset($config["maxLength"]) && is_numeric($config["maxLength"])) ? (int)$config["maxLength"] : null;
		$this->size = (isset($config["size"]) && is_numeric($config["size"])) ? (int)$config["size"] : null;
		$this->answer = (isset($config["answer"])) ? $config["answer"] : null;
		$this->question = (isset($config["question"])) ? $config["question"] : null;
	}
	function getConfigure(){
		$config = parent::getConfigure();
		$config["size"] = $this->size;
		$config["maxLength"] = $this->maxLength;
		$config["answer"] = $this->answer;
		$config["question"] = $this->question;
		return $config;
	}

	function getContent(){
		return "";
	}

	function validate(){
		$value = $this->getValue();

		if($this->getIsRequire() && strlen($value)<1){
			$this->setErrorMessage($this->getLabel()."を入力してください。");
			return false;
		}

		$answer = $this->answer;

		if($value !== $this->answer){
			$this->setErrorMessage($this->getLabel()."への回答が間違っています。");
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
}
