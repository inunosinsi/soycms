<?php
class DetailPage extends MainMyPagePageBase{

	private $purchaseId;
	private $userId;

    function doPost(){}

    function __construct($args){
		$this->checkIsLoggedIn(); //ログインチェック

		//買取プラグインがアクティブでない場合はトップページに飛ばす
		SOY2::import("util.SOYShopPluginUtil");
		if(!SOYShopPluginUtil::checkIsActive("purchase_manager")) $this->jumpToTop();

        //orderIdがない場合はorderトップへ戻す
        if(!isset($args[0])) $this->jump("purchase");

		$this->purchaseId = (int)$args[0];
        $this->userId = (int)$this->getUser()->getId();

		//確認
		$logic = SOY2Logic::createInstance("module.plugins.purchase_manager.logic.PurchaseLogic");
		$purchase = $logic->getByIdAndUserId($this->purchaseId, $this->userId);
		if(is_null($purchase->getId())) $this->jump("purchase");

		SOY2::import("module.plugins.purchase_manager.domain.SOYShop_PurchaseHistory");
		SOY2::import("util.SOYAppUtil");

		if(soy2_check_token()){
			self::_approval($purcahse);
		}

        parent::__construct();

		$this->addLabel("purchase_number", array(
			"text" => $purchase->getPurchaseNumber()
		));

		$this->addLabel("inquiry_date", array(
			"text" => date("Y年m月d日 H時i分s秒", $logic->getInquiryDateById($purchase->getId()))
		));

		$status = $logic->getStatusById($purchase->getId());
		$this->addLabel("status", array(
			"text" => SOYShop_PurchaseHistory::getStatusText($status),
		));

		//査定書
		$user = soyshop_get_user_object($purchase->getUserId());
		$isPdfFile = (strlen($purchase->getPdfName()) && file_exists($user->getAttachmentsPath() . $purchase->getPdfName() . ".pdf"));
		DisplayPlugin::toggle("assessment", $isPdfFile);

		$this->addLink("assessment_file_link", array(
			"link" => ($isPdfFile) ? $user->getAttachmentsUrl() . $purchase->getPdfName() . ".pdf" : "",
			"target" => "_blank"
		));

		$this->addLabel("inquiry_content", array(
			"html" => nl2br($logic->getInquiryContentsByInquiryId($purchase->getInquiryId()))
		));

		//@ToDo 発送済み
		DisplayPlugin::toggle("approval_button", ($isPdfFile && ($status < PurchaseAppUtil::STATUS_APPROVAL || $status == PurchaseAppUtil::STATUS_END)));	//ユーザ側で査定書の確認後のボタン
		$this->addActionLink("approval_link", array(
			"link" => soyshop_get_mypage_url() . "/purchase/detail/" . $purchase->getId(),
			"style" => "color:#FFFFFF;",
			"onclick" => "return confirm('査定書を承認しますがよろしいですか？');"
		));

		SOY2::import("util.SOYShopPluginUtil");
		DisplayPlugin::toggle("inquiry", SOYShopPluginUtil::checkIsActive("purchase_manager"));

		$this->addLink("inquiry_link", array(
			"link" => soyshop_get_mypage_url() . "/inquiry?plugin_id=purchase_manager&purchase_id=" . $purchase->getId()
		));

		$this->addLink("back_link", array(
			"link" => soyshop_get_mypage_url() . "/purchase"
		));
    }

	private function _approval(){
		SOY2::import("module.plugins.purchase_manager.util.PurchaseAppUtil");
		SOY2Logic::createInstance("module.plugins.purchase_manager.logic.PurchaseLogic")->changeStatus($this->purchaseId, PurchaseAppUtil::STATUS_APPROVAL);
		$this->jump("purchase/detail/" . $this->purchaseId);
	}
}
