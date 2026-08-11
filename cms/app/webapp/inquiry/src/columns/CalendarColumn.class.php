<?php

class CalendarColumn extends SOYInquiry_ColumnBase{

	private $days = array("日", "月", "火", "水", "木", "金", "土", "日");

	private $library = 0;

	// 過去の日を選択可にする
	private $allowPastDates = 0;

	// 選択不可の曜日
	private $disabledDays = array();

	//最短選択可能日
	private $leadTimeDays = 0;

	//本日の時刻で最短選択可能日に+1をするか決める
	private $cutoffAdjustment = "";

	//最短選択可能日の加算
	private $leadTimeDaysAdd = 0;

	//選択不可日(手動、カンマ区切り)
	private $disabledDates;

	//入力形式 logic.CalendarColumnLogicに記載あり
	private $dateFormat = "mm/dd/yy";
	private $dateFormatOtherLanguage = "mm/dd/yy";

	//フォームに挿入するクラス
	private $style;	//1.0.1からclassのみ指定は廃止されるが、1.0.0以前から使用しているユーザのために残しておく

	//フォームに自由に挿入する属性
	private $attribute;

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;

	public $isLinkageSOYMail;
	public $isLinkageSOYShop;

	private function _libraries(){
		return array(
			"なし",
			"jQuery UI"
		);
	}

    /**
	 * ユーザに表示するようのフォーム
	 * @param array
	 * @return string
	 */
	function getForm(array $attrs=array()){
		$value = $this->getValue();
		if(!is_string($value)) $value = "";
		
		return "<input name=\"data[".$this->getColumnId()."]\" value=\"".htmlspecialchars($value, ENT_QUOTES, "UTF-8")."\" " . self::_buildAttribute($attrs).">";
	}

	/**
	 * @param array
	 * @return string
	 */
	private function _buildAttribute(array $attrs=array()){
		$_attrs = array();

		//設定したattributeを挿入
		if(isset($this->attribute) && strlen($this->attribute) > 0){
			$attribute = str_replace("&quot;","\"",$this->attribute);	//"が消えてしまうから、htmlspecialcharsができない
			$attribute = str_replace("　", " ", $attribute);
			$_arr = explode(" ", $attribute);
			if(count($_arr)){
				foreach($_arr as $v){
					$_v = explode("=", $v);
					$key = (isset($_v[0])) ? trim($_v[0]) : "";
					if(!strlen($key)) continue;

					$_attrs[$key] = (isset($_v[1])) ? trim(trim(trim($_v[1], "\""), "'")) : "";
				}
			}
		}

		foreach($attrs as $key => $value){
			$key = htmlspecialchars($key, ENT_QUOTES, "UTF-8");
			$_attrs[$key] = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
		}

		$_attrs["autocomplete"] = "off";
		switch((int)$this->library){
			case 1:	// jQuery UI
				$logic = SOY2Logic::createInstance("logic.CalendarColumnLogic", array("columnId" => $this->id));
				$_attrs["type"] = "text";
				$_attrs["id"] = $logic->createIdPropertyName();
				$_attrs["readonly"] = "";
				break;
			default:
				$_attrs["type"] = "date";
				if(isset($_attrs["id"])) unset($_attrs["id"]);
				if(isset($_attrs["readonly"])) unset($_attrs["readonly"]);
		}
		

		if(!SOYINQUIRY_FORM_DESIGN_PAGE && $this->requiredProp){
			$_attrs["required"] = "";
		}
		
		$str = "";
		foreach($_attrs as $key => $value){
			if(strlen($value)){
				$str .= $key."=\"".$value."\" ";
			}else{
				$str .= $key." ";
			}
		}
		return trim($str);
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$logic = SOY2Logic::createInstance("logic.CalendarColumnLogic", array("columnId" => $this->id));
		$html = array();

		$html[] = '<label for="Column[config][library]'.$this->getColumnId().'">使用するライブラリ:</label>';
		$html[] = '<select id="Column[config][library]'.$this->getColumnId().'" name="Column[config][library]">';
		foreach(self::_libraries() as $idx => $lib){
			if($idx == $this->library){
				$html[] = "<option value=\"".$idx."\" selected>".$lib."</option>";
			}else{
				$html[] = "<option value=\"".$idx."\">".$lib."</option>";
			}
		}
		$html[] = "</select>";

		$html[] = "<br>";

		if(is_null($this->attribute) && isset($this->style)){
			$attribute = "class=&quot;".htmlspecialchars($this->style,ENT_QUOTES,"UTF-8")."&quot;";
		}else{
			$attribute = trim((string)$this->attribute);
		}

		$html[] = '<label for="Column[config][attribute]'.$this->getColumnId().'">属性:</label>';
		$html[] = '<input id="Column[config][attribute]'.$this->getColumnId().'" name="Column[config][attribute]" type="text" value="'.$attribute.'" style="width:90%;" /><br />';
		$html[] = "※記述例：class=\"sample\" title=\"サンプル\" placeholder=\"\" pattern=\"\"<br>";

		$reqHtml = "";

		$reqHtml = '<label><input type="checkbox" name="Column[config][requiredProp]" value="1"';
		if($this->requiredProp) $reqHtml .= ' checked';
		$reqHtml .= '>required属性を利用する</label>';
		$html[] = $reqHtml;

		$html[] = "<br>";

		// 定休日
		switch((int)$this->library){
			case 1:	// jQuery UI
				$html[] = "<label>本日より前の日の選択:</label>";
				$lab = "本日より前の日を選択可にする";
				if((int)$this->allowPastDates === 1){
					$html[] = "<label><input type=\"checkbox\" name=\"Column[config][allowPastDates]\" value=\"1\" checked>".$lab."</label>";
				}else{
					$html[] = "<label><input type=\"checkbox\" name=\"Column[config][allowPastDates]\" value=\"1\">".$lab."</label>";
				}
				
				$html[] = "<br>";
				$html[] = "<label>選択不可の曜日:</label>";
				for($i = 0; $i <= 6; $i++){
					if(is_array($this->disabledDays) && is_numeric(array_search($i, $this->disabledDays))){
						$html[] = "<label><input type=\"checkbox\" name=\"Column[config][disabledDays][]\" value=\"".$i."\" checked>".$this->days[$i]."</label>&nbsp;";
					}else{
						$html[] = "<label><input type=\"checkbox\" name=\"Column[config][disabledDays][]\" value=\"".$i."\">".$this->days[$i]."</label>&nbsp;";
					}
				}

				$html[] = "<br>";
				$html[] = "<label>選択不可日(記入例：".date("Y-m-d")."):</label><br>";
				$html[] = "<input type=\"text\" name=\"Column[config][disabledDates]\" value=\"".$this->disabledDates."\" placeholder=\"記入例:".date("Y-m-d").",".date("Y-m-d", strtotime("+1day"))."\" style=\"width:95%;\">";
				$html[] = "<br><small>※複数日の指定の場合はカンマ区切りで指定します。</small>";
				
				$html[] = "<br>";
				$html[] = "<label>最短選択可能日:</label>";
				$html[] = "最短で<input type=\"number\" name=\"Column[config][leadTimeDays]\" value=\"".(int)$this->leadTimeDays."\" style=\"width:50px;\">日後から選択可能にする";

				$html[] = "<br>";
				$html[] = "<label>本日の締切時刻:</label>";
				$h = "<input type=\"time\" name=\"Column[config][cutoffAdjustment]\" value=\"".$this->cutoffAdjustment."\">";
				$h .= "より後は最短選択可能日を";
				$h .= "<input type=\"number\" name=\"Column[config][leadTimeDaysAdd]\" value=\"".(int)$this->leadTimeDaysAdd."\" style=\"width:50px;\">日後にする";
				$html[] = $h;
				$html[] = "<br>";

				$fmtDefault = $logic->getDateFormatDefault();
				
				$html[] = "<label>入力形式:</label>";
				$html[] = "日本語：<input type=\"text\" name=\"Column[config][dateFormat]\" value=\"".$logic->getDateFormat()."\" placeholder=\"".$fmtDefault."\" style=\"width:150px;\">";
				$html[] = "他の言語：<input type=\"text\" name=\"Column[config][dateFormatOtherLanguage]\" value=\"".$logic->getDateFormatOtherLanguage()."\" placeholder=\"".$fmtDefault."\" style=\"width:150px;\">";
				

				
				break;
			default:
				//
		}

		/** サンプルコード **/
		switch((int)$this->library){
			case 1:	// jQuery UI
				$html[] = "<div class=\"alert alert-warning\">当カラムはページのテンプレートにJavaScriptのコードを書く必要があります。<br>&lt;script&gt;は必ず&lt;/body&gt;よりも上の行に挿入してください</div>";

				$colNum = $logic->extractColumnNumber();
				$idProp = "jquery_sample_".$colNum;
				$html[] = "<a href=\"javascript:void(0);\" class=\"btn btn-info\" onclick=\"$('#".$idProp."').toggle();\">サンプルコード</a>";
				$html[] = "<div id=\"".$idProp."\" style=\"display:none;\">";
				$html[] = "<pre style=\"width:100%;\">".self::_buildJQueryUiSampleCode()."</pre>";
				$html[] = "</div>";
				break;
			default:
				//
		}

		return implode("\n", $html);
	}

	/**
	 * @param int
	 * @return string
	 */
	private function _buildJQueryUiSampleCode(){
		// SOY Inquiryが設置されているサイトを調べる
		//CMSApplication::switchAdminMode();
		//CMSApplication::switchAppMode();
	
		$code = array();
		$code[] = "/** 下記コードは必要に応じて設置してください。 **/";
		$code[] = "<script src=\"//code.jquery.com/jquery-4.0.0-beta.js\"></script>";
		$code[] = "<script src=\"//code.jquery.com/ui/1.14.0/jquery-ui.min.js\"></script>";
		$code[] = "";
		$code[] = "/** 下記コードはjQuery UIの読み込みよりも後に指定してください。 **/";
		$code[] = "<script>$('<script>').attr('src', location.pathname + '?calendar_js=".$this->getId()."').appendTo('body');</script>";
		return htmlspecialchars(implode("\n", $code), ENT_QUOTES, "UTF-8");
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure(array $config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->allowPastDates = (isset($config["allowPastDates"])) ? (int)$config["allowPastDates"] : 0;
		$this->disabledDays = (isset($config["disabledDays"])) ? $config["disabledDays"] : array();
		$this->disabledDates = (isset($config["disabledDates"])) ? (string)$config["disabledDates"] : "";
		$this->library = (isset($config["library"])) ? (string)$config["library"] : 0;
		$this->leadTimeDays = (isset($config["leadTimeDays"])) ? (int)$config["leadTimeDays"] : 0;
		$this->cutoffAdjustment = (isset($config["cutoffAdjustment"]) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $config["cutoffAdjustment"])) ? $config["cutoffAdjustment"] : "";
		$this->leadTimeDaysAdd = (isset($config["leadTimeDaysAdd"])) ? (int)$config["leadTimeDaysAdd"] : 0;
		$this->dateFormat = (isset($config["dateFormat"])) ? $config["dateFormat"] : "";
		$this->dateFormatOtherLanguage = (isset($config["dateFormatOtherLanguage"])) ? $config["dateFormatOtherLanguage"] : "";
		$this->attribute = (isset($config["attribute"]) && is_string($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : "";
		$this->requiredProp = (isset($config["requiredProp"]) && $config["requiredProp"]);
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["library"] = $this->library;
		$config["allowPastDates"] = $this->allowPastDates;
		$config["disabledDays"] = $this->disabledDays;
		$config["disabledDates"] = $this->disabledDates;
		$config["leadTimeDays"] = $this->leadTimeDays;
		$config["cutoffAdjustment"] = $this->cutoffAdjustment;
		$config["leadTimeDaysAdd"] = $this->leadTimeDaysAdd;
		$config["dateFormat"] = $this->dateFormat;
		$config["dateFormatOtherLanguage"] = $this->dateFormatOtherLanguage;
		$config["attribute"] = $this->attribute;
		$config["requiredProp"] = $this->requiredProp;

		return $config;
	}

	/**
	 * @return bool
	 */
	function validate(){
		//$value = $this->getValue();
		
		// 未入力の場合のvalidate
		return parent::validate();
	}

	/**
	 * 確認画面で呼び出す
	 */
	function getView(){
		return parent::getView();
	}

	/**
	 * データ投入用
	 */
	function getContent(){
		return parent::getContent();
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

	function getLinkagesSOYShopFrom() {
		return array(
			SOYShopConnector::SOYSHOP_NONE  	=> "連携しない",
			SOYShopConnector::SOYSHOP_NAME 		=> "名前",
			SOYShopConnector::SOYSHOP_READING 	=> "フリガナ",
			SOYShopConnector::SOYSHOP_NICKNAME	=> "ニックネーム",
			SOYShopConnector::SOYSHOP_TEL		=> "電話番号",
			SOYShopConnector::SOYSHOP_FAX		=> "FAX番号",
			SOYShopConnector::SOYSHOP_URL		=> "URL",
			SOYShopConnector::SOYSHOP_CELLPHONE	=> "携帯電話番号",
			SOYShopConnector::SOYSHOP_JOB_TEL	=> "勤務先電話番号",
			SOYShopConnector::SOYSHOP_JOB_FAX	=> "勤務先FAX番号",
			SOYShopConnector::SOYSHOP_JOB_NAME	=> "勤務先名称・職種",
			SOYShopConnector::SOYSHOP_ATTR1 	=> "属性A",
			SOYShopConnector::SOYSHOP_ATTR2 	=> "属性B",
			SOYShopConnector::SOYSHOP_ATTR3 	=> "属性C",
			SOYShopConnector::SOYSHOP_MEMO  	=> "備考"
		);
	}
}
