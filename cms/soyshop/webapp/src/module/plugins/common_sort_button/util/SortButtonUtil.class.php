<?php

class SortButtonUtil{
	
	function SortButtonUtil(){
		
	}
	
	//ソートに使うキーリスト
	public static function getColumnList(){		
		return array("id" => "商品ID",
					"name" => "商品名",
					"code" => "商品コード",
					"stock" => "在庫数",
					"price" => "価格",
					"cdate" => "商品の登録日",
					"udate" => "商品情報の更新日"
				);
	}
}

?>