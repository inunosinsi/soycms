<?php

class PluginBlockUtil {

	public static function getTemplateByPageId($pageId){
		return self::__getTemplateByPageId($pageId);
	}

	private static function __getTemplateByPageId($pageId=null){
		static $templates;
		if(is_null($templates)) $templates = array();
		if(isset($templates[$pageId])) return $templates[$pageId];

		$blog = self::getBlogPageById($pageId);

		//ブログページを取得できた場合
		if(!is_null($blog) && !is_null($blog->getId())){
			$pathInfo = (isset($_SERVER["PATH_INFO"])) ? $_SERVER["PATH_INFO"] : null;
			if(is_null($pathInfo)) $pathInfo = (isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : null;
			//$pathInfo = (isset($_SERVER["PATH_INFO"])) ? $_SERVER["PATH_INFO"] : (isset($_SERVER["REQUEST_URI"])) ? $_SERVER["REQUEST_URI"] : null;
			//サイトIDを除く
			$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
			$uri = str_replace("/" . $siteId . "/", "/", $pathInfo);
			$uri = str_replace("/" . $_SERVER["SOYCMS_PAGE_URI"] . "/", "", $uri);

			//トップページ
			if(strlen($blog->getTopPageUri()) && $uri === (string)$blog->getTopPageUri()){
				if(strlen(trim($blog->getTopTemplate())) > 0){
					$templates[$pageId] = $blog->getTopTemplate();
				}

				//アーカイブページ
			}else if(
				(strlen($blog->getCategoryPageUri()) && strpos($uri, $blog->getCategoryPageUri()) === 0) ||
				(strlen($blog->getMonthPageUri()) && strpos($uri, $blog->getMonthPageUri()) === 0)
			){
				if(strlen(trim($blog->getArchiveTemplate())) > 0){
					$templates[$pageId] = $blog->getArchiveTemplate();
				}
				//記事ごとページ
			}else if(strlen($blog->getEntryPageUri()) && strpos($uri, $blog->getEntryPageUri()) === 0){
				if(strlen(trim($blog->getEntryTemplate()) > 0)){
					$templates[$pageId] = $blog->getEntryTemplate();
				}
			}

			//すべての条件を満たさなかった時は何らかのテンプレートを入れておく
			if(!isset($templates[$pageId])){
				if(strlen(trim($blog->getTopTemplate())) > 0){
					$templates[$pageId] = $blog->getTopTemplate();
				}else{
					if(strlen(trim($blog->getArchiveTemplate()))){
						$templates[$pageId] = $blog->getArchiveTemplate();
					}else{
						$templates[$pageId] = $blog->getEntryTemplate();
					}
				}

				//上記の対応でもまだ取得出来なかった場合は空文字を入れておく
				if(!isset($templates[$pageId])) $templates[$pageId] = "";
			}

		//ブログページ以外
		}else{
			$templates[$pageId] = self::getPageById($pageId)->getTemplate();
		}

		return (isset($templates[$pageId])) ? $templates[$pageId] : null;
	}

	public static function getBlockByPageId($pageId){
		return self::__getBlockByPageId($pageId);
	}

	private static function __getBlockByPageId($pageId){
		static $plugBlocks;
		if(is_null($plugBlocks)) $plugBlocks = array();
		if(isset($plugBlocks[$pageId])) return $plugBlocks[$pageId];
		try{
			$blocks = SOY2DAOFactory::create("cms.BlockDAO")->getByPageId($pageId);
		}catch(Exception $e){
			$blocks = array();
		}

		$plugBlocks[$pageId] = array();

		if(count($blocks)){
			foreach($blocks as $obj){
				if($obj->getClass() == "PluginBlockComponent"){
					$plugBlocks[$pageId][] = $obj;
				}
			}
		}

		return $plugBlocks[$pageId];
	}

	public static function getBlogPageByPageId($pageId){
		return self::getBlogPageById($pageId);
	}

	public static function getLimitByPageId($pageId){
		$template = self::__getTemplateByPageId($pageId);
		if(is_null($template)) return null;

		$blocks = self::__getBlockByPageId($pageId);
		if(!is_array($blocks) || !count($blocks)) return null;

		foreach($blocks as $block){
			if(preg_match('/(<[^>]*[^\/]block:id=\"' . $block->getSoyId() . '\"[^>]*>)/', $template, $tmp)){
				if(preg_match('/cms:count=\"(.*?)\"/', $tmp[1], $ctmp)){
					if(isset($ctmp[1]) && is_numeric($ctmp[1])) return (int)$ctmp[1];
				}
			}
		}

		return null;
	}

	public static function getSortRandomMode($pageId){
		$template = self::__getTemplateByPageId($pageId);
		if(is_null($template)) return false;

		$blocks = self::__getBlockByPageId($pageId);
		if(!is_array($blocks) || !count($blocks)) return false;

		foreach($blocks as $block){
			if(preg_match('/(<[^>]*[^\/]block:id=\"' . $block->getSoyId() . '\"[^>]*>)/', $template, $tmp)){
				if(preg_match('/cms:random=\"(.*?)\"/', $tmp[1], $ctmp)){
					if(isset($ctmp[1]) && is_numeric($ctmp[1]) && (int)$ctmp[1] === 1) return true;
				}
			}
		}

		return false;
	}

	public static function getLabelIdByPageId($pageId){
		$template = self::__getTemplateByPageId($pageId);
		if(is_null($template)) return null;

		$blocks = self::__getBlockByPageId($pageId);
		if(!is_array($blocks) || !count($blocks)) return null;

		foreach($blocks as $block){
			if(preg_match('/(<[^>]*[^\/]block:id=\"' . $block->getSoyId() . '\"[^>]*>)/', $template, $tmp)){
				if(preg_match('/cms:label=\"(.*?)\"/', $tmp[1], $ctmp)){
					if(isset($ctmp[1]) && is_numeric($ctmp[1])) return (int)$ctmp[1];
				}
			}
		}

		return null;
	}

	public static function getLabelIdsByPageId($pageId){
		$template = self::__getTemplateByPageId($pageId);
		if(is_null($template)) return null;

		$blocks = self::__getBlockByPageId($pageId);
		if(!is_array($blocks) || !count($blocks)) return null;

		foreach($blocks as $block){
			if(preg_match('/(<[^>]*[^\/]block:id=\"' . $block->getSoyId() . '\"[^>]*>)/', $template, $tmp)){
				if(preg_match('/cms:labels=\"(.*?)\"/', $tmp[1], $ctmp)){
					if(isset($ctmp[1]) && strlen($ctmp[1])){
						$v = str_replace("、", ",", $ctmp[1]);
						$values = explode(",", $v);
						if(count($values)){
							$labelIds = array();
							foreach($values as $v){
								$v = (int)trim($v);
								if(is_numeric($v) && $v > 0){
									$labelIds[] = $v;
								}
							}
							return $labelIds;
						}
					}
				}
			}
		}

		return array();
	}

	private static function getBlogPageById($pageId){
		static $pages;
		if(is_null($pages)) $pages = array();
		if(isset($pages[$pageId])) return $pages[$pageId];

		try{
			$pages[$pageId] = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($pageId);
		}catch(Exception $e){
			$pages[$pageId] = new BlogPage();
		}

		return $pages[$pageId];
	}

	private static function getPageById($pageId){
		static $pages;
		if(is_null($pages)) $pages = array();
		if(isset($pages[$pageId])) return $pages[$pageId];

		try{
			$pages[$pageId] = SOY2DAOFactory::create("cms.PageDAO")->getById($pageId);
		}catch(Exception $e){
			$pages[$pageId] = new Page();
		}

		return (isset($pages[$pageId])) ? $pages[$pageId] : new Page();
	}
}
