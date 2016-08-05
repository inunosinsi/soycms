<?php

class RemovePage extends WebPage{
	
	function __construct($args){
		
		$id = (isset($args[0])) ? $args[0] : null;
		
		/**
		 * HTMLを持たずに処理だけのページの場合はWebPage::WebPage();を書かない
		 */
		
		$dao = SOY2DAOFactory::create("Sample.SOYMock_SampleDAO");
		try{
			$dao->deleteById($id);
		}catch(Exception $e){
			//
		}
		
		CMSApplication::jump("Page?success");
	}
}
?>