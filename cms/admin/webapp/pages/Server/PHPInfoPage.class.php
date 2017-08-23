<?php

class PHPInfoPage  extends CMSWebPageBase{

    function PHPInfoPage() {
		if(!UserInfoUtil::isDefaultUser()){
    		$this->jump("");
		}
		
		phpinfo();
		
		exit;		
    }
}
?>