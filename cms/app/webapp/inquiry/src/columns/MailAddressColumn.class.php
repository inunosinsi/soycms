<?php

class MailAddressColumn extends SOYInquiry_ColumnBase{

	//最大文字数
	private $maxLength;
	//幅
	private $size;

	//入力モード指定
	private $ime_mode = 0;
	//携帯の入力モード指定
	private $mobile_ime_mode = 0;

	//ドメインの禁止
	private $ban_mail_domain;

	//フォームに挿入するクラス
	private $style;	//1.0.1からclassのみ指定は廃止されるが、1.0.0以前から使用しているユーザのために残しておく

	//フォームに自由に挿入する属性
	private $attribute;

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;

    /**
	 * ユーザに表示するようのフォーム
	 * @param array
	 * @return string
	 */
	function getForm(array $attrs=array()){

		$attributes = $this->getAttributes();
		$required = $this->getRequiredProp();

		//入力モード指定
		if($this->ime_mode) $attributes[] = $this->getAttributeForInputMode();
		if($this->mobile_ime_mode) $attributes[] = $this->getAttributeForMobileInputMode();

		foreach($attrs as $key => $value){
			$attributes[] = htmlspecialchars($key) . "=\"".htmlspecialchars($value)."\"";
		}
		
		$html = array();
		$html[] = "<input type=\"email\" name=\"data[".$this->getColumnId()."]\" value=\"".htmlspecialchars($this->getValue(), ENT_QUOTES, "UTF-8")."\" " . implode(" ",$attributes) . "" . $required . ">";

		return implode("\n",$html);
	}

	function getAttributes(){
		$attributes = array();
		if(is_numeric($this->maxLength))$attributes[] = "maxlength=\"".$this->maxLength."\"";
		if(is_numeric($this->size))$attributes[] = "size=\"".$this->size."\"";

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
		$html = "";

		$html .= '<label for="Column[config][maxLength]'.$this->getColumnId().'">最大文字数:</label>';
		$html .= '<input  id="Column[config][maxLength]'.$this->getColumnId().'" name="Column[config][maxLength]" type="text" value="'.$this->maxLength.'" size="3"/>&nbsp;';
		$html .= '<label for="Column[config][size]'.$this->getColumnId().'">入力欄の幅:</label>';
		$html .= '<input  id="Column[config][size]'.$this->getColumnId().'" name="Column[config][size]" type="text" value="'.$this->size.'" size="3" />&nbsp;';

		$html .= '<input type="hidden" name="Column[config][ime_mode]" value="0" />';
		$html .= '<input  id="Column[config][ime_mode]'.$this->getColumnId().'" type="checkbox" name="Column[config][ime_mode]" value="1" '.($this->ime_mode ? 'checked="checked"' : '').' />';
		$html .= '<label for="Column[config][ime_mode]'.$this->getColumnId().'">入力モードを指定する</label>&nbsp;';

		$html .= '<input type="hidden" name="Column[config][mobile_ime_mode]" value="0" />';
		$html .= '<input  id="Column[config][mobile_ime_mode]'.$this->getColumnId().'" type="checkbox" name="Column[config][mobile_ime_mode]" value="1" '.($this->mobile_ime_mode ? 'checked="checked"' : '').' />';
		$html .= '<label for="Column[config][mobile_ime_mode]'.$this->getColumnId().'">携帯電話で入力モードを指定する</label>&nbsp;';

		$html .= "<br/>";

		$html .= '<label for="Column[config][ban_mail_domain]'.$this->getColumnId().'">お問い合わせ受付を禁止するドメイン:</label>';
		$html .= '<input  id="Column[config][ban_mail_domain]'.$this->getColumnId().'" name="Column[config][ban_mail_domain]" type="text" value="'.$this->ban_mail_domain.'" placeholder="example.com,sample.jp"> ※カンマ区切り';

		$html .= "<br/>";

		if(is_null($this->attribute) && isset($this->style)){
			$attribute = "class=&quot;".htmlspecialchars($this->style,ENT_QUOTES,"UTF-8")."&quot;";
		}else{
			$attribute = trim((string)$this->attribute);
		}

		$html .= '<label for="Column[config][attribute]'.$this->getColumnId().'">属性:</label>';
		$html .= '<input  id="Column[config][attribute]'.$this->getColumnId().'" name="Column[config][attribute]" type="text" value="'.$attribute.'" style="width:90%;" /><br />';
		$html .= "※記述例：class=\"sample\" title=\"サンプル\" placeholder=\"info@soyinquiry.jp\" pattern=\"\"<br>";

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
	function setConfigure(array $config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->maxLength = (isset($config["maxLength"]) && is_numeric($config["maxLength"])) ? (int)$config["maxLength"] : "";
		$this->size = (isset($config["size"]) && is_numeric($config["size"])) ? (int)$config["size"] : "";
		$this->ime_mode = (isset($config["ime_mode"])) ? $config["ime_mode"] : 0 ;
		$this->mobile_ime_mode = (isset($config["mobile_ime_mode"])) ? $config["mobile_ime_mode"] : 0 ;
		$this->ban_mail_domain = (isset($config["ban_mail_domain"])) ? $config["ban_mail_domain"] : "";
		$this->style = (isset($config["style"])) ? (string)$config["style"] : "";
		$this->attribute = (isset($config["attribute"]) && is_string($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : "";
		$this->requiredProp = (isset($config["requiredProp"]) && $config["requiredProp"]);
	}
	function getConfigure(){
		$config = parent::getConfigure();
		$config["size"] = $this->size;
		$config["maxLength"] = $this->maxLength;
		$config["ime_mode"] = $this->ime_mode;
		$config["mobile_ime_mode"] = $this->mobile_ime_mode;
		$config["ban_mail_domain"] = $this->ban_mail_domain;
		$config["style"] = $this->style;
		$config["attribute"] = $this->attribute;
		$config["requiredProp"] = $this->requiredProp;
		return $config;
	}

	function validate(){
		$email = $this->getValue();
		if($this->getIsRequire()){

			$ascii  = '[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]';//'[\x01-\x7F]';
			$domain = '(?:[-a-z0-9]+\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
			$d3     = '\d{1,3}';
			$ip     = $d3.'\.'.$d3.'\.'.$d3.'\.'.$d3;
			$validEmail = "^$ascii+\@(?:$domain|\\[$ip\\])$";

			if(! preg_match('/'.$validEmail.'/i', $email) ) {
				switch(SOYCMS_PUBLISH_LANGUAGE){
					case "en":
						$msg = $this->getLabel() . " format is incorrect.";
						break;
					default:
						$msg = $this->getLabel() . "の書式が正しくありません。";
				}
				$this->setErrorMessage($msg);
				return false;
			}
		}

		// 未入力の場合のvalidate
		return parent::validate();
    }

	function getAttributeForInputMode(){
		//$attribute = "style=\"ime-mode:inactive;\"";
		$attribute = "style=\"ime-mode:disabled;\"";
		return $attribute;
	}


	/**
	 * 携帯の入力モードを設定する属性をキャリアを判別して返す
	 */
	function getAttributeForMobileInputMode(){
		return "";
		// $attributes = array();
		// $attributes["docomo"] = "istyle=\"3\"";
		// $attributes["softbank"] = "mode=\"alphabet\"";
		// $attributes["au"] = "format=\"{$wap_length}x\"";
		// $attributes["docomo_xhtml"] = "style=\"-wap-input-format:&quot;*&lt;ja:en&gt;&quot;;-wap-input-format:{$wap_length}m;\"";

		// $agent = @$_SERVER['HTTP_USER_AGENT'];
		// switch(true){
		// 	case preg_match("/^DoCoMo/i", $agent) :
		// 		return $attributes["docomo"]." ".$attributes["docomo_xhtml"];
		// 		break;
		// 	case preg_match("/^(J-PHONE|Vodafone|MOT-[CV]|SoftBank)/i", $agent) :
		// 		return $attributes["softbank"];
		// 		break;
		// 	case preg_match("/^KDDI-/i", $agent) :
		// 		return $attributes["au"];
		// 		break;
		// 	default:
		// 		return "";
		// 		break;
		// }
	}


    function getErrorMessage(){
    	return $this->errorMessage;
    }


    function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_MAIL 	=> "メールアドレス",
			SOYMailConverter::SOYMAIL_NONE  => "連携しない",
			SOYMailConverter::SOYMAIL_ATTR1 => "属性A",
			SOYMailConverter::SOYMAIL_ATTR2 => "属性B",
			SOYMailConverter::SOYMAIL_ATTR3 => "属性C",
			SOYMailConverter::SOYMAIL_MEMO  => "備考"
		);
	}

	function getLinkagesSOYShopFrom() {
		return array(
			SOYShopConnector::SOYSHOP_NONE  => "連携しない",
			SOYShopConnector::SOYSHOP_MAIL 	=> "メールアドレス"
		);
	}

	function getReplacement() {
		if(!is_string($this->replacement)) $this->replacement = "";
		return (strlen($this->replacement) == 0) ? "#EMAIL#" : $this->replacement;
	}
}
