<?php

class IndexPage extends WebPage{

    function __construct($agrs) {
    	if(count($agrs) < 1)SOY2PageController::jump("");
    	WebPage::WebPage();
    }
}
?>