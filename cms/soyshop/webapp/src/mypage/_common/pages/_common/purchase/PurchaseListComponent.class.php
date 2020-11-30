<?php

class PurchaseListComponent extends HTMLList{

	private $userId;

    function populateItem($entity){
        //受付時刻
        $this->addLabel("log_date", array(
            "text" => (isset($entity["log_date"]) && is_numeric($entity["log_date"])) ? date("Y年m月d日 H:i", $entity["log_date"]) : ""
        ));

        //買取番号
        $this->addLabel("purchase_number", array(
            "text" => (isset($entity["purchase_number"])) ? $entity["purchase_number"] : ""
        ));

		$this->addLabel("status", array(
			"text" => (isset($entity["status"])) ? SOYShop_PurchaseHistory::getStatusText($entity["status"]) : ""
		));

        //詳細リンク
        $this->addLink("detail_link", array(
            "link" => (isset($entity["id"])) ? soyshop_get_mypage_url() . "/purchase/detail/" . $entity["id"] : ""
        ));


		if(!isset($entity["user_id"]) || $entity["user_id"] != $this->userId) return false;
    }

	function setUserId($userId){
		$this->userId = $userId;
	}
}
