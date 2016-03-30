<?php

class OrderInvoiceCommon{
	
	public static function getConfig(){
		return SOYShop_DataSets::get("order_invoice.config", array(
			"title" => "お店からのお便り",
			"content" => "",
			"payment" => 0	//振込先情報を表示するか？
		));
	}
	
	public static function saveConfig($values){
		SOYShop_DataSets::put("order_invoice.config", $values);
	}
	
	public static function getTemplateName(){
		return SOYShop_DataSets::get("order_invoice.template", "default");
	}
	
	public static function saveTemplateName($template){
		SOYShop_DataSets::put("order_invoice.template", $template);
	}
	
	public static function getTemplateList(){
		$files = array();
		if ($dir = opendir(dirname(dirname(__FILE__)) . "/template")) {
			while(($file = readdir($dir)) !== false){
				if($file != "." && $file != ".." && strpos($file, ".html") > 0){
					$files[] = str_replace(".html", "", $file);
				}
			} 
			closedir($dir);
		}
		
		return $files;
	}
}
?>