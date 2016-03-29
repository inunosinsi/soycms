<?php
SOY2HTMLFactory::importWebPage("_common.CommonPartsPage");
class RemovePage extends CommonPartsPage{

    function RemovePage($args) {
    	$this->redirectCheck();
    	$id = @$args[0];
    	
    	$logic = SOY2Logic::createInstance("logic.user.UserLogic");
    	$logic->remove($id);
    	    	
    	//echo $id . "を削除しました";
    	
    	CMSApplication::jump("User");
    }
}
?>