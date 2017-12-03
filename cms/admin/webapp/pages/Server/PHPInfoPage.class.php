<?php

class PHPInfoPage  extends CMSWebPageBase{

    function __construct() {
		if(!UserInfoUtil::isDefaultUser()){
    		$this->jump("");
		}

		phpinfo();

		exit;
    }
}
