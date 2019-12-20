<?php

class CMSHTMLPageBase extends WebPage{

	/**
	 * Flashセッションを読み込む
	 */
    function &getFlashSession(){
    	$flashSession = SOY2ActionSession::getFlashSession();
    	return $flashSession;
    }

    function jump($path,$array = array()){
    	$flashSession = $this->getFlashSession();
    	$flashSession->clearAttributes();
    	$flashSession->resetFlashCounter();

    	foreach($array as $key => $value){
    		$flashSession->setAttribute($key,$value);
    	}

    	CMSMessageManager::save();

    	SOY2PageController::jump($path);
    }

    function reload($array = array()){
    	$flashSession = $this->getFlashSession();
    	$flashSession->clearAttributes();
    	$flashSession->resetFlashCounter();

    	foreach($array as $key => $value){
    		$flashSession->setAttribute($key,$value);
    	}

    	CMSMessageManager::save();

    	SOY2PageController::reload();
    }

    /**
     * SOY2ActionFactory::createInstanceのエイリアス
     *
     * @return SOY2ActionResult
     */
    function run($actionName,$array = array()){
    	return SOY2ActionFactory::createInstance($actionName,$array)->run();
    }

    /**
     * メッセージの追加のエイリアス
     */
    function addMessage($key,$replace = array()){
    	return CMSMessageManager::addMessage(
    		CMSMessageManager::get($key,$replace)
    	);
    }

    /**
     * エラーメッセージ追加のエイリアス
     */
    function addErrorMessage($key,$replace = array()){
    	return CMSMessageManager::addErrorMessage(
    		CMSMessageManager::get($key,$replace)
    	);
    }

    /**
     * メッセージ取得
     */
    function getMessage($key, $replace = array()){
    	return CMSMessageManager::get($key,$replace);
    }

    /**
     * Overwrite SOY2.HTMLPage::getTemplateFilePath
     */
    function getTemplateFilePath(){

    	/* Copy Start */
		$dir = dirname($this->getClassPath());
		if(strlen($dir) >0) $dir .= '/';

		$lang = SOY2HTMLConfig::Language();

		$lang_html = $dir . get_class($this) . "_" . $lang . ".html";
		$default_html = $dir . get_class($this) . ".html";
		/* End */

		//If language is not specified, return the default html.
		if(strlen($lang)<1){
			return $default_html;
		}

		//If language is specified and its SOY CMS html template exists, return it.
		if(defined("SOYCMS_LANGUAGE_DIR")){
			$soy2html_root = SOY2HTMLConfig::PageDir();
			$language_root = SOYCMS_LANGUAGE_DIR.SOY2HTMLConfig::Language()."/";
			$custom_lang_html = str_replace($soy2html_root, $language_root, $dir) . get_class($this) . ".html";

			if(file_exists($custom_lang_html)){
				return $custom_lang_html;
			}
		}

		//If language is specified and its SOY2HTML html template exists, return the template.
		if(strlen($lang)>0 && file_exists($lang_html)){
			return $lang_html;
		}

		return $default_html;
    }

    /**
     * main
     */
    function main(){
    	//default do nothing
    }

	/**
	 * Overwrite display
	 */
	function display(){

		$this->main();

		ob_start();
		parent::display();
		$html = ob_get_contents();
		ob_end_clean();

		echo $html;
	}

}

class CMSWebPageBase extends CMSHTMLPageBase{

	function prepare(){

		//ログインチェック
		if(!UserInfoUtil::isLoggined()){
			exit;
		}

		$flashSession = $this->getFlashSession();
		$keys = $flashSession->getAttributeKeys();

		foreach($keys as $key){

			if(method_exists($this,"set".strtoupper($key))){
				$method = "set".strtoupper($key);
				$this->$method($flashSession->getAttribute($key));
			}else{
				$this->$key = $flashSession->getAttribute($key);
			}
		}

		parent::prepare();

		$this->createAdd("wrapper","WrapperModel");
	}

}


class WrapperModel extends HTMLModel{

	function getStartTag(){
		return '<div style="text-align:center;">'.parent::getStartTag().
			"<div class=\"minwidth\"><div class=\"container\">";
	}

	function getEndTag(){
		return "</div></div>".parent::getEndTag().'</div>';
	}

}

/**
 * CSRF対策でトークン付きのフォームを生成し、doPost前にtokenチェックを行う
 * 編集対象のデータのサイトがログイン中のサイトかどうかのチェックも行なう
 */
class CMSUpdatePageBase extends CMSWebPageBase{

	const SOYCMS_TOKEN = "_soycms_token_";
	const SOYCMS_FORM = "_soycms_form_";
	const SOYCMS_SITE = "_soycms_site_";

	public function prepare(){

		//CSRF対策
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$inSite = defined("SOYCMS_CMS_DIR") && UserInfoUtil::getSite();
			if(
				empty($_POST[self::SOYCMS_TOKEN])
				OR empty($_POST[self::SOYCMS_FORM])
				OR !isset($_SESSION[self::SOYCMS_TOKEN][$this->getRequestUrl()][$_POST[self::SOYCMS_FORM]])
				OR $_POST[self::SOYCMS_TOKEN] != @$_SESSION[self::SOYCMS_TOKEN][$this->getRequestUrl()][$_POST[self::SOYCMS_FORM]]
				OR ( $inSite && $_POST[self::SOYCMS_SITE] != UserInfoUtil::getSiteId() )
			){
				SOY2PageController::reload();
			}
			unset($_SESSION[self::SOYCMS_TOKEN][$this->getRequestUrl()]);
		}

		parent::prepare();
	}

	protected function __destructor(){
		//SOY2ActionSession::regenerateSessionId();
	}

	/**
	 * @final
	 * トークン付きのフォームを生成する
	 */
	function addForm($form_soy_id, $attributes = array()){

		$token = md5(mt_rand());

		if(isset($attributes["action"])){
			$actionUrl = $attributes["action"];
			$actionUrl = preg_replace("/^([^\?]*)\??.*/", "$1", $actionUrl);
			$actionUrl = str_replace(SOY2PageController::createLink(""), "", $actionUrl);
			$actionUrl = str_replace("index.php/", "", $actionUrl);
			$actionUrl = str_replace("/", ".", $actionUrl);
		}else{
			$actionUrl = $this->getRequestUrl() ;
		}

		//ないときは初期化
		if(!isset($_SESSION[self::SOYCMS_TOKEN])){
			$_SESSION[self::SOYCMS_TOKEN] = array();
		}
		if(!isset($_SESSION[self::SOYCMS_TOKEN][$actionUrl])){
			$_SESSION[self::SOYCMS_TOKEN][$actionUrl] = array();
		}

		//トークンはURL別フォーム別に設定する
		$_SESSION[self::SOYCMS_TOKEN][$actionUrl][$form_soy_id] = $token;

		$form = SOY2HTMLFactory::createInstance("CMSUpdateForm", $attributes);
		$form->createAdd(self::SOYCMS_TOKEN, "HTMLHidden", array(
			"name"  => self::SOYCMS_TOKEN,
			"value" => $token
		));
		$form->createAdd(self::SOYCMS_FORM, "HTMLHidden", array(
			"name"  => self::SOYCMS_FORM,
			"value" => $form_soy_id
		));
		$inSite = defined("SOYCMS_CMS_DIR") && UserInfoUtil::getSite();
		$form->createAdd(self::SOYCMS_SITE, "HTMLHidden", array(
			"name"  => self::SOYCMS_SITE,
			"value" => $inSite ? UserInfoUtil::getSiteId() : "",
			"visible" =>$inSite
		));
		$this->add($form_soy_id, $form);
	}

	private function getRequestUrl(){
		$url = SOY2PageController::getRequestPath();
		$args = SOY2PageController::getArguments();
		if(is_array($args) AND count($args) >0) $url .= "." . implode(".",$args);

		return $url;
	}
}

/**
 * トークンのためのinput[type=hidden]を追加したフォーム
 */
class CMSUpdateForm extends HTMLForm{

	function setContent($content){
		parent::setContent($content);
		$this->_soy2_innerHTML =
			'<input soy:id="'.CMSUpdatePageBase::SOYCMS_TOKEN.'" />'//トークン
			.'<input soy:id="'.CMSUpdatePageBase::SOYCMS_FORM.'" />'//フォームID
			.'<input soy:id="'.CMSUpdatePageBase::SOYCMS_SITE.'" />'//サイトID
			.$this->_soy2_innerHTML;
	}

}
?>
