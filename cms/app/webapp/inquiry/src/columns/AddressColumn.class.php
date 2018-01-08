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

		$value = $this->getValue();

		$html[] = "郵便番号を入力して「住所検索」ボタンをクリックすると該当する住所が自動で入力されます。";

		$html[] = '<table class="soyinquiry_address_form" cellspacing="0" cellpadding="5" border="0" '. implode(" ",$attributes) .'>';
		$html[] = '<tbody><tr>';
		$html[] = '<td width="70">郵便番号：<br/></td>';
		$html[] = '<td><input type="text" size="7" class="soyinquiry_address_zip1" name="data['.$this->getColumnId().'][zip1]" value="'.htmlspecialchars($value["zip1"], ENT_QUOTES, "UTF-8").'"' . $required . '>';
		$html[] = '-';
		$html[] = '<input type="text" size="7" class="soyinquiry_address_zip2" name="data['.$this->getColumnId().'][zip2]" value="'.htmlspecialchars($value["zip2"], ENT_QUOTES, "UTF-8").'"' . $required . '>';
		$html[] = '<input type="submit" name="test" value="住所検索"/></td>';
		$html[] = '</tr>';
		$html[] = '<tr>';
		$html[] = '<td>都道府県：</td>';
		$html[] = '<td><select class="soyinquiry_address_prefecture" name="data['.$this->getColumnId().'][prefecture]">';
		$html[] = '<option value="">選択してください</option>';
		foreach($this->prefecture as $id => $pref){
			if($pref == $value["prefecture"]){
				$html[] ="<option selected=\"selected\">".$pref."</option>";
			}else{
				$html[] ="<option>".$pref."</option>";
			}
		}
		$html[] = '</select></td></tr>';

		$html[] = '<tr>
					<td>市区町村：</td>
					<td><input class="soyinquiry_address_input1" type="text" size="37" name="data['.$this->getColumnId().'][address1]" value="'.htmlspecialchars($value["address1"], ENT_QUOTES, "UTF-8").'"></td>
				</tr>';
		$html[] = '<tr>
					<td>番地：</td>
					<td><input class="soyinquiry_address_input2" type="text" size="37" name="data['.$this->getColumnId().'][address2]" value="'.htmlspecialchars($value["address2"], ENT_QUOTES, "UTF-8").'"></td>
				</tr>';
		$html[] = '<tr>
					<td colspan="2">建物名・部屋番号：
					<input class="soyinquiry_address_input3" type="text" size="37" name="data['.$this->getColumnId().'][address3]" value="'.htmlspecialchars($value["address3"], ENT_QUOTES, "UTF-8").'" /></td>
				</tr>';
		$html[] = '</tbody></table>';

		return implode("\n",$html);

	}

	function getRequiredProp(){
		return (!SOYINQUIRY_FORM_DESIGN_PAGE && $this->requiredProp) ? " required" : "";
	}

	function validate(){
		$value = $this->getValue();

		if(!isset($_POST["test"]) && $this->getIsRequire()){
			if(
				   empty($value)
				|| @$value["zip1"] == ""
				|| @$value["zip2"] == ""
				|| @$value["prefecture"] == ""
				|| @$value["address1"] == ""
				|| @$value["address2"] == ""
			){
				$this->errorMessage = "住所を入力してください。";
				return false;
			}
		}

		if(empty($value)){
			$value["zip1"] = "";
			$value["zip2"] = "";
			$value["prefecture"] = "";
			$value["address1"] = "";
			$value["address2"] = "";
			$value["address3"] = "";
			$this->setValue($value);
			return true;
		}

		if(!empty($value["zip2"]) && !is_numeric($value["zip1"])){
			$this->errorMessage = "郵便番号の書式が不正です。";
			return false;
		}

		if(!empty($value["zip2"]) && !is_numeric($value["zip2"])){
			$this->errorMessage = "郵便番号の書式が不正です。";
			return false;
		}
		if(isset($_POST["test"])){

			$logic = SOY2Logic::createInstance("logic.AddressSearchLogic");
			$res = $logic->search($value["zip1"],$value["zip2"]);

			$value["prefecture"] = $res["prefecture"];
			$value["address1"] = $res["address1"];
			$value["address2"] = $res["address2"];

			$this->setValue($value);
		}
	}

	function getErrorMessage(){
		return $this->errorMessage;
	}

	/**
	 * 確認画面で呼び出す
	 */
	function getView($html = true){
		$value = $this->getValue();
		if(empty($value)){
			return "";
		}

		$address = $value["zip1"]  ."-" . $value["zip2"] . "\n" .
		           $value["prefecture"] . $value["address1"] . $value["address2"];
		if(strlen($value["address3"])) $address.= "\n" . $value["address3"];

		$address = htmlspecialchars($address, ENT_QUOTES, "UTF-8");

		if($html) $address = nl2br($address);

		return $address;
	}

	/**
	 * データ投入用
	 *
	 */
	function getContent(){
		$address = $this->getView(false);
		return $address;
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
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->requiredProp = (isset($config["requiredProp"])) ? $config["requiredProp"] : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["requiredProp"] = $this->requiredProp;
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
}
