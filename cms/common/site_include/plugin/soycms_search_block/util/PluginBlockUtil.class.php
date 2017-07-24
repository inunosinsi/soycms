<?php

class PluginBlockUtil {

  public static function getTemplateByPageId($pageId){
    $template = "";
    $blog = self::getBlogPageById($pageId);
    
    //ブログページを取得できた場合
    if(!is_null($blog) && !is_null($blog->getId())){
      $uri = str_replace("/" . $_SERVER["SOYCMS_PAGE_URI"] . "/", "", $_SERVER["PATH_INFO"]);
      //トップページ
      if($uri === (string)$blog->getTopPageUri()){
          $template = $blog->getTopTemplate();
          //アーカイブページ
      }else if(strpos($uri, $blog->getCategoryPageUri()) === 0 || strpos($uri, $blog->getMonthPageUri()) === 0){
          $template = $blog->getArchiveTemplate();
          //記事ごとページ
      }else{
          $template = $blog->getEntryTemplate();
      }
    //ブログページ以外
    }else{
      $template = self::getPageById($pageId)->getTemplate();
    }

    return $template;
  }

  public static function getBlockByPageId($pageId){
    try{
        $blocks = SOY2DAOFactory::create("cms.BlockDAO")->getByPageId($pageId);
    }catch(Exception $e){
        return null;
    }

    if(!count($blocks)) return null;

    $block = null;
    foreach($blocks as $obj){
        if($obj->getClass() == "PluginBlockComponent"){
            $block = $obj;
        }
    }

    return $block;
  }

  public static function getBlogPageByPageId($pageId){
    return self::getBlogPageById($pageId);
  }

  private function getBlogPageById($pageId){
    static $page;
    if(is_null($page)){
      try{
        $page = SOY2DAOFactory::create("cms.BlogPageDAO")->getById($pageId);
      }catch(Exception $e){
        $page = new BlogPage();
      }
    }
    return $page;
  }

  private function getPageById($pageId){
    static $page;
    if(is_null($page)){
      try{
        $page = SOY2DAOFactory::create("cms.PageDAO")->getById($pageId);
      }catch(Exception $e){
        $page = new Page();
      }
    }
    return $page;
  }
}
