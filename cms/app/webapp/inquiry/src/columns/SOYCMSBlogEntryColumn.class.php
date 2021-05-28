<?php

class SOYCMSBlogEntryColumn extends SOYInquiry_ColumnBase{

	//公開側で記事の編集を有りにするか？
	private $isEditable;

	//フォームに自由に挿入する属性
	private $attribute;

	//HTML5のrequired属性を利用するか？
	private $requiredProp = false;

    /**
	 * ユーザに表示する用のフォーム
	 */
	function getForm($attr = array()){
		$title = $this->getValue();
		if(is_null($title)) $title = self::_getEntryTitle();
		if(strlen($title)) $title = htmlspecialchars($title, ENT_QUOTES, "UTF-8");

		//ページIDを記録しておく
		$dust = SOYInquiryUtil::getParameter("page_id");

		$html = array();
		if($this->isEditable){
			$attributes = self::_getAttributes();
			$required = self::_getRequiredProp();

			foreach($attr as $key => $value){
				$attributes[] = htmlspecialchars($key, ENT_QUOTES, "UTF-8") . "=\"".htmlspecialchars($value, ENT_QUOTES, "UTF-8")."\"";
			}

			$html[] = "<input type=\"text\" name=\"data[" . $this->getColumnId() . "]\" value=\"" . $title . "\" " . implode(" ",$attributes) . "" . $required . ">";
		}else{
			$html[] = $title;
			$html[] = "<input type=\"hidden\" name=\"data[" . $this->getColumnId() . "]\" value=\"" . $title . "\"><br>";
		}

		return implode("\n", $html);
	}

	private function _getEntryTitle(){
		$entryId = SOYInquiryUtil::getParameter("entry_id");
		if(!is_numeric($entryId)) return null;

		CMSApplication::switchAdminMode();

		$siteId = SOYInquiryUtil::getParameter("site_id");
		if(is_null($siteId)){
			$siteAlias = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
			try{
				$siteId = SOY2DAOFactory::create("admin.SiteDAO")->getBySiteId($siteAlias)->getId();
				SOYInquiryUtil::setParameter("site_id", $siteId);
			}catch(Exception $e){
				//
			}
		}

		try{
			$site = SOY2DAOFactory::create("admin.SiteDAO")->getById($siteId);
		}catch(Exception $e){
			$site = new Site();
		}

		$old["dsn"] = SOY2DAOConfig::dsn();
		$old["user"] = SOY2DAOConfig::user();
		$old["pass"] = SOY2DAOConfig::pass();

		SOY2DAOConfig::dsn($site->getDataSourceName());
		if(strpos($site->getDataSourceName(), "mysql") === 0){
			include_once(_CMS_COMMON_DIR_ . "/config/db/mysql.php");
			SOY2DAOConfig::user(ADMIN_DB_USER);
			SOY2DAOConfig::pass(ADMIN_DB_PASS);
		}

		try{
			$title = SOY2DAOFactory::create("cms.EntryDAO")->getOpenEntryById($entryId, time())->getTitle();
		}catch(Exception $e){
			$title = null;
		}

		SOY2DAOConfig::dsn($old["dsn"]);
		SOY2DAOConfig::user($old["user"]);
		SOY2DAOConfig::pass($old["pass"]);

		CMSApplication::switchAppMode();

		return $title;
	}

	private function _getAttributes(){
		$attributes = array();

		//設定したattributeを挿入
		if(isset($this->attribute) && strlen($this->attribute) > 0){
			$attribute = str_replace("&quot;","\"",$this->attribute);	//"が消えてしまうから、htmlspecialcharsができない
			$attributes[] = trim($attribute);
		}

		return $attributes;
	}

	private function _getRequiredProp(){
		return (!SOYINQUIRY_FORM_DESIGN_PAGE && $this->requiredProp) ? " required" : "";
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		//ブログの記事名の編集を可能にするフォームを出力するか？
		$html = "";

		$html .= '<label><input type="checkbox" name="Column[config][isEditable]" value="1"';
		if($this->isEditable){
			$html .= ' checked';
		}
		$html .= '>テキストフォームでブログ記事のタイトルの形式にする</label>';

		$attribute = trim($this->attribute);

		$html .= '<label for="Column[config][attribute]'.$this->getColumnId().'">属性:</label>';
		$html .= '<input id="Column[config][attribute]'.$this->getColumnId().'" name="Column[config][attribute]" type="text" value="'.$attribute.'" style="width:90%;" /><br />';
		$html .= "※記述例：class=\"sample\" title=\"サンプル\" placeholder=\"\" pattern=\"\"<br>";

		$html .= '<label><input type="checkbox" name="Column[config][requiredProp]" value="1"';
		if($this->requiredProp){
			$html .= ' checked';
		}
		$html .= '>required属性を利用する</label>';

		$html .= "<br><br>このカラムの使用方法は";
		$html .= "<a href=\"https://saitodev.co/article/3754/\" target=\"_blank\" rel=\"noopener\">SOY CMSでSOY Inquiry連携プラグインを作成しました - saitodev.co</a>";
		$html .= "をご覧ください。<br>";


		return $html;
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);
		$this->isEditable = (isset($config["isEditable"])) ? $config["isEditable"] : null;
		$this->attribute = (isset($config["attribute"])) ? str_replace("\"","&quot;",$config["attribute"]) : null;
		$this->requiredProp = (isset($config["requiredProp"])) ? $config["requiredProp"] : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["isEditable"] = $this->isEditable;
		$config["attribute"] = $this->attribute;
		$config["requiredProp"] = $this->requiredProp;
		return $config;
	}

	function validate(){}

	function getView(){
		return htmlspecialchars((string)$this->getValue(), ENT_QUOTES, "UTF-8");
	}
}
