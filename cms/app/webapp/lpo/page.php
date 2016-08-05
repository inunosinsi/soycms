<?php
define('APPLICATION_ID', "lpo");
/**
 * ページ表示
 */
class SOYLpo_PageApplication{

	var $page;
	var $serverConfig;
	

	function init(){
		CMSApplication::main(array($this,"main"));
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			return;
		}
	}
	
	function prepare(){
		
	}

	function main($page){
		
		$this->page = $page;
		
		
		//SOY2::RootDir()の書き換え
		$oldRooDir = SOY2::RootDir();
		$oldPagDir = SOY2HTMLConfig::PageDir();
		$oldCacheDir = SOY2HTMLConfig::CacheDir();
		$oldDaoDir = SOY2DAOConfig::DaoDir();
		$oldEntityDir = SOY2DAOConfig::EntityDir();
		$oldDsn = SOY2DAOConfig::Dsn();
		$oldUser = SOY2DAOConfig::user();
		$oldPass = SOY2DAOConfig::pass();
		
		//設定ファイルの読み込み
		include_once(dirname(__FILE__) . "/config.php");
		$this->prepare();
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
		
		$arguments = CMSApplication::getArguments();

		//app:id="soylpo"
		$this->page->createAdd("soylpo","SOYLpo_EntryComponent",array(
			"application" => $this,
			"page" => $page,
			"soy2prefix" => "app"
		));
				
		//元に戻す
		SOY2::RootDir($oldRooDir);
		SOY2HTMLConfig::PageDir($oldPagDir);
		SOY2HTMLConfig::CacheDir($oldCacheDir);
		SOY2DAOConfig::DaoDir($oldDaoDir);
		SOY2DAOConfig::EntityDir($oldEntityDir);
		SOY2DAOConfig::Dsn($oldDsn);
		SOY2DAOConfig::user($oldUser);
		SOY2DAOConfig::pass($oldPass);

	}

}

class SOYLpo_EntryComponent extends SOYBodyComponentBase{
	
	private $page;
	private $application;
	
	
	function setPage($page){
		$this->page = $page;
	}
	
	function execute(){
		
		$this->display();
			
		parent::execute();
	}
	
	function display(){
				
		$referer = $_SERVER["HTTP_REFERER"];		
//		$referer = $this->debug("regoogle");
		
		$listDao = SOY2DAOFactory::create("SOYLpo_ListDAO");
		
		$mode = SOYLpo_List::MODE_DEFAULT;
		$q = "";
		
		//Googleから遷移してきた場合は、リファラモード
		if(preg_match('/^http:\/\/www.google/',$referer)){
			
			//getにoqがある場合は、oqを読みに行く
			if(preg_match('/oq=/',$referer)){
				preg_match('/&oq=(.*?)&/',$referer,$value);
			//getにqのみの場合
			}else{
				preg_match('/&q=(.*?)&/',$referer,$value);
			}
				
			list($q,$keyword) = $this->getKeyword($value[1]);

			if(strlen($q)>0){
				$mode = SOYLpo_List::MODE_REFERER;
			}
			
		//Yahooから遷移してきた場合は、リファラモード
		}elseif(preg_match('/^http:\/\/search.yahoo/',$referer)){
			
			//一番初めの検索ワードを取得
			preg_match('/p=(.*?)&/',$referer,$value);
			list($q,$keyword) = $this->getKeyword($value[1]);

			if(strlen($q)>0){
				$mode = SOYLpo_List::MODE_REFERER;
			}
		
		//Bingから遷移してきた場合は、リファラモード
		}elseif(preg_match('/^http:\/\/www.bing/',$referer)){
			
			//一番初めの検索ワードを取得
			preg_match('/q=(.*?)&/',$referer,$value);
			list($q,$keyword) = $this->getKeyword($value[1]);

			if(strlen($q)>0){
				$mode = SOYLpo_List::MODE_REFERER;
			}
		
		//それ以外の場合はドメインモードかどうかチェックする
		}else{
			
			//リファラがnullではない場合はURLモード続行
			if(!is_null($referer)){
				//始めはURLモードをチェックする
				$q = $referer;
				$keyword = $referer;
				$mode = SOYLpo_List::MODE_URL;
			}
		}
		
		$logic = SOY2Logic::createInstance("logic.SearchLogic");
		
		//対応するIDを返す
		list($id,$keyword) = $logic->search($q,$mode,$keyword);
		
		try{
			$entry = $listDao->getById($id);
		}catch(Exception $e){
			//念の為ここでもディフォルトを取得するようにする
			$entry = $listDao->getById(1);
			$id = 1;
		}
		
		//ログをとる
		$logic->addLog($id,$keyword);
		
		$this->createAdd("title","HTMLLabel",array(
			"text" => $entry->getTitle(),
			"soy2prefix" => "cms"
		));
		
		$this->createAdd("content","HTMLLabel",array(
			"html" => $entry->getContent(),
			"soy2prefix" => "cms"
		));
	}
	
	/**
	 * 検索用のqueryとログ用の検索ワードを取得する
	 * @param str
	 * @return q keyword
	 */
	function getKeyword($str){
		
		if(strpos($str,"+")){
			$q = substr($str,0,strpos($str,"+"));
			$keyword = str_replace("+",",",$str);
		}elseif(strpos($str,"%20")){
			$q = substr($str,0,strpos($str,"%20"));
			$keyword = str_replace("%20",",",$str);
		}elseif(strpos($str,"　")){
			$q = substr($str,0,strpos($str," "));
			$keyword = str_replace("　",",",$str);
		}
		
		return array($q,$keyword);
	}
	
	function debug($site){
		$list = $this->debugList();
		return $list[$site];
	}
	
	function debugList(){
		return array(
			"google" => "http://www.google.co.jp/url?sa=t&source=web&cd=2&ved=0CCgQFjAB&url=http%3A%2F%2Fwww.soyshop.net%2F&rct=j&q=soy%20lpo&ei=mig1TsOVAcvUmAWPk_3wCg&usg=AFQjCNFboas6kOqNXS3YebTjW39OhiZ7sg&sig2=HzYQL9ZSDNIt044iF6mtIw",
			"yahoo" => "http://search.yahoo.co.jp/search?p=soy+lpo&search.x=1&fr=top_ga1_sa&tid=top_ga1_sa&ei=UTF-8&aq=&oq=",
			"bing" => "http://www.bing.com/search?q=soy+lpo&form=ASUMHP&qs=n&sk=&pc=ASU2&x=0&y=0",
			"regoogle" => "http://www.google.co.jp/search?q=soy&ie=utf-8&oe=utf-8&aq=t&rls=org.mozilla:ja:official&hl=ja&client=firefox-a#sclient=psy&hl=ja&client=firefox-a&hs=AE3&rls=org.mozilla:ja%3Aofficial&source=hp&q=soy+shop&pbx=1&oq=soy+shop&aq=f&aqi=g4&aql=&gs_sm=e&gs_upl=23851l23851l3l24049l1l1l1l0l1l0l0l0ll0l0&bav=on.2,or.r_gc.r_pw.&fp=290ed995c4ecddcd&biw=1163&bih=590"
		);
	}
		
	function getApplication(){
		return $this->application;
	}
	
	function setApplication($application){
		$this->application = $application;
	}
	
}

$app = new SOYLpo_PageApplication();
$app->init();

?>