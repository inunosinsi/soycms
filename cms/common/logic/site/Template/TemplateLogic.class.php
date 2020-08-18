<?php
SOY2::import("domain.cms.Template");
class TemplateLogic extends SOY2LogicBase{

	const SITE_ROOT_MARKER = "@@SITE_ROOT@@";

	function get(){
		if(!$this->isSimpleXmlEnabled()) return array();
		$dao = SOY2DAOFactory::create("cms.TemplateDAO");
		return $dao->get();
	}

	function getByFileName($filename){
		if(!$this->isSimpleXmlEnabled()) return null;
		$dao = SOY2DAOFactory::create("cms.TemplateDAO");
		return $dao->getByFileName($filename);
	}

	function getByPageType($pageType){
		if(!$this->isSimpleXmlEnabled()) return null;
		$dao = SOY2DAOFactory::create("cms.TemplateDAO");
		return $dao->get($pageType);
	}

	function getById($id){
		if(!$this->isSimpleXmlEnabled()) return null;
		$dao = SOY2DAOFactory::create("cms.TemplateDAO");
		return $dao->getById($id);
	}

	function insert(Template $data){
		$dao = SOY2DAOFactory::create("cms.TemplateDAO");
		return $dao->insert($data);
	}

	function update(Template $data){
		$dao = SOY2DAOFactory::create("cms.TemplateDAO");
		return $dao->update($data);
	}

	function delete($filename){
		$dao = SOY2DAOFactory::create("cms.TemplateDAO");
		return $dao->delete($filename);
	}

	function deleteById($id){
		$dao = SOY2DAOFactory::create("cms.TemplateDAO");
		return $dao->deleteById($id);
	}

	/**
	 * simplexml_load_fileが利用可能かどうか
	 * @return boolean
	 */
	private function isSimpleXmlEnabled(){
		return function_exists("simplexml_load_file");
	}

	/**
	 * テンプレートのインストールを行う
	 *
	 * @param id
	 * @param installFileList インストールするファイル名
	 */
	function installTemplate($id,$installFileList){

		$template = $this->getById($id);
		if($template->isActive())throw new Exception("Already installed");

		$className = CMSUtil::checkZipEnable(true);

    	if($className === false){
    		throw new Exception("Can not create Zip_Archive");
    	}

    	//Pear版
    	if($className == "Archive_Zip"){

    		$filepath = $template->getArchieveFileName();

    		$tempDir = ServerInfoUtil::sys_get_writable_temp_dir() . "/" . md5($filepath);
    		@mkdir($tempDir);
    		chdir($tempDir);

    		$zip = new Archive_Zip($filepath);
    		$res = $zip->extract();
    		if(!$res){
    			rmdir($tempDir);
    			return false;
    		}

    		$zip = null;

    		$siteRoot = UserInfoUtil::getSiteDirectory();
	    	$templateDir = UserInfoUtil::getSiteDirectory() . ".template/" . $template->getId() ."/";
	    	if(!file_exists($templateDir)){
	    		mkdir($templateDir);
	    	}

	    	SOY2::import("util.CMSFileManager");

	    	$fileList = $template->getFileList();
	    	$templateList = $template->getTemplate();

	    	$files = scandir($tempDir);

	    	foreach($files as $detect){

	    		if($detect == '.' OR $detect == '..')continue;

	    		if(isset($fileList[$detect]) && in_array($detect,$installFileList)){
	    			if($fileList[$detect]["path"][0] == "/"){
	    				$path = $siteRoot . substr($fileList[$detect]["path"],1);
	    			}else{
	    				$path = $siteRoot . $fileList[$detect]["path"];
	    			}
	    			//ディレクトリの作成
					$tmpPath = dirname($path);
					$counter = 0;

					while(!file_exists($tmpPath)){
	    				$tmpPath = dirname($tmpPath);
	    				$counter++;
	    			}

	    			while($counter > 0){
	    				$tmpPath = $path;
	    				for($i=0;$i<$counter;++$i){
	    					$tmpPath = dirname($tmpPath);
	    				}
	    				mkdir($tmpPath);

	    				CMSFileManager::add($tmpPath);
	    				$counter--;
	    			}

	    			if(@rename($tempDir."/".$detect,$path)){
	    				CMSFileManager::add($path);
	    			}

	    			continue;
	    		}

	    		if(isset($templateList[$detect])){

	    			$path = $templateDir . $detect;
	    			@rename($tempDir."/".$detect,$path);

	    			$this->replaceSiteRootMarker($path);

	    			continue;
	    		}

	    		unlink($tempDir."/".$detect);

	    	}

	    	@rmdir(realpath($tempDir));

    	//PHP版
    	}else{

			//ファイルの設置
	    	$zip = zip_open($template->getArchieveFileName());

	    	if($zip === false){
	    		return false;
	    	}

	    	$siteRoot = UserInfoUtil::getSiteDirectory();
	    	$templateDir = UserInfoUtil::getSiteDirectory() . ".template/" . $template->getId() ."/";
	    	if(!file_exists($templateDir)){
	    		mkdir($templateDir);
	    	}

	    	SOY2::import("util.CMSFileManager");

	    	$fileList = $template->getFileList();
	    	$templateList = $template->getTemplate();


	    	while(true){
	    		$entry = zip_read($zip);
	    		if(!$entry)break;

	    		$name = zip_entry_name($entry);

	    		$detect = $name;

	    		if(isset($fileList[$detect]) && in_array($detect,$installFileList)){
	    			if($fileList[$detect]["path"][0] == "/"){
	    				$path = $siteRoot . substr($fileList[$detect]["path"],1);
	    			}else{
	    				$path = $siteRoot . $fileList[$detect]["path"];
	    			}
	    			//ディレクトリの作成
					$tmpPath = dirname($path);
					$counter = 0;

					while(!file_exists($tmpPath)){
	    				$tmpPath = dirname($tmpPath);
	    				$counter++;
	    			}

	    			while($counter > 0){
	    				$tmpPath = $path;
	    				for($i=0;$i<$counter;++$i){
	    					$tmpPath = dirname($tmpPath);
	    				}
	    				mkdir($tmpPath);

	    				CMSFileManager::add($tmpPath);
	    				$counter--;
	    			}

	    			$buf = zip_entry_read($entry, zip_entry_filesize($entry));
	    			file_put_contents($path,$buf);
	    			CMSFileManager::add($path);
	    			continue;
	    		}

	    		if(isset($templateList[$detect])){

	    			$path = $templateDir . $name;
	    			$buf = zip_entry_read($entry, zip_entry_filesize($entry));


	    			file_put_contents($path,$buf);

	    			$this->replaceSiteRootMarker($path);
	    			continue;
	    		}
	    	}

	    	zip_close($zip);
    	}

    	//マニフェストファイルを有効に設定
    	$xmlPath = UserInfoUtil::getSiteDirectory() .".template/" . $id . ".xml";
    	$doc = new DOMDocument();
		$doc->load($xmlPath);

		$root = $doc->firstChild;

		$active = $doc->createElement("active");
		$root->appendChild($active);
		$active->appendChild($doc->createTextNode("1"));

		file_put_contents($xmlPath,$doc->saveXml());
	}

	/**
	 * テンプレートのアンストールを行う
	 *
	 * @param id
	 * @param installFileList アンインストールするファイル名
	 * @mock
	 */
	function uninstallTemplate($id,$uninstallFileList){
		$template = $this->getById($id);
		if(!$template->isActive())throw new Exception("Not installed");

		if(!is_array($uninstallFileList)) $uninstallFileList = array();

		//ファイルの設置

    	$siteRoot = UserInfoUtil::getSiteDirectory();
    	$templateDir = UserInfoUtil::getSiteDirectory() . ".template/" . $template->getId() ."/";


    	SOY2::import("util.CMSFileManager");

    	$fileList = $template->getFileList();
    	$templateList = $template->getTemplate();

    	foreach($uninstallFileList as $key => $uninstFile){
    		$path = $siteRoot . (($fileList[$uninstFile]["path"][0] == "/") ? substr($fileList[$uninstFile]["path"],1): $fileList[$uninstFile]["path"]);

    		if(file_exists($path)){
    			unlink($path);
    		}

    	}

    	foreach($templateList as $key => $template){
    		$path = $templateDir . $template["id"];
    		if(file_exists($path)){
    			unlink($path);
    		}
    	}

    	if(file_exists($templateDir)){
    		rmdir($templateDir);
    	}


    	$xmlPath = UserInfoUtil::getSiteDirectory() .".template/" . $id . ".xml";
    	$doc = new DOMDocument();
		$doc->load($xmlPath);

		$root = $doc->firstChild;

		$activs = $root->getElementsByTagName("active");
		//1個しかないよね！　きっと
		foreach($activs as $active){
			$root->removeChild($active);
		}
		file_put_contents($xmlPath,$doc->saveXml());

	}

	/**
	 * テンプレートのアップロードを行う
	 */
	function uploadTemplate($file,$filepath = null){

		if($filepath){
			$res = $this->checkValidTemplatePack($filepath);
		}else{
			$res = $this->checkValidTemplatePack($file["tmp_name"]);
		}

		if($res === false){
			return false;
		}

		list($xml,$contents) = $res;

    	//有効なテンプレートだった場合
    	$id = (string)$xml->id;
    	$type = (string)$xml->type;
    	$xml = null;

    	$newFileName = $type . "_" . $id . "_" . "manifest.xml";

    	file_put_contents(UserInfoUtil::getSiteDirectory() .".template/" . $newFileName,$contents);

    	if($filepath){
			$result = copy($filepath,UserInfoUtil::getSiteDirectory() .".template/" . $id . ".zip");
		}else{
    		if($id . ".zip" != $file["name"]){
    			$result = false;
    		}else{
    			$result = move_uploaded_file($file["tmp_name"],UserInfoUtil::getSiteDirectory() .".template/" . $id . ".zip");
    		}
    	}

		if(!$result){
			return false;
		}

		return true;

	}

	/**
     * Zipファイルが有効なテンプレートかどうかを判断
     */
    function checkValidTemplatePack($filepath){

    	$className = CMSUtil::checkZipEnable(true);

    	if($className === false){
    		throw new Exception("Can not create Zip_Archive");
    	}

    	//Pear版
    	if($className == "Archive_Zip"){

    		$tempDir = ServerInfoUtil::sys_get_writable_temp_dir() . "/" . md5($filepath);
    		mkdir($tempDir);

    		chdir($tempDir);

    		$zip = new Archive_Zip($filepath);

    		//by_name は実装してないらしいが。
    		$res = $zip->extract(array(
    			"by_name" => "manifest.xml"
    		));

    		$result = true;

    		if(file_exists($tempDir ."/manifest.xml")){
    			$contents = file_get_contents($tempDir ."/manifest.xml");

	    		if($contents === false){
	    			$result = false;
	    		}
    		}

    		//ごみ掃除
    		$files = scandir($tempDir);
    		if($tempDir[strlen($tempDir)-1] != "/")$tempDir .= "/";
    		foreach($files as $file){
    			if($file == "." || $file == "..")continue;
    			unlink($tempDir . $file);
    		}
    		@rmdir(realpath($tempDir));

    		if($result !== true)return false;

    	//PHP版
    	}else{

	    	$zip = zip_open($filepath);
	    	if(!$zip){
	    		return false;
	    	}

	    	$manifest = null;

	    	while(true){
	    		$entry = zip_read($zip);
	    		if(!$entry)break;

	    		$name = zip_entry_name($entry);

	    		if($name == "manifest.xml"){
	    			$manifest = $entry;
	    			break;
	    		}
	    	}

	    	if(!$manifest){
	    		return false;
	    	}

	    	$contents = zip_entry_read($manifest,zip_entry_filesize($manifest));

	    	zip_close($zip);
    	}

    	$xml = @simplexml_load_string($contents);

    	if($xml === false){
    		return false;
    	}

    	return array($xml,$contents);
    }

    /**
     * Zip圧縮を行う
     */
    function createTemplatePack($id,$dir){

    	$targetFile = dirname($dir) . "/" . $id . ".zip";

    	@unlink($targetFile);

    	$className = CMSUtil::checkZipEnable();

    	if($className === false){
    		throw new Exception("Can not create Zip_Archive");
    	}

    	if($className == "Archive_Zip"){

    		chdir($dir);
    		$zip = new Archive_Zip($targetFile);


    	}else{

	    	$zip = new ZipArchive;
			$res = $zip->open($targetFile, ZipArchive::CREATE);

	    	if($res !== true){
	    		throw new Exception("failed");
	    	}
    	}

    	$files = scandir($dir);
    	$fileList = array();
    	foreach($files as $file){
    		 if($file == '.' or $file == '..')continue;

    		if($className == "Archive_Zip"){
    			$zip->add("./".$file);
    		}else{
    			$realpath = realpath($dir."/".$file);
    			$zip->addFile($realpath,$file);
    		}
    	}

    	if($className == "Archive_Zip"){
    		$zip = null;
    	}else{
    		$zip->close();
    	}

    	return realpath($targetFile);
    }

    /**
     * テンプレートに書かれているURLをテンプレートパックに使用可能なURLにリプレースする
     */
    function replaceURL($siteUrl,$templateFileList,$fileReplaceList){

    	foreach($templateFileList as $filepath){

    		$content = file_get_contents($filepath);

    		foreach($fileReplaceList as $url => $replace){

    			$content = str_replace($siteUrl,"",$content);
    			$content = str_replace($url,self::SITE_ROOT_MARKER.$replace, $content);

    		}


    		file_put_contents($filepath,$content);
    	}
    }

    /**
     * SITE_ROOT_MARKERをリプレースする
     */
    function replaceSiteRootMarker($path){

    	$content = file_get_contents($path);

    	if(defined("SOYCMS_ASP_MODE")){
    		$content = str_replace(self::SITE_ROOT_MARKER,"",$content);
    	}else{
    		$content = str_replace(self::SITE_ROOT_MARKER,"/".UserInfoUtil::getSite()->getSiteId(),$content);
    	}
    	file_put_contents($path,$content);

    }
}


?>
