<?php

class RegisterPage extends WebPage{
	
	function doPost(){
		
		if(soy2_check_token()){
			
			$dao = SOY2DAOFactory::create("sample.SOYMock_SampleDAO");
			
			//入力した値を配列として取得
			$sample = $_POST["Sample"];
			
			//DAOでデータベースに放り込むようにオブジェクトに変更
			$obj = SOY2::cast("SOYMock_Sample", (object)$sample);
			
			try{
				$id = $dao->insert($obj);
			}catch(Exception $e){
				//
				$id = null;
			}

			CMSApplication::jump("Page?updated");
		}
	}
	
	function __construct(){
		
		WebPage::__construct();
		
		$this->createAdd("form", "HTMLForm");
		
		$this->createAdd("name", "HTMLInput", array(
			"name" => "Sample[name]",
			"value" => ""
		));
		
		$this->createAdd("description", "HTMLTextArea", array(
			"name" => "Sample[description]",
			"value" => ""
		));
	}
}
?>