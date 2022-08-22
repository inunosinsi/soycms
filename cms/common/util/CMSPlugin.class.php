<?php
/*
 * definedスイッチ
 * CMS_PLUGIN_ADMIN_MODE true | false
 */
class CMSPlugin {

	/**
	 * イベントを整理
	 * 引数はarray
	 * 説明の頭に*がついているものはまだ動作しない
	 */
	var $_event = array(

		//PathInfoBuilder
		"onPathInfoBuilder"=>array(),	//PathInfoBuilder内				array(uri, args) @return array("uri" => "", args => array())

		//ページ関連
		"onPageLoad"=>array(),			//ページが読み込まれる直前			array(page,webPage)
		"onPageCreate"=>array(),		//ページが作成される直前			array(page)
		"onPageUpdate"=>array(),		//ページが更新される直前			array(new_page,old_page)
		"onPageRemove"=>array(),		//ページが削除される直前			array(pageId)
		"onPutTrash"=>array(),			//ページがゴミ箱に移動したとき		array(pageId)
		"onRecover"=>array(),			//ページがゴミ箱から復元されたとき	array(pageId)
		"onPageOutput" => array(),		//ページが出力される前				array($pageObject)
		"onPageEdit" => array(),		//ページの編集画面が呼び出される直前	array(page)
		"onPageTitleFormat" => array(),	//タイトルフォーマットの変換 	array(format) @return string

		//管理画面の拡張
		"onAdminTop" => array(),		//管理画面トップページの拡張	@params array()、@return array("title" => "", "content" => "")

		//エントリー関連
		"onEntryGet"=>array(),			//エントリーを取得する時			array(blogLabelId, entryId)
		"onEntryLoad"=>array(),			//*エントリーが読み込まれる直前		array(entry)
		"onEntryCreate"=>array(),		//エントリーが作成される直後		array(entry)
		"onEntryUpdate"=>array(),		//エントリーが更新される直後		array(entry)
		"onEntryRemove"=>array(),		//エントリーが削除された直後		array(entryIds)
		"onEntryStateChange"=>array(),	//*エントリー公開状態が変更された直後array(entryId,state)
		"onEntryOutput"=>array(),		//エントリーが呼び出された際に呼ばれる array(entryId,SOYHTMLObject,entry)
		"onEntryCopy"=>array(),			//エントリー複製時に呼び出される	array(oldId,newId)
		"onSetupWYSIWYG"=>array(),		//WYSIWYGエディタをセットアップしている時 array(entryId, labelIds)
		"onEntryListBeforeOutput"=>array(),	//エントリーリストが呼び出される直前 array(&entries)
		"onEntryStateMessage"=>array(),	//エントリーの状態を出力する時		array(entryId)

		//記事のCSV
		"onEntryCSVExImport"=>array(),			//インポート、エクスポート時に対象となるプラグインを探す array()
		"onEntryCSVExport"=>array(),		//記事データのエクスポート array(entryId)
		"onEntryCSVImport"=>array(),		//記事データのインポート array(entryId, value)

		//ラベル関連
		"onLabelCreate"=>array(),		//ラベルが作成される直前			array(label)
		"onLabelUpdate"=>array(),		//ラベルが更新される直前			array(new_label)
		"onLabelRemove"=>array(),		//ラベルが削除される直前			array(labelId)
		"onLabelOutput"=>array(),		//ラベルが呼び出された際に呼ばれる array(labelId,SOYHTMLObject,label)
		"onLabelSetupWYSIWYG"=>array(),	//ラベル詳細でWYSIWYGエディタをセットアップしている時

		//エントリーラベル関連
		"onEntryLabelApply"=>array(),	//エントリーがラベル付けされる直前	array(entryId,labelId)
		"onEntryLabelRemove"=>array(),	//エントリーからラベルが削除される直前 array(entryId,labelId)
		"afterEntryLabelsApply"=>array(),	//エントリーのラベル付けが全部終わったされた直後	array(entryId)

		//サイト関連
		"onSiteCreate"=>array(),		//*サイトが作成される直前
		"onSiteRemove"=>array(),		//*サイトが削除される直前

		//ファイルマネージャ関連
		"onFileUpload"=>array(),		//*ファイルがアップロードされる直前(filename)
		"onFileUploadConvertFileName"=>array(),	//*ファイルがアップロードされた後、ファイル名の変更を行うarray(filename) 上の拡張ポイントが何の為に作られたのか？が不明の為、、新規で設ける
		"onFileRemoved"=>array(),		//*ファイルが削除される直前(filename)

		//ブログ関連
		"onSubmitComment"=>array(),		//*コメントが投稿されたとき			array(entryComment)
		"afterSubmitComment"=>array(),	//*コメントを挿入された後			array(entryComment)
		"onSubmitTrackback"=>array(),	//*トラックバックを受信したとき		array(trackback)
		"afterSubmitTrackback"=>array(),//*トラックバックを挿入した後		array(trackback)
		"onBlogSetupWYSIWYG"=>array(),	//ブログページ詳細でWYSIWYGエディタをセットアップしている時
		"onBlogPageUpdate"=>array(),		//ページが更新される直前			array(new_page,old_page)
		"onBlogPageConfigUpdate"=>array(),	//ブログページの設定が更新される

		//ブロック関連
		"onBlockLoad"=>array(),			//*ブロックが呼び出される直前(blockId)
		"onBlockCreate"=>array(),		//*ブロックが作成される直前(blockId)
		"onBlockUpdate"=>array(),		//*ブロックが更新される直前(blockId)
		"onBlockRemove"=>array(),		//*ブロックが削除される直前(blockId)

		//プラグインブロック
		"onPluginBlockLoad"=>array(),   //*プラグインブロックが呼び出される直前 array()
		"onPluginBlockAdminReturnPluginId"=>array(),  //*プラグインブロックの管理画面が表示される直前 array()

		//その他
		"onActive"=>array(),			//プラグインが有効になる直前
		"onDisable"=>array(),			//プラグインが無効になる直前
		"onLoadPageTemplate"=>array(),	//ページのテンプレートが呼び出される直前	array()
		"beforeOutput"=>array(),		//出力される直前
		"afterOutput"=>array(),			//出力された直後
		"onOutput"=>array(),			//出力されるHTMLに対して最後に呼ばれるイベント（最終的に出力されるHTML）	array(html,page,webPage),

		//キャッシュの削除
		"onClearCache"=>array(),			//キャッシュの削除の際

		//アクセス関連
		"onSiteAccess"=>array(),		//サイトにアクセスがあった場合 array()
		"onSite404NotFound"=>array()	//404NotFoundページを開いた時
	);

	var $_plugins = array();

	var $_blocks = array(
		"blog_entry" => array(),	//エントリー内部で使うことができる　引数として、エントリーのIDが渡される
		"page" => array(),			//通常のテンプレート内部で使うことが出来る。
	);

	var $_init = array(
		'all' => array(),
		'blog'=> array(),
		'page'=> array()
	);

	var $_actionTypes = array(

	);

	var $_activeHook = array(
	);

	var $_customFieldFunctions = array();

	private function __construct() {}

	/* internal functions */


	/* public static functions */

	/**
	 * @singleton
	 */
	private static function &getInstance(){
		static $_static;
		if(is_null($_static)){
			$_static = new CMSPlugin();
			$_static->loadPlugins();
		}
		return $_static;
	}

	/**
	 * プラグインの読み込み
	 *
	 * プラグイン設置ディレクトリにあるファイルを読み込みます。
	 * CMS_PLUGIN_DIR_/XXXX/XXXX.php
	 */
	function loadPlugins(){
		//プラグインのページではすべてのプラグインのファイルを読み込む	!defined("_SITE_ROOT_")で管理画面側であることを調べている
		$isAll = (!defined("_SITE_ROOT_") && is_numeric(strpos($_SERVER["REQUEST_URI"], "Plugin")));

		$dir = CMS_PAGE_PLUGIN;
		$files = scandir($dir);
		
		foreach($files as $file){
			if(!$isAll && !self::activeCheck($file)) continue;	//プラグインのページ以外では有効でないプラグインは読み込まない
			if($file[0] == "." || !is_dir($dir . $file) || !is_readable($dir . $file ."/".$file.".php")) continue;
			include_once($dir . $file ."/".$file.".php");
		}
	}

	/* 便利関数 */

	/**
	 * 有効になっているかチェック
	 */
	static function activeCheck(string $pluginId){
		static $l;
		if(is_null($l)) $l = self::readActiveCheckCache(); // インストール状況のキャッシュを作成		
		return (is_numeric(array_search($pluginId, $l)));
	}

	static function readActiveCheckCache(){
		$path = self::activeCheckCacheFilePath();
		if(!file_exists($path)) self::createActiveCheckCache();	//キャッシュファイルを生成する
		return unserialize(file_get_contents($path));
	}
	/**
	 * プラグインの有効状態のキャッシュを作成
	 */
	static function createActiveCheckCache(){
		$installedList = array();

		if(!function_exists("soycms_get_plugin_ids")) include_once(SOY2::RootDir() . "site_include/func/plugin.php");
		foreach(soycms_get_plugin_ids(true) as $pluginFileName){
			if(file_exists(self::getSiteDirectory() .'/.plugin/'. $pluginFileName .".active")){
				$installedList[] = $pluginFileName;
			}else{	//PLUGIN_IDとプラグインのファイル名が異なる場合対策 (例：SOYShopLoginCheckとsoyshop_login_check)
				$pluginId = soycms_get_plugin_id_by_plugin_file_name($pluginFileName);
				if(!strlen($pluginId) || !file_exists(self::getSiteDirectory() .'/.plugin/'. $pluginId .".active") || $pluginId == $pluginFileName || is_numeric(array_search($pluginFileName, $installedList))) continue;	
				$installedList[] = $pluginFileName;
			}
		}
		file_put_contents(self::activeCheckCacheFilePath(), serialize($installedList));
	}

	static function activeCheckCacheFilePath(){
		$dir = rtrim(self::getSiteDirectory(), "/") . "/.cache/plugin/";
		if(!file_exists($dir)) mkdir($dir);
		return $dir . "activelist.log";
	}

	static function getPluginIds(){
		if(!function_exists("soycms_get_plugin_ids")) include_once(SOY2::RootDir() . "site_include/func/plugin.php");
		return soycms_get_plugin_ids();
	}

	/**
	 * サイトのディレクトリを取得
	 */
	static function getSiteDirectory(){
		return (defined("_SITE_ROOT_")) ? _SITE_ROOT_ : UserInfoUtil::getSiteDirectory();
	}



	/**
	 * プラグインの追加及び初期化関数の呼び出し
	 */
	static function addPlugin(string $id, array $initFunc){
		$instance =& CMSPlugin::getInstance();

		if(is_array($initFunc) && !method_exists($initFunc[0],$initFunc[1])) return;
		if(!is_array($initFunc) && !function_exists($initFunc)) return;

		$instance->_plugins[$id] = array();
		call_user_func($initFunc);
	}


	/**
	 * プラグイン管理にメニューの追加
	 */
	static function addPluginMenu(string $id, array $args){
		if(!defined("CMS_PAGE_PLUGIN_ADMIN_MODE") || !CMS_PAGE_PLUGIN_ADMIN_MODE) return;
	
		$instance =& CMSPlugin::getInstance();
		if(!isset($instance->_plugins[$id])) return;

		$instance->_plugins[$id] = $args;
	}

	/**
	 * 設定メニューの追加
	 */
	static function addPluginConfigPage(string $id, array $func){
		if(!defined("CMS_PAGE_PLUGIN_ADMIN_MODE") || !CMS_PAGE_PLUGIN_ADMIN_MODE) return;
		if(!CMSPlugin::activeCheck($id)) return;

		$instance =& CMSPlugin::getInstance();
		if(!isset($instance->_plugins[$id])) return;

		$instance->_plugins[$id]["config"] = $func;
	}

	/**
	 * カスタムメニューの追加
	 * @param html メニュー部分に表示するHTML
	 * @param alt <a>のalt属性
	 */
	static function addWidget(string $id, array $func,string $html=""){
		if(!defined("CMS_PAGE_PLUGIN_ADMIN_MODE") || !CMS_PAGE_PLUGIN_ADMIN_MODE) return;
		
		$instance =& CMSPlugin::getInstance();
		if(!isset($instance->_plugins[$id])) return;

		if(!CMSPlugin::activeCheck($id)) return;

		if(!isset($instance->_plugins[$id]["custom"])) $instance->_plugins[$id]["custom"] = array();

		$widget = array();
		$widget["func"] = $func;
		$widget["html"] = (strlen($html)) ? $html : $id;

		$instance->_plugins[$id]["custom"][] = $widget;
	}

	/**
	 * カスタムフィールドの呼び出し(修正後)
	 */
	static function addCustomFieldFunction(string $id, string $rule, array $func, bool $flag=false){
		if(!CMSPlugin::activeCheck($id)) return;

		$instance =& CMSPlugin::getInstance();
		if(!isset($instance->_customFieldFunctions[$rule]))$instance->_customFieldFunctions[$rule] = array();

		if($flag){
			array_unshift($instance->_customFieldFunctions[$rule],$func);
		}else{
			$instance->_customFieldFunctions[$rule][] = $func;
		}
	}

	/**
	 * カスタムフィールドの呼び出し(修正前)
	 */
	static function addCustomFiledFunction(string $id, string $rule, array $func, bool $flag=false){
		return self::addCustomFieldFunction($id,$rule,$func,$flag);
	}

	static function callCustomFieldFunctions(string $path){
		$instance =& CMSPlugin::getInstance();
		$array = @$instance->_customFieldFunctions[$path];
		if(!is_array($array)) $array = array();

		$html = "";

		foreach($array as $func){
			$html .= call_user_func($func);
		}

		return $html;
	}

	/**
	 * callCustomFieldFunctionsのエイリアス
	 */
	static function callCustomFiledFunctions(string $path){
		return self::callCustomFieldFunctions($path);
	}


	/**
	 * @param string
	 * @return array
	 */
	static function getPluginMenu(string $id=""){
		$instance =& CMSPlugin::getInstance();
		return (strlen($id) && isset($instance->_plugins[$id])) ? $instance->_plugins[$id] : $instance->_plugins;
	}

	/**
	 * 以下、テンプレートに書くことで動作するプラグイン
	 */
	static function addBlock(string $id, string $type, array $func){
		if(!CMSPlugin::activeCheck($id)) return;

		$instance =& CMSPlugin::getInstance();
		if(!isset($instance->_blocks[$type])) return;

		$instance->_blocks[$type][$id] = $func;
	}

	/**
	 * プラグインブロックを複数追加する
	 * @param string $pluginId プラグインID
	 * @param string $blockId テンプレートに記述するcms:plugin
	 * @param string $type
	 * @param array $func
	 */
	static function addMultipleBlock(string $pluginId, string $blockId, string $type, array $func){
		if(!CMSPlugin::activeCheck($pluginId)) return;

		$instance =& CMSPlugin::getInstance();
		if(!isset($instance->_blocks[$type])) return;

		$instance->_blocks[$type][$blockId] = $func;
	}

	static function getBlocks($type){
		$instance =& CMSPlugin::getInstance();
		return @$instance->_blocks[$type];
	}

	/**
	 * イベントをセットします
	 * @param event イベント名
	 * @param id プラグインID
	 * @param func コールバック
	 * @param args イベント引数
	 * @param byForce 強制セット アクティブチェックを無視する
	 */
	static function setEvent(string $event, string $id, array $func, array $args=array(), bool $byForce=false){
		$instance =& CMSPlugin::getInstance();

		//activeなプラグインだけ追加する
		//onActiveだけ限定的に使用可能
		if($event !== "onActive" && !CMSPlugin::activeCheck($id) && !$byForce) return;
		if(!isset($instance->_event[$event])) return;

		$old = null;

		if(isset($instance->_event[$event][$id])){
			$old = $instance->_event[$event][$id];
		}

		$instance->_event[$event][$id] = array(
			$func,
			$args
		);

		return $old;
	}

	/**
	 * イベント関数の呼び出し
	 * イベント引数で処理をしない場合これを呼び出せば早い
	 */
	static function callEventFunc(string $event, $arg=array(), bool $overloadReturn=false){
		$instance =& CMSPlugin::getInstance();
		if(!isset($instance->_event[$event])){
			throw new Exception("対応していないイベント".$event."が呼び出されました");
		}
		$events = $instance->_event[$event];
		$returns = array();
		foreach($events as $id => $e){
			$return = call_user_func($e[0],$arg);
			if($overloadReturn){
				if(!is_null($return)) $returns = $return;
			}else{
				$returns[$id] = $return;
			}

		}
		return $returns;
	}

	/**
	 * プラグインIDに限定してイベントを呼び出す
	 */
	static function callLocalPluginEventFunc(string $event, string $pluginId, array $arg=array()){
		$instance =& CMSPlugin::getInstance();

		if(!isset($instance->_event[$event])){
			throw new Exception("対応していないイベント".$event."が呼び出されました");
		}
		$events = $instance->_event[$event];
		return (isset($events[$pluginId])) ? array($pluginId => call_user_func($events[$pluginId][0],$arg)) : array();
	}


	static function getEvent(string $event){
		$instance =& CMSPlugin::getInstance();
		return $instance->_event[$event];
	}


	/*
	 static function addAction($key,$func){
		$instance =& CMSPlugin::getInstance();

		if(!isset($instance->_actions[$key])){
			$instance->_actions[$key] = array();
		}

		$instance->_actions[$key][] = $func;
	}

	static function fireAction($key,$args = array()){
		$instance =& CMSPlugin::getInstance();

		if(!isset($instance->_actions[$key])){
			return;
		}

		$functions = $instance->_actions[$key];

		foreach($functions as $_key => $function){
   			call_user_func_array($function,$args);
		}
	}*/

	/**
	 * プラグイン情報の保存
	 */
	static function savePluginConfig(string $id, $obj){
		return file_put_contents(self::getSiteDirectory().'/.plugin/'.$id.'.config', serialize($obj));
	}

	/**
	 * プラグイン情報の取得
	 * @return object
	 */
	static function loadPluginConfig(string $id){
		$fname = self::getSiteDirectory().'/.plugin/'.$id.'.config';
		if(file_exists($fname)){
			return unserialize(file_get_contents($fname));
		}else{
			return null;
		}

	}

	/**
	 * 自分自身にリダイレクト
	 */
	static function redirectConfigPage(array $array=array()){
		//ie対策
		$flashSession = SOY2ActionSession::getFlashSession();
		$flashSession->clearAttributes();
		$flashSession->resetFlashCounter();


		$flashSession->setAttribute("config_redirect",$array);

		$url = SOY2PageController::createRelativeLink($_SERVER['REQUEST_URI'], true);
		header("Location: {$url}#config");
		exit;
	}
}
