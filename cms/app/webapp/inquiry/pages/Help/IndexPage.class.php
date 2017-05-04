<?php

class IndexPage extends WebPage{

    function __construct() {
    	//SUPER USER以外には表示させない
    	if(CMSApplication::getAppAuthLevel() != 1)CMSApplication::jump("");
    	
    	WebPage::__construct();
    }
}
?>