<?php

class CMSPageLinkPlugin extends PluginBase{
	protected $_soy2_prefix = "cms";

	var $siteRoot;

	function setSiteRoot($root){
		$this->siteRoot = $root;
	}

	/**
	 * executePluginで<?php ... ?>を記述するためにオーバーライド
	 */
	function getStartTag(){
		if($this->tag == "!--")return '';

		$attributes = array();

		foreach($this->_attribute as $key => $value){

			if(is_object($value)){
				$value = $value->__toString();
			}

			if(!preg_match("/$key=\"/i",$value)){
				if($key == "href"){
					$value = $key."=\"".$value."\"";//executePluginでPHPコードが入っているのでhrefだけはエスケープしない
				}else{
					$value = $key."=\"".htmlspecialchars((string)$value, ENT_QUOTES, SOY2HTML::ENCODING)."\"";
				}
			}

			$attributes[] = $value;
		}

		$attribute = implode(" ",$attributes);

		$out = '<'.$this->tag;
		if(strlen($attribute))$out .= ' '.$attribute;
		if($this->getComponentType() == SOY2HTML::SKIP_BODY){
			$out .= ' /';
		}else if($this->getIsSkipEndTag()){
			$out .= ' /';
		}

		$out .= '>';

		return $out;
	}

	function executePlugin($soyValue){

		//元のhrefの#や?以降の文字列を変換後のURLにも付加する。
		$prefix = $this->getPrefix();
		$prefix = str_replace("'", "\\'", $prefix);//シングルクオートで囲むので

		$arguments = $this->buildArguments($soyValue);
		$this->_attribute["href"] = '<?php echo CMSPageLinkPlugin::convert(\''.$this->siteRoot.'\',unserialize(\''.serialize($arguments).'\')).\''.$prefix.'\'; ?>';

		/**
		 * TODO title
		$this->_attribute["title"] = '<?php $arguments = unserialize(\''.serialize($arguments).'\'); echo $arguments[\'title\']; ?>';
		 */

	}

	function executeReplace($soyValue){

		$prefix = $this->getPrefix();
		$arguments = $this->buildArguments($soyValue);

		$url = CMSPageLinkPlugin::convert($this->siteRoot,$arguments);

		$this->_attribute["href"] = $url.$prefix;
	}

	/**
	 * 元のhrefの#や?以降の文字列を取得する
	 */
	function getPrefix(){

		if(!isset($this->_attribute["href"])){
			return "";
		}

		$prefix = "";
		$orig_url = $this->_attribute["href"];

		if( strpos($orig_url, "?") !== false ){
			$prefix = strstr($orig_url, "?");
		}elseif( strpos($orig_url, "#") !== false ){
			$prefix = strstr($orig_url, "#");
		}

		if($prefix == "#" OR $prefix == "?"){
			$prefix = "";
		}

		return $prefix;
	}

	function buildArguments($soyValue){
		$arguments = array(
			"page" => $soyValue,
			"site" => $this->getAttribute("site"),
			"month" => $this->getAttribute("month"),
			"category" => $this->getAttribute("category"),
			"entry" => $this->getAttribute("entry"),
			"treeid" => $this->getAttribute("treeid"),
			"offset" => $this->getAttribute("offset")
		);

		//出力されないように削除しておく
		$this->clearAttribute("site");
		$this->clearAttribute("month");
		$this->clearAttribute("category");
		$this->clearAttribute("entry");
		$this->clearAttribute("treeid");
		$this->clearAttribute("offset");

		return $arguments;
	}

	public static function convert($siteRoot,$arguments){

		try{
			$pageId = $arguments["page"];


			$oldDsn = SOY2DAOConfig::Dsn();
			$oldSiteRoot = $siteRoot;
			SOY2DAOConfig::Dsn(ADMIN_DB_DSN);

			/* サイトのURLを取得する */
			try{
				$dao = SOY2DAOFactory::create("admin.SiteDAO");

				if(isset($arguments["site"]) && !is_null($arguments["site"])){
					// リンク先が他のサイトの場合
					$site = $dao->getById($arguments["site"]);
				}else{
					if(!defined("_SITE_ROOT_")) define("_SITE_ROOT_", UserInfoUtil::getSiteDirectory());
					$site = $dao->getBySiteId(basename(_SITE_ROOT_));
				}
				$siteRoot = $site->getUrl();

				if($site->getIsDomainRoot()){
					$siteRoot = "/";
				}

				//指定したサイトのDSNを使う
				SOY2DAOConfig::Dsn($site->getDataSourceName());
			}catch(Exception $e){
				SOY2DAOConfig::Dsn($oldDsn);
				$siteRoot = $oldSiteRoot;
			}

			//末尾に/を付けておく
			if(strlen($siteRoot) === 0){
				$siteRoot = "/";
			}elseif($siteRoot[strlen($siteRoot)-1] !== "/"){
				$siteRoot .= "/";
			}

			$pageDao = SOY2DAOFactory::create("cms.PageDAO");
			try{
				$page = $pageDao->getById($pageId);
			}catch(Exception $e){
				$page = new Page();
			}

			$url = $siteRoot . $page->getUri();
			//末尾の / を削る（$page->uriが空の時）、ただし / だけのときは削らない
			if(strlen($url) >0 && $url[strlen($url)-1] == "/" && $url != "/"){
				$url = substr($url,0,strlen($url)-1);
			}

			//ブログページのリンク
			if($page->isBlog()){
				$page = SOY2::cast("domain.cms.BlogPage",$page);
				$config = $page->getPageConfigObject();
		    	if($config){
		    		$config = unserialize($page->getPageConfig());
		    		SOY2::cast($page,$config);
		    	}

				$url .= "/";

				if($arguments["month"]){
					$month = $arguments["month"];
					$url .= $page->getMonthPageURL(false) . str_replace("-","/",$month);
				}elseif($arguments["category"]){
					$category = $arguments["category"];
					try{
						$logic = SOY2Logic::createInstance("logic.site.Label.LabelLogic");
						$label = $logic->getById($category);
					}catch(Exception $e){
						$label = new Label();
					}
					$url .= $page->getCategoryPageURL(false) . rawurlencode($label->getAlias());
				}elseif($arguments["entry"]){
					$entryId = $arguments["entry"];
					try{
						$entry = SOY2DAOFactory::create("cms.EntryDAO")->getById($entryId);
					}catch(Exception $e){
						$entry = new Entry();
					}
					$url .=  $page->getEntryPageURL(false) . rawurlencode($entry->getAlias());
				}else{
					$url .= $page->getTopPageURL(false);
				}

			//携帯ページの場合
			}else if($page->isMobile()){
				if($arguments["treeid"]){
					SOY2::import("domain.cms.MobilePage");
					$page = MobilePage::cast($page);
					$treeObjects = $page->getVirtual_tree();

					$obj = @$treeObjects[(int)$arguments["treeid"]];
					if($obj && $obj->getAlias()){
						$url .= "/" . $treeObjects[$arguments["treeid"]]->getAlias();

						if($arguments["offset"]){
							$url .= "/".$arguments["offset"];
						}
					}else{
						//ツリーオブジェクト無し
						$url .= "/" . $arguments["treeid"];
					}

				}
			}

			//sid付加
			if(CMSPageLinkPlugin::isAppendSid()){
				$url = CMSPageLinkPlugin::appendSid($url);
			}

			if(isset($arguments["site"]) && !is_null($arguments["site"])){
				SOY2DAOConfig::Dsn($oldDsn);
				$siteRoot = $oldSiteRoot;
			}

			// スラッシュ２つから始まる場合はスラッシュを一つにする
			if(strpos($url, "//") === 0) $url = "/" . substr($url, 2);

			return $url;
		}catch(Exception $e){

		}
	}

	/**
	 * SIDを付加する必要があるかどうか
	 *
	 * @return boolean
	 */
	private static function isAppendSid(){
		if(defined("SOYCMS_APPEND_SID") && SOYCMS_APPEND_SID){
			return true;
		}
		return false;
	}

	/**
	 * SIDを付加する
	 * @return string
	 */
	private static function appendSid($url){
		if(strpos($url,"?") != -1){
			$url .= "&" . session_name() . "=" . session_id();
		}else{
			$url .= "?" . session_name() . "=" . session_id();
		}
		return $url;
	}
}
