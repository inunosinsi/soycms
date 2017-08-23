<?php

class MakeDirectoryPage extends CMSWebPageBase{
	
	function doPost(){
		
		//パス
		$path = $_POST["path"];
		
		//ディレクトリ名
		$dirname = $_POST["name"];
		
		//返り値
		$flag = 1;
		echo $flag;	//成功もしくは失敗を返す		
		
		exit;				
	}


    function __construct() {
    	parent::__construct();
    	
    }
}
?>