<?php
class CompletePage extends MainMyPagePageBase{

	function __construct(){

		$mypage = $this->getMyPage();

		SOY2::import("module.plugins.transfer_information.util.TransferInfoUtil");
		$mypage->clearAttribute(TransferInfoUtil::BANK_INFO);
		$mypage->clearErrorMessage();
		$mypage->save();

		parent::__construct();

		$this->addLink("edit_link", array(
			"link" => soyshop_get_mypage_url() . "/bank/"
		));
	}
}
