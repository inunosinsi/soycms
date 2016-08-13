<?php

class IndexPage extends WebPage{
	
	/**
	 * POSTした時の処理はこちら。
	 * IndexPageのWebPage::__construct()を読み込んだ際に$_POSTの値があればdoPostを読みにいく
	 */
	function doPost(){
		
	}
	
	function __construct(){
		
		WebPage::__construct();	//IndexPage.htmlを表示する
	}	
}
?>