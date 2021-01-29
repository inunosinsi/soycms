<?php
if(defined("SOYCMS_ASP_MODE")){
	include(dirname(__FILE__)."/userinfoutil/asp.php");
}else{
	include(dirname(__FILE__)."/userinfoutil/normal.php");
}

interface IUserInfoUtil {

	/**
	 * ログアウトする
	 */
	public static function logout();

	/**
	 * ログイン：ログイン状態をセッションに保存する
	 */
	public static function login($user);

	/**
	 * サイトへログイン：権限をセッションに保存する
	 */
	public static function loginSite(SiteRole $siteRole, $onlyOneSiteAdministor);

	/**
	 * Appへログイン：権限をセッションに保存する
	 * @param boolean ログイン先が１つのみで自動ログインしたかどうか
	 */
	public static function loginApp($hasOnlyOneRole = false);

	/**
	 * 現在ログインしているかどうかを返す
	 * SOY2Actionを利用
	 */
    public static function isLoggined();

    /**
     * 現在ログインユーザがデフォルトユーザであるかどうか
     */
    public static function isDefaultUser();

    /**
     * 現在ログインしているユーザが一般管理者権限を持っているか
     */
    public static function hasSiteAdminRole();

    /**
     * 現在ログインしているユーザがエントリー公開権限を持っているか
     */
    public static function hasEntryPublisherRole();

	/**
	 * 現在ログインしているユーザが自動ログインしたユーザかどうか
	 * （１つのサイト/アプリにしかログイン権限がないということ）
	 */
	public static function hasOnlyOneRole();

    /**
     * 現在ログインしているユーザのIDを返す
     */
    public static function getUserId();

    /**
     * 現在ログインしているユーザのログインID（User.UserId）を返す
     */
    public static function getLoginId();

    /**
     * 現在ログインしているユーザ名を返す
     */
    public static function getUserName();

	/**
     * 現在ログインしているユーザのメールアドレスを返す
     */
    public static function getUserMailAddress();

    /**
     * 現在ログインしているサイトの情報を返す
     */
    public static function getSite();

    /**
     * 現在ログインしているサイトのIDを返す
     */
    public static function getSiteId();

    /**
     * サイトの情報を更新する（セッション内部）
     */
    public static function updateSite(Site $site);

    /**
     * 現在ログインしているサイトのディレクトリを返す
     *
     * @param isRealpath(=false) trueならば場所を返す
     */
    public static function getSiteDirectory($isRealpath = false);

    /**
     * 現在ログインしているサイトのサイトのURLを取得
     */
    public static function getSiteURL();

    /**
     * サイトの公開URLを取得（ルート設定ならルート設定のURLを返す）
     */
    public static function getSitePublishURL();

    /**
     * 現在ログインしているサイトがルート設定されているかどうか
     */
    public static function getSiteIsDomainRoot();

    /**
     * URLからサーバーのパスを取得する
     */
    public static function url2serverpath($address);

    /**
     * サイトの設定を取得
     */
    public static function getSiteConfig($key = null);

    /**
     * サイトのIDからサイトのURLを取得
     */
    public static function getSiteURLBySiteId($siteId = null);

    /**
     * App権限情報を返す
     * @return array
     */
    public static function getAppAuth();
}
