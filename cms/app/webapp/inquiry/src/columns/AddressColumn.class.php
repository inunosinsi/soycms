<?php

class AddressColumn extends SOYInquiry_ColumnBase{

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

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;

	//住所フォームを分割するか？
	private $zipDivide = true;

	/**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		$attributes = array();
		foreach($attr as $key => $value){
			$attributes[] = htmlspecialchars($key, ENT_QUOTES, "UTF-8") . "=\"".htmlspecialchars($value, ENT_QUOTES, "UTF-8")."\"";
		}
		$required = $this->getRequiredProp();

		$html = array();

		$values = $this->getValue();

		$html[] = "郵便番号を入力して「住所検索」ボタンをクリックすると該当する住所が自動で入力されます。";

		$html[] = '<table class="soyinquiry_address_form" cellspacing="0" cellpadding="5" border="0" '. implode(" ",$attributes) .'>';
		$html[] = '<tbody><tr>';
		$html[] = '<td width="70">郵便番号：<br/></td>';
		$html[] = '<td>';

		if($this->zipDivide){
			$zip1 = (isset($values["zip1"])) ? htmlspecialchars($values["zip1"], ENT_QUOTES, "UTF-8") : null;
			$zip2 = (isset($values["zip2"])) ? htmlspecialchars($values["zip2"], ENT_QUOTES, "UTF-8") : null;
			$html[] = '<input type="text" size="7" class="soyinquiry_address_zip1" name="data['.$this->getColumnId().'][zip1]" value="'.$zip1.'"' . $required . '>';
			$html[] = '-';
			$html[] = '<input type="text" size="7" class="soyinquiry_address_zip2" name="data['.$this->getColumnId().'][zip2]" value="'.$zip2.'"' . $required . '>';
		}else{
			$zip = (isset($values["zip"])) ? htmlspecialchars($values["zip"], ENT_QUOTES, "UTF-8") : null;
			$html[] = '<input type="text" size="10" class="soyinquiry_address_zip1" name="data['.$this->getColumnId().'][zip]" value="'.$zip.'"' . $required . '>';
		}
		$html[] = '<input type="submit" name="test" value="住所検索"/></td>';
		$html[] = '</tr>';
		$html[] = '<tr>';
		$html[] = '<td>都道府県：</td>';
		$html[] = '<td><select class="soyinquiry_address_prefecture" name="data['.$this->getColumnId().'][prefecture]">';
		$html[] = '<option value="">選択してください</option>';
		foreach($this->prefecture as $id => $pref){
			if(is_array($values) && $pref == $values["prefecture"]){
				$html[] ="<option selected=\"selected\">".$pref."</option>";
			}else{
				$html[] ="<option>".$pref."</option>";
			}
		}
		$html[] = '</select></td></tr>';

		$addr1 = (isset($values["address1"])) ? htmlspecialchars($values["address1"], ENT_QUOTES, "UTF-8") : "";
		$addr2 = (isset($values["address2"])) ? htmlspecialchars($values["address2"], ENT_QUOTES, "UTF-8") : "";
		$addr3 = (isset($values["address3"])) ? htmlspecialchars($values["address3"], ENT_QUOTES, "UTF-8") : "";
		$html[] = '<tr>
					<td>市区町村：</td>
					<td><input class="soyinquiry_address_input1" type="text" size="37" name="data['.$this->getColumnId().'][address1]" value="'.$addr1.'"></td>
				</tr>';
		$html[] = '<tr>
					<td>番地：</td>
					<td><input class="soyinquiry_address_input2" type="text" size="37" name="data['.$this->getColumnId().'][address2]" value="'.$addr2.'"></td>
				</tr>';
		$html[] = '<tr>
					<td colspan="2">建物名・部屋番号：
					<input class="soyinquiry_address_input3" type="text" size="37" name="data['.$this->getColumnId().'][address3]" value="'.$addr3.'" /></td>
				</tr>';
		$html[] = '</tbody></table>';

		return implode("\n",$html);

	}

	function getRequiredProp(){
		return (!SOYINQUIRY_FORM_DESIGN_PAGE && $this->requiredProp) ? " required" : "";
	}

	function validate(){
		$values = $this->getValue();

		if(isset($values["zip"])){
			$zip = trim($values["zip"]);
		}else if(isset($values["zip1"])){
			$zip = trim($values["zip1"] . $values["zip2"]);
		}


		if(!isset($_POST["test"]) && $this->getIsRequire()){
			if(
				   empty($values)
				|| $zip == ""
				|| @$values["prefecture"] == ""
				|| @$values["address1"] == ""
				|| @$values["address2"] == ""
			){
				$this->errorMessage = "住所を入力してください。";
				return false;
			}
		}

		if(empty($values)){
			$zip = "";
			$values["prefecture"] = "";
			$values["address1"] = "";
			$values["address2"] = "";
			$values["address3"] = "";
			$this->setValue($values);
			return true;
		}

		if(!empty($zip)){
			list($zip1, $zip2) = self::divideZipCode($zip);
			if(!is_numeric($zip1.$zip2)){
				$this->errorMessage = "郵便番号の書式が不正です。";
				return false;
			}
		}

		if(isset($_POST["test"])){
			if(!$this->zipDivide){
				list($zip1, $zip2) = self::divideZipCode($values["zip"]);
				$values["zip1"] = $zip1;
				$values["zip2"] = $zip2;
			}
			$res = SOY2Logic::createInstance("logic.AddressSearchLogic")->search($values["zip1"],$values["zip2"]);

			$values["prefecture"] = $res["prefecture"];
			$values["address1"] = $res["address1"];
			$values["address2"] = $res["address2"];

			$this->setValue($values);
		}
	}

	function getErrorMessage(){
		return $this->errorMessage;
	}

	/**
	 * 確認画面で呼び出す
	 */
	function getView($html = true){
		$values = $this->getValue();
		if(empty($values)){
			return "";
		}

		if(!$this->zipDivide){
			list($zip1, $zip2) = self::divideZipCode($values["zip"]);
			$values["zip1"] = $zip1;
			$values["zip2"] = $zip2;
		}
		$address = $values["zip1"]  ."-" . $values["zip2"] . "\n" .
		           $values["prefecture"] . $values["address1"] . $values["address2"];
		if(strlen($values["address3"])) $address.= "\n" . $values["address3"];

		$address = htmlspecialchars($address, ENT_QUOTES, "UTF-8");
		if($html) $address = nl2br($address);
		return $address;
	}

	/**
	 * データ投入用
	 *
	 */
	function getContent(){
		return $this->getView(false);
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = '<label><input type="checkbox" name="Column[config][zipDivide]" value="1"';
		if($this->zipDivide){
			$html .= ' checked';
		}
		$html .= '>郵便番号フォームを分割する</label><br>';
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
		$this->requiredProp = (isset($config["requiredProp"])) ? $config["requiredProp"] : null;
		$this->zipDivide = (isset($config["zipDivide"])) ? $config["zipDivide"] : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["requiredProp"] = $this->requiredProp;
		$config["zipDivide"] = $this->zipDivide;
		return $config;
	}

	function factoryConnector(){
		return new AddressConnector();
	}

	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE			=> "連携しない",
			SOYMailConverter::SOYMAIL_ADDRESS 		=> "住所",
			SOYMailConverter::SOYMAIL_JOBADDRESS	=> "勤務先住所",
			SOYMailConverter::SOYMAIL_MEMO 			=> "備考"
		);
	}

	function getLinkagesSOYShopFrom() {
		return array(
			SOYShopConnector::SOYSHOP_NONE			=> "連携しない",
			SOYShopConnector::SOYSHOP_ADDRESS		=> "住所",
			SOYShopConnector::SOYSHOP_JOBADDRESS	=> "勤務先住所"
		);
	}

	function factoryConverter() {
		return new AddressConverter();
	}

	function getReplacement() {
		return (strlen($this->replacement) == 0) ? "#ADDRESS#" : $this->replacement;
	}

	private function divideZipCode($zip){
		$zip = trim(mb_convert_kana($zip, "a"));
		$zip = str_replace(array("-", "ー"), "", $zip);
		$zip1 = (strlen($zip) > 3) ? substr($zip, 0, 3) : $zip;
		$zip2 = (strlen($zip) > 3) ? substr($zip, 3) : "";
		return array($zip1, $zip2);
	}
}
