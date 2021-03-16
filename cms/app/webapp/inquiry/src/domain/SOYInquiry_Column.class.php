<?php

SOY2::import("domain.SOYMailConverter");
SOY2::import("domain.SOYShopConnector");

/**
 * @table soyinquiry_column
 */
class SOYInquiry_Column{

	/**
	 * @id
	 */
	private $id;

	/**
	 * @column form_id
	 */
	private $formId;

	/**
	 * @column column_id
	 */
	private $columnId;

	private $label;

	/**
	 * @column column_type
	 */
	private $type;

	private $config;

	/**
	 * @column display_order
	 */
	private $order = 1;

	/**
	 * @column is_require
	 */
	private $require = 0;

	/**
	 * @no_persistent
	 */
	private $value;

	/**
	 * @no_persistent
	 */
	public static $columnTypes = array(
		"SingleText" => "1行テキスト",
		"MultiText" => "複数行テキスト",
		"Radio" => "ラジオボタン",
		"CheckBox" => "チェックボックス",
		"SelectBox" => "セレクトボックス",
		"Date" => "日付",
		"DateWithoutDay" => "日付(日なし)",
		"Prefecture" => "都道府県",
		"Address" => "住所",
		"AddressJs" => "住所(JS版)",
		"File" => "アップロード",
		"Files" => "アップロード(複数)",
		"Telephone" => "電話番号",
		"MultiSingleText" => "分割1行テキスト",
		"NameText" => "[名前]",
		"MailAddress" => "[メールアドレス]",
		"ConfirmMailAddress" => "[メールアドレス(確認用フォーム有り)]",
		"PrivacyPolicy" => "[個人情報保護方針]",
		"Question" => "[質問]",
		"PlainText" => "[見出し表示]",
		"SOYCMSBlogEntryPage" => "カスタムフィールド [SOY CMSブログ詳細ページ]",
		"SOYCMSBlogEntry" => "記事名 [SOY CMSブログ連携]",
		"SOYShop" => "商品名 [SOY Shop連携]",
		"Enquete" => "アンケート項目",
		"EnqueteFree" => "アンケート自由記述",
		"SerialNumber" => "連番",
		//"CustomfieldAdvanced" => "カスタムフィールドアドバンスド連携"
	);

	/**
	 * @no_persistent
	 */
	private $inquiry;

	/**
	 * #helper function
	 */
	private static function _types(){
		static $types;
		if(is_null($types)){
			$types = SOYInquiry_Column::$columnTypes;

			//拡張 /common/inquiry.config.phpがあれば読み込む
			if(file_exists(CMS_COMMON . "/config/inquiry.config.php")){
				include_once(CMS_COMMON . "/config/inquiry.config.php");
				$types = array_merge($types, $advancedColumns);
			}
		}
		return $types;
	}

	/**
	 * #factory
	 */
	function getColumn(SOYInquiry_Form $form = null){

		if($this->type) SOY2::import("columns." . $this->type . "Column");
		$className = $this->type . "Column";

		if(!class_exists($className)){
			$column = new SOYInquiry_ColumnBase();
		}else{
			$column = new $className();
			$column->setConfigure($this->config);
		}

		$column->setId($this->id);
		$column->setFormId($this->formId);
		if(strlen($this->columnId)>0){
			$column->setColumnId($this->columnId);
		}else{
			$column->setColumnId($this->id);
		}
		if($form)$column->setFormObject($form);
		$column->setInquiry($this->getInquiry());
		$column->setLabel($this->label);
		$column->setValue($this->value);
		$column->setIsRequire($this->require);
		return $column;
	}
	function setColumn($columnObject){
		$configure = $columnObject->getConfigure();
		$this->config = $configure;
	}
	/* 以下 getter setter */

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getFormId() {
		return $this->formId;
	}
	function setFormId($formId) {
		$this->formId = $formId;
	}
	function getLabel() {
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}
	function getType() {
		return $this->type;
	}
	function setType($type) {
		$this->type = $type;
	}
	function getConfig() {
		return serialize($this->config);
	}
	function setConfig($config) {
		if(is_string($config)){
			$this->config = unserialize($config);
		}else{
			$this->config = $config;
		}
	}
	function getOrder() {
		return $this->order;
	}
	function setOrder($order) {
		$this->order = $order;
	}
	function getRequire() {
		return (int)$this->require;
	}
	function setRequire($require) {
		$this->require = $require;
	}
	function getValue() {
		return $this->value;
	}
	function setValue($value) {
		$this->value = $value;
	}

	/**
	 * 保存用
	 */
	function getContent(){
		$obj = $this->getColumn();
		return $obj->getContent();
	}

	public static function getTypes(){
		return self::_types();
	}

	function getTypeText(){
		$types = self::_types();
		return (isset($types[$this->type])) ? $types[$this->type] : "無効な種別(".$this->type.")";
	}

	function getInquiry() {
		return $this->inquiry;
	}
	function setInquiry($inquiry) {
		$this->inquiry = $inquiry;
	}

	function getColumnId() {
		return $this->columnId;
	}
	function setColumnId($columnId) {
		$this->columnId = $columnId;
	}

	function getNoPersistent(){
		return $this->getColumn()->getNoPersistent();
	}
}

interface ISOYInquiry_Column{

	/**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attribute = array());

	/**
	 * 確認画面で呼び出す
	 */
	function getView();

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm();

	/**
	 * データ投入用
	 */
	function getContent();

	/**
	 * Inquiry#append時に呼び出し
	 */
	function onAppend();

	/**
	 * 値が正常かどうかチェック
	 * @return boolean
	 */
	function validate();

	/**
	 * エラーメッセージを取得
	 */
	function getErrorMessage();

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config);

	/**
	 * 保存に必要な設定を取得する
	 */
	function getConfigure();

}

class SOYInquiry_ColumnBase implements ISOYInquiry_Column{

	protected $id;
	protected $formId;
	protected $columnId;
	protected $label;
	protected $value;
	protected $isRequire;
	protected $errorMessage = "";
	protected $formObject = null;
	protected $inquiry;
	protected $SOYMailTo = SOYMailConverter::SOYMAIL_NONE;
	protected $SOYShopFrom = SOYShopConnector::SOYSHOP_NONE;
	protected $replacement;
	protected $annotation;
	protected $trProperty;
	protected $noPersistent = false;

	/**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attributes = array()){}

	/**
	 * 確認画面で呼び出す
	 */
	function getView(){
		return htmlspecialchars((string)$this->getValue(), ENT_QUOTES, "UTF-8");
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){}

	/**
	 * SOYMailへの連携先一覧を返す
	 */
	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE => "連携しない"
		);
	}

	/**
	 * SOY Shopとの連携項目一覧を返す
	 */
	function getLinkagesSOYShopFrom(){
		return array(
			SOYShopConnector::SOYSHOP_NONE => "連携しない"
		);
	}

	/**
	 * SOYMailに情報を登録するときのConverterを返す
	 */
	function factoryConverter() {
		return new SOYMailConverter();
	}

	function factoryConnector(){
		return new SOYShopConnector();
	}

	/**
	 * SOYMail連携用のデータ(convert後のデータを取得)
	 *
	 * @return array
	 */
	function convertToSOYMail(){
		$converter = $this->factoryConverter();

		$value = $this->getValue();
		$soyMailTo = $this->getSOYMailTo();

		//確認用メールアドレス対策、カラムファイルでgetValueを持ちたかったが、validateが動かなくなるのでこちらで対応
		if($soyMailTo === "mail_address" && is_array($value) === true){
			$value = $value[0];
		}

		return $converter->convert($value, $soyMailTo);
	}

	/**
	 * SOYMail連携用のデータ(convert後のデータを取得)
	 *
	 * @return array
	 */
	function convertToSOYShop(){
		$converter = $this->factoryConverter();	//ConverterはSOY Mailを流用

		$value = $this->getValue();
		$soyShopTo = $this->getSOYShopFrom();	//SOY Shopの場合はToとFromを一緒にする

		//確認用メールアドレス対策、カラムファイルでgetValueを持ちたかったが、validateが動かなくなるのでこちらで対応
		if($soyShopTo === "mail_address" && is_array($value) === true){
			$value = $value[0];
		}

		return $converter->convert($value, $soyShopTo);
	}

	/**
	 * @return String
	 */
	function insertFromSOYShop(){
		$connector = $this->factoryConnector();
		return $connector->insert($this->SOYShopFrom);
	}

	/**
	 * データ投入用
	 *
	 * 標準はgetView()を呼びだす
	 */
	function getContent(){
		return $this->getView();
	}

	/**
	 * メールの本文に使用する
	 *
	 * 標準はgetContent()を呼びだす
	 */
	function getMailText(){
		return $this->getContent();
	}

	/**
	 * Inquiry#append時に呼び出し
	 */
	function onAppend(){}

	/**
	 * onSend
	 */
	function onSend($obj){}



	/**
	 * 値が正常かどうかチェック
	 */
	function validate(){
		if($this->getIsRequire() && strlen($this->getValue()) < 1){
			$this->setErrorMessage($this->getLabel() . "を入力してください。");
			return false;
		}
		return true;
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		$this->isLinkageSOYMail = isset($config["isLinkageSOYMail"]) ? $config["isLinkageSOYMail"] : false;
		$this->isLinkageSOYShop = isset($config["isLinkageSOYShop"]) ? $config["isLinkageSOYShop"] : false;
		$this->SOYMailTo = isset($config["SOYMailTo"]) ? $config["SOYMailTo"] : null;
		$this->SOYShopFrom = isset($config["SOYShopFrom"]) ? $config["SOYShopFrom"] : null;
		$this->replacement = isset($config["replacement"])? $config["replacement"] : null;
		$this->annotation = isset($config["annotation"]) ? $config["annotation"] : null;
		$this->trProperty = (isset($config["trProperty"])) ? $config["trProperty"] : null;

		if(!defined("SOYINQUIRY_FORM_DESIGN_PAGE")){
			define("SOYINQUIRY_FORM_DESIGN_PAGE", (isset($_SERVER["PATH_INFO"]) && strpos($_SERVER["PATH_INFO"], "/" . APPLICATION_ID . "/Form/Design/") !== false));
		}
	}

	/**
	 * 保存に必要な設定を取得する
	 */
	function getConfigure(){
		$config = array(
			"isLinkageSOYMail"=>$this->isLinkageSOYMail,
			"SOYMailTo" => $this->SOYMailTo,
			"SOYShopFrom" => $this->SOYShopFrom,
			"SOYShopFrom" => $this->SOYShopFrom,
			"replacement" => $this->replacement,
			"annotation" => $this->annotation,
			"trProperty" => $this->trProperty
		);
		return $config;
	}

	/**
	 * エラーメッセージを取得
	 */
	function getErrorMessage(){
    	return $this->errorMessage;
	}

	function getId() {
		return $this->id;
	}
	function setId($id) {
		$this->id = $id;
	}
	function getLabel() {
		return $this->label;
	}
	function setLabel($label) {
		$this->label = $label;
	}
	function getValue() {
		//SOYShop連携
		if(
			is_null($this->value) &&
			(defined("SOYINQUERY_SOYSHOP_CONNECT_SITE_ID") && SOYINQUERY_SOYSHOP_CONNECT_SITE_ID) &&
			$this->SOYShopFrom != SOYShopConnector::SOYSHOP_NONE &&
			SOYInquiryUtil::checkSOYShopInstall()
		){
			$this->value = $this->insertFromSOYShop();
		}
		return $this->value;
	}
	function setValue($value) {
		$this->value = $value;
	}

	function getIsRequire() {
		return $this->isRequire;
	}
	function isRequire() {
		return $this->isRequire;
	}
	function setIsRequire($isRequire) {
		$this->isRequire = $isRequire;
	}

	function setErrorMessage($errorMessage) {
		$this->errorMessage = $errorMessage;
	}

	function getFormId() {
		return $this->formId;
	}
	function setFormId($formId) {
		$this->formId = $formId;
	}

	function getFormObject() {
		return $this->formObject;
	}
	function setFormObject($formObject) {
		$this->formObject = $formObject;
	}

	function getSOYMailTo() {
		return $this->SOYMailTo;
	}

	function setSOYMailTo($to) {
		$this->SOYMailTo = $to;
	}

	function getSOYShopFrom(){
		return $this->SOYShopFrom;
	}
	function setSOYShopFrom($from){
		$this->SOYShopFrom = $from;
	}

	function getInquiry() {
		return $this->inquiry;
	}
	function setInquiry($inquiry) {
		$this->inquiry = $inquiry;
	}
	function getReplacement() {
		return $this->replacement;
	}
	function setReplacement($replacement) {
		$this->replacement = $replacement;
	}

	function getColumnId() {
		return $this->columnId;
	}
	function setColumnId($columnId) {
		$this->columnId = $columnId;
	}

	function getAnnotation(){
		return $this->annotation;
	}
	function setAnnotation($annotation){
		$this->annotation = $annotation;
	}

	function getTrProperty(){
		return $this->trProperty;
	}
	function setTrProperty($trProperty){
		$this->trProperty = $trProperty;
	}

	function getNoPersistent(){
		return $this->noPersistent;
	}
	function setNoPersistent($noPersistent){
		$this->noPersistent = $noPersistent;
	}
}
