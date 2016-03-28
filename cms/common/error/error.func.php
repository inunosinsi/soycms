<?php
function get_report($e){
	$str = array();

	$str[] = 'DETECT DATE: '.date('c');
	$str[] = '';
	$str[] = get_exception_report($e);
	$str[] = '';
	$str[] = get_soycms_report();
	$str[] = '';
	$str[] = '';
	$str[] = 'STACK TRACE';
	$str[] = get_exception_trace($e);
	$str[] = '';
	$str[] = 'Server Environment';
	$str[] = get_environment_report();
	$str[] = '';
	$str[] = 'SOY CMS Options';
	$str[] = get_soycms_options();

	return implode("\n",$str);
}

function get_soycms_report(){
	$str = array();

	$str[] = 'SOY CMS Version:           '.SOYCMS_VERSION;
	$str[] = 'SOY CMS Build Date:        '.SOYCMS_BUILD;
	$str[] = 'SOY CMS DB Type:           '.SOYCMS_DB_TYPE;
	$str[] = 'SOY2RootDir:               '.SOY2::RootDir();
	$str[] = 'SOY2_DOCUMENT_ROOT:        '.( (defined("SOY2_DOCUMENT_ROOT")) ? SOY2_DOCUMENT_ROOT : "undefined");

	return implode("\n",$str);
}

function get_soycms_options(){
	$str = array();

	$str[] = 'SOYCMS_ALLOWED_EXTENSIONS: '.( (defined("SOYCMS_ALLOWED_EXTENSIONS")) ? SOYCMS_ALLOWED_EXTENSIONS : "undefined");
	$str[] = 'SOYCMS_ALLOW_PHP_SCRIPT:   '.( (defined("SOYCMS_ALLOW_PHP_SCRIPT")) ? SOYCMS_ALLOW_PHP_SCRIPT : "undefined");
	$str[] = 'SOYCMS_SKIP_MOBILE_RESIZE: '.( (defined("SOYCMS_SKIP_MOBILE_RESIZE")) ? SOYCMS_SKIP_MOBILE_RESIZE : "undefined");
	$str[] = 'SOYCMS_BLOCK_LIST:         '.( (defined("SOYCMS_BLOCK_LIST")) ? strtr(SOYCMS_BLOCK_LIST,array("," => "\n                           ")) : "undefined");
	$str[] = 'SOYCMS_TARGET_DIRECTORY:   '.( (defined("SOYCMS_TARGET_DIRECTORY")) ? SOYCMS_TARGET_DIRECTORY : "undefined");
	$str[] = 'SOYCMS_TARGET_URL:         '.( (defined("SOYCMS_TARGET_URL")) ? SOYCMS_TARGET_URL : "undefined");
	$str[] = 'SOYCMS_ADMIN_ROOT:         '.( (defined("SOYCMS_ADMIN_ROOT")) ? SOYCMS_ADMIN_ROOT : "undefined");
	$str[] = 'SOYCMS_LANGUAGE:           '.( (defined("SOYCMS_LANGUAGE")) ? SOYCMS_LANGUAGE : "undefined");

	return implode("\n",$str);

}

function get_exception_report($e){
	$str = array();

	$document_root = $_SERVER["DOCUMENT_ROOT"];
	$file = str_replace("\\","/",$e->getFile());
	$file = str_replace($document_root,"",$file);

	$str[] = 'MESSAGE: '.get_exception_message($e);
	$str[] = 'EXCEPTION TYPE: '.get_class($e);
	$str[] = 'LOCATION: '.$file." (".$e->getLine().")";

	return implode("\n",$str);
}

function get_exception_trace($e){
	$str = array();

	$trace = $e->getTrace();
	for($i = 0; $i < min( 5 , count($e->getTrace()) ); $i++){
		$str[] = get_trace_report($trace[$i],$i);
	}

	return implode("\n",$str);
}

function get_environment_report(){
	$str = array();

	$str[] = 'PHP Version:          '.phpversion();
	$str[] = '';
	$str[] = 'PHP SAPI NAME:        '.php_sapi_name();
	$str[] = 'PHP SAFE MODE:        '.(ini_get("safe_mode")? "Yes" : "No");
	$str[] = 'MAGIC_QUOTE_GPC:      '.( get_magic_quotes_gpc() ? "Yes" : "No" );
	$str[] = 'SHORT_OPEN_TAG:       '.( ini_get("short_open_tag") ? "Yes" : "No" );
	$str[] = '';
	$str[] = 'MEMORY_LIMIT:         '.ini_get("memory_limit")." Bytes";
	if(function_exists("memory_get_usage")){
		$str[] = 'Memory Usage:         '.number_format(memory_get_usage())." Bytes";
		$str[] = '                      '.number_format(memory_get_usage(true))." Bytes (Real)";
	}
	if(function_exists("memory_get_peak_usage")){
		$str[] = '                      '.number_format(memory_get_peak_usage())." Bytes (Peak)";
		$str[] = '                      '.number_format(memory_get_peak_usage(true))." Bytes (Peak, Real)";
	}
	$str[] = '';
	$str[] = 'MAX_EXECUTION_TIME:   '.ini_get("max_execution_time") ." sec.";
	$str[] = 'POST_MAX_SIZE:        '.ini_get("post_max_size")." Bytes";
	$str[] = 'UPLOAD_MAX_FILESIZE:  '.ini_get("upload_max_filesize")." Bytes";
	$str[] = '';
	$str[] = 'mb_string:            '.( extension_loaded("mbstring") ? "Yes" : "No" );
	$str[] = 'PDO:                  '.( extension_loaded("PDO") ? "Yes" : "No" );
	$str[] = 'PDO_SQLite:           '.( extension_loaded("PDO_SQLITE") ? "Yes" : "No" );
	$str[] = 'PDO_MySQL:            '.( extension_loaded("PDO_MySQL") ? "Yes" : "No" );
	$str[] = 'Standard PHP Library: '.( extension_loaded("SPL") ? "Yes" : "No" );
	$str[] = 'SimpleXML:            '.( extension_loaded("SimpleXML") ? "Yes" : "No" );
	$str[] = 'JSON:                 '.( extension_loaded("json") ? "Yes" : "No" );
	$str[] = 'Services_JSON:        '.( class_exists("Services_JSON") ? "Yes" : "No" );
	$str[] = 'ZIP:                  '.( extension_loaded("zip") ? "Yes" : "No" );
	$str[] = 'ZipArchive:           '.( class_exists("ZipArchive") ? "Yes" : "No" );
	$str[] = 'Archive_Zip:          '.( class_exists("Archive_Zip") ? "Yes" : "No" );
	$str[] = 'OpenSSL:              '.( extension_loaded("openssl") ? "Yes" : "No" );
	$str[] = 'HASH:                 '.( extension_loaded("hash") ? "Yes" : "No" );
	$str[] = 'GD:                   '.( extension_loaded("GD") ? "Yes" : "No" );
	$str[] = '';
	$str[] = 'Module/CGI            '.( (stripos(php_sapi_name(),"cgi")!==false) ? "CGI" : "Module");
	$str[] = 'Rewrite               '.( function_exists("apache_get_modules") ? ( in_array("mod_rewrite", apache_get_modules()) ? "OK" : "NG") : "Unknown");
	$str[] = '';
	$str[] = 'USER_AGENT:           '.@$_SERVER["HTTP_USER_AGENT"];
	$str[] = 'REQUEST_URI:          '.@$_SERVER["REQUEST_URI"];
	$str[] = 'SCRIPT_NAME:          '.@$_SERVER["SCRIPT_NAME"];
	$str[] = 'PATH_INFO:            '.@$_SERVER["PATH_INFO"];
	$str[] = 'QUERY_STRING:         '.@$_SERVER["QUERY_STRING"];
	$str[] = '';
	$str[] = 'DOCUMENT_ROOT:        '.@$_SERVER["DOCUMENT_ROOT"];
	$str[] = 'SCRIPT_FILENAME:      '.@$_SERVER["SCRIPT_FILENAME"];

	return implode("\n",$str);
}

function get_exception_message($e){
	if($e instanceof SOY2DAOException OR $e instanceof PDOException){
		return $e->getMessage()." (".$e->getPDOExceptionMessage().")";
	}else{
		return $e->getMessage();
	}
}

function get_trace_report($trace,$index){

	$document_root = $_SERVER["DOCUMENT_ROOT"];
	@$file = str_replace("\\","/",$trace["file"]);
	$file = str_replace($document_root,"",$file);

	$str = array();
	$str[] = '-----------------------';
	@$str[] = $index. ":".$trace["class"].$trace["type"].$trace["function"];
	for($i = 0; $i<count(@$trace["args"]); $i++){
		$str[] = "\t".'argument['.$i.']: '.get_argument_string($trace["args"][$i]);
	}
	$str[] = '';
	@$str[] = "\t".''.$file."(".$trace["line"].")";

	return implode("\n",$str);
}

function get_argument_string($arg){
	if(is_string($arg)){
		return 'String("'.$arg.'")';
	}else if(is_int($arg)){
		return $arg;
	}else if(is_bool($arg)){
		return ($arg)? "true" : "false";
	}else if(is_null($arg)){
		return "null";
	}else if(is_resource($arg)){
		return "resource";
	}else if(is_object($arg)){
		if(method_exists($arg,"__toString")){
			return get_class($arg)." [\"".(string)$arg."\"]";
		}else{
			return get_class($arg)." [".preg_replace("/\\n  /xms", "\n\t", var_export($arg, true))."]";
		}
	}else if(is_array($arg)){
		return preg_replace("/\\n  /xms", "\n\t", var_export($arg, true));
	}else{
		return "unknown type argument";
	}
}

/**
 * エラーの解決方法を出力する
 * @return text/html 必要に応じてエスケープされたHTML
 */
function get_resolve_message($e){
	if(method_exists($e,"getResolve")){
		return htmlspecialchars($e->getResolve(), ENT_QUOTES, "UTF-8");
	}

	if($e instanceof SOY2HTMLException){
		if(!is_writable(SOY2HTMLConfig::CacheDir())){
			return
				'SOY2HTMLはキャッシュファイルの生成に失敗しました。<br>'.
				'現在のキャッシュディレクトリは<br><span style="margin-left:10px">'.htmlspecialchars(str_replace("\\","/",SOY2HTMLConfig::CacheDir()), ENT_QUOTES, "UTF-8").'</span><br>となっています。<br>'.
				'キャッシュディレクトリが存在するか、また書き込み権限があるかなどを確認してください。';
		}
	}else if($e instanceof SOY2DAOException){
		return
			'データベースへのアクセス中にエラーが発生しました。。<br>'.
			'<ul style="margin-left:50px;font-size:small;list-style-type:circle">'.
				'<li>SOY CMSのアップデートでデータベースの仕様が変更された可能性があります。<a href="http://www.soycms.net/">公式ページ</a>をご確認ください。</li>'.
				'<li>データベースへのアクセス権限が無い可能性があります。アクセス権限を確認してください。</li>'.
			'</ul>';
	}else if($e instanceof PDOException){
		return
			'データベースへのアクセス中にエラーが発生しました。<br>'.
			'SOY CMSのアップデートを行った直後にこのエラーが発生した場合は、データベースの仕様変更があった可能性があります。'.
			'<a href="http://www.soycms.net/">公式ページ</a>にてご確認ください。';
	}

	return '開発元にご連絡ください。';

}
