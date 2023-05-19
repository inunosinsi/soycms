<?php

class PrivacyPolicyColumn extends SOYInquiry_ColumnBase{

	const MODE_TEXTAREA = 0;	//文章の表示をtextarea形式にする
	const MODE_PRE = 1;			//文章の表示をpre形式にする

	private $mode = self::MODE_TEXTAREA;

	private $policy;
	const POLICYDEFAULT = "個人情報保護方針を入力して下さい。";

	private $checkLabel;
	const CHECK_LABEL_DEFAULT = "同意する";
	private $noCheckMessage;
	const NO_CHECK_MESSAGE_DEFAULT = "個人情報保護方針への同意をお願いします。";

    private $cols;
    private $rows;

	//値の保存（出力）はしない
	protected $noPersistent = true;

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
	 * @param array
	 * @return string
	 */
	function getForm(array $attrs=array()){

		$attributes = array();
		$attributes[] = (is_numeric($this->cols)) ? "cols=\"".$this->cols."\"" : 'style="width:90%;"';
		if(is_numeric($this->rows)) $attributes[] = "rows=\"".$this->rows."\"";

		foreach($attrs as $key => $value){
			$attributes[] = htmlspecialchars($key,ENT_QUOTES,"UTF-8") . "=\"".htmlspecialchars($value,ENT_QUOTES,"UTF-8")."\"";
		}

		$html = array();
		if($this->mode == self::MODE_TEXTAREA){
			$html[] = "<textarea " . implode(" ",$attributes) . " readonly=\"readonly\">".$this->policy."</textarea>";
		}else{
			$html[] = "<pre " . implode(" ",$attributes) . ">" . $this->policy . "</pre>";
		}

		$html[] = "<br/>";
		$html[] = "<input type=\"checkbox\" id=\"data_".$this->getColumnId() . "\" name=\"data[".$this->getColumnId()."]\" value=\"1\" />";
		$html[] = "<label for=\"data_".$this->getColumnId() . "\">".htmlspecialchars($this->checkLabel,ENT_QUOTES,"UTF-8")."</label>";

		return implode("\n",$html);

	}

    /**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = "";
		$html.= "個人情報保護方針：";
		$html.="<span style=\"color:red;\">※設定にかかわらず、常に必須扱いとなります。</span><br>";
		$html.= '<textarea type="text" name="Column[config][policy]" style="height:100px;">'."\n".$this->policy.'</textarea>';
		$html.= "<br/>";
		$html.= '<label for="">表示モード：</label>';
		if($this->mode == self::MODE_TEXTAREA){
			$html.= '<label><input type="radio" name="Column[config][mode]" value="' . self::MODE_TEXTAREA . '" checked="checked">textarea</label>';
			$html.= "&nbsp;&nbsp;";
			$html.= '<label><input type="radio" name="Column[config][mode]" value="' . self::MODE_PRE . '">pre</label>';
		}else{
			$html.= '<label><input type="radio" name="Column[config][mode]" value="' . self::MODE_TEXTAREA . '">textarea</label>';
			$html.= "&nbsp;&nbsp;";
			$html.= '<label><input type="radio" name="Column[config][mode]" value="' . self::MODE_PRE . '" checked="checked">pre</label>';
		}

		$html.= "<br/>";
		$html.= '<label for="">チェックボックスの文言：</label>';
		$html.= '<input type="text" size="60" name="Column[config][checkLabel]" value="'.htmlspecialchars($this->checkLabel,ENT_QUOTES,"UTF-8").'" />';
		$html.= "<br/>";
		$html.= '<label for="">チェックされなかったときのの文言：</label>';
		$html.= '<input type="text" size="60" name="Column[config][noCheckMessage]" value="'.htmlspecialchars($this->noCheckMessage,ENT_QUOTES,"UTF-8").'" />';

		$html.= "<br/>";
		$html .= '幅:<input type="number" name="Column[config][cols]" value="'.$this->cols.'" size="6"/>&nbsp;';
		$html .= '高さ:<input type="number" name="Column[config][rows]" value="'.$this->rows.'" size="6" />';

		return $html;
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure(array $config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->mode = (isset($config["mode"]) && is_numeric($config["mode"])) ? (int)$config["mode"] : self::MODE_TEXTAREA;
		$this->cols = (isset($config["cols"]) && is_numeric($config["cols"])) ? (int)$config["cols"] : "";
		$this->rows = (isset($config["rows"]) && is_numeric($config["rows"])) ? (int)$config["rows"] : "";
		$this->policy = (isset($config["policy"])) ? $config["policy"] : self::POLICYDEFAULT;
		$this->checkLabel = (isset($config["checkLabel"])) ? $config["checkLabel"] : self::CHECK_LABEL_DEFAULT ;
		$this->noCheckMessage = (isset($config["noCheckMessage"])) ? $config["noCheckMessage"] : self::NO_CHECK_MESSAGE_DEFAULT ;
	}
	function getConfigure(){
		$config = parent::getConfigure();
		$config["mode"] = (int)$this->mode;
		$config["cols"] = $this->cols;
		$config["rows"] = $this->rows;
		$config["policy"] = $this->policy;
		$config["checkLabel"] = $this->checkLabel;
		$config["noCheckMessage"] = $this->noCheckMessage;
		return $config;
	}
	function getLabel(){
		return "";
	}

	function getContent(){
		return "";
	}

	function validate(){
		if(!$this->getIsRequire()) return true;

		if($this->getValue() != 1){
			$this->setErrorMessage($this->noCheckMessage);
			return false;
		}

		return true;
    }

    /**
     * 強制的に必須にする
     */
    function getIsRequire(){
    	return true;
    }
}
