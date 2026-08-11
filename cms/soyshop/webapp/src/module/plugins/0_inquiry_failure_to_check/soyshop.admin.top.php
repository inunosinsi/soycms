<?php
class FailureToCheckAdminTop extends SOYShopAdminTopBase{

	function getLink(){
		return "";
	}

	function getLinkTitle(){
		return "";
	}

	function getTargetBlank(){
		return false;
	}

	function getTitle(){
		if(self::_isUnconfirmed()){
			return "SOY Inquiryからの通知";
		}else{
			return null;
		}
	}

	function getContent(){
		if(self::_isUnconfirmed()){
			return "<div class=\"alert alert-danger\">未確認のお問い合わせがあります。</div>";
		}else{
			return null;
		}
	}

	/** 
	 * お問い合わせの確認状況を調べる
	 * @return bool
	 */
	private function _isUnconfirmed(){
		static $isUnconfirmed;
		if(is_bool($isUnconfirmed)) return $isUnconfirmed;
		if(!class_exists("SOYAppUtil")) SOY2::import("util.SOYAppUtil");
		$old = SOYAppUtil::switchAppMode("inquiry");
		$dao = SOY2DAOFactory::create("SOYInquiry_InquiryDAO");
				
		try{
			$res = $dao->executeQuery(
				"SELECT id FROM soyinquiry_inquiry ".
				"WHERE flag = ".SOYInquiry_Inquiry::FLAG_NEW." ".
				"LIMIT 1"
			);
		}catch(Exception $e){
			$res = aray();
		}
		SOYAppUtil::resetAppMode($old);

		$isUnconfirmed = (isset($res[0]["id"]));
		return $isUnconfirmed;
	}
}
SOYShopPlugin::extension("soyshop.admin.top", "0_inquiry_failure_to_check", "FailureToCheckAdminTop");
