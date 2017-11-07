<?php

class CMSMessageManager {

	private $errorMessage = array();
	private $message = array();

	/**
	 * メッセージパス
	 */
	private $messagePath = array();

	/**
	 * メッセージデータ
	 */
	private $messageArray = null;

    function __construct() {}

    static function &getInstance(){
    	static $_instance;

    	if(!$_instance){
    		$messages = SOY2ActionSession::getFlashSession()->getAttribute("messages");
    		$errors = SOY2ActionSession::getFlashSession()->getAttribute("errorMessages");

    		$_instance = new CMSMessageManager();

    		if($messages)$_instance->addMessage($messages);
    		if($errors)$_instance->addErrorMessage($errors);
    	}

    	return $_instance;
    }

    public static function save(){
    	$instance = &self::getInstance();
    	SOY2ActionSession::getFlashSession()->setAttribute("messages",$instance->message);
    	SOY2ActionSession::getFlashSession()->setAttribute("errorMessages",$instance->errorMessage);
    }

    public static function addMessage($str){
    	$instance = &self::getInstance();

    	if(is_array($str)){
			$instance->message += $str;
    	}else{
    		$instance->message[] = $str;
    	}
    }

    public static function addErrorMessage($str){
    	$instance = &self::getInstance();

    	if(is_array($str)){
			$instance->errorMessage += $str;
    	}else{
    		$instance->errorMessage[] = $str;
    	}
    }

    public static function getMessages(){
    	$instance = &self::getInstance();
    	return $instance->message;
    }

    public static function getErrorMessages(){
    	$instance = &self::getInstance();
    	return $instance->errorMessage;
    }

    /**
     * メッセージファイルの追加
     * @param filepath ファイルは存在しなくてはいけません
     * @return boolean ファイルがない場合は失敗
     * @description メッセージファイルは追加された順番にパースされます。従って後に追加されたメッセージは前のメッセージを上書きします。またgetが呼ばれた後で追加されてもパースはおこなわれません。
     */
    static function addMessageFilePath($filePath){
    	$instance = &self::getInstance();
    	if(file_exists($filePath)){
    		$instance->messagePath[] = $filePath;
    		return true;
    	}else{
    		return false;
    	}
    }

    /**
     * メッセージディレクトリの追加
     * @param directoryPath メッセージファイルの詰まったディレクトリ　存在しないとダメです
     * @return boolean ディレクトリが存在しない場合は失敗
     * @description  指定したディレクトリ以下に存在する.meessageファイルをすべて登録する。再帰読み込みは行わず1層のみです。
     */
    static function addMessageDirectoryPath($directory){
		if($cd=opendir($directory)){
			while(false!==($file=readdir($cd))){
				if(!is_dir($directory.'/'.$file) && strrchr($file, ".") == ".message"){
					self::addMessageFilePath($directory.'/'.$file);
				}
			}
			closedir($cd);
		}
	}

    /**
     * メッセージの取得
     * @param key メッセージのキーです
     * @return string メッセージです
     * @description メッセージの取得を行います。一番初めに呼ばれた段階でメッセージファイルをパースし、それ以降はメッセージファイルが追加されてもパースはしません。
     */
    static function get($key,$replace = array()){
    	$instance = &self::getInstance();

    	if(is_null($instance->messageArray)){
    		$instance->messageArray = self::parse();
    	}
    	if(isset($instance->messageArray[$key])){
    		$tmpMsg = $instance->messageArray[$key];
    		//メッセージの置き換え
    		foreach($replace as $key => $value){
    			$tmpMsg = preg_replace('/%'.$key.'%/i',$value,$tmpMsg);
    		}
    		return $tmpMsg;
    	}else{
    		//throw new Exception($key."に対応するメッセージがありません");
    		if(defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"){
    			return '[ERROR] "'.$key.'"  に対応するメッセージがありません';
    		}else{
    			return '[ERROR] Message for "'.$key.'" is not found';
    		}

    	}
    }

    /**
     * メッセージファイルのパースを行います
     */
    private static function parse(){
    	$instance = &self::getInstance();
    	$result = array();
    	foreach($instance->messagePath as $path){
    		$hFile = fopen($path,"r");
    		while($line = fgets($hFile,1024)){
    			//コメントの除去
    			$line = preg_replace('/(\/\/.*)$/i','',$line);

    			//空白行ならばスキップ
    			if(trim($line) == ""){
    				continue;
    			}

    			//とりあえずpreg_matchで、遅いようなら考えよう
    			$match = array();
    			if(preg_match('/^([^=]+)=(.+)$/i',$line,$match)){
    				$result[trim($match[1])] = trim($match[2]);
    			}
    		}
    		fclose($hFile);
    	}
    	return $result;
    }
}
?>
