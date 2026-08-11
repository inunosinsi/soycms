<?php
SOY2::imports("module.plugins.item_review.domain.*");
SOY2::imports("module.plugins.item_review.logic.*");
class DetailPage extends MainMyPagePageBase{

	private $id;
	private $user;
	private $reviewDao;
	private $itemDao;

	function doPost(){

		if(soy2_check_token() && soy2_check_referer() && isset($_POST["Review"])){

			try{
				$oldReview = $this->reviewDao->getByIdAndUserId($this->id, $this->user->getId());
			}catch(Exception $e){
				return false;
			}

			//他人のレビューを編集させない
			if($oldReview->getUserId() != $this->getUserId()){
				return false;
			}

			$postReview = (object)$_POST["Review"];
			$review = SOY2::cast($oldReview, $postReview);

			//ユーザIDの変更を不許可
			$review->setUserId($this->user->getId());
			try{
				$this->reviewDao->update($review);
				$this->jump("review/detail/" . $this->id . "?updated");
			}catch(Exception $e){
				$this->jump("review/detail/" . $this->id . "?failed");
			}
		}
	}

    function __construct($args) {
		$this->checkIsLoggedIn(); //ログインチェック

		//レビュープラグインがアクティブでない場合はマイページトップへ飛ばす
		if(!SOYShopPluginUtil::checkIsActive("item_review")) $this->jumpToTop();

		//設定を確認
		SOY2::import("module.plugins.item_review.util.ItemReviewUtil");
		$config = ItemReviewUtil::getConfig();
		if(!isset($config["edit"]) || (int)$config["edit"] === 0) $this->jump("review");

		//IDが存在していない場合は、レビュー一覧に飛ばす
		if(!isset($args[0])) $this->jump("review");

		$this->id = (int)$args[0];

		$this->user = $this->getUser();
		$this->reviewDao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");

		parent::__construct();

		try{
			$review = $this->reviewDao->getByIdAndUserId($this->id, $this->user->getId());
		}catch(Exception $e){
			$this->jump("review");
		}

		//ユーザIDがnullの場合はレビュートップに飛ばす or レビューの投稿者とログインユーザが一致しない場合もレビュートップに飛ばす
		if(is_null($review->getUserId()) || $review->getUserId() != $this->user->getId()) $this->jump("review");

		DisplayPlugin::toggle("updated", isset($_GET["updated"]));
		$this->addModel("updated", array(
			"visible" => (isset($_GET["updated"]))
		));

		DisplayPlugin::toggle("failed", isset($_GET["failed"]));
		$this->addModel("failed", array(
			"visible" => (isset($_GET["failed"]))
		));

		self::buildForm($review);

		$this->addLink("review_link", array(
			"link" => soyshop_get_mypage_url() . "/review"
		));
    }

    private function buildForm(SOYShop_ItemReview $review){

		$this->addForm("form");

		$item = soyshop_get_item_object($review->getItemId());

		$this->addLink("item_link", array(
			"link" => soyshop_get_item_detail_link($item)
		));

    	$this->addLabel("item_name", array(
    		"text" => $item->getName()
    	));

    	$this->addInput("nickname", array(
    		"name" => "Review[nickname]",
    		"value" => $review->getNickname()
    	));

		$this->addInput("title", array(
			"name" => "Review[title]",
			"value" => $review->getTitle()
		));

    	$this->addTextArea("content", array(
    		"name" => "Review[content]",
    		"value" => $review->getContent()
    	));

		$config = ItemReviewUtil::getConfig();

		//セレクトボックス形式の評価選択
		DisplayPlugin::toggle("evaluation_select", (!isset($config["evaluation_star"]) || $config["evaluation_star"] != 1));
    	$this->addSelect("evaluation", array(
    		"name" => "Review[evaluation]",
    		"options" => range(5, 1),
    		"selected" => $review->getEvaluation()
    	));

		//5つ星形式の評価選択
		DisplayPlugin::toggle("evaluation_star", (isset($config["evaluation_star"]) && $config["evaluation_star"] == 1));
		SOY2::import("module.plugins.item_review.component.EvaluationStarComponent");
		$this->addLabel("evaluation_star", array(
			"html" => EvaluationStarComponent::buildEvaluateArea($review->getEvaluation())
		));

    	$this->addLabel("is_approved", array(
    		"text" => ($review->getIsApproved()) ? MessageManager::get("STATUS_ALLOW") : MessageManager::get("STATUS_REFUSE")
    	));

    	$this->addLabel("create_date", array(
			"text" => (is_numeric($review->getCreateDate())) ? date("Y年n月j日 H:i", $review->getCreateDate()) : ""
		));

		$this->addLabel("update_date", array(
			"text" => (is_numeric($review->getUpdateDate())) ? date("Y年n月j日 H:i", $review->getUpdateDate()) : ""
		));
    }
}
