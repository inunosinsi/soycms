<?php

class IndexPage extends WebPage{

    function IndexPage($agrs) {
    	if(count($agrs) < 1)SOY2PageController::jump("");
    	WebPage::WebPage();
    }
}
?>