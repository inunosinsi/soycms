<?php
class IndexPage extends MainMyPagePageBase{

	function IndexPage(){

		$mypage = MyPageLogic::getMyPage();
		
		//ログインしていなかったら飛ばす
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}

		//編集中のセッションが残っている可能性があるので消しておく
		$mypage->clearAttribute("address");

		WebPage::WebPage();

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