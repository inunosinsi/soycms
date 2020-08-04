<?php
class MessageManager{

    const MODE_ADMIN = "admin";
    const MODE_CART = "cart";
    const MODE_MYPAGE = "mypage";

    /**
     * メッセージパス
     */
    private $messagePath = array();

    /**
     * メッセージデータ
     */
    private $messageArray = null;

    private $messageDir = null;

    function __construct(){}

    static function &getInstance(){
         static $_instance;

        if(!$_instance){
            $_instance = new MessageManager();

            if(is_null($_instance->messageDir)){
                if(!defined("SOYSHOP_PUBLISH_LANGUAGE")) define("SOYSHOP_PUBLISH_LANGUAGE", "jp");

                //多言語の翻訳ディレクトリがない場合は日本語用
                if(is_dir(SOY2::RootDir() . "message/language/" . SOYSHOP_PUBLISH_LANGUAGE . "/")){
                    $_instance->messageDir = SOY2::RootDir() . "message/language/" . SOYSHOP_PUBLISH_LANGUAGE . "/";
                }else{
                    $_instance->messageDir = SOY2::RootDir() . "message/language/jp/";
                }

            }
        }

        return $_instance;
    }

    static function addMessagePath($dirName){
        $instance = &self::getInstance();
        $dir = $instance->messageDir . $dirName;
        $messagePath = self::getFilesByDirectory($dir);

        $commonDir = $instance->messageDir . "common";
        $instance->messagePath = array_merge($messagePath, self::getFilesByDirectory($commonDir));

        return (count($instance->messagePath));
    }

    public static function get($key, $replace = array()){
        $instance = &self::getInstance();

        if(is_null($instance->messageArray)){
            $instance->messageArray = self::parse();
        }

        if(isset($instance->messageArray[$key])){
            $tmpMsg = $instance->messageArray[$key];
            //メッセージの置き換え
            foreach($replace as $key => $value){
				$tmpMsg = preg_replace('/%' . $key . '%/i', $value, $tmpMsg);
            }
			return $tmpMsg;
        }else{
            //throw new Exception($key."に対応するメッセージがありません");
            if(SOYSHOP_PUBLISH_LANGUAGE == "jp"){
                return '[ERROR] "' . $key . '"  に対応するメッセージがありません';
            }else{
                return '[ERROR] Message for "' . $key . '" is not found';
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

    /**
     * ディレクトリ内のファイルを取得します
     */
    private static function getFilesByDirectory($dir){
        $files = scandir($dir);
        $messagePath = array();
        foreach($files as $file){
            if(preg_match('/^(.*).message/',$file) && file_exists($dir . "/" . $file)){
                $messagePath[] = $dir . "/" . $file;
            }
        }
        return $messagePath;
    }
}
