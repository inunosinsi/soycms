<?php
SOY2::imports("module.plugins.item_review.domain.*");
class RemovePage extends MainMyPagePageBase{

    function __construct($args) {
		$this->checkIsLoggedIn(); //ログインチェック
		if(!isset($args[0])) $this->jump("review"); //IDが存在していない場合はレビュー一覧へ飛ばす

		if(soy2_check_token()){
			$id = (int)$args[0];

			$dao = SOY2DAOFactory::create("SOYShop_ItemReviewDAO");

			try{
				$review = $dao->getByIdAndUserId($id, $this->getUserId());
			}catch(Exception $e){
				$this->jump("review?failed");
			}

			//他人のレビューを削除させない
			if($review->getUserId() != $this->getUserId()) $this->jump("review");

			try{
				$dao->delete($id);
				$this->jump("review?deleted");
			}catch(Exception $e){
				//
			}
		}
		$this->jump("review?failed");
    }
}
