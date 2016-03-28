<?php

/**
 * ログインできるサイトまたはアプリが一つしかないときはそのサイト、アプリにログインする
 */
class DefaultLoginAction extends SOY2Action{

	private $redirect;

	function execute($req,$form,$res) {
    	//転送先
    	$this->redirect = $req->getParameter('r');

    	$userId = UserInfoUtil::getUserId();

		//基本的には全てfalse
		$isSiteAdministrator = false;
		$isEntryAdministrator = false;
		$isEntryPublisher = false;


		if(! UserInfoUtil::isDefaultUser()){//初期管理者は自動ログインしない
			$siteRoles = $this->getSiteRoles($userId);
			$appRoles = $this->getAppRoles($userId);

			if(count($siteRoles) == 1 && count($appRoles) == 0){
				//ログインできるのがサイト１個のみなので、それにログイン
				$siteRole = array_shift($siteRoles);
				if( $this->redirectToCMS($siteRole) ){
					return SOY2Action::SUCCESS;
				}
			}elseif(count($siteRoles) == 0 && count($appRoles) == 1){
				//ログインできるのがApp１個のみなので、それにログイン
				$appRole = array_shift($appRoles);//@index appIdがかかってるので$appRoles[0]ではだめ

				//App操作者のみ <-- ログインできるサイトが0なのでShopに関してはこの判定は不要では？
				if($this->checkAppRole($appRole)){
					if( $this->redirectToApp($appRole->getAppId()) ){
						return SOY2Action::SUCCESS;
					}
				}
			}elseif(count($siteRoles) == 1 && count($appRoles) == 1){
				//Shop用：ShopはSiteにも登録されているのでこれが必要

				$siteRole = array_shift($siteRoles);
				$appRole  = array_shift($appRoles);//@index appIdがかかってるので$appRoles[0]ではだめ

				//SOY Shopだけに権限があるとき
				if("shop" == $appRole->getAppId()){
					//サイト情報を取得
					$site = $this->getSiteById($siteRole->getSiteId());
					if( !$site ){
						return SOY2Action::FAILED;
					}

					//サイトがショップのサイトなら管理画面に移動
					if( Site::TYPE_SOY_SHOP == $site->getSiteType() ){
						$this->redirectToApp($appRole->getAppId());
						return SOY2Action::SUCCESS;
					}
				}
			}
		}

		return SOY2Action::FAILED;
    }

	/**
	 * SOY CMSのサイト管理画面に移動
	 * 転送先が指定されている場合はそこへ
	 */
    function redirectToCMS($siteRole){
		try{
			//ここは1つのサイトの権限を持っている人のみなので第２引数はtrue
			//自動ログインなので第３引数もtrue
			UserInfoUtil::loginSite($siteRole, true, true);
		}catch(Exception $e){
			return false;
		}

		if(strlen($this->redirect) >0 && CMSAdminPageController::isAllowedPath($this->redirect, "../soycms/")){
			SOY2PageController::redirect($this->redirect);
		}else{
			SOY2PageController::redirect("../soycms/");
		}

		return true;
    }

	/**
	 * SOY Appの管理画面に移動
	 * 転送先が指定されている場合はそこへ
	 */
    function redirectToApp($appId){
		//自動ログインなので第1引数はtrue
		UserInfoUtil::loginApp(true);

		if(strlen($this->redirect) >0 && CMSAdminPageController::isAllowedPath($this->redirect, "../app/index.php/" . $appId)){
			SOY2PageController::redirect($this->redirect);
		}else{
			SOY2PageController::redirect("../app/index.php/" . $appId);
		}
    }

    /**
     * App権限でApp操作者の場合はAppのログインを表示しない（直接ログインしてしまう）
     * @TODO この仕様は要検討
     * Shopの場合はApp管理画面にログインされると都合が悪いのでそれに合わせたが
     * 他のAppの場合では管理画面にログインできる必要があるかもしれない
     */
    function checkAppRole($appRole){
   		return ($appRole->getAppRole() == AppRole::APP_USER);
    }

	/**
	 * サイトを取得する
	 */
    function getSiteById($siteId){
		try{
			$dao = SOY2DAOFactory::create("admin.SiteDAO");
			return $dao->getById($siteId);
		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * ユーザーのサイト権限を取得する
	 */
	function getSiteRoles($userId){
		$siteRoleDao = SOY2DAOFactory::create("admin.SiteRoleDAO");
		try{
			$siteRoles = $siteRoleDao->getByUserId($userId);
		}catch(Exception $e){
			$siteRoles = array();
		}
		return $siteRoles;
	}

	/**
	 * ユーザーのApp権限を取得する
	 */
	function getAppRoles($userId){
		$appRoleDao = SOY2DAOFactory::create("admin.AppRoleDAO");
		try{
			$appRoles = $appRoleDao->getByUserId($userId);
		}catch(Exception $e){
			$appRoles = array();
		}
		return $appRoles;
	}
}
