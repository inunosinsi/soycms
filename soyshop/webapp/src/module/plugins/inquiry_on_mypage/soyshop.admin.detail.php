<?php
class InquiryOnMypageAdminDetail extends SOYShopAdminDetailBase{

	function getTitle(){
		return "お問い合わせ詳細";
	}

	function getContent(){
		/** @ToDo いずれはlistの方で確認済みお問い合わせ一覧を確認できるようにしたい **/
		$mailLogId = $this->getDetailId();
		SOY2::imports("module.plugins.inquiry_on_mypage.domain.*");
		$inqDao = SOY2DAOFactory::create("SOYShop_InquiryDAO");
		try{
			$inquiry = $inqDao->getByMailLogId($mailLogId);
		}catch(Exception $e){
			SOY2PageController::jump("");
		}

		$inquiry->setIsConfirm(SOYShop_Inquiry::IS_CONFIRM);
		try{
			$inqDao->update($inquiry);
		}catch(Exception $e){
			var_dump($e);
		}

		SOY2PageController::jump("Order.Mail.Log." . $mailLogId);
	}
}
SOYShopPlugin::extension("soyshop.admin.detail", "inquiry_on_mypage", "InquiryOnMypageAdminDetail");
