<?php

/**
 * CSSを保存する
 * 
 * file_put_contentsは成功したらバイト数を返すのでその前でチェックする必要がある。
 * 
 */
class SaveCSSAction extends SOY2Action {

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){
		$contents = $form->css_editor;
		$address = $form->css_list;
		
		$address = UserInfoUtil::url2serverpath($address);
		
		$dao = SOY2DAOFactory::create("cms.SiteConfigDAO");
		$siteConfig = $dao->get();
		$contents = $siteConfig->convertToSiteCharset($contents);
		
		if(!file_exists($address)){
			return SOY2Action::FAILED;
		}
		
		if(strlen($contents) < 1){
			return SOY2Action::FAILED;
		}
		
		if(!file_put_contents($address,$contents)){
			return SOY2Action::FAILED;
		}
		
			
		return SOY2Action::SUCCESS;
    }
}

class SaveCSSActionForm extends SOY2ActionForm {
	var $css_editor;
	var $css_list;
	
	function setCss_editor($css){
		$this->css_editor = $css;
	}
	
	function setCss_list($addr){
		$this->css_list = $addr;
	}
}
?>