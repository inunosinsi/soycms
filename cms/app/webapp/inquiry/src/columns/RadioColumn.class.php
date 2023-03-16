<?php

class RadioColumn extends SOYInquiry_ColumnBase{

	//項目
	private $items;

	//フォームに挿入するクラス
	private $style;	//1.0.1からclassのみ指定は廃止されるが、1.0.0以前から使用しているユーザのために残しておく

	//フォームに自由に挿入する属性
	private $attribute;

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;

	//公開側で各項目毎に改行の<br>を加えるか？
	private $isBr = false;

	//各項目にサムネイルを設定する
	private $isThumbnail = false;
	private $thumbWidth = SOYInquiry_ColumnBase::THUMBNAIL_WIDTH;

	/**
	 * ユーザに表示するようのフォーム
	 * @param array
	 * @return string
	 */
	function getForm(array $attrs=array()){

		$items = explode("\n",$this->items);
		$value = $this->getValue();
		$required = $this->getRequiredProp();

		$__attributes = $this->getAttributes();//HTML

		$html = array();
		foreach($items as $key => $item){

			$attributes = $__attributes;

			$item = trim($item);
			if(strlen($item) < 1) continue;

			$checked = "";

			if($item[0] == "*"){
				$item = substr($item, 1);
				if(!$value){
					$checked = 'checked="checked"';
				}
			}

			if($value == $item){
				$checked = 'checked="checked"';
			}

			$radioReq = ($key == 0) ? $required : "";
			if(strlen($radioReq) && SOYInquiryUtil::checkIsParsley()) $radioReq .= " data-parsley-errors-container=\"#parsley-error-" . $this->getColumnId() . "\" ";

			$html[] = "<label>";

			//サムネイル @ToDo 表示方法は要検討
			if($this->isThumbnail){
				$path = SOYInquiryUtil::getThumbnailFilePath($this->getFormId(), $this->getColumnId(), $key, $item);
				if(file_exists($path)) $html[] = "<img src=\"" . SOYInquiryUtil::getThumbnailSrc($path, $this->thumbWidth) . "\">";
			}

			$html[] = "<input type=\"radio\" id=\"data_".$this->getColumnId() . "_" . $key. "\" name=\"data[".$this->getColumnId()."]\" value=\"".$item."\" " . implode(" ",$attributes). " ". $checked . " " . $radioReq . ">";
			$html[] = $item;
			$html[] = "</label>";

			if($this->isBr) $html[] = "<br>";
		}
		if(SOYInquiryUtil::checkIsParsley()) $html[] = "<span id=\"parsley-error-" . $this->getColumnId() . "\"></span>";

		return implode("\n",$html);
	}

	function getAttributes(){
		$attributes = array();

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
		$html = "項目を1行ずつを設定して下さい：<br>";
		$html.= '<textarea type="text" name="Column[config][items]" style="height:100px;padding:0;">'.$this->items.'</textarea>';
		$html.= '<p>初期値として選択される項目がある場合、項目の前に[*]を入力して下さい。</p>';

		$checked = ($this->isBr) ? " checked=\"checked\"" : "";
		$html .= "<label><input type=\"checkbox\" name=\"Column[config][isBr]\" value=\"1\"" . $checked. "> 各項目毎に改行コード&lt;br&gt;を追加する。</label><br>";

		$checked = ($this->isThumbnail) ? " checked=\"checked\"" : "";
		$html .= "<label><input type=\"checkbox\" name=\"Column[config][isThumbnail]\" value=\"1\"" . $checked. "> 各項目毎にサムネイルを設定する</label>";
		if($this->isThumbnail){	//サムネイルに関する説明文
			$html .= "<br>";
			$html .= "サムネイルのサイズ(width):<input type=\"number\" name=\"Column[config][thumbWidth]\" value=\"" . (int)$this->thumbWidth . "\" style=\"width:60px;\">&nbsp;px<br>";
			$items = explode("\n", trim((string)$this->items));
			if(count($items)){
				$html .= "下記のように画像ファイルを配置します。<br>";
				$html .= SOYInquiryUtil::buildThumbnailPathListTable($items, $this->getFormId(), $this->getColumnId());
			}else{
				$html .= "<strong style=\"color:red;\">先に項目の設定を行ってください。</strong>";
			}	
		}

		$html .= "<br><br>";

		if(is_null($this->attribute) && isset($this->style)){
			$attribute = "class=&quot;".htmlspecialchars($this->style,ENT_QUOTES,"UTF-8")."&quot;";
		}else{
			$attribute = trim((string)$this->attribute);
		}

		$html .= '<label for="Column[config][attribute]'.$this->getColumnId().'">属性:</label>';
		$html .= '<input  id="Column[config][attribute]'.$this->getColumnId().'" name="Column[config][attribute]" type="text" value="'.$attribute.'" style="width:90%;" /><br />';
		$html .= "※記述例：class=\"sample\" title=\"サンプル\"<br>";

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
		$this->items = (isset($config["items"])) ? $config["items"] : "*項目１\n項目２\n項目３";
		$this->style = (isset($config["style"])) ? $config["style"] : null ;
		$this->attribute = (isset($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : null;
		$this->requiredProp = (isset($config["requiredProp"])) ? $config["requiredProp"] : null;
		$this->isBr = (isset($config["isBr"]) && $config["isBr"] == 1);
		$this->isThumbnail = (isset($config["isThumbnail"]) && $config["isThumbnail"] == 1);
		$this->thumbWidth = (isset($config["thumbWidth"]) && is_numeric($config["thumbWidth"])) ? (int)$config["thumbWidth"] : SOYInquiry_ColumnBase::THUMBNAIL_WIDTH;
	}
	function getConfigure(){
		$config = parent::getConfigure();
		$config["items"] = $this->items;
		$config["style"] = $this->style;
		$config["attribute"] = $this->attribute;
		$config["requiredProp"] = $this->requiredProp;
		$config["isBr"] = $this->isBr;
		$config["isThumbnail"] = $this->isThumbnail;
		$config["thumbWidth"] = $this->thumbWidth;
		return $config;
	}

	function validate(){
		if(!$this->getIsRequire()) return true;
		$value = (is_string($this->getValue())) ? trim($this->getValue()) : "";
		
		if(strlen($value)<1){
			switch(SOYCMS_PUBLISH_LANGUAGE){
				case "en":
					$msg = "Please choose one from the ".$this->getLabel().".";
					break;
				default:
					$msg = $this->getLabel()."から1つ選んでください。";
			}
			$this->setErrorMessage($msg);
			return false;
		}
		return true;
	}

	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE		=> "連携しない",
			SOYMailConverter::SOYMAIL_GENDER	=> "性別",
			SOYMailConverter::SOYMAIL_ATTR1 => "属性A",
			SOYMailConverter::SOYMAIL_ATTR2 => "属性B",
			SOYMailConverter::SOYMAIL_ATTR3 => "属性C",
			SOYMailConverter::SOYMAIL_MEMO  => "備考"
		);
	}

	function factoryConverter() {
		return new RadioConverter();
	}
}
