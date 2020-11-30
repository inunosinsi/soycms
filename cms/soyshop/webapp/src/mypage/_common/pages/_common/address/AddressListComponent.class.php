<?php

class AddressListComponent extends HTMLList{

	function populateItem($entity, $key, $index){

		//法人名(勤務先)
    	$this->addLabel("send_office_text", array(
    		"text" => (isset($entity["office"])) ? $entity["office"] : "",
    	));

		//氏名
		$this->addLabel("send_name_text", array(
    		"text" => (isset($entity["name"])) ? $entity["name"] : "",
    	));

		//フリガナ
    	$this->addLabel("send_reading_text", array(
    		"text" => (isset($entity["reading"])) ? $entity["reading"] : "",
    	));

		//郵便番号
    	$this->addLabel("send_zip_code_text", array(
    		"text" => (isset($entity["zipCode"])) ? $entity["zipCode"] : "",
    	));

		//都道府県
		$this->addLabel("send_area_text", array(
			"text" => (isset($entity["area"])) ? SOYShop_Area::getAreaText($entity["area"]) : ""
		));

		//住所入力1
    	$this->addLabel("send_address1_text", array(
    		"text" => (isset($entity["address1"])) ? $entity["address1"] : "",
    	));

		//住所入力2
    	$this->addLabel("send_address2_text", array(
    		"text" => (isset($entity["address2"])) ? $entity["address2"] : "",
    	));

		//電話番号
    	$this->addLabel("send_tel_number_text", array(
    		"text" => (isset($entity["telephoneNumber"])) ? $entity["telephoneNumber"] : "",
    	));

		//編集
		$this->addLink("edit_link", array(
			"link" => soyshop_get_mypage_url() . "/address/edit/" . $key
		));

		//削除
		$this->addActionLink("delete_link", array(
			"link" => soyshop_get_mypage_url() . "/address/delete/" . $key,
			"onclick" => "return confirm('住所" . $index . "を削除してよろしいですか？');"
		));

		//番号
		$this->addLabel("address_index", array(
			"text" => $index
		));

		//名前と住所が分からなければ、絶対に届けられないので、この二つが存在していない場合は表示させない
		if(isset($entity["name"]) && strlen($entity["name"]) === 0 &&
		   isset($entity["area"]) && (int)$entity["area"] === 0) return false;
	}
}
