<?php
/**
 * @param string
 * @param HTMLPage
 * @return string
 */
function soycms_form(string $html, HTMLPage $htmlObj){
	SOY2::import("site_include.plugin.reviewing_entries.util.REUtil");
	// URIにトークンがある場合
	$token = "";
	if (preg_match('/[a-f0-9]{32}/i', $_SERVER["REQUEST_URI"], $tmp)) {
		$token = $tmp[0];
	}
	$isSetToken = false;
	if(strlen($token)){
		if(isset($_GET["deleted"])){
			$f = "Finished";
		}else{
			// トークンが期限切れか調べる
			$tokenObj = REUtil::getByToken($token);
			if(is_numeric($tokenObj->getReviewId()) && REUtil::getDateOfExpiry($tokenObj->getReviewId()) > time()){
				$f = "Delete";
				$isSetToken = true;
			}else{	//期限切れ
				$f = "Expired";
			}
		}
	}else{
		$f = REUtil::getPage();
	}
	SOY2::import("site_include.plugin.reviewing_entries.pages.RE".$f."Page");
	$form = SOY2HTMLFactory::createInstance("RE".$f."Page");
	if(strlen($isSetToken)) $form->setToken($token);
	$form->execute();
	return $form->getObject();
}
