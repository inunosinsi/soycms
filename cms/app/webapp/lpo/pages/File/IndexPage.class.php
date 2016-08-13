<?php

class IndexPage extends WebPage{

	private $dir = null;
	
	function doPost(){
		if(isset($_FILES["files"])){
			$name = $_FILES["files"]["name"];
			$tmpname = $_FILES["files"]["tmp_name"];
			move_uploaded_file(
				$tmpname,
				$this->dir . $name
			);
		}
		
		if(isset($_POST["dirname"])){
			file_put_contents("dirname.txt",var_export($_POST["dirname"],true),FILE_APPEND);
			$name = str_replace(".","",$_POST["dirname"]);
			if($name){
				mkdir($this->dir . $name);
			}
		}
			
		CMSApplication::jump("File?path=". str_replace(SOY_LPO_IMAGE_UPLOAD_DIR,"",$this->dir));
	}

    function __construct() {
	
		$this->dir = soy2_realpath(SOY_LPO_IMAGE_UPLOAD_DIR);
		$parentPath = null;
		if(isset($_GET["path"])){
			$_GET["path"] = str_replace(".","",$_GET["path"]);
			$this->dir = soy2_realpath($this->dir . $_GET["path"]);
			$parentPath = str_replace(SOY_LPO_IMAGE_UPLOAD_DIR,"",soy2_realpath(dirname($this->dir)));
		}
	
    	WebPage::__construct();
		
		$this->buildList();
		
		$this->createAdd("move_up_link","HTMLLink",array(
			"link" => ($parentPath) ? SOY2PageController::createLink("lpo.File") . "?path=" . $parentPath
			: SOY2PageController::createLink("lpo.File")
		));
		
		$this->createAdd("directory_path","HTMLLabel",array(
			"text" => str_replace(SOY_LPO_IMAGE_UPLOAD_DIR,"/",$this->dir)
		));
		$this->createAdd("makedir_link","HTMLLink",array(
			"link" => "javascript:void(0);",
			"onclick" => "var val=prompt('ディレクトリ名を入力してください');".
				'if(val){$(\'#dirname_input\').val(val);$(\'#mkdir_form\').trigger(\'submit\');}'
		));
		
		$this->createAdd("upload_form","HTMLForm",array(
			"attr:id" => "upload_form",
			"attr:enctype" => "multipart/form-data"
		));
		$this->createAdd("mkdir_form","HTMLForm",array(
			"attr:id" => "mkdir_form"
		));
		
    }
    
	
	function buildList(){
		$scan = soy2_scandir($this->dir);
		$dirs = array();
		$files = array();
		foreach($scan as $file){
			if(is_dir($this->dir . $file)){
				$dirs[] = $this->dir . $file;
				continue;
			}
			
			if(strpos($file,".php")!==false)continue;
			
			$files[] = $this->dir . $file;
		}
		$scan = array_merge($dirs,$files);
		
		$this->createAdd("file_list","FileList",array(
			"list" => $scan,
			"dir" => $this->dir
		));
	}
}

class FileList extends HTMLList{
	
	private $dir;
	private $dirLink;
	private $detailLink;
	
	function init(){
		$this->dirLink = SOY2PageController::createLink("lpo.File");
		$this->detailLink = SOY2PageController::createLink("lpo.File.Detail");
	}
	
	function setDir($dir){
		$this->dir = $dir;
	}
	
	function populateItem($entity){
		if(!$this->dirLink)$this->init();
		
		$path = str_replace(SOY_LPO_IMAGE_UPLOAD_DIR,"",$entity);
		$this->createAdd("file_detail_link","HTMLLink",array(
			"link" => (is_dir($entity)) ? 
				 $this->dirLink . "?path=" . $path
				:$this->detailLink . "?path=" . $path
		));
		
		$this->createAdd("file_name","HTMLLabel",array(
			"text" => (is_dir($entity)) ? "/" . basename($entity) : basename($entity)
		));
		
		$this->createAdd("file_type","HTMLLabel",array(
			"text" => $this->getType($entity)
		));
		
		$size = filesize($entity);
		$size_text = $size;
		if($size >= 1000)$size_text = number_format($size / 1000,2) . "KB";
		if($size >= 1000 * 1000)$size_text = number_format($size / 1000 / 1000,2) . "MB";
		$this->createAdd("file_size","HTMLLabel",array(
			"text" => (is_dir($entity)) ? "" : $size_text
		));
		
		
	}
	
	function getType($path){
		if(is_dir($path))return "ディレクトリ";
		
		$ext = pathinfo($path);
		$ext = strtolower($ext["extension"]);
		return $ext;
	}
	
}
?>