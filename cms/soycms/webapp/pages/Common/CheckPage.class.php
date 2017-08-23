<?php

class CheckPage extends CMSWebPageBase{

    function CheckPage() {
    
    	$site = UserInfoUtil::getSite();
    	
    	if($site){
    		echo $site->getId();
    	}else{
    		echo 0;
    	}
    	
    	exit;
    }
}
?>