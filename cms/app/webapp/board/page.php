<?php
define("APPLICATION_ID","board");

/**
 * ページ表示
 */
class SOYBoard_PageApplication{
	
	var $page;
	
	function init(){
	
		CMSApplication::main(array($this,"main"));
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			return;
		}
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
		
		//DBの初期化を行う
		if(!file_exists(CMS_COMMON . "db/".APPLICATION_ID.".db")){
			$logic = SOY2Logic::createInstance("logic.InitLogic");
			$logic->init();
		}
				
		$arguments = CMSApplication::getArguments();
		
		$flashSession = SOY2ActionSession::getFlashSession();
		
		/* メイン処理開始 */
		
		$this->page->createAdd("soyboard","SOYBoard_ThreadComponent",array(
			"page"=>$page,
			"soy2prefix" => "app"
		));
		/* メイン処理ここまで */
				
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



class SOYBoard_ThreadComponent extends SOYBodyComponentBase{
	
	private $page;
	
	function setPage($page){
		$this->page = $page;
	}
	
	function execute(){
	
		$threadId = $this->buildThread($this->getAttribute("app:threadid"));
		$this->buildPage($threadId);
		parent::execute();
	}
		
	function buildThread($threadId){
		$logic = SOY2Logic::createInstance("logic.ThreadLogic");
		if(is_null($threadId)){
			//スレッドIDが指定してなかった場合はページ関連付けスレッドを取得
			try{
				$thread = $logic->getByPageId($this->page->page->getId());
				$threadId = $thread->getId();

			}catch(Exception $e){
			
				//ページ関連付けスレッドが無い場合は自動作成
				$threadId = $logic->insert(array(
					"title"=>$this->page->page->getTitle(),
					"name"=>""
				),$this->page->page->getId());
				$thread = $logic->getById($threadId);
				return $threadId;
			}
		}else{
			//スレッドIDが指定してある場合はそちらを表示
			try{
				$thread = $logic->getById($threadId);
			
			}catch(Exception $e){
				return $threadId;
			}
		}
		return $threadId;
	}
	
	
	function buildPage($threadId){		
		$logic = SOY2Logic::createInstance("logic.ThreadLogic");
		$resplogic = SOY2Logic::createInstance("logic.ResponseLogic");
		
		$thread = $logic->getById($threadId);
		
		$config = SOY2Logic::createInstance("logic.ConfigLogic")->getByThreadId($thread->getId());
		$offset = isset($_GET["offset"])? $_GET["offset"] : 1;
		$viewcount = isset($_GET["viewcount"]) ? $_GET["viewcount"] : 100;
		
		try{
			$responses = $resplogic->getByThreadId($threadId,$offset,$viewcount);	

		}catch(Exception $e){
			return;
		
		}
		
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			if($config->getMaxResponse() <= $thread->getResponse() || $config->getIsStopped()){
				//do nothing
					
			}else{
				$resplogic->insert($threadId,$_POST);
				$logic->updateLastUpdateDate($threadId);
		
			}
			SOY2PageController::redirect($this->page->getPageUrl());
		}
		
		$this->createAdd("thread_title","HTMLLabel",array(
			"text"=>$thread->getTitle(),
			"soy2prefix" => "app"
		));		
		
		$this->createAdd("thread_loop","SOYBoard_ResponseList",array(
			"list"=>$responses,
			"soy2prefix"=>"app"
		));
		
		$this->createAdd("response_form","SOYBoard_ResponseForm",array(
			"soy2prefix"=>"app",
			"defaultName"=>$config->getDefaultName()
		));
		
		
		$max_res = $thread->getResponse();
		$str = array();
		
		if($offset - $viewcount >= 1){
			$str[] = '<a href="'.$this->page->getPageUrl()."?offset=".($offset-$viewcount)."&viewcount=".$viewcount.'">&lt;&lt;</a>';
		}
		
		for($i = 1; $i<=$max_res;$i+=100){
			$str[] = '<a href="'.$this->page->getPageUrl().'?offset='.($i).'&viecount=100">'.$i.'-</a>';
		}
		
		if($offset + $viewcount <= $thread->getResponse()){
			$str[] = '<a href="'.$this->page->getPageUrl()."?offset=".($offset+$viewcount)."&viewcount=".$viewcount.'">&gt;&gt;</a>';
		}
		
		$str[] = '<a href="'.$this->page->getPageUrl()."?offset=".($thread->getResponse())."&viewcount=".$viewcount.'">新着</a>';
		
		$this->createAdd("response_pager","HTMLLabel",array(
			"html"=>implode("&nbsp;",$str),
			"soy2prefix"=>"app"
		));
	}
}

/*
 * <!-- app:id="thread_loop" -->
	<p>
		<span app:id="id"></span>
		<a href="#" app:id="name"></a>
		<span app:id="submitdate"></span>
		<span app:id="hash"></span>
		<p app:id="body"></p>
	</p>
	<!-- /app:id="thread_loop" -->
 */
class SOYBoard_ResponseList extends HTMLList{
	
	function populateItem($entity){
		$this->createAdd("id","HTMLLabel",array(
			"text"=>$entity->getResponseId(),
			"soy2prefix"=>"app"
		));
		
		$this->createAdd("name","HTMLLink",array(
			"text"=>$entity->getName(),
			"link"=>"mailto:".$entity->getEmail(),
			"soy2prefix"=>"app"
		));
		$this->createAdd("submitdate","DateLabel",array(
			"text"=>strtotime($entity->getSubmitDate()),
			"soy2prefix"=>"app"
		));
		$this->createAdd("hash","HTMLLabel",array(
			"text"=>"ID:".$entity->getHash(),
			"soy2prefix"=>"app"
		));
		
		$body = str_replace("\n","<br>",htmlspecialchars($entity->getBody(),ENT_QUOTES));
		
		$this->createAdd("body","HTMLLabel",array(
			"html"=>$body,
			"soy2prefix"=>"app"
		));
		
	}
}

/*
 * <form app:id="response_form">
		<table>
			<tr>
				<th>名前</th>
				<td><input app:id="name" /></td>
			</tr>

			<tr>
				<th>Email</th>
				<td><input app:id="email" /></td>
			</tr>

			<tr>
				<th>本文</th>
				<td><textarea app:id="body" ></textarea></td>
			</tr>
		</table>
		<input type="submit" value="送信">
	</form app:id="response_form">
 */
class SOYBoard_ResponseForm extends HTMLForm{
	const SOY_TYPE = SOY2HTML::HTML_BODY;
	
	private $defaultName;
	
	function execute(){
		$this->createAdd("name","HTMLInput",array(
			"name"=>"name",
			"value"=>$this->defaultName,
			"soy2prefix"=>"app"
		));
		
		$this->createAdd("email","HTMLInput",array(
			"name"=>"email",
			"soy2prefix"=>"app"
		));
		
		$this->createAdd("body","HTMLTextArea",array(
			"name"=>"body",
			"soy2prefix"=>"app"
		));
		
		parent::execute();
	}

	function getDefaultName() {
		return $this->defaultName;
	}
	function setDefaultName($defaultName) {
		$this->defaultName = $defaultName;
	}
}


$app = new SOYBoard_PageApplication();
$app->init();
?>