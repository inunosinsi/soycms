<?php
if ( !function_exists('json_decode') ){
    function json_decode($content, $assoc=false){
		static $json;
		
		if(!$json[$assoc]){
	        if(!class_exists("Services_JSON")){
		        @include_once('Services/JSON.php');
		        $isIncluded = @include_once('Services/JSON.php');
		        
		        if(!$isIncluded){
		        	$lib_json_name = ( is_readable(SOY2::RootDir()."lib/JSON.php") ) ? SOY2::RootDir()."lib/JSON.php" : SOY2::RootDir()."lib/Services/JSON.php" ;
		        	@include_once($lib_json_name);
		        	$isIncluded = @include_once($lib_json_name);
		        }
		
		    	if(!$isIncluded){
		    		throw new Exception("JSON library or Services/JSON package is required.");
		    	}
	        }
	        
	        if ( $assoc ){
	            $json[$assoc] = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
	        } else {
	            $json[$assoc] = new Services_JSON;
	        }
		}
		
        return $json[$assoc]->decode($content);
    }
}

if ( !function_exists('json_encode') ){
    function json_encode($content){
		static $json;
		
		if(!$json){
	        if(!class_exists("Services_JSON")){
			    @include_once('Services/JSON.php');
			    $isIncluded = @include_once('Services/JSON.php');
			    
			    if(!$isIncluded){
			    	$lib_json_name = ( is_readable(SOY2::RootDir()."lib/JSON.php") ) ? SOY2::RootDir()."lib/JSON.php" : SOY2::RootDir()."lib/Services/JSON.php" ;
			    	@include_once($lib_json_name);
			    	$isIncluded = @include_once($lib_json_name);
			    }
			
				if(!$isIncluded){
					throw new Exception("JSON library or Services/JSON package is required.");
				}
	        }
		
		    $json = new Services_JSON;
		}

        return $json->encode($content);
    }
}
?>
