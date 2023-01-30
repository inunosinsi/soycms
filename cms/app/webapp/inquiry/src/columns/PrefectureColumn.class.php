<?php

class PrefectureColumn extends SOYInquiry_ColumnBase{

	private $prefecture = array(
			"1" => "北海道",
			"2" => "青森県",
			"3" => "岩手県",
			"4" => "宮城県",
			"5" => "秋田県",
			"6" => "山形県",
			"7" => "福島県",
			"8" => "茨城県",
			"9" => "栃木県",
			"10" => "群馬県",
			"11" => "埼玉県",
			"12" => "千葉県",
			"13" => "東京都",
			"14" => "神奈川県",
			"15" => "新潟県",
			"16" => "富山県",
			"17" => "石川県",
			"18" => "福井県",
			"19" => "山梨県",
			"20" => "長野県",
			"21" => "岐阜県",
			"22" => "静岡県",
			"23" => "愛知県",
			"24" => "三重県",
			"25" => "滋賀県",
			"26" => "京都府",
			"27" => "大阪府",
			"28" => "兵庫県",
			"29" => "奈良県",
			"30" => "和歌山県",
			"31" => "鳥取県",
			"32" => "島根県",
			"33" => "岡山県",
			"34" => "広島県",
			"35" => "山口県",
			"36" => "徳島県",
			"37" => "香川県",
			"38" => "愛媛県",
			"39" => "高知県",
			"40" => "福岡県",
			"41" => "佐賀県",
			"42" => "長崎県",
			"43" => "熊本県",
			"44" => "大分県",
			"45" => "宮崎県",
			"46" => "鹿児島県",
			"47" => "沖縄県",
			"48" => "その他・海外",
	);

	private $size;

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;


    /**
	 * ユーザに表示するようのフォーム
	 * @param array
	 * @return string
	 */
	function getForm(array $attrs=array()){

		$attributes = array();
		if($this->size)$attributes[] = "size=\"".$this->size."\"";

		$required = $this->getRequiredProp();

		foreach($attrs as $key => $value){
			$attributes[] = htmlspecialchars($key) . "=\"".htmlspecialchars($value)."\"";
		}

		$html = array();
		$html[] = "<select name=\"data[".$this->getColumnId()."]\" " . implode(" ",$attributes) . "" . $required . ">";
		$html[] ="<option value=\"\">----</option>";

		$my_value = $this->getValue();

		foreach($this->prefecture as $id => $value){
			if($value == $my_value){
				$html[] ="<option selected=\"selected\">".$value."</option>";
			}else{
				$html[] ="<option>".$value."</option>";
			}
		}

		$html[] = "</select>";

		return implode("\n",$html);
	}

	function getRequiredProp(){
		return (!SOYINQUIRY_FORM_DESIGN_PAGE && $this->requiredProp) ? " required" : "";
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = '<label><input type="checkbox" name="Column[config][requiredProp]" value="1"';
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
		$this->size = (isset($config["size"]) && is_numeric($config["size"])) ? (int)$config["size"] : null;
		$this->requiredProp = (isset($config["requiredProp"])) ? $config["requiredProp"] : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["size"] = $this->size;
		$config["requiredProp"] = $this->requiredProp;
		return $config;
	}

	function validate(){
		if(!$this->getIsRequire()) return true;

		if(!strlen($this->getValue())){
			switch(SOYCMS_PUBLISH_LANGUAGE){
				case "en":
					$msg = "Please select the ".$this->getLabel().".";
					break;
				default:
					$msg = $this->getLabel() . "を選んでください。";
			}
			$this->setErrorMessage($msg);
			return false;
		}
		return true;
	}

	function factoryConnector(){
		return new AddressConnector();
	}

	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE  => "連携しない",
			SOYMailConverter::SOYMAIL_AREA	=> "都道府県",
			SOYMailConverter::SOYMAIL_ATTR1 => "属性A",
			SOYMailConverter::SOYMAIL_ATTR2 => "属性B",
			SOYMailConverter::SOYMAIL_ATTR3 => "属性C",
			SOYMailConverter::SOYMAIL_MEMO  => "備考"
		);
	}

	function getLinkagesSOYShopFrom() {
		return array(
			SOYShopConnector::SOYSHOP_NONE  	=> "連携しない",
			SOYShopConnector::SOYSHOP_AREA		=> "都道府県",
			SOYShopConnector::SOYSHOP_JOB_AREA	=> "勤務先都道府県"
		);
	}

	function getReplacement() {
		if(!is_string($this->replacement)) $this->replacement = "";
		return (strlen($this->replacement) == 0) ? "#PREFECTURE#" : $this->replacement;
	}
}
