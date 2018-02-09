<?php
class ReviewsListComponent extends HTMLList{

	private $config;
	private $item;
	private $mypage;

	protected function populateItem($entity){

		$nickname = (strlen($entity->getNickname()) > 0) ? $entity->getNickname() : $this->config["nickname"];

		//プロフィール閲覧が許可されている場合はリンクを出力する
		$profileLink = $this->mypage->getProfileUserLink($entity->getUserId());
		if(isset($profileLink) && (strlen($profileLink) > 0)){
			$nickname = "<a href=\"" . $profileLink."\">" . $nickname . "</a>";
		}

		$this->addLabel("nickname", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"html" => $nickname
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
			"text" => date("Y年m月d日", $entity->getUpdateDate())
		));

		$this->addLabel("item_name", array(
			"soy2prefix" => SOYSHOP_SITE_PREFIX,
			"text" => $this->item->getName()
		));
	}

	function setConfig($config){
		return $this->config;
	}

	function setItem($item){
		$this->item = $item;
	}

	function setMypage($mypage){
		$this->mypage = $mypage;
	}
}
