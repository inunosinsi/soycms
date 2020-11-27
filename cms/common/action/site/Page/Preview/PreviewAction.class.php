<?php
//site_include
SOY2::import('site_include.CMSPageController');
SOY2::import('site_include.CMSPathInfoBuilder');
SOY2::import('site_include.CMSPage');
SOY2::import('site_include.CMSBlogPage');
SOY2::import('site_include.CMSMobilePage');
SOY2::import('site_include.CMSPageLinkPlugin');
SOY2::import('site_include.CMSPagePluginBase');
SOY2::import('site_include.CMSLabel');
SOY2::import('site_include.DateLabel');

class PreviewAction extends SOY2Action {

	private $arg;
	private $showAll;

	function setArg($arg){
		$this->arg = $arg;
	}
	function setShowAll($showAll){
		$this->showAll = $showAll;
	}

	protected function execute(SOY2ActionRequest &$request,SOY2ActionForm &$form,SOY2ActionResponse &$response){

		if(!defined("CMS_PREVIEW_MODE")) define("CMS_PREVIEW_MODE",true);

		//非公開エントリーの表示
		if($this->showAll) define("CMS_PREVIEW_ALL",true);

		//PHPの許可
		if(defined("SOYCMS_ALLOW_PHP_SCRIPT")) define("SOY2HTML_ALLOW_PHP_SCRIPT",SOYCMS_ALLOW_PHP_SCRIPT);

		//デフォルトページ
		$siteConfig = SOY2DAOFactory::create("cms.SiteConfigDAO")->get();

		//id　GETを優先する
		if(isset($_GET["id"])){
			$pageId = $_GET["id"];
		}else{
			$pageId = (isset($this->arg[0])) ? (int)$this->arg[0] : 0;
			if(isset($this->arg[0])) unset($this->arg[0]);
		}

		//URI 先頭のスラッシュは削る
		$uri = $request->getParameter("uri");
		$uri = preg_replace("/^\//","",$uri);
		list($uri, $args) = CMSPathInfoBuilder::parsePath($uri);

		$logic = SOY2Logic::createInstance("logic.site.Page.PageLogic");

		try{
			try{
				try{
					if(strlen($pageId)>0){
						$page = $logic->getById($pageId);
					}else{
						$page = $logic->getByUri($uri);
					}
				}catch(Exception $e){
					$page = $logic->getDefaultPage();
				}

				switch($page->getPageType()){

					case Page::PAGE_TYPE_BLOG:
						if($args){
							$this->arg = $args;
						}

						$webPage = &SOY2HTMLFactory::createInstance("CMSBlogPage", array(
							"arguments" => array($page->getId(),$this->arg,$siteConfig),
							"siteRoot" => SOY2PageController::createLink("Page.Preview")."?uri="
						));
						$webPage->pageUrl = SOY2PageController::createLink("Page.Preview") ."?id={$page->getId()}&uri=";
						//$webPage->imageUrl = SOY2PageController::createRelativeLink(".");

						break;

					case Page::PAGE_TYPE_MOBILE:
						if($args){
							$this->arg = $args;
						}

						$webPage = &SOY2HTMLFactory::createInstance("CMSMobilePage", array(
							"arguments" => array($page->getId(),$this->arg,$siteConfig),
							"siteRoot" => SOY2PageController::createLink("Page.Preview") . "?uri="
						));
						$webPage->pageUrl = SOY2PageController::createLink("Page.Preview") . "?id={$page->getId()}&uri=";
						$webPage->imageUrl = UserInfoUtil::getSiteUrl()."im.php";

						break;

					case Page::PAGE_TYPE_NORMAL:
					default:
						$webPage = &SOY2HTMLFactory::createInstance("CMSPage", array(
							"arguments" => array($page->getId(),$this->arg,$siteConfig),
							"siteRoot" => SOY2PageController::createLink("Page.Preview") ."?uri="
						));
						break;

				}

				$webPage->main();

				//プレビューではプラグインonLoadイベントを呼び出さない？
//				$onLoad = CMSPlugin::getEvent('onPageLoad');
//				foreach($onLoad as $plugin){
//					$func = $plugin[0];
//					$filter = $plugin[1]['filter'];
//					switch($filter){
//						case 'all':
//							call_user_func($func,array('page'=>&$page, 'webPage'=>&$webPage));
//							break;
//						case 'blog':
//							if($page->getPageType() == Page::PAGE_TYPE_BLOG){
//								call_user_func($func,array('page'=>&$page, 'webPage'=>&$webPage));
//							}
//							break;
//						case 'page':
//							if($page->getPageType() == Page::PAGE_TYPE_NORMAL){
//								call_user_func($func,array('page'=>&$page, 'webPage'=>&$webPage));
//							}
//							break;
//					}
//				}

				ob_start();
				CMSPlugin::callEventFunc("beforeOutput");
				$webPage->display();
				CMSPlugin::callEventFunc("afterOutput");
				$html = ob_get_contents();
				ob_end_clean();

				//onOutputを呼び出す。
				$onLoad = CMSPlugin::getEvent('onOutput');
				foreach($onLoad as $plugin){
					$func = $plugin[0];

					$res = call_user_func($func,array('html'=>$html,'page'=>&$page, 'webPage'=>&$webPage));
					if(is_string($res) && !is_null($res)) $html = $res;

				}

				$html = $webPage->beforeConvert($html);
				$html =  $siteConfig->convertToSiteCharset($html);
				$html = $webPage->afterConvert($html);

				$this->setAttribute("page",$html);
				$this->setAttribute("pageObj",$webPage);

				$this->setAttribute("page_id",$page->getId());


			}catch(Exception $e){
				echo "notFound";
				echo "<!--\n". $e->getMessage() . "\n-->";
				return SOY2Action::FAILED;
			}
		}catch(Exception $e){
			return SOY2Action::FAILED;
		}

		return SOY2Action::SUCCESS;
    }
}
