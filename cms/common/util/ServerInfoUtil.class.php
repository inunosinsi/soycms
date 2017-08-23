<?php

class ServerInfoUtil {

	/**
	 * mod_rewriteが使用可能かどうかを返す
	 * 2010-02-19 CMSUtilから移動
	 */
    public static function isEnableModRewrite(){
		if(function_exists("apache_get_modules") && strpos("cgi",php_sapi_name()) !== false){
			if( in_array("mod_rewrite", apache_get_modules()) ){
				return true;
			}else{
				return false;
			}
		}
		return null;
    }
    
    /**
     * メール送信が可能かどうかを返す
     */
    public static function isEnableSendEmail(){
    	return true;
    }
    
	/**
	 * Zipが利用可能かどうか判断
	 * 2010-02-19 CMSUtilから移動
	 * @return クラス名
	 */
	public static function checkZipEnable($expandOnly = false){
		
		//5.2.2以上ならOK
		if(version_compare(phpversion(),"5.2.2",">=")){
			if(class_exists("ZipArchive")){			
				return "ZipArchive";
			}
		}
		
		//解凍のみなら5．2.0でも可
		if($expandOnly && version_compare(phpversion(),"5.2.0",">=")){
			if(class_exists("ZipArchive")){			
				return "ZipArchive";
			}
		}
		
		@include_once("Archive/Zip.php");
		$res = @include_once("Archive/Zip.php");
		
		//PearのArchive_ZipがあるならOK
		if($res && extension_loaded('zlib')){
			return "Archive_Zip";
		}

		//PearのArchive_Zipをcommon/lib以下にインストールしていてもOK
	   	$lib_zip_name = ( is_readable(SOY2::RootDir()."lib/Zip.php") ) ? SOY2::RootDir()."lib/Zip.php" : SOY2::RootDir()."lib/Archive/Zip.php" ;
		@include_once($lib_zip_name);
		$result = @include_once($lib_zip_name);
		
		if($result && extension_loaded('zlib')){
			return "Archive_Zip";
		}   	
		
		return false;
	}
	
	
    /**
     * 書き込み可能な一時ディレクトリを返す
     */
    public static function sys_get_writable_temp_dir(){
    	static $dirname = null;
    	
    	if(is_null($dirname)){
    	 	$dirname = sys_get_temp_dir();
    	
	    	if(!$dirname || !is_writable($dirname)){
	    		//テンポラリディレクトリに書き込み権限がないとき
	    		$dirname = SOY2::RootDir()."tmp";
	    		
	    		if(!is_writable($dirname)){
	    			$dirname = null;
	    			return false;
	    		}
	    	}
    	}
    	
    	return $dirname;
	    	
    }
}

/**
 * sys_get_temp_dirはPHP 5.2.1以降
 * http://php.net/manual/ja/function.sys-get-temp-dir.phpから参照
 */
if(!function_exists("sys_get_temp_dir")){
	function sys_get_temp_dir()
    {
        // Try to get from environment variable
        if ( !empty($_ENV['TMP']) )
        {
            return realpath( $_ENV['TMP'] );
        }
        else if ( !empty($_ENV['TMPDIR']) )
        {
            return realpath( $_ENV['TMPDIR'] );
        }
        else if ( !empty($_ENV['TEMP']) )
        {
            return realpath( $_ENV['TEMP'] );
        }

        // Detect by creating a temporary file
        else
        {
            // Try to use system's temporary directory
            // as random name shouldn't exist
            $temp_file = tempnam( md5(uniqid(rand(), TRUE)), '' );
            if ( $temp_file )
            {
                $temp_dir = realpath( dirname($temp_file) );
                @unlink( $temp_file );
                return $temp_dir;
            }
            else
            {
                return FALSE;
            }
        }
    }
}
?>