<?php

class RemovePage extends WebPage{

    function __construct($arg) {
    	$threadId = $arg[0];

    	$logic = SOY2Logic::createInstance("logic.ThreadLogic");
    	$logic->deleteById($threadId);

    	CMSApplication::jump();
    }
}
?>