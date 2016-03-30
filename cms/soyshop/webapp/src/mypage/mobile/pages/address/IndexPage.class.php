<?php
class IndexPage extends MobileMyPagePageBase{

	function IndexPage(){
		WebPage::WebPage();

		$mypage = MyPageLogic::getMyPage();
		if(!$mypage->getIsLoggedin())$this->jump("login");//ログインしていなかったら飛ばす

		//編集中のセッションが残っている可能性があるので消しておく
		$mypage->clearAttribute("address");

		$user = $this->getUser();
		$list = $user->getAddressListArray();

		$this->createAdd("address_list","AddressList", array(
			"list" => $list
		));

		//新規作成
		$this->addLink("create_link", array(
			"link" => soyshop_get_mypage_url() . "/address/edit/".count($list)
		));

		$this->addLink("return_link", array(
			"link" => soyshop_get_mypage_url() . "/top"
		));
	}

}

class AddressList extends HTMLList{

	function populateItem($entity,$key,$index){

		//法人名(勤務先)
    	$this->createAdd("send_office_text","HTMLLabel", array(
    		"text" => @$entity["office"],
    	));

		//氏名
		$this->createAdd("send_name_text","HTMLLabel", array(
    		"text" => @$entity["name"],
    	));

		//フリガナ
    	$this->createAdd("send_reading_text","HTMLLabel", array(
    		"text" => @$entity["reading"],
    	));

		//郵便番号
    	$this->createAdd("send_zip_code_text","HTMLLabel", array(
    		"text" => @$entity["zipCode"],
    	));

		//都道府県
		$this->createAdd("send_area_text","HTMLLabel", array(
			"text" => SOYShop_Area::getAreaText(@$entity["area"])
		));

//    	$this->createAdd("send_area","HTMLSelect","HTMLLabel", array(
//    		"name" => "Address[area]",
//    		"options" => SOYShop_Area::getAreas(),
//    		"value" => @$entity["area"],
//    	));

		//住所入力1
    	$this->createAdd("send_address1_text","HTMLLabel", array(
    		"text" => @$entity["address1"],
    	));

		//住所入力2
    	$this->createAdd("send_address2_text","HTMLLabel", array(
    		"text" => @$entity["address2"],
    	));

		//電話番号
    	$this->createAdd("send_tel_number_text","HTMLLabel", array(
    		"text" => @$entity["telephoneNumber"],
    	));

		//編集
		$this->createAdd("edit_link","HTMLLink", array(
			"link" => soyshop_get_mypage_url() ."/address/edit/" . $key
		));

		//削除
		$this->createAdd("delete_link","HTMLActionLink", array(
			"link" => soyshop_get_mypage_url() ."/address/delete/" . $key,
		));

		//番号
		$this->createAdd("address_index","HTMLLabel", array(
			"text" => $index
		));
	}
}
?>