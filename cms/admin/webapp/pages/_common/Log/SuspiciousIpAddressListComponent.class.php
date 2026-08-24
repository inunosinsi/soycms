<?php

class SuspiciousIpAddressListComponent extends HTMLList{

	protected function populateItem($entity){
		$id = (isset($entity["id"])) ? $entity["id"] : 0;
		$ip = (isset($entity["v4"])) ? $entity["v4"] : "";

		$this->addLabel("ipv4", array(
			"text" => $ip
		));

		$this->addActionLink("register_btn", array(
			"link" => SOY2PageController::createLink("NotFound.Suspicious?ip_id=".$id),
			"onclick" => "return confirm('悪意のあるIPアドレスとして登録しますか？')"
		));
	}
}
