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
	
    function DetailPage($args) {
    	
    	//IDがない場合はトップに飛ばす
    	if(!isset($args[0])){
    		SOY2PageController::jump("Review");
    	}
    	$this->id = (isset($args[0])) ? $args[0] : null;
    	$id = $this->id;
    	
    	WebPage::WebPage();
    	
    	$this->addForm("detail_form");    	    	
    	$this->buildForm($this->getReview($id));
    }
    
    function buildForm(SOYShop_ItemReview $review){
    	
    	$item = $this->getItem($review->getItemId());
    	$user = $this->getUser($review->getUserId());
    	    	
    	$this->addModel("update", array(
    		"visible" => (isset($_GET["updated"]))
    	));
    	
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
    	
    	$this->addModel("is_user_id", array(
    		"visible" => ($user->getId())
    	));
    	
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
    
    function getReview($id){
    	$reviewDao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
    	try{
    		$review = $reviewDao->getById($id);
    	}catch(Exception $e){
    		SOY2PageController::jump("Review");
			exit;
    	}
    	
    	return $review;
    }
    
    function getItem($itemId){
    	$itemDao = SOY2DAOFactory::create("shop.SOYShop_ItemDAO");
    	try{
    		$item = $itemDao->getById($itemId);
    	}catch(Exception $e){
    		$item = new SOYShop_Item();
    	}
    	return $item;
    }
    
    function getUser($userId){
    	$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");
    	try{
    		$user = $userDao->getById($userId);
    	}catch(Exception $e){
    		$user = new SOYShop_User();
    	}
    	return $user;
    }    
}
?>