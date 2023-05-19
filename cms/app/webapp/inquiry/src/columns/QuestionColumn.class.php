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
	 * @param array
	 * @return string
	 */
	function getForm($attrs=array()){

		$attributes = array();
		if(is_numeric($this->maxLength)) $attributes[] = "maxlength=\"".$this->maxLength."\"";
		if(is_numeric($this->size)) $attributes[] = "size=\"".$this->size."\"";

		foreach($attrs as $key => $value){
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
	function setConfigure(array $config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->maxLength = (isset($config["maxLength"]) && is_numeric($config["maxLength"])) ? (int)$config["maxLength"] : "";
		$this->size = (isset($config["size"]) && is_numeric($config["size"])) ? (int)$config["size"] : "";
		$this->answer = (isset($config["answer"])) ? $config["answer"] : "";
		$this->question = (isset($config["question"])) ? $config["question"] : "";
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
		$value = (is_string($this->getValue())) ? trim($this->getValue()) : "";
		if(!strlen($value)) return parent::validate();

		$answer = $this->answer;

		if($value !== $this->answer){
			switch(SOYCMS_PUBLISH_LANGUAGE){
				case "en":
					$msg = "Wrong answer to ".$this->getLabel().".";
					break;
				default:
				$msg = $this->getLabel()."への回答が間違っています。";
			}
			$this->setErrorMessage($res);
			return false;
		}
		
		return true;
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
