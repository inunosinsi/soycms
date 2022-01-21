<?php

class AddressSearchLogic extends SOY2LogicBase{

    function search($zip1,$zip2){
    	if(empty($zip2)){
    		$zip = $zip1 . "0000";
    	}else{
    		$zip = $zip1 . $zip2;
    	}
    	
    	$file = dirname(__FILE__) . "/address.csv";
    	
    	$fp = fopen($file, "r");
    	
		$res = array(
			"prefecture" => "",
			"address1" => "",
			"address2" => ""
		);
    	
    	while(($str = fgets($fp))!==false){
    		$array = explode(",",$str);
    		
    		$zipCode = trim(str_replace("\"","",$array[2]));
    		
    		if($zip != $zipCode)continue;
    		
    		$prefecture = trim(str_replace("\"","",$array[6]));
    		$address1 = trim(str_replace("\"","",$array[7]));
    		$address2 = trim(str_replace("\"","",$array[8]));
    		if($address2 == "以下に掲載がない場合")$address2 = "";
    		
    		$res = array(
    			"prefecture" => $prefecture,
    			"address1" => $address1,
    			"address2" => $address2
    		);
    		
    		break;
    	}
    	
    	fclose($fp);
    	
    	return $res;
    }
    
    function convert(){
    	$file = file(dirname(__FILE__) . "/address.csv");
    	$dir = dirname(__FILE__) . "/address/";
    	
    	foreach($file as $str){
    		$array = explode(",",$str);
    		
    		$zip = trim(str_replace("\"","",$array[2]));
    		$prefecture = trim(str_replace("\"","",$array[6]));
    		$address1 = trim(str_replace("\"","",$array[7]));
    		$address2 = trim(str_replace("\"","",$array[8]));
    		if($address2 == "以下に掲載がない場合")$address2 = "";
    		
    		$filename = substr($zip,0,3);
    		
    		$fp = fopen($dir . $filename . ".csv","a+");
    		fwrite($fp,$zip . "," . $prefecture . "," . $address1 . "," . $address2);
    		fwrite($fp,"\n");
    		fclose($fp);
    	}
    }
}