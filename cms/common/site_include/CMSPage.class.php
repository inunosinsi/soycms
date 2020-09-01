<?php

class CMSPage extends WebPage{

	var $id;
	var $page;
	var $template;
	var $siteConfig;
	var $arguments;
	var $siteRoot;
	var $parseTime;
	var $title;

	protected $siteUrl;
	protected $pageUrl;

	protected $_soy2_prefix = "block";

	const SOYCMS_COMMENT_PHRASE = "cms:ignore";

	function __construct($args){

		$id = $args[0];
		$this->arguments = $args[1];
		$this->siteConfig = $args[2];

		$pageDao = SOY2DAOFactory::create("cms.PageDAO");
		$this->page = $pageDao->getById($id);
		$this->id = $id;

		//サイトのURL
		$this->siteUrl = $this->getSiteUrl();

		//application用に追加
		$this->pageUrl = SOY2PageController::createLink("") . $this->page->getUri();

		parent::__construct();
	}

	function main(){

		$dao = SOY2DAOFactory::create("cms.BlockDAO");
		$entryDAO = SOY2DAOFactory::create("cms.EntryDAO");

		$blocks = $dao->getByPageId($this->id);

		foreach($blocks as $block){
			$object = $block->getBlockComponent();
			if(!is_object($object)){
				continue;
			}
			$soy2HtmlObject = $object->getViewPage($this);
			/*
			 * ブロック
			 * block:id="xxx"
			 */
			$this->add($block->getSoyId(),$soy2HtmlObject);
			/*
			 * 記事がなければ表示しない領域
			 * if_has_entry_in:id="xxx"
			 */
			$this->addModel($block->getSoyId().":has_entry", array(
				"visible"    => (isset($soy2HtmlObject->list) && count($soy2HtmlObject->list)),
				"soy2prefix" => "if",
			));
			/*
			 * 記事があるときに表示しない領域
			 * if_no_entry_in:id="xxx"
			 */
			$this->addModel($block->getSoyId().":no_entry", array(
				"visible"    => (isset($soy2HtmlObject->list) && !count($soy2HtmlObject->list)),
				"soy2prefix" => "if",
			));
		}

		CMSPlugin::callEventFunc('onPageOutput',$this);

		$pageFormat = $this->page->getPageTitleFormat();
		if(strlen($pageFormat) == 0){
			//空っぽだったらデフォルト追加
			$pageFormat = '%PAGE%';
		}
		$pageFormat = preg_replace('/%SITE%/',$this->siteConfig->getName(),$pageFormat);
		$pageFormat = preg_replace('/%PAGE%/',$this->page->getTitle(),$pageFormat);
		$this->setTitle($pageFormat);

		$this->addLink("site_url_link", array(
			"link" => $this->siteUrl,
			"soy2prefix" => "cms"
		));

		$this->addLabel("site_url", array(
			"text" => $this->siteUrl,
			"soy2prefix" => "cms"
		));

		//メッセージの設定
		$this->addLabel("site_name", array(
			"text" => $this->siteConfig->getName(),
			"soy2prefix" => "cms"
		));
		$this->addLabel("page_title", array(
			"text" => $pageFormat,
			"soy2prefix" => "cms"
		));
		$this->addLabel("raw_page_title", array(
			"text" => $this->page->getTitle(),
			"soy2prefix" => "cms"
		));

		//canonical ブログページのみCMSBlogPage.class.phpの__construct内で一度読み込んで、canonical urlを組み立てている
		$canonicalUrl = SOY2Logic::createInstance("logic.site.Page.PageLogic", array("page" => $this->page, "siteUrl" => $this->siteConfig->getConfigValue("url")))->buildCanonicalUrl();

		$this->addLabel("page_link", array(
			"text" => $canonicalUrl,
			"soy2prefix" => "cms"
		));

		$this->addMessageProperty("site_name",'<?php echo $'.$this->_soy2_pageParam.'["site_name"]; ?>');
		$this->addMessageProperty("page_title",'<?php echo $'.$this->_soy2_pageParam.'["page_title"]; ?>');
		$this->addMessageProperty("raw_page_title",'<?php echo $'.$this->_soy2_pageParam.'["raw_page_title"]; ?>');

		/**
		 * SOY Appを複数呼び出す（実際に複数を呼び出した場合、APPLICATION_IDが繰り返しdefineされてしまうので問題が発生しうる。）
		 * 記述例
		 * <!-- cms:id="apps" cms:app="inquiry mail catalog" /-->
		 */
		SOY2::import("site_include.component.CMSAppContainer");
		$this->createAdd("apps","CMSAppContainer",array(
			"page" => $this,
			"soy2prefix" => "cms"
		));

		$this->buildModules();

	}

	function getTemplate(){
		$html = $this->onLoadPageTemplate($this->page->getTemplate());
		return $this->parseComment($html);
	}

	function getCacheFilePath($extension = ".html.php"){
		//ダイナミック編集では管理側にキャッシュを作るのでサイトを区別する必要がある
		if(defined("CMS_PREVIEW_MODE") && CMS_PREVIEW_MODE){
			 $pageUri = UserInfoUtil::getSite()->getSiteId() . "/" . $this->page->getUri();
		}else{
			 $pageUri = $this->page->getUri();
		}
		$cacheFileName = "cache_" . str_replace("/",".",$pageUri) . $extension;
		return SOY2HTMLConfig::CacheDir().$cacheFileName;
	}

	function getArguments(){
		return $this->arguments;
	}

	function setSiteRoot($root){
		$this->siteRoot = $root;
	}

	function getSiteRoot(){
		return $this->siteRoot;
	}

	/**
	 * @overrride
	 */
	function isModified(){
		if(defined("SOY2HTML_CACHE_FORCE") && SOY2HTML_CACHE_FORCE == true){
			return true;
		}

		if(file_exists($this->getCacheFilePath()) && $this->siteConfig->getLastUpdateDate() > filemtime($this->getCacheFilePath())){
			return true;
		}

		if(!file_exists($this->getCacheFilePath()) || $this->page->getUdate() > filemtime($this->getCacheFilePath())){
			return true;
		}else{
			return false;
		}
	}

	function executePlugin($id,$soyValue,$plugin){

		while(true){
			list($tag,$line,$innerHTML,$outerHTML,$value,$suffix,$skipendtag) =
				$plugin->parse($id,$soyValue,$this->_soy2_content);

			if(!strlen($tag))break;

			$plugin->_attribute = array();

			$plugin->setTag($tag);
			$plugin->parseAttributes($line);
			$plugin->setInnerHTML($innerHTML);
			$plugin->setOuterHTML($outerHTML);
			$plugin->setParent($this);
			$plugin->setSkipEndTag($skipendtag);
			$plugin->setSoyValue($value);
			$plugin->execute();

			$this->_soy2_content = $this->getContent($plugin,$this->_soy2_content);
		}

	}

	/**
	 * @override
	 */
	function parsePlugin($plugin = null){

		//リンクの置換え
		$plugin = new CMSPageLinkPlugin();

		$plugin->setSiteRoot($this->siteRoot);

		while(true){
			list($tag,$line,$innerHTML,$outerHTML,$value,$suffix,$skipendtag) =
				$plugin->parse("link","[0-9]+",$this->_soy2_content);

			if(!strlen($tag))break;

			$plugin->_attribute = array();
			$plugin->_soy2_attribute = array();

			$plugin->setTag($tag);
			$plugin->parseAttributes($line);
			$plugin->setInnerHTML($innerHTML);
			$plugin->setOuterHTML($outerHTML);
			$plugin->setParent($this);
			$plugin->setSkipEndTag($skipendtag);
			$plugin->setSoyValue($value);
			$plugin->execute();

			$this->_soy2_content = $this->getContent($plugin,$this->_soy2_content);
		}

		//pageブロック
		$plugins = CMSPlugin::getBlocks("page");
		$plugin = new CMSPagePluginBase();
		$plugin->setPage($this->page);
		$plugin->setArguments($this->arguments);

		while(true && count($plugins)){
			list($tag,$line,$innerHTML,$outerHTML,$value,$suffix,$skipendtag) =
				$plugin->parse("plugin","[a-zA-Z0-9\.\/\-_]*",$this->_soy2_content);

			if(!strlen($tag))break;

			//リセット
			$plugin->_attribute = array();
			$plugin->_soy2_attribute = array();

			//ページにプラグインの記述がないとき
			if(!array_key_exists($value,$plugins)){
				$tmpTag = $plugin->getTag();

				$plugin->setTag($tag);
				$plugin->parseAttributes($line);
				$plugin->setInnerHTML($innerHTML);
				$plugin->setOuterHTML($outerHTML);
				$plugin->setSkipEndTag($skipendtag);
				$this->_soy2_content = $this->getContent($plugin,$this->_soy2_content);
				$plugin->setTag($tmpTag);
				continue;
			}

			//処理
			$plugin->setTag($tag);
			$plugin->parseAttributes($line);
			$plugin->setInnerHTML($innerHTML);
			$plugin->setOuterHTML($outerHTML);
			$plugin->setParent($this);
			$plugin->setSkipEndTag($skipendtag);
			$plugin->setSoyValue($plugins[$value]);
			$plugin->execute();

			$this->_soy2_content = $this->getContent($plugin,$this->_soy2_content);
		}

		$plugin = null;
	}

	/**
	 * テンプレートを読み込む前に、置換のためのプラグインを実行します
	 */
	function onLoadPageTemplate($html){
		$onLoad = CMSPlugin::getEvent('onLoadPageTemplate');
		foreach($onLoad as $plugin){
			$func = $plugin[0];
			$res = call_user_func($func, array('html' => $html));
			if(!is_null($res) && is_string($res)) $html = $res;
		}
		return $html;
	}

	function buildModules(){

		SOY2::import("site_include.CMSPageModulePlugin");
		$plugin = new CMSPageModulePlugin();

		while(true){
			list($tag, $line, $innerHTML, $outerHTML, $value, $suffix, $skipendtag) =
				$plugin->parse("module", "[a-zA-Z0-9\.\_\{\}]+", $this->_soy2_content);

			if(!strlen($tag)) break;

			$plugin->_attribute = array();

			$plugin->setTag($tag);
			$plugin->parseAttributes($line);
			$plugin->setInnerHTML($innerHTML);
			$plugin->setOuterHTML($outerHTML);
			$plugin->setParent($this);
			$plugin->setSkipEndTag($skipendtag);
			$plugin->setSoyValue($value);
			$plugin->execute();

			$this->_soy2_content = $this->getContent($plugin, $this->_soy2_content);
		}
    }

	/**
	 * コメントを消去します。
	 */
	function parseComment($html){

		//コメントがなければ何もせずにそのまま返す
		if(strpos($html, self::SOYCMS_COMMENT_PHRASE) === false){
			return $html;
		}

		$startRegex = '/(<[^>]*[^\/]cms:ignore[^>]*>)/';
		$endRegex =  '/(<[^>]*\/cms:ignore[^>]*>)/';

		while(true){
			if(preg_match($startRegex,$html,$tmp1,PREG_OFFSET_CAPTURE)
					&& preg_match($endRegex,$html,$tmp2,PREG_OFFSET_CAPTURE)
			){
				$startOffset = $tmp1[1][1];
				$endOffset = $tmp2[1][1] + strlen($tmp2[1][0]);

				$innerHTML = substr($html,$startOffset + strlen($tmp1[1][0]),$tmp2[1][1] - ($startOffset + strlen($tmp1[1][0])));

				if(preg_match($startRegex,$innerHTML)){

					$tmp  = substr($html,0,$tmp1[1][1]);
					$tmp .= substr($html,$startOffset +  + strlen($tmp1[1][0]));

					$html = $tmp;
					continue;
				}

				if($endOffset > $startOffset){

					$tmp  = substr($html,0,$startOffset);
					$tmp .= substr($html,$endOffset);

					$html = $tmp;

				}else{
					$tmp  = substr($html,0,$tmp2[1][1]);
					$tmp .= substr($html,$endOffset);

					$html = $tmp;
				}

			}else{
				break;
			}
		}

		return $html;
	}

	/**
	 * 最終的に表示するHTMLがここに設定される
	 */
	function beforeConvert($html){
		return $html;
	}

	/**
	 * 最終的に表示するHTMLがここに設定される
	 */
	function afterConvert($html){
		SOY2::import("lib.SOYCMSEmojiUtil");	//絵文字用のUtility
		return SOYCMSEmojiUtil::replace($html,$this->siteConfig->getCharsetText());
		//return $html;
	}

    function getPageUrl() {
    	return $this->pageUrl;
    }
    function setPageUrl($pageUrl) {
    	$this->pageUrl = $pageUrl;
    }

    /**
     * @return string リンク用 $page->siteRootを使わないで、このメソッドを用いる
     */
    function getSiteRootUrl(){
		if($this->siteConfig->getConfigValue("url")){
			return $this->siteConfig->getConfigValue("url");
		}else{
			return $this->siteRoot;
		}
    }

    /**
     * @return string サイトURL
     */
    function getSiteUrl(){
		if($this->siteConfig->getConfigValue("url")){
			return $this->siteConfig->getConfigValue("url");
		}else{
			return CMSPageController::createLink("", true);
		}
    }

    /**
     * @param string $path
     * @return string http~からの絶対パスをページURL
     */
    function getAbusolutePageUrl($path){
		if($this->siteConfig->getConfigValue("url")){
			return $this->siteConfig->getConfigValue("url");
		}else{
			return CMSPageController::createRelativeLink($path, true);
		}
    }
}
