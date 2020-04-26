<?php

/**
 * SOY CMSのBlogページの記事詳細ページで使うブロック
 */
class SOYCMSBlogEntryPageColumn extends SOYInquiry_ColumnBase{

	//連携用のcms:id
	private $cms_id;

    /**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		//エラーで入力画面に戻って来たときはすでに値が入っている
		$value = $this->getValue();

		//なければ取得
		if(!strlen($value)){
			//ReflectionClassを使って無理矢理createAddされた値を取得する
			$pageCont = SOY2PageController::init();
			if(property_exists($pageCont, "webPage")){
				$values = $this->getPrivateProperty($pageCont->webPage, "_soy2_page");

				//b_block:id="entry"から値を取得する
				if(is_array($values) && isset($values["entry"]) && is_array($values["entry"]) && isset($values["entry"][$this->cms_id])){
					$value = $values["entry"][$this->cms_id];
				}
			}
		}

		$html = array();
		$html[] = htmlspecialchars($value, ENT_QUOTES, "UTF-8");
		$html[] = "<input type=\"hidden\" name=\"data[".$this->getColumnId()."]\" value=\"".htmlspecialchars($value, ENT_QUOTES, "UTF-8")."\" />";
		return implode("\n",$html);
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html = "";
		$html .= '<label for="Column[config][cms_id]'.$this->getColumnId().'">カスタムフィールドのID（cms:id）:</label>';
		$html .= '<input  id="Column[config][cms_id]'.$this->getColumnId().'" name="Column[config][cms_id]" type="text" value="'.$this->cms_id.'" size="40"/>';
		return $html;
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);

		$this->cms_id = isset($config["cms_id"]) ? $config["cms_id"] : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["cms_id"] = $this->cms_id;
		return $config;
	}

	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE  	=> "連携しない",
			SOYMailConverter::SOYMAIL_ATTR1 	=> "属性A",
			SOYMailConverter::SOYMAIL_ATTR2 	=> "属性B",
			SOYMailConverter::SOYMAIL_ATTR3 	=> "属性C",
			SOYMailConverter::SOYMAIL_MEMO  	=> "備考"
		);
	}

	/**
	 * PrivateまたはProtectedなプロパティの値を取得する
	 */
	private function getPrivateProperty($object, $propertyName){
		$values = array();

		try{
			if(version_compare(PHP_VERSION, "5.3", ">=")&&false){
				$ref = new ReflectionClass($object);
				$prop = $ref->getProperty($propertyName);
				$prop->setAccessible(true);// PHP 5.3 or higher
				$values = $prop->getValue($object);
			}else{
				$className = get_class($object);
				$childClassName = $className."__child__";
				$propNameLength = strlen($propertyName);
				$copy = unserialize(strtr(serialize($object), array(
					"O:".strlen($className).":\"".$className."\"" => "O:8:\"StdClass\"",
					"s:".(strlen($className) + $propNameLength + 2).":\"\0".$className."\0".$propertyName."\"" => "s:".$propNameLength.":\"".$propertyName."\"",
					"s:".($propNameLength + 3).":\"\0*\0".$propertyName."\"" => "s:".$propNameLength.":\"".$propertyName."\"",
				)));
				$values = $copy->$propertyName;
			}
		}catch(Exception $e){
			//
		}
		return $values;
	}
}
