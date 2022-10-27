<?php
class RegisterPage extends WebPage{

    function __construct() {
		//廃止
		CMSApplication::jump("User.Detail");
    }
