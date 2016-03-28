<?php
define('ICON_MANAGER_PLUGIN',"icon_manager");

$obj = CMSPlugin::loadPluginConfig(ICON_MANAGER_PLUGIN);
if(is_null($obj)){
	$obj = new IconManagerPlugin();
}

CMSPlugin::addPlugin(ICON_MANAGER_PLUGIN,array($obj,"init"));

class IconManagerPlugin{

	private $counter = 0;

	function init(){
		CMSPlugin::addPluginMenu(ICON_MANAGER_PLUGIN,array(
			"name"=>"アイコン管理プラグイン",
			"description"=>"ページアイコン、ブログアイコン、ラベルアイコンの管理を行います。",
			"author"=>"日本情報化農業研究所",
			"url"=>"http://www.n-i-agroinformatics.com/",
			"mail"=>"soycms@soycms.net",
			"version"=>"1.1"
		));
		CMSPlugin::addPluginConfigPage(ICON_MANAGER_PLUGIN,array(
			$this,"config_page"
		));

	}

	function getPluginDirectory(){
		return dirname(__FILE__);
	}

	function getImageDirectory(){
		return realpath(SOY2::RootDir()."../soycms/image")."/";
	}

	function getImageAddress(){
		return SOY2PageController::createRelativeLink("./image/");
	}

	function getLabelIcons(){
		$icons = scandir($this->getImageDirectory()."labelicon");
		$result = array();
		foreach($icons as $file){
			if($file[0] == '.') continue;
			$result[] = $file;
		}
		return $result;
	}

	function getPageIcons(){
		$icons = scandir($this->getImageDirectory()."pageicon");
		$pageicons = array();
		foreach($icons as $file){
			if($file[0] == '.') continue;

			if(preg_match('/^page_(.+)$/i',$file,$match)){
				$pageicons[] = $match[1];
			}else{
				continue;
			}
		}
		return $pageicons;
	}

	function getBlogIcons(){
		$icons = scandir($this->getImageDirectory()."pageicon");
		$blogicons = array();
		foreach($icons as $file){
			if($file[0] == '.') continue;

			if(preg_match('/^blog_(.+)$/i',$file,$match)){
				$blogicons[] = $match[1];
			}else{
				continue;
			}
		}
		return $blogicons;
	}

	function getDefaultIcons($type){
		switch($type){
			case "page":
				$icons = $this->getPageIcons();
				break;
			case "blog":
				$icons = $this->getBlogIcons();
				break;
			case "label":
				$icons = $this->getLabelIcons();
				break;
			default:
				return null;
		}

		foreach($icons as $icon){
			if(preg_match('/^default\.(png|jpg|jpeg|gif)$/i',$icon)){
				return $icon;
			}
		}
		return null;
	}

	function setDefaultIcon($type,$fname,$copy = false){
		switch($type){
			case "page":
				$prefix = $this->getImageDirectory()."/pageicon/page_";
				break;
			case "blog":
				$prefix = $this->getImageDirectory()."/pageicon/blog_";
				break;
			case "label":
				$prefix = $this->getImageDirectory()."/labelicon/";
				break;
			default:
				return false;
		}

		$full_fname = $prefix.$fname;

		if(!file_exists($full_fname)){
			return false;
		}

		$info = pathinfo($full_fname);

		$full_destname = $prefix."default.".strtolower($info["extension"]);

		if(!is_null($this->getDefaultIcons($type))){
			return false;
		}

		if(!copy($full_fname,$full_destname)){
			return false;
		}

		if($copy){
			return true;
		}else{
			if(!unlink($full_fname)){
				return false;
			}else{
				return true;
			}
		}
	}

	function config_page($arg = array()){

		if($_SERVER["REQUEST_METHOD"] == "POST"){
			//post時の動作
			if(isset($_POST["type"])){
				$fname = $_FILES["file"]["name"];

				if(!preg_match('/\.(jpg|jpeg|gif|png)$/i',$fname)){
					CMSPlugin::redirectConfigPage("ファイル形式が不正です。");
				}
				
				if(strpos($fname,'default')!==false){
					CMSPlugin::redirectConfigPage("<b>default</b>の文字列を含むファイルはアップロードできません。");
				}

				if($_POST["type"] == "label"){
					$dest_name = $this->getImageDirectory()."labelicon/".$fname;
				}else if($_POST["type"] == "page"){
					$dest_name = $this->getImageDirectory()."pageicon/page_".$fname;
				}else if($_POST["type"] == "blog"){
					$dest_name = $this->getImageDirectory()."pageicon/blog_".$fname;
				}else{
					CMSPlugin::redirectConfigPage("フォームデータが壊れています。再度お願いします。");
				}
				
				if(file_exists($dest_name)){
					CMSPlugin::redirectConfigPage("ファイルがすでに存在するためアップロードすることができません。");
				}
					
				if(@move_uploaded_file($_FILES["file"]["tmp_name"],$dest_name) === false){
					CMSPlugin::redirectConfigPage("ファイルの移動に失敗しました。");	
				}
				
				if(extension_loaded('gd')){
					//gdがあったらリサイズ
					list($width, $height) = getimagesize($dest_name);
					$mime = $_FILES["file"]["type"];
					
					
					if(preg_match('/(jpeg|jpg)/i',$mime)){
						//JPEG
						$src = imagecreatefromjpeg($dest_name);
						$type = "jpeg";
					}else if(preg_match('/gif/i',$mime)){
						//GIF
						$src = imagecreatefromgif($dest_name);
						$type = "gif";
					}else if(preg_match('/png/i',$mime)){
						//PNG
						$src = imagecreatefrompng($dest_name);
						$type = "png";
					}else{
						//do nothing
						CMSPlugin::redirectConfigPage("ファイル形式が不正です。");
					}
					
					
					$new_image = imagecreatetruecolor(64,64);
					imagecopyresampled($new_image,$src, 0, 0, 0, 0,64, 64, $width, $height);
					
					@unlink($dest_name);
					
					switch($type){
						case "jpeg":
							imagejpeg($new_image,$dest_name);
						break;
						case "gif":
							imagegif($new_image,$dest_name);	
						break;
						case "png":
							imagepng($new_image,$dest_name);
						break;
						default:
							//error
					}
				}else{
					
				}
				
				CMSPlugin::redirectConfigPage("ファイルのアップロードに成功しました。");

			}else if(isset($_POST["delete"])){
				$deletes = @$_POST["deletes"];

				if(is_null($deletes)){
					CMSPlugin::redirectConfigPage("削除するファイルがありません");
				}


				$action = array("label"=>array(),"page"=>array(),"blog"=>array());
				foreach($deletes as $file){
					if(preg_match('/^(label|page|blog)_(.*)$/i',$file,$match)){
						$type = $match[1];
						$fname = $match[2];

						$action[$type][] = $fname;
					}
				}

				if(count($this->getLabelIcons()) == count($action["label"])){
					CMSPlugin::redirectConfigPage("アイコンを全て削除することはできません。");
				}else{
					foreach($action["label"] as $fname){
						if(stripos($file, "default") !== false) continue;
						@unlink($this->getImageDirectory()."labelicon/".$fname);
					}
				}

				if(count($this->getPageIcons()) == count($action["page"])){
					CMSPlugin::redirectConfigPage("アイコンを全て削除することはできません。");
				}else{

					foreach($action["page"] as $fname){
						if(stripos($file, "default") !== false) continue;
						@unlink($this->getImageDirectory()."pageicon/page_".$fname);
					}
				}

				if(count($this->getBlogIcons()) == count($action["blog"])){
					CMSPlugin::redirectConfigPage("アイコンを全て削除することはできません。");
				}else{
					foreach($action["blog"] as $fname){
						if(stripos($file, "default") !== false) continue;
						@unlink($this->getImageDirectory()."pageicon/blog_".$fname);
					}
				}
				CMSPlugin::redirectConfigPage();
			}
		}



		$html = '<style type="text/css">.image_box{margin:15px;float:left;text-align:center;}</style>';
		if(is_string($arg) && strlen($arg) != 0){
			$html .= '<span style="color:red">'.$arg.'</span>';
		}
		$html .= '<form method="POST">';
		$html .= '<h4>ラベル一覧</h4>';

		$labelicons =$this->getLabelIcons();
		foreach($labelicons as $file){
			if($file[0] == '.') continue;

			$html .= '<div class="image_box">';
			$html .= '<img src="'.$this->getImageAddress()."labelicon/".$file.'" width="64px" height="64px"/><br />';
			if(stripos($file, "default") === false){
				$html .= '<input type="checkbox" name="deletes[]" value="label_'.$file.'">';
			}
			$html .= $file;
			$html .= '</div>';
		}


		$html .= '<br style="clear:both;"/>';
		$html .= '<h4>ページアイコン一覧</h4>';

		$pageicons = $this->getPageIcons();
		foreach($pageicons as $page){
			$html .= '<div class="image_box">';
			$html .= '<img src="'.$this->getImageAddress()."pageicon/page_".$page.'" width="64px" height="64px"/><br />';
			if(stripos($page, "default") === false){
				$html .= '<input type="checkbox" name="deletes[]" value="page_'.$page.'">';
			}
			$html .= $page;
			$html .= '</div>';
		}

		$html .= '<br style="clear:both;"/>';
		$html .= '<h4>ブログアイコン一覧</h4>';

		$blogicons = $this->getBlogIcons();
		foreach($blogicons as $page){
			$html .= '<div class="image_box">';
			$html .= '<img src="'.$this->getImageAddress()."pageicon/blog_".$page.'" width="64px" height="64px"/><br />';
			if(stripos($page, "default") === false){
				$html .= '<input type="checkbox" name="deletes[]" value="blog_'.$page.'">';
			}
			$html .= $page;
			$html .= '</div>';
		}

		$html .= '<br style="clear:both;"/>';
		$html .= '<h5>アイコンの削除</h5>';
		$html .= '<span>チェックを入れたものを削除する</span>';
		$html .= '<input type="submit" name="delete" onclick="return confirm(\'削除してもよろしいですか？\');" value="削除"/>';
		$html .= '</form>';
		$html .= file_get_contents($this->getPluginDirectory()."/form.html");
		
		if(extension_loaded("gd")){
			$html = str_replace('@@@@_GD_MESSAGE_@@@@',"自動でリサイズされます",$html);
		}else{
			$html = str_replace('@@@@_GD_MESSAGE_@@@@',"アップロードされた画像の大きさのまま使用されます。",$html);
		}

		return $html;

	}
}

?>