<?php
SOY2::imports("module.plugins.item_review.domain.*");
SOY2::imports("module.plugins.item_review.logic.*");
class DetailPage extends MainMyPagePageBase{

	private $id;
	private $user;
	private $reviewDao;
	private $itemDao;

	function doPost(){

		if(soy2_check_token() && isset($_POST["Review"])){
			
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
				$this->jump("review/detail/" . $this->id . "?update");
			}catch(Exception $e){
				$this->jump("review/detail/" . $this->id . "?failed");
			}
		}
	}

    function __construct($args) {

    	$mypage = MyPageLogic::getMyPage();
    	
    	//ログインチェック
		if(!$mypage->getIsLoggedin()){
			$this->jump("login");
		}

		//レビュープラグインがアクティブでない場合はマイページトップへ飛ばす
		if(!SOYShopPluginUtil::checkIsActive("item_review")){
			$this->jumpToTop();
		}

		//IDが存在していない場合は、レビュー一覧に飛ばす
		if(!isset($args[0])){
			$this->jump("review");
		}
		
		$this->id = (int)$args[0];
		
		$this->user = $this->getUser();
		$this->reviewDao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");

		parent::__construct();
		
		try{
			$review = $this->reviewDao->getByIdAndUserId($this->id, $this->user->getId());
		}catch(Exception $e){
			$review = new SOYShop_ItemReview();
		}
		
		//ユーザIDがnullの場合はレビュートップに飛ばす
		if(is_null($review->getUserId())){
			$this->jump("review");
		}
		
		//レビューの投稿者とログインユーザが一致しない場合もレビュートップに飛ばす
		if($review->getUserId() != $this->user->getId()){
			$this->jump("review");
		}
		
		$this->addModel("update", array(
			"visible" => (isset($_GET["update"]))
		));
		
		$this->addModel("failed", array(
			"visible" => (isset($_GET["failed"]))
		));

		$this->buildForm($review);

		$this->addLink("review_link", array(
			"link" => soyshop_get_mypage_url() . "/review"
		));
    }

    function buildForm(SOYShop_ItemReview $review){

		$this->addForm("form");

		$item = $this->getItem($review->getItemId());

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

    	$this->addSelect("evaluation", array(
    		"name" => "Review[evaluation]",
    		"options" => range(5, 1),
    		"selected" => $review->getEvaluation()
    	));

    	$this->addLabel("is_approved", array(
    		"text" => ($review->getIsApproved()) ? MessageManager::get("STATUS_ALLOW") : MessageManager::get("STATUS_REFUSE")
    	));
    	
    	$this->addLabel("create_date", array(
			"text" => date("Y年n月j日 H:i", $review->getCreateDate())
		));
		
		$this->addLabel("update_date", array(
			"text" => date("Y年n月j日 H:i", $review->getUpdateDate())
		));
    }

    function getItem($itemId){
		if(!$this->itemDao){
			$this->itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
		}

		try{
			$item = $this->itemDao->getById($itemId);
		}catch(Exception $e){
			$item = new SOYShop_Item();
		}

		return $item;
	}
}
?>