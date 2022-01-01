<?php

class EntryInfoUtil {

	const MODE_REACQUIRE = 1;	//記事詳細でメタ情報が無い場合はトップページから再取得する
	const MODE_NONE = 0;	//記事詳細でメタ情報が無い場合はトップページから再取得しない
	const FIELD_ID = "entry_info_keyword_plugin";

	const TYPE_KEYWORD = 0;
	const TYPE_DESCRIPTION = 1;

    /**
	 * キーワードを取得
	 * @param int entryId
	 * @return string keyword
	 */
	public static function getEntryKeyword(int $entryId=0){
		if(!is_numeric($entryId) || $entryId === 0) return "";

		$keyword = (string)soycms_get_entry_attribute_object($entryId, self::FIELD_ID)->getValue();
		if(strlen($keyword)) return $keyword;

		/** 互換性 **/
		$dao = new SOY2DAO();

		try{
			$res = $dao->executeQuery("select keyword from Entry where id = :id",
				array(":id" => $entryId)
			);
		}catch(Exception $e){
			return "";
		}

		
		return (isset($res[0]["keyword"])) ? $res[0]["keyword"] : "";
	}

	public static function save(int $entryId, string $keyword){
		$attr = soycms_get_entry_attribute_object($entryId, self::FIELD_ID);
		$attr->setValue($keyword);
		soycms_save_entry_attribute_object($attr);
	}
	
	public static function getBlogTopMetaValue(int $typ=0){
		static $html;
		if(is_null($html)){
			$url = substr($_SERVER["REQUEST_URI"], 0, strrpos($_SERVER["REQUEST_URI"] ,"/"));
			$url = substr($url, 0, strrpos($url ,"/"));
			$http = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
			$html = @file_get_contents($http . "://" . $_SERVER["HTTP_HOST"] . $url);
			if(!$html){
				$html = "";
			}else{
				$html = substr($html, 0, strpos($html, "</head>"));
				$html = substr($html, strpos($html, "<head") + 5);
				if(strpos($html, "<link")){
					$html = preg_replace('/<link.*>/', "", $html);
				}
				if(strpos($html, "<script")){
					$html = preg_replace('/<script.*\/script>/s', "", $html);
				}
				if(strpos($html, "<style")){
					$html = preg_replace('/<style.*\/style>/s', "", $html);
				}
				if(strpos($html, "<title")){
					$html = preg_replace('/<title.*\/title>/s', "", $html);
				}

				$html = trim($html);
			}
		}

		switch($typ){
			case self::TYPE_KEYWORD:
				$str = "keyword";
				break;
			case self::TYPE_DESCRIPTION:
				$str = "description";
				break;
		}
		
		if(!strlen($html) || is_bool(strpos($html, $str))) return "";

		$txt = substr($html, strpos($html, $str));
		$txt = substr($txt, strpos($txt, "=") + 2);
		return trim(substr($txt, "0", strpos($txt, "\"")), "\"");
	}
}