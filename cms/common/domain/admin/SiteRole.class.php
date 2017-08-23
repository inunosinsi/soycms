<?php
/**
 * @table SiteRole
 * @date 2007-08-22 18:42:19
 */
class SiteRole {

	/**
	 * サイトの権限なし
	 */
	const SITE_NO_ROLE = 0;

	/**
	 * サイトの一般管理者
	 */
	const SITE_SUPER_USER = 1;

	/**
	 * サイトのエントリー編集権限（公開権限あり）
	 */
	const SITE_ENTRY_ADMINISTRATOR = 2;

	/**
	 * サイトのエントリー管理者（公開権限なし）
	 */
	const SITE_LIMITED_USER = 3;

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column user_id
	 */
	private $userId;

	/**
	 * @column site_id
	 */
	private $siteId;

	/**
	 * 権限の値が入る（以前はフラグが入っていた）
	 * @column is_limit
	 */
	private $isLimitUser;

	public function setUserId($userId){
		$this->userId = $userId;
	}

	public function getUserId(){
		return $this->userId;
	}

	public function setSiteId($siteId){
		$this->siteId = $siteId;
	}

	public function getSiteId(){
		return $this->siteId;
	}

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}

	function getIsLimitUser() {
		return $this->isLimitUser;
	}
	function setIsLimitUser($isLimitUser) {
		$this->isLimitUser = $isLimitUser;
	}

	/**
	 * この管理者権限を定数の形で返す関数
	 */
	public function getSiteRole(){
		return $this->isLimitUser;
	}

	/**
	 * この管理者権限を定数の形で設定する関数
	 */
	public function setSiteRole($siteRole){

		if($siteRole == self::SITE_NO_ROLE){
			throw new Exception("SITE_NO_ROLEはデータベースに格納できません");
		}

		$this->isLimitUser = $siteRole;
	}

	/**
	 * この管理者権限をテキストで返す関数
	 */
	public function getSiteRoleText(){
		$list = self::getSiteRoleLists();
		return $list[$this->isLimitUser];
	}

	/**
	 * 一般管理者かどうか
	 */
	public function isSiteAdministrator(){
		switch($this->getSiteRole()){
			case self::SITE_SUPER_USER :
				return true;
				break;
			case self::SITE_ENTRY_ADMINISTRATOR :
			case self::SITE_LIMITED_USER :
			case self::SITE_NO_ROLE :
			default:
				return false;
				break;
		}
	}

	/**
	 * エントリー管理者かどうか
	 */
	public function isEntryAdministrator(){
		$siteRole = $this->getSiteRole();

		switch($siteRole){
			case self::SITE_ENTRY_ADMINISTRATOR :
			case self::SITE_LIMITED_USER :
				return true;
				break;
			case self::SITE_SUPER_USER :
			case self::SITE_NO_ROLE :
			default:
				return false;
				break;
		}
	}

	/**
	 * エントリーの公開権限があるかどうか
	 *
	 */
	public function isEntryPublisher(){
		$siteRole = $this->getSiteRole();

		switch($siteRole){
			case self::SITE_SUPER_USER :
			case self::SITE_ENTRY_ADMINISTRATOR :
				return true;
				break;
			case self::SITE_LIMITED_USER :
			case self::SITE_NO_ROLE :
			default:
				return false;
				break;
		}
	}

	public static function getSiteRoleLists(){
		return array(
			self::SITE_NO_ROLE             => CMSMessageManager::get("ADMIN_NO_ROLE"),
			self::SITE_SUPER_USER          => CMSMessageManager::get("ADMIN_USER"),
			self::SITE_ENTRY_ADMINISTRATOR => CMSMessageManager::get("ADMIN_ENTRY_EDITOR"),
			self::SITE_LIMITED_USER        => CMSMessageManager::get("ADMIN_ENTRY_EDITOR_LIMITED")
		);
	}
}
?>
