<?php
if(!function_exists("soyshop_output_user")) SOY2::import("module.site.common.output_user", ".php");
class UserListComponent extends HTMLList{

	protected function populateItem($entity){
		$user = ($entity instanceof SOYShop_User) ? $entity : new SOYShop_User();
		soyshop_output_user($this, $user);

		// board用のアカウント詳細へのリンク
		$this->addLink("user_detail_link", array(
			"link" => soyshop_get_mypage_url() . "/board/user/detail/" . $user->getId()
		));

		$this->addLabel("post_count", array(
			"text" => $user->getAttribute("post_count")
		));

		$this->addLabel("register_date", array(
			"text" => (is_numeric($user->getRegisterDate())) ? date("Y-m-d H:i:s", $user->getRegisterDate()) : ""
		));
	}
}
