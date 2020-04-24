<?php
class SOYShopUserCustomfield implements SOY2PluginAction{

	private $app;
	private $userId;

	function clear($app){}

	/**
	 * @param array $param 中身は$_POST["custom_field"]
	 */
	function doPost($app){}

	/**
	 * マイページ・カートの登録で表示するフォーム部品の生成
	 * @param MyPageLogic || CartLogic $mypage
	 * @param integer $userId
	 * @return array(["name"], ["description"], ["error"])
	 */
	function getForm($app, $userId){}

	/**
	 * 各項目ごとに、createAdd()を行う。
	 * @param MyPageLogic || CartLogic $mypage
	 * @param SOYBodyComponentBase $pageObj
	 * @param integer $userId
	 */
	function buildNamedForm($app, SOYBodyComponentBase $pageObj, $userId = null){}

	/**
	 * エラーチェック
	 * @return Boolean
	 */
	function hasError($param){
		return false;
	}

	/**
	 * 登録確認で表示する
	 */
	function confirm($app){}

	/**
	 * 管理画面の注文の追加で表示できるエリア
	* @return Array array(array("name" => "", "value" => "", "style" => "")) ※styleはなしで良い
	 */
	function order($userId){}

	/**
	 * UserAttributeに登録する
	 * @param MyPageLogic || CartLogic $mypage
	 */
	function register($app, $userId){}

	function getApp() {
		return $this->app;
	}
	function setApp($app) {
		$this->app = $app;
	}

	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
}
class SOYShopUserCustomfieldDelegateAction implements SOY2PluginDelegateAction{

	private $mode = "register";
	private $app;
	private $param;//$_POST["custom_field"][key]
	private $userId;
	private $pageObj;

	private $_list = array();
	private $_confirm = array();
	private $hasError = false;

	function run($extetensionId, $moduleId, SOY2PluginAction $action){

		$action->setApp($this->getApp());
		$action->setUserId($this->getUserId());

		switch($this->mode){
			case "form";//_listプロパティに、input生成用配列を詰める。要$delegate->getList();
				$this->_list[$moduleId] = $action->getForm($this->app, $this->userId);
				break;

			case "clear":
				$action->clear($this->app);
				break;

			case "post":
				$action->doPost($this->param);
				break;

			case "checkError":
				if($action->hasError($this->param)){
					$this->hasError = true;
				}else{
					//do nothing
				}
				break;

			case "confirm"://_confirmプロパティに、確認画面の表示用は列を詰める。要$delegate->getConfirm();
				$this->_confirm[$moduleId] = $action->confirm($this->app);
				break;

			case "register":
				$action ->register($this->app, $this->userId);
				break;

			case "build_named_form"://各項目ごとに、createAdd()を行う
				$action ->buildNamedForm($this->app, $this->pageObj, $this->userId);
				break;

			case "order":
				$this->_list[$moduleId] = $action->order($this->userId);
				break;

			case "none":
				//何もしない
				break;
		}
	}

	function getList(){
		return $this->_list;
	}
	function getConfirm(){
		return $this->_confirm;
	}
	function getMode() {
		return $this->mode;
	}
	function setMode($mode) {
		$this->mode = $mode;
	}
	function getApp() {
		return $this->app;
	}
	function setApp($app) {
		$this->app = $app;
	}
	function getUserId(){
		return $this->userId;
	}
	function setUserId($userId){
		$this->userId = $userId;
	}
	function getParam() {
		return $this->param;
	}
	function setParam($param) {
		$this->param = $param;
	}
	function hasError(){
		return $this->hasError;
	}

	public function getPageObj() {
		return $this->pageObj;
	}
	public function setPageObj($pageObj) {
		$this->pageObj = $pageObj;
	}
}
SOYShopPlugin::registerExtension("soyshop.user.customfield","SOYShopUserCustomfieldDelegateAction");
