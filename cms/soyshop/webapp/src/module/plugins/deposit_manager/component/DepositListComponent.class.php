<?php
SOY2::import("domain.user.SOYShop_User");
class DepositListComponent extends HTMLList {

	protected function populateItem($entity) {

		$this->addLabel("deposit_date", array(
			"text" => (is_numeric($entity->getDepositDate())) ? date("Y-m-d", $entity->getDepositDate()) : "-"
		));

		$user = ($entity instanceof SOYShop_DepositManagerDeposit) ? soyshop_get_user_object($entity->getUserId()) : new SOYShop_User();
		$this->addLink("user_name", array(
			"text" => $user->getName(),
			"link" => SOY2PageController::createLink("User.Detail" . $entity->getUserId())
		));

		$this->addLabel("subject", array(
			"text" => (is_numeric($entity->getSubjectId())) ? self::_subjectText($entity->getSubjectId()) : "-"
		));

		$this->addLabel("deposit_price", array(
			"text" => number_format($entity->getDepositPrice())
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Extension.Detail.deposit_manager." . $entity->getId())
		));
	}

	private function _subjectText($subjectId){
		static $list;
		if(is_null($list)){
			SOY2::import("module.plugins.deposit_manager.util.DepositManagerUtil");
			$list = DepositManagerUtil::getSubjectList(true);
		}
		return (isset($list[$subjectId])) ? $list[$subjectId] : "-";
	}
}
