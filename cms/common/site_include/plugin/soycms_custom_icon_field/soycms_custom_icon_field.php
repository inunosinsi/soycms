<?php
CustomIconFieldPlugin::registerPlugin();

class CustomIconFieldPlugin{
	
	var $label = "アイコンフィールド";
	var $iconDirecotry = "icons";
	var $customFields = array();
	
	function getId(){
		return SOYCMS_CUSTOM_ICON_FIELD_PLUGIN;
	}
	
	function init(){
		CMSPlugin::addPluginMenu($this->getId(),array(
			"name"=>"アイコンフィールド追加プラグイン",
			"description"=>"エントリー編集画面にアイコン編集フィールドを追加します。",
			"author"=>"日本情報化研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.2"
		));	
		CMSPlugin::addPluginConfigPage($this->getId(), array(
			$this, "config_page"
		));
		
		if(CMSPlugin::activeCheck($this->getId())){
			
			CMSPlugin::setEvent('onEntryUpdate', $this->getId(), array($this, "onEntryUpdate"));
			CMSPlugin::setEvent('onEntryCreate', $this->getId(), array($this, "onEntryUpdate"));
			
			CMSPlugin::addCustomFieldFunction($this->getId(), "Entry.Detail", array($this, "onCallCustomField"));
			CMSPlugin::addCustomFieldFunction($this->getId(), "Blog.Entry", array($this, "onCallCustomField_inBlog"));
			
			CMSPlugin::setEvent('onEntryOutput', $this->getId(), array($this, "display"));
		}else{
			CMSPlugin::setEvent('onActive', $this->getId(), array($this, "createTable"));
		}
	}
	
	function display($arg){
		
		$entryId = $arg["entryId"];
		$htmlObj = $arg["SOY2HTMLObject"];
		
		$dao = new SOY2DAO();
		
		try{
			$result = $dao->executeQuery("select custom_icon_field from Entry where id = :id", array(":id" => $entryId));
		}catch(Exception $e){
			$result = array();
		}
		
		$icons = array();
		if(isset($result[0]["custom_icon_field"])){
			$icons_array = explode(",", $result[0]["custom_icon_field"]);
			
			foreach($icons_array as $str){
				if(strlen($str)){
					$icons[] = '<img src="' . htmlspecialchars(/*SOY2PageController::createLink("")*/substr($str, 0), ENT_QUOTES) . '" />';	
				}
			}
		}
		
		$htmlObj->addLabel("custom_icon_field", array(
			"html" => implode("\n", $icons),
			"soy2prefix" => "cms"
		));
	}
	
	function config_page($message = array()){
		
		//アップロードを押したとき
		if($_SERVER["REQUEST_METHOD"] == "POST"){
			
			//$_FILES["type"]が存在する場合は何らかのファイルがアップロードされたことになる
			if(strlen($_FILES["file"]["type"]) > 0){
				
				//ファイルの拡張子をチェックする
				if(!preg_match('/(jpg|jpeg|gif|png)$/', $_FILES["file"]["name"])){
					CMSPlugin::redirectConfigPage("ファイル形式が不正です。");
				}
				
				$fname = $_FILES["file"]["name"];
				
				$dest_name = $this->getIconDirectory() . "/" . $fname;

				//iconsディレクトリの中にすでにファイルがないかチェックする				
				if(file_exists($dest_name)){
					CMSPlugin::redirectConfigPage("ファイルがすでに存在するためアップロードすることができません。");
				}
				
				//ファイルの移動が失敗していないかどうかをチェック
				if(@move_uploaded_file($_FILES["file"]["tmp_name"], $dest_name) === false){
					CMSPlugin::redirectConfigPage("ファイルの移動に失敗しました。");	
				}
				
				CMSPlugin::redirectConfigPage("ファイルのアップロードに成功しました。");
				
			}else{
				//
			}
		}
			
		//削除を押したとき
		if(isset($_POST["delete"])){
			$deletes = (isset($_POST["deletes"])) ? $_POST["deletes"] : null;
			
			//一応確認
			if(is_null($deletes)){
				CMSPlugin::redirectConfigPage("削除するファイルがありません");
			}
			
			//チェックしたアイコンを削除する
			foreach($deletes as $fname){
				@unlink($this->getIconDirectory() . "/" . $fname);
			}
			
			CMSPlugin::redirectConfigPage();
		}
		
		ob_start();
		include_once(dirname(__FILE__) . "/form.php");
		$html = ob_get_contents(); 
		ob_end_clean();	
		
		return $html;
	}
	
	function onEntryUpdate($arg){
		$entry = $arg["entry"];		
		if(isset($_POST["custom_icon_field"]) && strlen($_POST["custom_icon_field"])){
			$fields = implode(",", array_unique(explode(",", $_POST["custom_icon_field"])));
			
			$dao = new SOY2DAO();
			try{
				$dao->executeQuery("update Entry set custom_icon_field = :custom where Entry.id = :id",
					array(
						":id" => $entry->getId(),
						":custom" => $fields
				));
			}catch(Exception $e){
				return false;	
			}
			
			return true;
		}
		
		return false;
	}
	
	function onCallCustomField(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[0])) ? $arg[0] : null;
		
		return self::getForm($entryId);
	}
	
	function onCallCustomField_inBlog(){
		$arg = SOY2PageController::getArguments();
		$entryId = (isset($arg[1])) ? $arg[1] : null;
		
		return self::getForm($entryId);
	}
	
	private function getForm($entryId){
		$dao = new SOY2DAO();
		
		try{
			$result = $dao->executeQuery("select custom_icon_field from Entry where id = :id", array(":id" => $entryId));
		}catch(Exception $e){
			$result = array();
		}
		
		$icons = (isset($result[0]["custom_icon_field"])) ? $result[0]["custom_icon_field"] : null;
		
		$files = @scandir(UserInfoUtil::getSiteDirectory() . $this->iconDirecotry);
		if(!$files) $files = array();
		
		$html = array();
		
		$html[] = '<div class="section">';
		$html[] = '<p class="sub">' . htmlspecialchars($this->label) . '</p>';
		
		$icons_array = explode(",", $icons);
		$html []= '<div id="custom_icon_field_current">';
		foreach($icons_array as $str){
			$str = str_replace(CMSUtil::getSiteUrl(), "", $str);
			if(strlen($str)){
				$tmpStr = str_replace($this->iconDirecotry, "", $str);
				$html[] = '<img id="custom_icon_field_hidden_' . str_replace(".", "_", substr($tmpStr, strrpos("/", $tmpStr) + 1)) . '" src="' . htmlspecialchars(UserInfoUtil::getSiteURL() . $str, ENT_QUOTES) . '" />';	
			}
		}
		$html[] = '</div>';
		
		$html[] = '<input type="hidden" name="custom_icon_field" id="custom_icon_field_hidden" value="' . htmlspecialchars($icons, ENT_QUOTES) . '">';
		$html[] = '<div id="custom_icon_field_icon_list" style="">';
		foreach($files as $file){
			if($file[0] == ".") continue;
			$html[] = '<img onclick="add_custom_icon_field(this.src);" src="' . htmlspecialchars(UserInfoUtil::getSiteURL() . $this->iconDirecotry . "/" . $file, ENT_QUOTES) . '" />';
		}
		$html[] = '</div>';		
		$html[] = '</div>';
		
		$script = file_get_contents(dirname(__FILE__) . "/soycms_custom_icon_field.js");
		$script = str_replace("@@SITE_URL@@", UserInfoUtil::getSiteURL(), $script);
		$html[] = '<script type="text/javascript">' . $script . '</script>';
		
		return implode("\n", $html);
	}

	/**
	 * アイコンディレクトリを取得
	 */
	function getIconDirectory(){
		return UserInfoUtil::getSiteDirectory() . "icons";
	}
	
	/**
	 * アイコンディレクトリを設定
	 */
	function setIconDirectory($dir){
		//先頭は必ず「/」
		if($dir[0] != "/") $dir = "/" . $dir;
		
		//末尾が/なら除く
		if($dir[strlen($dir) - 1] == "/") $dir = substr($dir, 0, strlen($dir) - 2);		
		$this->iconDirectory = $dir;
	}
	
	function createTable(){
		$dao = new SOY2DAO();
		try{
			$dao->executeQuery("alter table Entry add custom_icon_field text", array());
		}catch(Exception $e){
			//
		}
		
		//アイコン用のディレクトリを作成
		$getDir = UserInfoUtil::getSiteDirectory(). "icons";
		mkdir($getDir);
		
		return;
	}
	
	public static function registerPlugin(){
		define('SOYCMS_CUSTOM_ICON_FIELD_PLUGIN', "SOYCMS_CUSTOM_ICON_FIELD_PLUGIN");
		
		$obj = CMSPlugin::loadPluginConfig(SOYCMS_CUSTOM_ICON_FIELD_PLUGIN);
		if(is_null($obj)){
			$obj = new CustomIconFieldPlugin();
		}
		
		CMSPlugin::addPlugin(SOYCMS_CUSTOM_ICON_FIELD_PLUGIN, array($obj, "init"));
	}
}
?>