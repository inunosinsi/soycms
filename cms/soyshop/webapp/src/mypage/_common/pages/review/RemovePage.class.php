<?php
SOY2::imports("module.plugins.item_review.domain.*");
class RemovePage extends MainMyPagePageBase{

    function RemovePage($args) {

		if(soy2_check_token()){
			$mypage = MyPageLogic::getMyPage();
	
			//ログインチェック
			if(!$mypage->getIsLoggedin()){
				$this->jump("login");
			}
	
			//IDが存在していない場合はレビュー一覧へ飛ばす
	    	if(!isset($args[0])){
	    		$this->jump("review");
	    	}
	    	
			$id = (int)$args[0];
	
			$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");
	
			try{
				$review = $dao->getByIdAndUserId($id, $this->getUserId());
			}catch(Exception $e){
				$this->jump("review?failed");
			}
	
			//他人のレビューを削除させない
			if($review->getUserId() != $this->getUserId()){
				$this->jump("review");
			}
	
			try{
				$dao->delete($id);
			}catch(Exception $e){
				$this->jump("review?failed");
			}
	
			$this->jump("review?deleted");
		}
    }
}
?>