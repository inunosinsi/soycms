<?php

class SortButtonUtil{
	
	function __construct(){}
	
	//ソートに使うキーリスト
	public static function getColumnList(){		
		$list = array("id" => "商品ID",
					"name" => "商品名",
					"code" => "商品コード",
					"stock" => "在庫数",
					"price" => "価格",
					"cdate" => "商品の登録日",
					"udate" => "商品情報の更新日"
				);
		
		//カスタムフィールド		
		SOY2::import("domain.shop.SOYShop_ItemAttribute");
		$fields = SOYShop_ItemAttributeConfig::load(true);
		if(!count($fields)) return $list;
		
		foreach($fields as $field){
			$conf = $field->getConfig();
			if(isset($conf) && is_array($conf) && isset($conf["isIndex"]) && $conf["isIndex"] == 1){
				$list["custom_" . $field->getFieldId()] = $field->getLabel() . "(カスタムフィールド)";
			}
		}
				
		return $list;
	}
}

?>