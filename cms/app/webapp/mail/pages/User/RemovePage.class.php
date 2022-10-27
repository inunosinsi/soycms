<?php
SOY2HTMLFactory::importWebPage("_common.CommonPartsPage");
class RemovePage extends CommonPartsPage{

    function __construct($args) {
		if(soy2_check_token()){
			$this->redirectCheck();
	    	$id = (isset($args[0])) ? (int)$args[0] : null;

	    	SOY2Logic::createInstance("logic.user.UserLogic")->remove($id);

	    	//echo $id . "を削除しました";

	    	CMSApplication::jump("User");
		}
    }
}
