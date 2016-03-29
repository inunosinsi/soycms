<?php

class IndexPage extends WebPage{

    function IndexPage() {
    	//SUPER USER以外には表示させない
    	if(CMSApplication::getAppAuthLevel() != 1)CMSApplication::jump("");
    	
    	WebPage::WebPage();
    }
}
?>