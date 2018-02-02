<?php
class IndexPage extends MainMyPagePageBase{

	function __construct(){
		$this->checkIsLoggedIn(); //ログインチェック

		//編集中のセッションが残っている可能性があるので消しておく
		$this->getMyPage()->clearAttribute("address");

		parent::__construct();

		$user = $this->getUser();

		$this->addLabel("user_name", array(
			"text" => $user->getName()
		));

		$list = $user->getAddressListArray();

		$this->createAdd("address_list", "_common.address.AddressListComponent", array(
			"list" => $list
		));

		//新規作成
		$this->addLink("create_link", array(
			"link" => soyshop_get_mypage_url() . "/address/edit/" . count($list)
		));

		$this->addLink("top_link", array(
			"link" => soyshop_get_mypage_url()
		));
	}

}
?>
