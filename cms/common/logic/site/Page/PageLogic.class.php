<?php

class PageLogic extends SOY2LogicBase{

	/**
	 * pageIdを親に持つ子をすべて返す
	 */
    function getChildIds(int $pageId){
    	return soycms_get_hash_table_dao("page")->getByParentPageId($pageId);
    }

    /**
     * pageIdを親に持つ子を再帰的にすべて取得します
     * @return array 子ページと自身のページのidの配列
     */
    function getAllChildIds(int $pageId){
    	$ret_val = array();
    	$this->_getAllChildIds($pageId, $ret_val);
    	return $ret_val;
    }

    /**
     * getAllChildIdsの再帰呼び出し関数
     *
     */
    private function _getAllChildIds(int $pageId,&$array){
    	foreach(soycms_get_hash_table_dao("page")->getByParentPageId($pageId) as $page){
    		$this->_getAllChildIds($page->getId(),$array);
    	}
    	return array_push($array,$pageId);
    }

    /**
     * pageId自身とその子ページすべてにtrashフラグをオンにする
     * 一つでも削除できないものがあるとロールバックされfalseが返る
     */
    function putTrash(int $pageId){
    	$ids = $this->getAllChildIds($pageId);
    	$dao = soycms_get_hash_table_dao("page");

    	$dao->begin();
    	foreach($ids as $id){
			$page = soycms_get_page_object($id);
    		if($page->isDeletable()){
    			$dao->updateTrash($id,1);
    		}else{
    			$dao->rollback();
    			return false;
    		}
    	}
    	$dao->updateTrash($pageId,1);
    	$dao->commit();
    	return true;
    }

    /**
     * pageId自身とその子ページすべてをDBから削除する
     * 一つでも削除できないものがあるとロールバックされfalseが返る
     */
    function removePage(int $pageId){
    	//自身も含まれる
    	$ids = $this->getAllChildIds($pageId);
    	//外部キー制約を満たすためにarrayを逆順にする
    	array_reverse($ids);
    	$dao = soycms_get_hash_table_dao("page");
    	$blockDao = soycms_get_hash_table_dao("block");
    	$dao->begin();
    	foreach($ids as $id){
			$page = soycms_get_page_object($id);
    		if($page->isDeletable()){
    			//ページを削除
    			$dao->delete($id);
    			//Blockも削除する
    			$blockDao->deleteByPageId($id);
    		}else{
    			$dao->rollback();
    			return false;
    		}
    	}
    	$dao->commit();
    	return true;
    }

    /**
     * ページの復元を行う
     * -親ページがゴミ箱の中　→　ページルートに復元
     * -親ページが健在　　　　→　その親の元に復元
     */
    function recoverPage(int $pageId){
    	$dao = soycms_get_hash_table_dao("page");
    	$page = soycms_get_page_object($pageId);
    	$dao->begin();

    	//戻す位置の決定
    	if(is_null($page->getParentPageId())){
    		//do nothing
    	}else{
    		$parentPage = $dao->getById($page->getParentPageId());
    		if($parentPage->getIsTrash() == 1){
    			$page->setParentPageId(null);
    		}else{
    			//do nothing
    		}
    	}

    	$page->setIsTrash(0);
    	$dao->update($page);

    	$ids = $this->getAllChildIds($pageId);
    	foreach($ids as $id){
    		$dao->updateTrash($id,0);
    	}
    	$dao->commit();


    	return true;
    }

    /**
     * IDからページ情報を取得する
     */
    function getById(int $id){
		return soycms_get_page_object($id);
    }

    /**
     * URIからページ情報を取得する
     */
    function getByUri(string $uri){
    	return soycms_get_hash_table_dao("page")->getByUri($uri);

    }

    /**
     * すべてのページを取得する
     */
    function get(){
 		return soycms_get_hash_table_dao("page")->get();
    }

    /**
     * ページの更新
     */
    function update($bean){
    	return soycms_get_hash_table_dao("page")->update($bean);
    }

    /**
     * テンプレートの履歴のリストを取得する
     */
    function getHistoryList($pageId){
    	$dao = SOY2DAOFactory::create("cms.TemplateHistoryDAO");
    	return $dao->getByPageId($pageId);
    }

    /**
     * テンプレートの履歴を取得する
     */
    function getHistoryById($histId){
    	$dao = SOY2DAOFactory::create("cms.TemplateHistoryDAO");
    	return $dao->getById($histId);
    }

    /**
     * ページのコンフィグオブジェクトを更新する
     */
    function updatePageConfig($bean){
    	return soycms_get_hash_table_dao("page")->updatePageConfig($bean);
    }

    /**
     * トップページを取得する（Previewの初期画面）
     */
    function getDefaultPage(){
    	$dao = soycms_get_hash_table_dao("page");

    	//トップページの候補
    	$defaultUris = array(
    		"",//このページがあるならPreviewActionでURIが空で取れているからここには来ないけど
    		"index.html",
			"index.htm",
			"index.php",
			"index",
			"top.html",
			"top.htm",
			"top"
    	);

    	//見つかったらそれ
    	foreach($defaultUris as $uri){
	    	try{
	    		return $dao->getByUri($uri);
	    	}catch(Exception $e){
	    		//
	    	}
    	}

    	//見つからないならIDの小さいもの
    	//まずは標準ページ
    	try{
    		$pages = $dao->getByPageType(Page::PAGE_TYPE_NORMAL);
    		if(count($pages)>0){
    			return array_shift($pages);//@index idなので$pages[0]ではだめ
    		}
    	}catch(Exception $e){
    		//
    	}

    	//次に全体から探す
    	try{
    		//PageType順、ID順：404ページは一番最後
	    	$dao->setLimit(1);
    		$pages = $dao->get();
    		return array_shift($pages);
    	}catch(Exception $e){
    		//
    	}

    	throw new Exception("No Page.");
    }

    function hasMultipleErrorPage(){
    	try{
    		$errorPageCount = soycms_get_hash_table_dao("page")->countByPageType(Page::PAGE_TYPE_ERROR);
    	}catch(Exception $e){
    		return false;
    	}
    	return ($errorPageCount >0);
    }

	private $page;
	private $siteUrl;
	private $params = array();	//ブログページで使用する値を格納する entry→記事のエイリアス label→ラベルのエイリアス mode→ブログのモード等

	/**
	 * @return string
	 */
	function buildCanonicalUrl(){
		static $url;
		if(isset($url)) return $url;

		$url = self::_buildUrlCommon();

		switch($this->page->getPageType()){
			case Page::PAGE_TYPE_BLOG:
				if(isset($this->params["mode"])){
					switch($this->params["mode"]){
						case "_entry_":
							$alias = (isset($this->params["entry"])) ? $this->params["entry"] : null;
							if(strlen($this->page->getEntryPageUri())){
								$url .= "/" . $this->page->getEntryPageUri();
							}
							$url .= "/" . rawurlencode($alias);
							break;
						case "_category_":
							$alias = (isset($this->params["label"])) ? $this->params["label"] : null;
							if(strpos($alias, " ")) $alias = str_replace(" ", "_", $alias);
							if(strlen($this->page->getCategoryPageUri())){
								$url .= "/" . $this->page->getCategoryPageUri();
							}
							$url .= "/" . rawurlencode($alias);
							break;
						case "_month_":
							if(strlen($this->page->getMonthPageUri())){
								$url .= "/" . $this->page->getMonthPageUri();
							}
							if(isset($this->params["year"]) && strlen($this->params["year"]) === 4){
								$url .= "/" . $this->params["year"];
								if(isset($this->params["month"]) && strlen($this->params["month"]) > 0){
									$_v = sprintf("%02d", $this->params["month"]);
									if($_v != "00"){
										$url .= "/" . $_v;
										if(isset($this->params["day"]) && strlen($this->params["day"]) > 0){
											$_v = sprintf("%02d", $this->params["day"]);
											if($_v != "00"){
												$url .= "/" . $_v;
											}
										}
									}
								}
							}
							break;
						default:
							if(strlen((string)$this->page->getTopPageUri())){
								$url .= "/" . $this->page->getTopPageUri();
							}
					}
					break;
				}
			default:
				//何もしない
		}
		
		return $url;
	}

	/**
	 * @return string
	 */
	function buildShortLinkUrl(){
		static $url;
		if(!is_null($url)) return $url;

		$url = "";
		if($this->page->getPageType() != Page::PAGE_TYPE_BLOG || !isset($this->params["mode"]) || !isset($this->params["id"]) || !is_numeric($this->params["id"])) return $url;

		//ショートリンクが存在する分だけ
		switch($this->params["mode"]){
			case "_entry_":
				$url = self::_buildUrlCommon();
				if(strlen($this->page->getEntryPageUri())){
					$url .= "/" . $this->page->getEntryPageUri();
				}
				$url .= "/" . $this->params["id"];
				break;
			case "_category_":
				$url = self::_buildUrlCommon();
				if(strlen($this->page->getCategoryPageUri())){
					$url .= "/" . $this->page->getCategoryPageUri();
				}
				$url .= "/" . $this->params["id"];
				break;
			default:
				//何もしない
		}
		
		return $url;
	}

	private function _buildUrlCommon(){
		static $url;
		if(isset($url)) return $url;

		//siteConfigから取得したサイトのURL
		if(strlen($this->siteUrl)){
			$url = rtrim($this->siteUrl, "/");
			//http→https
			if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on"){
				if(strpos($url, "http://") != false){
					$url = str_replace("http:", "https:", $url);
				}
			//https→http
			}else{
				if(strpos($url, "https://") != false){
					$url = str_replace("https:", "http:", $url);
				}
			}
		}else{
			$http = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https" : "http";
			$url = $http . "://" . $_SERVER["HTTP_HOST"];

			//ルート設定
			$isDocumentRoot = false;
			$documentRoot = $_SERVER["DOCUMENT_ROOT"];
			if(file_exists($documentRoot . "/index.php") && file_exists($documentRoot . "/.htaccess")){
				$f = fopen($documentRoot . "/.htaccess", "r");
				if($f){
					while ($line = fgets($f)) {
						if(strpos($line, "generated by SOY CMS") !== false){
							$isDocumentRoot = true;
							break;
						}
					}
				}
				fclose($f);
			}

			if(!$isDocumentRoot){
				$url .= "/" . self::getSiteId();
			}
		}

		// 多言語化
		if(defined("SOYCMS_PUBLISH_LANGUAGE") && SOYCMS_PUBLISH_LANGUAGE != "jp"){
			SOY2::import("site_include.plugin.util_multi_language.util.SOYCMSUtilMultiLanguageUtil");
			$prefix = SOYCMSUtilMultiLanguageUtil::getLanguagePrefix(SOYCMS_PUBLISH_LANGUAGE);
			if(strlen($prefix) && !preg_match('/\/'.$prefix.'$/', $url)){
				$url .= "/".$prefix;
			}
		}

		if(strlen($this->page->getUri())){
			$url .= "/" . $this->page->getUri();
		}
		return $url;
	}

	private function getSiteId(){
		static $siteId;
		if(is_null($siteId)) $siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");
		return $siteId;
	}

	function setPage($page){
		$this->page = $page;
	}
	function setSiteUrl($siteUrl){
		$this->siteUrl = $siteUrl;
	}
	function setParams($params){
		$this->params = $params;
	}
}
