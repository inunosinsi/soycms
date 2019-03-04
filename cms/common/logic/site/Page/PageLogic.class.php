<?php

class PageLogic extends SOY2LogicBase{

	/**
	 * pageIdを親に持つ子をすべて返す
	 */
    function getChildIds($pageId){
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	return $dao->getByParentPageId($pageId);
    }

    /**
     * pageIdを親に持つ子を再帰的にすべて取得します
     * @return array 子ページと自身のページのidの配列
     */
    function getAllChildIds($pageId){
    	$ret_val = array();
    	$this->_getAllChildIds($pageId,$ret_val);
    	return $ret_val;
    }

    /**
     * getAllChildIdsの再帰呼び出し関数
     *
     */
    private function _getAllChildIds($pageId,&$array){
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	foreach($dao->getByParentPageId($pageId) as $page){
    		$this->_getAllChildIds($page->getId(),$array);
    	}
    	return array_push($array,$pageId);
    }

    /**
     * pageId自身とその子ページすべてにtrashフラグをオンにする
     * 一つでも削除できないものがあるとロールバックされfalseが返る
     */
    function putTrash($pageId){
    	$ids = $this->getAllChildIds($pageId);
    	$dao = SOY2DAOFactory::create("cms.PageDAO");

    	$dao->begin();
    	foreach($ids as $id){
    		$page = $dao->getById($id);
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
    function removePage($pageId){
    	//自身も含まれる
    	$ids = $this->getAllChildIds($pageId);
    	//外部キー制約を満たすためにarrayを逆順にする
    	array_reverse($ids);
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	$blockDao = SOY2DAOFactory::create("cms.BlockDAO");
    	$dao->begin();
    	foreach($ids as $id){
    		$page = $dao->getById($id);
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
    function recoverPage($pageId){
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	$page = $dao->getById($pageId);
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
    function getById($id){
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	return $dao->getById($id);

    }

    /**
     * URIからページ情報を取得する
     */
    function getByUri($uri){
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	return $dao->getByUri($uri);

    }

    /**
     * すべてのページを取得する
     */
    function get(){
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
 		return $dao->get();
    }

    /**
     * ページの更新
     */
    function update($bean){
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	return $dao->update($bean);
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
    	$dao = SOY2DAOFactory::create("cms.PageDAO");
    	return $dao->updatePageConfig($bean);
    }

    /**
     * トップページを取得する（Previewの初期画面）
     */
    function getDefaultPage(){
    	$dao = SOY2DAOFactory::create("cms.PageDAO");

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
    		$dao = SOY2DAOFactory::create("cms.PageDAO");
    		$errorPageCount = $dao->countByPageType(Page::PAGE_TYPE_ERROR);
    	}catch(Exception $e){
    		return false;
    	}
    	return ($errorPageCount >0);
    }

	private $page;
	private $siteUrl;
	private $params = array();	//ブログページで使用する値を格納する entry→記事のエイリアス label→ラベルのエイリアス mode→ブログのモード等

	function buildCanonicalUrl(){
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

		if(strlen($this->page->getUri())){
			$url .= "/" . $this->page->getUri();
		}

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
									$url .= "/" . sprintf("%02d", $this->params["month"]);
									if(isset($this->params["day"]) && strlen($this->params["day"]) > 0){
										$url .= "/" . sprintf("%02d", $this->params["day"]);
									}
								}
							}
							break;
						default:
							if(strlen($this->page->getTopPageUri())){
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
