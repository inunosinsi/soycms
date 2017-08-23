<?php
SOY2::import("domain.admin.Administrator");

class IndexPage extends CMSWebPageBase{

	function __construct(){
    	if(!UserInfoUtil::isDefaultUser()){
    		$this->jump("Administrator.Detail");
    	}

		parent::__construct();

		$this->outputMessage();

		$entities = $this->getLimitedAdministratorList();

		//管理者がいないときはリストを隠して、メッセージを表示
		$this->addModel("main_table", array(
			"visible"=>(count($entities) > 0)
		));
		$this->addLabel("table_title", array(
			"text"=>CMSMessageManager::get("ADMIN_ADMIN_ID"),
			"visible"=>(count($entities) > 0)
		));
		$this->createAdd("list", "AdministratorList", array(
			"list"    => $entities,
			"sites"   => $this->getSiteLists(),
			"visible" => (count($entities) > 0)
		));
		$this->addLabel("no_administrator", array(
			"text"=>CMSMessageManager::get("ADMIN_MESSAGE_NO_USER"),
			"visible" => (count($entities) == 0)
		));

		$this->addLink("addAdministrator", array(
			"link"=>SOY2PageController::createLink("Administrator.Create"),
			"visible"=>UserInfoUtil::isDefaultUser()
		));

		//自分のパスワード変更
		$this->addLink("changepassword", array(
			"link" => SOY2PageController::createLink("Administrator.ChangePassword")
		));

		$this->addLink("reminderconfig", array(
			"link" => SOY2PageController::createLink("Administrator.Mail"),
			"visible" => UserInfoUtil::isDefaultUser(),
		));
	}

	function getSiteLists(){
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		return $SiteLogic->getSiteList();
	}

	/**
	 * 現在のユーザIDからログイン可能なサイトオブジェクトのリストを取得する
	 */
	function getLoginableSiteList(){
		$SiteLogic = SOY2Logic::createInstance("logic.admin.Site.SiteLogic");
		return $SiteLogic->getSiteByUserId(UserInfoUtil::getUserId());
	}
	/**
	 * 現在のユーザIDからログイン可能なサイトのIDのリストを取得する
	 */
	function getLoginableSiteIds(){
		$ids = array();
		$list = $this->getLoginableSiteList();
		foreach($list as $key => $site){
			$ids[] = $site->getId();
		}
		return $ids;
	}

	/**
	 * 管理者一覧を取得
	 * Administrator.ListActionを呼び出して、管理者オブジェクトのリストを返す
	 */
	function getAdministratorList(){
		$AdministratorLogic = SOY2Logic::createInstance("logic.admin.Administrator.AdministratorLogic");
		return $AdministratorLogic->getAdministratorList();
	}

	/**
	 * 自分がログイン可能なサイトにログイン可能な管理者のリスト
	 */
	function getLimitedAdministratorList(){
		if(UserInfoUtil::isDefaultUser()){
			return $this->getAdministratorList();
		}else{
			$list = array();
			$loginableSiteIds = $this->getLoginableSiteIds();
			$administratorList = $this->getAdministratorList();
			foreach($administratorList as $administrator){
				foreach($administrator->sites as $key => $siteroll){
					if(! in_array($siteroll->getSiteId(), $loginableSiteIds) ){
						unset($administrator->sites[$key]);
					}
				}
				if( count($administrator->sites) >0 ){
					$list[] = $administrator;
				}
			}
			return $list;
		}
	}

    /**
     * メッセージ出力
     */
    function outputMessage(){
    	$messages = CMSMessageManager::getMessages();
    	$this->addLabel("message", array(
    		"text" => implode("\n",$messages),
    		"visible" => !empty($messages)
    	));
    }
}

class AdministratorList extends HTMLList{

	private $sites = null;

	protected function populateItem($entity){

		$this->addLabel("userId", array(
			"text" => $entity->getUserId()
		));

		$this->addLabel("userName", array(
			"text" => $entity->getName()
		));

		$this->addLink("detail_link", array(
			"link" => SOY2PageController::createLink("Administrator.SiteRole.".$entity->getId()),
			"visible"=> !$entity->getIsDefaultUser(),
			"text"=>(UserInfoUtil::isDefaultUser()) ? CMSMessageManager::get("ADMIN_ROLE_SETTING") : CMSMessageManager::get("ADMIN_DISPLAY_ROLES")
		));

		$this->addLink("update_link", array(
			"link" => SOY2PageController::createLink("Administrator.Detail.".$entity->getId()),
			"text"=>(UserInfoUtil::isDefaultUser() || $entity->getId() == UserInfoUtil::getUserId()) ? CMSMessageManager::get("ADMIN_DETAIL_EDIT") : CMSMessageManager::get("ADMIN_DISPLAY_DETAILS")
		));

		//パスワード変更（初期管理者限定）
		//遷移先では現在のパスワードがなくても変更できてしまうので、自身のパスワード変更は行えないようにしておく
		$this->addLink("update_password_link", array(
				"link" => SOY2PageController::createLink("Administrator.Password.".$entity->getId()),
				"visible"=> UserInfoUtil::isDefaultUser() && $entity->getId() != UserInfoUtil::getUserId(),
		));

		//自身のパスワード変更
		$this->addLink("update_password_link_for_current_user", array(
				"link" => SOY2PageController::createLink("Administrator.ChangePassword"),
				"visible"=> $entity->getId() == UserInfoUtil::getUserId(),
		));

		$this->addLink("remove_link", array(
			"link" => SOY2PageController::createLink("Administrator.Remove." . $entity->getId()),
			"visible"=> UserInfoUtil::isDefaultUser() && !$entity->getIsDefaultUser(),
		));

		$siteName = array();

		if($entity->getIsDefaultUser()){
			$siteName[] = CMSMessageManager::get("ADMIN_SUPER_USER");
		}else{
			foreach($entity->sites as $managed){
				if(isset($this->sites[$managed->getSiteId()])){
					$siteName[] = htmlspecialchars($this->sites[$managed->getSiteId()]->getSiteName(), ENT_QUOTES, "UTF-8");
					            //."<br/>". htmlspecialchars(" => ".$managed->getSiteRoleText(),ENT_QUOTES);
				}
			}
		}

		$this->addLabel("managingSite", array(
			"html"=>implode("<br />", $siteName)
		));
	}

	function setSites($sites){
		$this->sites = $sites;
	}

	function getSiteRoleText($siteRole){
		$list = SiteRole::getSiteRoleLists();
		$text = $list[(int)$siteRole];
	}
}
?>