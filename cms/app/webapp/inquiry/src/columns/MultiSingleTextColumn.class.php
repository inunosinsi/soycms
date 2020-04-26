<?php

class MultiSingleTextColumn extends SOYInquiry_ColumnBase{

	//最大文字数
	private $maxLength;

	//幅
	private $size;

	//種類
	private $type;

	//分割数
	private $count = 1;

	//separator
	private $separator = "-";

	//<input type="***">
	private $inputType = "text";

	//入力モード指定
	private $ime_mode = 0;
	//携帯の入力モード指定
	private $mobile_ime_mode = 0;

	//フォームに自由に挿入する属性
	private $attribute;

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;


    /**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		$attributes = $this->getAttributes();
		$required = $this->getRequiredProp();

		//入力モード指定
		if($this->ime_mode) $attributes[] = $this->getAttributeForInputMode();
		if($this->mobile_ime_mode) $attributes[] = $this->getAttributeForMobileInputMode();

		foreach($attr as $key => $value){
			$attributes[] = htmlspecialchars($key, ENT_QUOTES, "UTF-8") . "=\"".htmlspecialchars($value, ENT_QUOTES, "UTF-8")."\"";
		}

		$count = $this->count;

		$values = $this->getValue();

		$html = array();
		for($i = 0; $i < $count; $i++){
			$_value = (isset($values[$i])) ? $values[$i] : "";
			$html[] = "<input type=\"" . htmlspecialchars($this->inputType, ENT_QUOTES, "UTF-8") . "\" name=\"data[".$this->getColumnId()."][$i]\" value=\"".htmlspecialchars($_value, ENT_QUOTES, "UTF-8")."\" " . implode(" ",$attributes) . "" . $required . ">";
		}

		return implode($this->separator,$html);
	}

	function getAttributes(){
		$attributes = array();
		if($this->maxLength) $attributes[] = "maxlength=\"{$this->maxLength}\"";
		if($this->size)      $attributes[] = "size=\"{$this->size}\"";

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

		$html .= '<label for="Column[config][count]'.$this->getColumnId().'">分割数:</label>';
		$html .= '<input type="text" name="Column[config][count]" value="'.$this->count.'" size="3" />&nbsp;';
		$html .= '<label for="Column[config][separator]'.$this->getColumnId().'">セパレータ:</label>';
		$html .= '<input type="text" name="Column[config][separator]" value="'.$this->separator.'" size="3" />&nbsp;';

		$html .= '<label for="Column[config][maxLength]'.$this->getColumnId().'">最大文字数:</label>';
		$html .= '<input  id="Column[config][maxLength]'.$this->getColumnId().'" name="Column[config][maxLength]" type="text" value="'.$this->maxLength.'" size="3"/>&nbsp;';
		$html .= '<label for="Column[config][size]'.$this->getColumnId().'">入力欄の幅:</label>';
		$html .= '<input  id="Column[config][size]'.$this->getColumnId().'" name="Column[config][size]" type="text" value="'.$this->size.'" size="3" />&nbsp;';

		$html .= "<br/>";

		$html .= '<label for="Column[config][type]'.$this->getColumnId().'">入力文字種別:</label>';
		$html .= '<select name="Column[config][type]"><option>全て</option>';
		$html .= '<option value="4" '.(($this->type == 4) ? "selected" : "").'>全角かなのみ</option>';
		$html .= '<option value="5" '.(($this->type == 5) ? "selected" : "").'>半角カナのみ</option>';
		$html .= '<option value="1" '.(($this->type == 1) ? "selected" : "").'>半角英数字のみ</option>';
		$html .= '<option value="3" '.(($this->type == 3) ? "selected" : "").'>半角数字のみ</option>';
		$html .= '<option value="2" '.(($this->type == 2) ? "selected" : "").'>メールアドレス</option>';
		$html .= '<option value="6" '.(($this->type == 6) ? 'selected="selected"' : '').'>全て（IMEオン）</option>';
		$html .= '</select>&nbsp;';

		$html .= '<input type="hidden" name="Column[config][ime_mode]" value="0" />';
		$html .= '<input  id="Column[config][ime_mode]'.$this->getColumnId().'" type="checkbox" name="Column[config][ime_mode]" value="1" '.($this->ime_mode ? 'checked="checked"' : '').' />';
		$html .= '<label for="Column[config][ime_mode]'.$this->getColumnId().'">入力モードを指定する</label>&nbsp;';

		$html .= '<input type="hidden" name="Column[config][mobile_ime_mode]" value="0" />';
		$html .= '<input  id="Column[config][mobile_ime_mode]'.$this->getColumnId().'" type="checkbox" name="Column[config][mobile_ime_mode]" value="1" '.($this->mobile_ime_mode ? 'checked="checked"' : '').' />';
		$html .= '<label for="Column[config][mobile_ime_mode]'.$this->getColumnId().'">携帯電話で入力モードを指定する</label>&nbsp;';

		$html .= "<br/>";

		$inputType = (isset($this->inputType) && strlen($this->inputType) > 0) ? htmlspecialchars($this->inputType,ENT_QUOTES,"UTF-8") : "text";
		$html .= '<label for="Column[config][inputType]'.$this->getColumnId().'">type:</label>';
		$html .= '<input id="Column[config][inputType]'.$this->getColumnId().'" name="Column[config][inputType]" type="text" value="'.$inputType.'" style="width:10%;" /><br />';
		$html .= "※入力例: text, email, tel, number等。";

		$html .= "<br>";

		if(is_null($this->attribute) && isset($this->style)){
			$attribute = "class=&quot;".htmlspecialchars($this->style,ENT_QUOTES,"UTF-8")."&quot;";
		}else{
			$attribute = trim($this->attribute);
		}

		$html .= '<label for="Column[config][attribute]'.$this->getColumnId().'">属性:</label>';
		$html .= '<input  id="Column[config][attribute]'.$this->getColumnId().'" name="Column[config][attribute]" type="text" value="'.$attribute.'" style="width:90%;" /><br />';
		$html .= "※記述例：class=\"sample\" title=\"サンプル\" placeholder=\"\" pattern=\"\"<br>";

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

		$this->maxLength = (isset($config["maxLength"]) && is_numeric($config["maxLength"])) ? (int)$config["maxLength"] : null;
		$this->size = (isset($config["size"]) && is_numeric($config["size"])) ? (int)$config["size"] : null;
		$this->type = (isset($config["type"]) && is_numeric($config["type"])) ? (int)$config["type"] : null;
		$this->count = (isset($config["count"]) && is_numeric($config["count"])) ? (int)$config["count"] : 1;
		$this->separator = (isset($config["separator"])) ? $config["separator"] : "";
		$this->inputType = (isset($config["inputType"])) && strlen($config["inputType"]) ? $config["inputType"] : "text";
		$this->ime_mode = (isset($config["ime_mode"])) ? $config["ime_mode"] : 0 ;
		$this->mobile_ime_mode = (isset($config["mobile_ime_mode"])) ? $config["mobile_ime_mode"] : 0 ;
		$this->attribute = (isset($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : null;
		$this->requiredProp = (isset($config["requiredProp"])) ? $config["requiredProp"] : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["size"] = $this->size;
		$config["type"] = $this->type;
		$config["maxLength"] = $this->maxLength;
		$config["count"] = max(1,$this->count);
		$config["separator"] = $this->separator;
		$config["ime_mode"] = $this->ime_mode;
		$config["inputType"] = $this->inputType;
		$config["mobile_ime_mode"] = $this->mobile_ime_mode;
		$config["attribute"] = $this->attribute;
		$config["requiredProp"] = $this->requiredProp;
		return $config;
	}
	function validate(){
		$values = $this->getValue();

		foreach($values as $value){
			$value = trim($value);

			if($this->getIsRequire() && strlen($value)<1){
				$this->setErrorMessage($this->getLabel()."を入力してください。");
				return false;
			}

			//全角かなのみ、英数字とハイフンとスペースも許可、漢字と半角カナと記号が不可
			if($this->type == 4 && strlen($value)>0){
				if(!preg_match('/^[ぁ-ヴーa-zA-Z0-9\\- 　]*$/',$value)){
					$this->setErrorMessage($this->getLabel() . "は全角かなで入力してください。");
					return false;
				}
			}
			//半角カナのみ、英数字とハイフンとスペースも許可、漢字と全角かなと記号が不可
			if($this->type == 5 && strlen($value)>0){
			if(!preg_match('/^[-ｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜｦﾝﾞﾟｧｨｩｪｫｬｭｮa-zA-Z0-9 ]*$/',$value)){
					$this->setErrorMessage($this->getLabel() . "は半角カナで入力してください。");
					return false;
				}
			}

			//英数字とハイフンとスペース
			if($this->type == 1 && strlen($value)>0){
				if(!preg_match('/^[a-zA-Z0-9\\- ]*$/',$value)){
					$this->setErrorMessage($this->getLabel() . "は半角英数字で入力してください。");
					return false;
				}
			}

			//数字とハイフン
			if($this->type == 3 && strlen($value)>0){
				if(!preg_match('/^[0-9\-]*$/',$value)){
					$this->setErrorMessage($this->getLabel() . "は半角数字で入力してください。[{$value}]");
					return false;
				}
			}

			//メールアドレスのマッチ、MailAddressColumnからコピー
			if($this->type == 2 && strlen($value)>0){
				$ascii  = '[a-zA-Z0-9!#$%&\'*+\\-\\/=?^_`{|}~.]';//'[\x01-\x7F]';
		    	$domain = '(?:[-a-z0-9]+\\.)+[a-z]{2,10}';//'([-a-z0-9]+\.)*[a-z]+';
				$d3     = '\d{1,3}';
				$ip     = $d3.'\\.'.$d3.'\\.'.$d3.'\\.'.$d3;
		    	$validEmail = "^$ascii+@(?:$domain|\\[$ip\\])$";

		    	if(! preg_match('/'.$validEmail.'/i', $value) ) {
					$this->setErrorMessage($this->getLabel() . "の書式が正しくありません。");
					return false;
		    	}
			}
		}
	}

	/**
	 * 確認画面で呼び出す
	 */
	function getView(){
		$value = $this->getValue();
		if(empty($value)){
			return "";
		}

		$value = implode($this->separator, $value);
		$empty = str_repeat($this->separator, $this->count -1);

		if($value != $empty){
			return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
		}else{
			return "";
		}
	}

	/**
	 * 絵文字削除
	 */
	function deleteEmoji($value){
		mb_substitute_character('none');
		$value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');

		//対Softbank
		$pattern = '/[\\x1B][\\x24][G|E|F|O|P|Q][\\x21-\\x7E]+([\\x0F]|$)/';
		preg_match_all($pattern, $value, $arr);// $arr[0]に対象絵文字が格納される
		$value = str_replace($arr[0], array(), $value);

		return $value;

	}

	function getAttributeForInputMode(){
		$attribute = "";
		switch($this->type){
			case 1 ://半角英数字
			case 2 ://メールアドレス
			case 3 ://半角数字
				//$attribute = "style=\"ime-mode:inactive;\"";
				$attribute = "style=\"ime-mode:disabled;\"";
				break;
			case 4 ://全角かな
			case 5 ://半角カナ
			case 6 ://全て（IMEオン）
				$attribute = "style=\"ime-mode:active;\"";
				break;
			default:
				break;
		}

		return $attribute;
	}


	/**
	 * 携帯の入力モードを設定する属性をキャリアを判別して返す
	 */
	function getAttributeForMobileInputMode(){
		$attributes = array();
		switch($this->type){
			case 1 ://半角英数字
				//i-mode (HTML)
				$attributes["docomo"] = "istyle=\"3\"";
				//softbank
				$attributes["softbank"] = "mode=\"alphabet\"";
				//au
				$attributes["au"] = "format=\"{$wap_length}m\"";
				//au, softbank (モード変更不可), i-mode (XHTML)
				$attributes["docomo_xhtml"] = "style=\"-wap-input-format:&quot;*&lt;ja:en&gt;&quot;;-wap-input-format:{$wap_length}m;\"";
				break;
			case 2 ://メールアドレス
				$attributes["docomo"] = "istyle=\"3\"";
				$attributes["softbank"] = "mode=\"alphabet\"";
				$attributes["au"] = "format=\"{$wap_length}x\"";
				$attributes["docomo_xhtml"] = "style=\"-wap-input-format:&quot;*&lt;ja:en&gt;&quot;;-wap-input-format:{$wap_length}m;\"";
				break;
			case 3 ://半角数字
				$attributes["docomo"] = "istyle=\"4\"";
				$attributes["softbank"] = "mode=\"numeric\"";
				$attributes["au"] = "format=\"format=\"{$wap_length}N\"";
				$attributes["docomo_xhtml"] = "style=\"-wap-input-format:&quot;*&lt;ja:n&gt;&quot;;-wap-input-format:{$wap_length}N;\"";
				break;
			case 4 ://全角かな
				$attributes["docomo"] = "istyle=\"1\"";
				$attributes["softbank"] = "mode=\"hiragana\"";
				$attributes["au"] = "format=\"format=\"{$wap_length}M\"";
				$attributes["docomo_xhtml"] = "style=\"-wap-input-format:&quot;*&lt;ja:h&gt;&quot;;-wap-input-format:{$wap_length}M;\"";
				break;
			case 5 ://半角カナ
				$attributes["docomo"] = "istyle=\"2\"";
				$attributes["softbank"] = "mode=\"hankakukana\"";
				$attributes["au"] = "format=\"format=\"{$wap_length}M\"";
				$attributes["docomo_xhtml"] = "style=\"-wap-input-format:&quot;*&lt;ja:hk&gt;&quot;;-wap-input-format:{$wap_length}M;\"";
				break;
			default:
				break;
		}

		$agent = @$_SERVER['HTTP_USER_AGENT'];
		switch(true){
			case preg_match("/^DoCoMo/i", $agent) :
				return $attributes["docomo"]." ".$attributes["docomo_xhtml"];
				break;
			case preg_match("/^(J-PHONE|Vodafone|MOT-[CV]|SoftBank)/i", $agent) :
				return $attributes["softbank"];
				break;
			case preg_match("/^KDDI-/i", $agent) :
				return $attributes["au"];
				break;
			default:
				return "";
				break;
		}
	}




	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE  	=> "連携しない",
			SOYMailConverter::SOYMAIL_NAME 		=> "名前",
			SOYMailConverter::SOYMAIL_READING 	=> "フリガナ",
			SOYMailConverter::SOYMAIL_TEL		=> "電話番号",
			SOYMailConverter::SOYMAIL_FAX		=> "FAX番号",
			SOYMailConverter::SOYMAIL_CELLPHONE	=> "携帯電話番号",
			SOYMailConverter::SOYMAIL_JOB_TEL	=> "勤務先電話番号",
			SOYMailConverter::SOYMAIL_JOB_FAX	=> "勤務先FAX番号",
			SOYMailConverter::SOYMAIL_JOB_NAME	=> "勤務先名称・職種",
			SOYMailConverter::SOYMAIL_ATTR1 	=> "属性A",
			SOYMailConverter::SOYMAIL_ATTR2 	=> "属性B",
			SOYMailConverter::SOYMAIL_ATTR3 	=> "属性C",
			SOYMailConverter::SOYMAIL_MEMO  	=> "備考"
		);
	}
}
