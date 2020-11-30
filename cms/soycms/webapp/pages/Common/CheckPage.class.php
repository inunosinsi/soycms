<?php

class CheckPage extends CMSWebPageBase{

    function __construct() {
    	$site = UserInfoUtil::getSite();

    	if(!is_null($site->getId())){
    		echo $site->getId();
    	}else{
    		echo 0;
    	}

    	exit;
    }
}
