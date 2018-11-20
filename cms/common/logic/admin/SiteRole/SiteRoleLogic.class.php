<?php
SOY2::import("domain.admin.SiteRole");
class SiteRoleLogic implements SOY2LogicInterface{

	public static function getInstance($a,$b){
		return SOY2LogicBase::getInstance($a,$b);
	}

	/**
	 * ユーザIDに登録されているサイト権限を取得する
	 */
    function getSiteRoleByUserId($userId) {
    	$dao = SOY2DAOFactory::create("admin.SiteRoleDAO");
    	return $dao->getByUserId($userId);
    }

    /**
     * サイトIDに登録されているサイト権限を取得する
     */
    function getSiteRoleBySiteId($siteId){
    	$dao = SOY2DAOFactory::create("admin.SiteRoleDAO");
    	return $dao->getBySiteId($siteId);
    }

    /**
     * ユーザID、サイトIDを指定し、それに対応する権限を返す
     * @param flushBuffer trueを設定すると、サイト権限のバッファーを破棄し、新しくデータベースより取得します
     */
    public static function getSiteRole($siteId,$userId,$flushBuffer = false){

    	static $siteRoleBuffer = null;

    	$dao = SOY2DAOFactory::create("admin.SiteRoleDAO");

    	try{
    		$siteRole = $dao->getSiteRole($siteId,$userId);
    		return $siteRole->getSiteRole();
    	}catch(Exception $e){
    		return SiteRole::SITE_NO_ROLE;
    	}

    }

    /**
     * 管理者権限を設定します。
     * @param siteRoleArray
     * 一つの単位が array(
     * 	"siteId"=>$siteId,
     * 	"userId"=>$userId,
     *  "siteRole"=>$siteRole
     * );
     * の配列を渡します。
     * 明示的にSITE_NO_ROLEを渡すことによってデータベースからエントリーを削除します
     * 設定された値がない場合はデータベースは更新されません。
     */
    public function updateSiteRoles($siteRoleArray){
    	$dao = SOY2DAOFactory::create("admin.SiteRoleDAO");
    	$dao->begin();
    	foreach($siteRoleArray as $key => $siteRole){
			if(!$this->updateSiteRole($siteRole)){
				$dao->rollback();
				return false;
			}
		}
		$dao->commit();
		return true;
	}

    /**
     * 上のArrayでなく1個のときのupdate
     *
     * START
     *  ↓
     * <今の値が>┬[一般管理者orEntry管理者]→<データベースの内容と変化があるか>-(Y)→<新しい値はNO_ROLE>-(Y)→deleteSQL→END
     *           │                                    └(N)→END                           └(N)→updateSQL→END
     *           ├[権限なし]→<データベースの内容と変化があるか>-(Y)→insertSQL→END
     *           │                          └(N)→END
     *           ×└[予期しない値]→Exception→END　これはやらない
     *
     */
    public function updateSiteRole($siteRoleArray){
    	$dao = SOY2DAOFactory::create("admin.SiteRoleDAO");

    	try{
    		$siteRole = $dao->getSiteRole($siteRoleArray["siteId"],$siteRoleArray["userId"]);
    		$role = $siteRole->getSiteRole();
    	}catch(Exception $e){
    		$siteRole = new SiteRole();
    		$role = SiteRole::SITE_NO_ROLE;
    	}

    	try{
			switch($role){
				case SiteRole::SITE_NO_ROLE:
					if($siteRoleArray["siteRole"] == SiteRole::SITE_NO_ROLE){
						break;//変化なし
					}
					//変化あり、insertSQL発行
					$siteRole->setUserId($siteRoleArray["userId"]);
					$siteRole->setSiteId($siteRoleArray["siteId"]);
					$siteRole->setSiteRole($siteRoleArray["siteRole"]);
					$dao->insert($siteRole);
					break;
//				case SiteRole::SITE_ENTRY_ADMINISTRATOR:
//				case SiteRole::SITE_SUPER_USER:
				default:
					if($siteRoleArray["siteRole"] == $siteRole->getSiteRole()){
						break;//変化なし
					}
					if($siteRoleArray["siteRole"] == SiteRole::SITE_NO_ROLE){
						//変化あり、権限を削除する場合、deleteSQL発行
						$dao->delete($siteRole->getId());
					}else{
						//変化あり、updateSQL発行
						$siteRole->setSiteRole($siteRoleArray["siteRole"]);
						$dao->update($siteRole);
					}
					break;
//					throw new Exception("定義されていない値");
//					break;
			}
			return true;
		}catch(Exception $e){
			return false;
		}
    }

    /**
     * ユーザIDに関連付けられているすべての権限を削除する
     */
    public function deleteByUserId($userId){
    	$dao = SOY2DAOFactory::create("admin.SiteRoleDAO");
    	try{
    		$dao->deleteByUserId($userId);
    		return true;
    	}catch(Exception $e){
    		return false;
    	}
    }

    /**
     * サイトIDに関連付けられているすべての権限を削除する
     */
    public function deleteBySiteId($siteId){
    	$dao = SOY2DAOFactory::create("admin.SiteRoleDAO");
    	try{
    		$dao->deleteBySiteId($siteId);
    		return true;
    	}catch(Exception $e){
    		return false;
    	}
    }
}
