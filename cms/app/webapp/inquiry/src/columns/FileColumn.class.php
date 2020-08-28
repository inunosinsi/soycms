<?php

class FileColumn extends SOYInquiry_ColumnBase{

	private $extensions = "jpg,jpeg,gif,png";
	private $uploadsize = 500;	//KB
	private $resize_w;
	private $resize_h;

	/** @ToDo リサイズで縦長過ぎる画像をアップロードされたらどうする？ **/

	const KB_SIZE = 1024;

	/**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		$values = $this->getValue();
		if(!is_array($values)) $values = array("name" => "", "size" => "");

		$html = array();
		$isUploaded = (is_numeric($values["size"]) && (int)$values["size"] > 0);

		//アップロードされていた場合
		if($isUploaded){
			$html[] = htmlspecialchars($values["name"], ENT_QUOTES, "UTF-8") . "(".(int)($values["size"] / self::KB_SIZE)."KB)";
			$new_value = base64_encode(soy2_serialize($values));
			$html[] = '<input type="hidden" name="data['.$this->getColumnId().']" value="'.$new_value.'" />';
			$html[] = "<br>";

			//return implode("\n",$html);
		}

		$attributes = array();

		foreach($attr as $key => $value){
			$attributes[] = htmlspecialchars($key, ENT_QUOTES, "UTF-8") . "=\"".htmlspecialchars($value, ENT_QUOTES, "UTF-8")."\"";
		}

		$html[] = "<input type=\"file\" name=\"data[".$this->getColumnId()."]\" value=\"\"" . implode(" ",$attributes) . " />";

		return implode("\n",$html);
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html  = 'サイズ:<input type="text" name="Column[config][uploadsize]" value="'.htmlspecialchars($this->uploadsize,ENT_QUOTES).'" size="3">KB&nbsp;';
		$html .= '拡張子:<input type="text" name="Column[config][extensions]" value="'.htmlspecialchars($this->extensions,ENT_QUOTES).'"><br>';
		$html .= '画像のリサイズ: width:<input type="text" name="Column[config][resize_w]" value="'.htmlspecialchars($this->resize_w, ENT_QUOTES) . '" size="5">px ';
		$html .= 'height:<input type="text" name="Column[config][resize_h]" value="'.htmlspecialchars($this->resize_h, ENT_QUOTES) . '" size="5">px(アスペクト比は維持)';

		return $html;
	}

	/**
	 * 確認画面用
	 */
	function getView(){
		$values = $this->getValue();

		if(is_array($values) && isset($values["name"]) && isset($values["size"])){
			$html = "";
/**
			if(strpos($values["type"], "image") !== false){
				$imgPath = str_replace(SOY_INQUIRY_UPLOAD_DIR, "", $values["tmp_name"]);
				$siteId = trim(substr(_SITE_ROOT_, strrpos(_SITE_ROOT_, "/")), "/");

				//リサイズ
				$resizeW = (is_numeric($this->resize_w) && $this->resize_w > 150) ? 150 : $this->resize_w;

				$html .= "<img src=\"/" . $siteId . "/im.php?src=/" . $imgPath . "&width=" . $resizeW . "\"><br>";
			}
			**/

			$html .= htmlspecialchars($values["name"] . " (".(int)($values["size"] / self::KB_SIZE)."KB)", ENT_QUOTES, "UTF-8");
			return $html;
		}

		return "";
	}

	/**
	 * データ投入用
	 */
	function getContent(){
		$values = $this->getValue();
		if(is_array($values) && isset($values["name"]) && isset($values["size"]) && is_numeric($values["size"])){
			$html = array();
			$html[] = $values["name"] . " (".(int)($values["size"] / self::KB_SIZE)."KB)";
			return implode("\n",$html);
		}
		return "";
	}

	/**
	 * onSend
	 */
	function onSend($inquiry){

		$values = $this->getValue();

		if(is_array($values)){
			$tmp_name = $values["tmp_name"];

			$new_dir = SOY_INQUIRY_UPLOAD_DIR . "/" . $this->getFormId() . "/" . date("Ym") . "/";
			if(!file_exists($new_dir)) mkdir($new_dir,0777,true);

			$new_name = str_replace(SOY_INQUIRY_UPLOAD_TEMP_DIR, $new_dir, $tmp_name);
			if(strpos($new_name, "//")) $new_name = str_replace("//", "/", $new_name);

			//同名のファイルがある場合は名前を変更する
			if(file_exists($new_name)){
				//拡張子を抜いて、ファイル名を少し変更する
				$ext = substr($new_name, strrpos($new_name, "."));
				$new_name = substr($new_name, 0, strrpos($new_name, "."));
				$new_name .= rand(100, 999) . $ext;
			}

			if(rename($tmp_name,$new_name)){
				$values["filepath"] = str_replace("\\","/",realpath($new_name));
				$values["filepath"] = str_replace(SOY_INQUIRY_UPLOAD_ROOT_DIR,"",$values["filepath"]);
				$this->setValue($value);

				//コメントに追加する
				$content = $this->getLabel() . ":";
				$content .= '<a href="'.htmlspecialchars($values["filepath"],ENT_QUOTES,"UTF-8").'">'.htmlspecialchars($values["name"],ENT_QUOTES,"UTF-8").'</a>';

				$pathinfo = pathinfo($values["filepath"]);
				$extensions = self::_shapeExtensions();
				if(count($extensions)){
					$res = false;
					foreach($extensions as $ext){	//拡張子を大文字小文字関係なく調べる
						if(!$res && is_numeric(stripos($pathinfo["extension"], $ext))) $res = true;
					}

					if($res) $content .= '<br/><img src="'.htmlspecialchars($values["filepath"],ENT_QUOTES,"UTF-8").'"/>';
				}

				$commentDAO = SOY2DAOFactory::create("SOYInquiry_CommentDAO");
				$comment = new SOYInquiry_Comment();

				$comment->setInquiryId($inquiry->getId());
				$comment->setTitle($this->getLabel());
				$comment->setAuthor("-");
				$comment->setContent($content);

				$commentDAO->insert($comment);

			}
		}

	}

	/**
	 * 値が正常かどうかチェック
	 */
	function validate(){

		$id = $this->getColumnId();
		if(isset($_FILES["data"]["size"][$id]) && $_FILES["data"]["size"][$id] > 0){	//アップロードした
			//ここでは何もしない

		}else{	//アップロードしてない
			$value = $this->getValue();
			$tmp = (isset($value) && strlen($value)) ? soy2_unserialize(base64_decode($value)) : array();

			//二回目のPOST
			if(is_array($tmp) && isset($tmp["tmp_name"])
				&& file_exists($tmp["tmp_name"]) && is_readable($tmp["tmp_name"])){

				$this->setValue($tmp);
				return;
			}
		}

		//チェック
		$name = @$_FILES["data"]["name"][$id];
		$type = @$_FILES["data"]["type"][$id];
		$tmp_name = @$_FILES["data"]["tmp_name"][$id];
		$error = @$_FILES["data"]["error"][$id];
		$size = @$_FILES["data"]["size"][$id];

		//必須チェック
		if($this->getIsRequire() && strlen($name)<1){
			$this->setErrorMessage($this->getLabel()."を入力してください。");
			return false;
		}

		//アップロードしていない場合は終了
		if(strlen($name)<1)return;

		//拡張子チェック
		$pathinfo = pathinfo($name);
		$extensions = self::_shapeExtensions();
		if(count($extensions)){
			$res = false;
			foreach($extensions as $ext){	//大文字小文字関係なく拡張子を確かめる
				if(!$res && is_numeric(stripos($pathinfo["extension"], $ext))) $res = true;
			}

			if(!$res) {
				$this->setErrorMessage($this->getLabel()."の形式が不正です。");
				return false;
			}
		}


		//ファイルサイズチェック
		if(($this->uploadsize * self::KB_SIZE)< $size){
			$this->setErrorMessage($this->getLabel()."が大きすぎます。");
			return false;
		}

		//一時的にアップロードする
		if(!file_exists(SOY_INQUIRY_UPLOAD_TEMP_DIR)){
			mkdir(SOY_INQUIRY_UPLOAD_TEMP_DIR);
		}
		$path_to = SOY_INQUIRY_UPLOAD_TEMP_DIR . md5($name . time()) . "." . $pathinfo["extension"];
		for(;;){	//複数フォームを設置して、同名のファイルを送信する際に以前アップロードしたものが上書きされないようにファイル名を変更する
			if(!file_exists($path_to)) break;
			$path_to = SOY_INQUIRY_UPLOAD_TEMP_DIR . md5($name . rand(1, 10) . time()) . "." . $pathinfo["extension"];
		}
		$result = move_uploaded_file($tmp_name,$path_to);

		//一時アップロードに失敗した場合
		if(!$result){
			$this->setErrorMessage("アップロードに失敗しました。");
			return false;
		}

		//$path_toにあるファイルをリサイズする @ToDo 画像ファイルであるか？を調べた後に実行
		if(strpos($type, "image") !== false && is_numeric($this->resize_w) && $this->resize_w > 0){
			$imgInfo = getimagesize($path_to);
			if(is_array($imgInfo) && count($imgInfo) >= 7){	//idx:0がwidthでidx:1がheight
				if($imgInfo[0] >= $imgInfo[1]){	//横長画像もしくは正方形
					$resizeW = $this->resize_w;
					$resizeH = (int)($this->resize_w * $imgInfo[1] / $imgInfo[0]);
				}else{ //縦長画像
					$resizeW = (int)($this->resize_h * $imgInfo[0] / $imgInfo[1]);
					$resizeH = $this->resize_h;
				}
				soy2_resizeimage($path_to, $path_to, $resizeW, $resizeH);
				$size = filesize($path_to);
			}
		}


		//もしアップロードされているのであれば値を保存する
		$this->setValue(array(
			"name" => $name,
			"type" => $type,
			"tmp_name" => $path_to,
			"error" => $error,
			"size" => $size
		));

		$new_value = base64_encode(serialize($this->getValue()));
		$_POST["data"][$this->getColumnId()] = $new_value;
	}

	private function _shapeExtensions(){
		$array = explode(",", $this->extensions);
		if(!count($array)) return array();
		$exts = array();

		foreach($array as $ext){
			$ext = trim($ext);
			if(!strlen($ext)) continue;
			$exts[] = $ext;
		}
		return $exts;
	}

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);

		$this->uploadsize  = (isset($config["uploadsize"]) && is_numeric($config["uploadsize"])) ? (int)$config["uploadsize"] : 500;
		$this->extensions = (isset($config["extensions"]) && strlen($config["extensions"])) ? $config["extensions"] : "jpg,gif,png";
		$this->resize_w = (isset($config["resize_w"]) && is_numeric($config["resize_w"])) ? (int)$config["resize_w"] : null;
		$this->resize_h = (isset($config["resize_h"]) && is_numeric($config["resize_h"])) ? (int)$config["resize_h"] : null;
	}

	function getConfigure(){
		$config = parent::getConfigure();
		$config["uploadsize"] = $this->uploadsize;
		$config["extensions"] = $this->extensions;
		$config["resize_w"] = (isset($this->resize_w) && is_numeric($this->resize_w)) ? (int)$this->resize_w : null;
		$config["resize_h"] = (isset($this->resize_h) && is_numeric($this->resize_h)) ? (int)$this->resize_h : null;

		//片方しか値を登録していない場合はwidthとheightで補完し合う。
		if(is_null($config["resize_w"]) && is_numeric($config["resize_h"])) $config["resize_w"] = $config["resize_h"];
		if(is_null($config["resize_h"]) && is_numeric($config["resize_w"])) $config["resize_h"] = $config["resize_w"];
		return $config;
	}

	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE  => "連携しない",
		);
	}
}
