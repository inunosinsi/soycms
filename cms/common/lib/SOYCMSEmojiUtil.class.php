<?php

class SOYCMSEmojiUtil {

    var $map;	//Ezweb→他へのマッピング
	var $emoji; //キャリア毎の絵文字データ
	var $carrier;
	var $encoding = "SJIS";
	var $emojiUrl;	//PC用の絵文字URL

	/**
	 * 絵文字に変換
	 *
	 * 絵文字書式[e:絵文字番号]
	 *
	 */
	public static function replace($html,$encoding = "UTF-8"){

		$instance = SOYCMSEmojiUtil::getInstance();

		$agent = @$_SERVER["HTTP_USER_AGENT"];
		$pictDir = SOY2::RootDir()."lib/pictogram-1.1/";

		$instance->encoding = $encoding;
		$instance->map = json_decode(file_get_contents($pictDir."ezweb_convert.json"));
		$instance->carrier = $instance->getCarrier($agent);

		//絵文字画像ファイルの置いてあるURLを設定
		$emojiDir = str_replace("\\","/",dirname(SOY2::RootDir())."/mobile_imgs/");
		if(strstr($emojiDir,$_SERVER["DOCUMENT_ROOT"]) !== false){
			$instance->emojiUrl = str_replace($_SERVER["DOCUMENT_ROOT"],"",$emojiDir);

		//サブドメイン実行時の対策
		}else{
			$instance->emojiUrl = "/mobile_imgs/";
		}
		if($instance->emojiUrl[0] != "/")$instance->emojiUrl = "/".$instance->emojiUrl;

		switch($instance->carrier){
			case "docomo":
				$instance->emoji = json_decode(file_get_contents($pictDir."docomo_emoji.json"));
				break;
			case "softback":
				$instance->emoji = json_decode(file_get_contents($pictDir."softbank_emoji.json"));
				break;
			/*case "au":
				$instance->emoji = json_decode(file_get_contents($pictDir."ezweb_emoji.json"));
				break;*/
		}

		$softbank = json_decode(file_get_contents($pictDir."softbank_convert.json"));
		$softbank = $softbank->softbank;

		$regex = '/\[e:([0-9]+)\]/';
		return preg_replace_callback($regex,array($instance,"replace_emoji"),$html);
	}

	/**
	 * 絵文字用ファイルがインストールされているかどうかチェック
	 * @return boolean
	 */
	public static function isInstalled(){
		return file_exists(dirname(SOY2::RootDir())."/mobile_imgs/");
	}

	/**
	 * 絵文字入力部分
	 */
	public static function getEmojiInputPageUrl(){

		if(!defined("SOYCMS_LANGUAGE")||SOYCMS_LANGUAGE=="ja"){
		   return SOY2PageController::createRelativeLink("../mobile_imgs/index.html");
		}else{
		   return SOY2PageController::createRelativeLink("../mobile_imgs/index_".SOYCMS_LANGUAGE.".html");
		}

	}

	private static function getInstance(){
		static $_instance;

		if(!$_instance)$_instance = new SOYCMSEmojiUtil();

		return $_instance;
	}

	/**
	 * 絵文字の置き換え
	 */
	function replace_emoji($array){

		if(!is_numeric($array[1])){
			return $array[0];
		}

		switch($this->carrier){
			case "docomo":
				$str = $this->getImodeEmoji($array[1],$this->encoding);
				break;
			case "softback":
				$str = $this->getSoftBankEmoji($array[1]);
				break;
			case "au":
				$str = $this->getEzwebEmoji($array[1]);
				break;
			default:
				$str = $this->getOtherEmoji($array[1]);
				break;
		}

		return $str;
	}

	/**
	 * キャリアを判定する
	 * @return string キャリア
	 */
	function getCarrier($data){
		if(preg_match("/DoCoMo/i", $data)){
			return "docomo";// i-mode
		} else if(preg_match("/(J\-PHONE|Vodafone|MOT\-[CV]980|SoftBank|Semulator)/i", $data)){
			return "softback";// softbank
		} else if(preg_match("/KDDI\-/i", $data) || preg_match("/UP\.Browser/i", $data)){
			return "au";// ezweb
		} else if(preg_match("/^PDXGW/i", $data) || preg_match("/(DDIPOCKET|WILLCOM);/i", $data)){
			return "willcom";// willcom
		} else if(preg_match("/^L\-mode/i", $data)){
			return "lmode";// l-mode
		} else {
			return "etc";
		}
	}

	/**
	 * ソフトバンク絵文字を出力
	 * @param number $id Ezweb絵文字コード
	 */
	function getSoftBankEmoji($id){
		$sId = @$this->map->ezweb->$id->softbank;

		if(!is_numeric($sId)){
			return $sId;
		}

		$webcode = @$this->emoji->softbank->$sId->webcode;
		return pack("H*","1B24" . $webcode . "0F");
	}

	/**
	 * i-mode絵文字を出力
	 * @param number $id Ezweb絵文字コード
	 */
	function getImodeEmoji($id,$encoding = "SJIS"){
		$sId = @$this->map->ezweb->$id->docomo;

		if(!is_numeric($sId)){
			return mb_convert_encoding($sId,$encoding,"UTF-8");
		}

		$code = (array)$this->emoji->docomo->$sId;
		$code = "&#x" . $code['unicode'] .";";
		return $code;
	}

	/**
	 * Ezweb絵文字を出力
	 * @param number $id Ezweb絵文字コード
	 */
	function getEzwebEmoji($id,$encoding = "SJIS"){
		return "<img localsrc=\"$id\" />";
	}

	/**
	 * その他の絵文字を出力
	 * @param number $id Ezweb絵文字コード
	 */
	function getOtherEmoji($id,$encoding = "SJIS"){
		return '<img src="'.$this->emojiUrl.$id.'.gif" width="14" height="15" />';
	}
}
?>