<?php
SOY2::import("domain.config.SOYShop_ShopConfig");

class SOYShopPageBase extends WebPage{

    private $pageObject;
    private $arguments = array();

    /**
     * 各クラスでメソッドに基本的な処理を記述します
     */
    function build($args){

    }

    /**
     * このメソッドは拡張して実行されます
     */
    function main($args){

    }

    /**
     * 共通処理
     */
    function common_execute(){
		$page = $this->getPageObject();
		if(!$page instanceof SOYShop_Page) throw new Exception("failed SOYShop_Page Object on SOYShopPageBase");
        $title = $page->getConvertedTitle();

        //SHOP_NAME
        $shopConfig = SOYShop_ShopConfig::load();
        $title = str_replace("%SHOP_NAME%", $shopConfig->getShopName(), $title);
        $title = str_replace("%PAGE_NAME%", $page->getName(), $title);

        $this->setTitle($title);

        //meta keywords
        $keywords = $page->getKeyword();
        $keywords = str_replace("%SHOP_NAME%", $shopConfig->getShopName(), $keywords);
        $keywords = $this->convertMeta($keywords);
        if(strlen($keywords)) $this->getHeadElement()->insertMeta("keywords", $keywords . ",");

        //meta description
        $description = $page->getDescription();
        $description = str_replace("%SHOP_NAME%", $shopConfig->getShopName(), $description);
        $description = $this->convertMeta($description);
        if(strlen($description)) $this->getHeadElement()->insertMeta("description", $description . " ");

        $url = $page->getCSSURL();
        if(file_exists(str_replace(SOYSHOP_SITE_URL, SOYSHOP_SITE_DIRECTORY, $url))){
            $this->getHeadElement()->appendHTML(
                '<link rel="stylesheet" href="' . $url . '" />' . "\n"
            );
        }

        $canonical = $page->getConvertedCanonical();

        if(!empty($canonical)){
			$this->getHeadElement()->appendHTML(
                '<link rel="canonical" href="' . $canonical . '" />' . "\n"
            );
        }

        $this->buildModules();
    }

    /**
     * 文字コード変換して出力
     */
    function display(){
        ob_start();
        parent::display();
        $html = ob_get_contents();
        ob_end_clean();

        //cms:ignore
        $html = $this->parseComment($html);
        $html = $this->replaceTags($html);

        $pageObj = $this->getPageObject();

        if($pageObj){
            echo mb_convert_encoding($html, $pageObj->getCharset(), "UTF-8");
        }else{
            echo $html;
        }
    }

    /**
     * keywordsの置換文字列を変換する
     */
    function convertMeta($string){
		$page = $this->getPageObject();
		if(!$page instanceof SOYShop_Page) throw new Exception("failed SOYShop_Page Object on SOYShopPageBase");
        switch($page->getType()){
            case "list":
				$obj = $page->getObject();
				if(!$obj instanceof SOYShop_ListPage) throw new Exception("failed SOYShop_ListPage Object on SOYShopPageBase");
                $current = $obj->getCurrentCategory();
				if($current instanceof SOYShop_Category && method_exists($current, "getName")) return str_replace("%CATEGORY_NAME%", $current->getOpenCategoryName(), $string);
				break;
            case "detail":
				$obj = $page->getObject();
				if(!$obj instanceof SOYShop_DetailPage) throw new Exception("failed SOYShop_DetailPage Object on SOYShopPageBase");
                $current = $obj->getCurrentItem();
				if($current instanceof SOYShop_Item){
					$string = str_replace("%ITEM_NAME%", $current->getOpenItemName(), $string);
	                $string = str_replace("%ITEM_CODE%", $current->getCode(), $string);
	                return str_replace("%CATEGORY_NAME%", soyshop_get_category_name($current->getCategory()), $string);
				}
				break;
            case "search":
                $q = "";
                if(isset($_GET["q"])){
                    $q = htmlspecialchars($_GET["q"], ENT_QUOTES, "UTF-8");
                //カスタムサーチフィールド
                }else if(isset($_GET["c_search"]["item_name"])){
                    $q = htmlspecialchars($_GET["c_search"]["item_name"], ENT_QUOTES, "UTF-8");
                }

                return str_replace("%SEARCH_WORD%", $q, $string);
            case "free":
            case "complex":
            default:
                return str_replace("%PAGE_NAME%", $page->getName(), $string);
        }

        return "";
    }

    /**
     * shop:moduleの実行
     */
    function buildModules(){
		$plugin = new SOYShopPageModulePlugin();

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

    //cms:ignoreを使えるようにする
    function parseComment($html){

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

    function replaceTags($html){
        //ページタイトルを置換@@page_title;
        if(!is_null($this->getPageObject()) && method_exists($this->getPageObject(), "getName")){
            $html = str_replace("@@page_title;", $this->getPageObject()->getName(), $html);
        }

		//ルート設定していれば、スラッシュのみ。設定をしていなければ/サイトID/に変換する置換文字列
		if(strpos($html, "%TOPPAGE_URL%")){
			$url = (defined("SOYSHOP_IS_ROOT") && SOYSHOP_IS_ROOT) ? "/" : "/" . SOYSHOP_ID . "/";
			$html = str_replace("%TOPPAGE_URL%", $url, $html);
		}

        return $html;
    }

    function getPageObject() {
        return $this->pageObject;
    }
    function setPageObject($pageObject) {
        $this->pageObject = $pageObject;
    }
    function getTemplateFilePath(){
        $obj = $this->getPageObject();
		if(is_null($obj)) return null;
        return $obj->getTemplateFilePath();

    }
    /**
     * @param isIncludeArguments
     * @return string
     */
    function getPageUrl($isIncludeArguments = false){
        $url = soyshop_get_page_url($this->getPageObject()->getUri());
        if($isIncludeArguments){
            //$url .= "/" . implode($this->getArguments(), "/");
			$url .= "/" . implode("/", $this->getArguments());
        }
        return $url;
    }

    function getArguments() {
        return $this->arguments;
    }
    function setArguments($arguments) {
        if(!is_array($arguments)) $arguments = explode("/", $arguments);
        $this->arguments = $arguments;
    }
}

class SOYShop_PagerBase{

    function getCurrentPage(){}

    function getTotalPage(){}

    function getLimit(){}

    function getPagerUrl(){}

    function getNextPageUrl(){}

    function getPrevPageUrl(){}

    function hasNext(){ return false; }
    function hasPrev(){ return false; }

    function execute(){}
}
