<?php

class PluginBlockUtil {

	/**
	 * @param int
	 * @return string
	 */
	public static function getTemplateByPageId(int $pageId){
		return self::__getTemplateByPageId($pageId);
	}

	/**
	 * @param int
	 * @return string
	 */
	private static function __getTemplateByPageId(int $pageId=0){
		static $templates;
		if(is_null($templates)) $templates = array();
		if(isset($templates[$pageId])) return $templates[$pageId];

		$blog = soycms_get_blog_page_object($pageId);
		
		//ブログページを取得できた場合
		if($blog instanceof BlogPage && is_numeric($blog->getId()) && $blog->getId() == $pageId){
			
			// テンプレートを取得
			switch(SOYCMS_BLOG_PAGE_MODE){
				case CMSBlogPage::MODE_TOP:
					$templates[$pageId] = $blog->getTopTemplate();
					break;
				case CMSBlogPage::MODE_MONTH_ARCHIVE:
				case CMSBlogPage::MODE_CATEGORY_ARCHIVE:
					$templates[$pageId] = $blog->getArchiveTemplate();
					break;
				case CMSBlogPage::MODE_ENTRY:
					$templates[$pageId] = $blog->getEntryTemplate();
					break;
				default:	//すべての条件を満たさなかった時は何らかのテンプレートを入れておく
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
					break;
			}

		//ブログページ以外
		}else{
			$templates[$pageId] = soycms_get_page_object($pageId, false)->getTemplate();
		}

		return (isset($templates[$pageId])) ? $templates[$pageId] : "";
	}

	public static function getBlockByPageId(int $pageId){
		return self::__getBlockByPageId($pageId);
	}

	/**
	 * @param int, string
	 * @return string
	 */
	public static function getSoyIdByPageIdAndPluginId(int $pageId, string $pluginId){
		$blocks = self::__getBlockByPageId($pageId);
		if(!count($blocks)) return "";

		if(!class_exists("PluginBlockComponent")) SOY2::import("site_include.block.PluginBlockComponent.block", ".php");
		foreach($blocks as $block){
			$pageObj = soy2_unserialize((string)$block->getObject());
			if($pageObj->getPluginId() == $pluginId){
				return $block->getSoyId();
			}
		}

		return "";
	}

	/**
	 * @param int
	 * @return array
	 */
	private static function __getBlockByPageId(int $pageId){
		static $plugBlocks;
		if(is_null($plugBlocks)) $plugBlocks = array();
		if(isset($plugBlocks[$pageId])) return $plugBlocks[$pageId];
		try{
			$blocks = soycms_get_hash_table_dao("block")->getByPageId($pageId);
		}catch(Exception $e){
			$blocks = array();
		}

		$plugBlocks[$pageId] = array();

		if(count($blocks)){
			foreach($blocks as $obj){
				if($obj->getClass() != "PluginBlockComponent") continue;
				$plugBlocks[$pageId][] = $obj;
			}
		}
		
		return $plugBlocks[$pageId];
	}

	/**
	 * @param int, string
	 * @return int
	 */
	public static function getLimitByPageId(int $pageId, string $soyId=""){
		$template = self::__getTemplateByPageId($pageId);
		if(is_null($template)) return 0;

		$blocks = self::__getBlockByPageId($pageId);
		if(!is_array($blocks) || !count($blocks)) return 0;

		foreach($blocks as $block){
			//soyIdに指定がある場合は正規表現をする前にチェック
			if(strlen($soyId) && $block->getSoyId() != $soyId) continue;

			if(preg_match('/(<[^>]*[^\/]block:id=\"' . $block->getSoyId() . '\"[^>]*>)/', $template, $tmp)){
				if(preg_match('/cms:count=\"(.*?)\"/', $tmp[1], $ctmp)){
					if(isset($ctmp[1]) && is_numeric($ctmp[1])) return (int)$ctmp[1];
				}
			}
		}

		return 0;
	}

	/**
	 * @param int
	 * @return bool
	 */
	public static function getSortRandomMode(int $pageId){
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

	/**
	 * @param int, string
	 * @return int
	 */
	public static function getLabelIdByPageId(int $pageId, string $soyId=""){
		$template = self::__getTemplateByPageId($pageId);
		if(is_null($template)) return 0;

		$blocks = self::__getBlockByPageId($pageId);
		if(!is_array($blocks) || !count($blocks)) return 0;

		foreach($blocks as $block){
			//soyIdに指定がある場合は正規表現をする前にチェック
			if(strlen($soyId) && $block->getSoyId() != $soyId) continue;

			if(preg_match('/(<[^>]*[^\/]block:id=\"' . $block->getSoyId() . '\"[^>]*>)/', $template, $tmp)){
				if(preg_match('/cms:label=\"(.*?)\"/', $tmp[1], $ctmp)){
					if(isset($ctmp[1]) && is_numeric($ctmp[1])) return (int)$ctmp[1];
				}
			}
		}

		return 0;
	}

	/**
	 * @param int
	 * @return array
	 */
	public static function getLabelIdsByPageId(int $pageId){
		$template = self::__getTemplateByPageId($pageId);
		if(is_null($template)) return array();

		$blocks = self::__getBlockByPageId($pageId);
		if(!is_array($blocks) || !count($blocks)) return array();

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
}
