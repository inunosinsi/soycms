<?php
class ReviewsListComponent extends HTMLList{

	private $itemId;

	protected function populateItem($entity){

		$this->addLabel("nickname", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => ($entity instanceof SOYShop_ItemReview) ? self::_getNickname($entity->getNickname(), $entity->getUserId()) : ""
		));

		$this->addLabel("evaluation", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => $entity->getEvaluationString()
		));

		$this->addLabel("title", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => (strlen($entity->getTitle()) > 0) ? $entity->getTitle() : "無題"
		));

		$this->addLabel("content", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => nl2br(htmlspecialchars($entity->getContent(), ENT_QUOTES))
		));

		$this->addLabel("update_date", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => (is_numeric($entity->getUpdateDate())) ? date("Y年m月d日", $entity->getUpdateDate()) : ""
		));

		$this->addLabel("item_name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => (is_numeric($this->itemId)) ? soyshop_get_item_object($this->itemId)->getOpenItemName() : ""
		));
	}

	private function _getNickname($nickname, $userId){
		$nickname = (strlen($nickname) > 0) ? $nickname : self::_getNicknameConfig();
		if(!is_numeric($userId)) return $nickname;

		//プロフィール閲覧が許可されている場合はリンクを出力する
		$link = self::_mypage()->getProfileUserLink($userId);
		if(!isset($link) || !strlen($link)) return $nickname;

		return "<a href=\"" . $link . "\">" . $nickname . "</a>";
	}

	private function _mypage(){
		static $my;
		if(is_null($my)) $my = MyPageLogic::getMyPage();
		return $my;
	}

	private function _getNicknameConfig(){
		static $cnf;
		if(is_null($cnf)){
			SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
			$c = ItemReviewUtil::getConfig();
			$cnf = (isset($c["nickname"])) ? $c["nickname"] : "";
		}
		return $cnf;
	}

	function setItemId($itemId){
		$this->itemId = $itemId;
	}
}
