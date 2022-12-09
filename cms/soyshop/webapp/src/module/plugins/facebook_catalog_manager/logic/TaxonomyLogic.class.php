<?php

class TaxonomyLogic extends SOY2LogicBase {

	function __construct(){}

	function getTaxonomy($hierarchy=1, $parent=null){
		$list = array();
		if(($fp = fopen(dirname(dirname(__FILE__)) . "/csv/google_product_taxonomy.csv", "r")) !== FALSE) {
			// 1行ずつfgetcsv()関数を使って読み込む
			while(($line = fgetcsv($fp))){
				if(is_numeric(array_search($line[$hierarchy], $list))) continue;
				if($hierarchy > 1 && isset($parent)){
					if($line[$hierarchy - 1] == $parent){
						$list[] = $line[$hierarchy];
					}
				}else{
					$list[] = $line[$hierarchy];
				}
			}
    	}
    	fclose($fp);
		return $list;
	}
}
