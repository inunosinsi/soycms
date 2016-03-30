<?php 

class CommonNoticeStockCommon{
	
	public static function getConfig(){
    	return SOYShop_DataSets::get("notice_stock", array(
    		"stock" => 10,
    		"has_stock" => "在庫あります",
    		"notice_stock" => "在庫数あと##COUNT##個です",
    		"no_stock" => "在庫はありません"
    	));
    }
}
?>