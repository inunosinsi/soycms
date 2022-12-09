<?php
SOY2::imports("module.plugins.item_review.domain.*");
SOY2::imports("module.plugins.item_review.logic.*");
class DetailPage extends WebPage{

	private $id;
	private $logic;

	function doPost(){

		if(isset($_POST["Review"]) && soy2_check_token()){

			$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
			$logic = SOY2Logic::createInstance("logic.review.ReviewLogic");

			try{
				$review = $dao->getById($this->id);
			}catch(Exception $e){
				$review = new SOYShop_ItemReview();
			}

			$review = SOY2::cast($review, (object)$_POST["Review"]);

			if(isset($_POST["do_close"])){
				$review->setIsApproved(SOYShop_ItemReview::REVIEW_NO_APPROVED);
			}
			if(isset($_POST["do_open"])){
				$review->setIsApproved(SOYShop_ItemReview::REVIEW_IS_APPROVED);
			}

			$review->setUpdateDate(time());

			$logic->update($review);
			$id = $this->id;

			SOY2PageController::jump("Review.Detail." . $id . "?updated");
			exit;
		}
	}

    function __construct($args) {

    	//IDがない場合はトップに飛ばす
    	if(!isset($args[0])){
    		SOY2PageController::jump("Review");
    	}
    	$this->id = (int)$args[0];

		parent::__construct();

    	self::_buildForm();
    }

    private function _buildForm(){
		$review = self::_getReview($this->id);

    	$item = soyshop_get_item_object($review->getItemId());
    	$user = soyshop_get_user_object($review->getUserId());

		$this->addForm("detail_form");

    	$this->addLabel("id", array(
    		"text" => $review->getId()
    	));

    	$this->addLink("item_name", array(
    		"link" => SOY2PageController::createLink("Item.Detail." . $item->getId()),
    		"text" => $item->getName()
    	));

    	$this->addInput("nickname", array(
    		"name" => "Review[nickname]",
    		"value" => $review->getNickname(),
    		"size" => 40
    	));

		DisplayPlugin::toggle("user_id", !is_null($user->getId()));

    	$this->addInput("title", array(
    		"name" => "Review[title]",
    		"value" => $review->getTitle()
    	));

    	$this->addLink("user_name", array(
    		"link" => SOY2PageController::createLink("User.Detail." . $user->getId()),
    		"text" => $user->getName()
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
    		"text" => ($review->getIsApproved()) ? "許可" : "拒否"
    	));

    	$this->addLabel("create_date", array(
    		"text" => date("Y-m-d H:i", $review->getCreateDate())
    	));

    	$this->addLabel("update_date", array(
    		"text" => date("Y-m-d H:i", $review->getUpdateDate())
    	));
    }

    private function _getReview($id){
    	try{
    		return SOY2DAOFactory::create("SOYShop_ItemReviewDAO")->getById($id);
    	}catch(Exception $e){
    		return new SOYShop_ItemReview();
    	}
    }

	function getBreadcrumb(){
		return BreadcrumbComponent::build("レビュー詳細", array("Review" => "レビュー"));
	}
}
