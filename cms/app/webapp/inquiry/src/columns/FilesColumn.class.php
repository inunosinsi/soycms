<?php

class FilesColumn extends SOYInquiry_ColumnBase{

	private $extensions = "jpg,jpeg,gif,png";
	private $uploadsize = 500;	//KB
	private $resize_w;
	private $resize_h;
	private $upload_limit = 3;

	/** @ToDo リサイズで縦長過ぎる画像をアップロードされたらどうする？ **/

	const KB_SIZE = 1024;

	/**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		$html = array();

		$values = self::getValues();
		if(count($values)){
			foreach($values as $v){
				if(isset($v["tmp_name"]) && strlen($v["tmp_name"])){
					$html[] = htmlspecialchars($v["name"], ENT_QUOTES, "UTF-8") . "(".(int)($v["size"] / self::KB_SIZE)."KB)";
					$html[] = "<br>";
				}
			}

			$html[] = '<input type="hidden" name="data['.$this->getColumnId().']" value="'.base64_encode(soy2_serialize($values)).'" />';
		}

		$attributes = array();

		foreach($attr as $key => $value){
			$attributes[] = htmlspecialchars($key, ENT_QUOTES, "UTF-8") . "=\"".htmlspecialchars($value, ENT_QUOTES, "UTF-8")."\"";
		}

		$html[] = "<input type=\"file\" name=\"data[".$this->getColumnId()."][]\" value=\"\"" . implode(" ",$attributes) . " onchange=\"inquiry_upload_file_limit_" . $this->getColumnId() . "(this)\" multiple>";

		//アップロードの枚数制限
		if(is_numeric($this->upload_limit) && $this->upload_limit){
			$html[] = "<script>";
			$html[] = "function inquiry_upload_file_limit_" . $this->getColumnId() ."(ele){";
			$html[] = "	var inquiry_confirm_button = document.getElementsByName('confirm')[0];";	//必ずボタンはある
			$html[] = "	var upload_limit = " . $this->upload_limit . ";";
			$html[] = "	if(ele.files.length > upload_limit){";
			$html[] = "		alert('アップロード枚数の上限は' + upload_limit + '枚です。');";
			$html[] = "		inquiry_confirm_button.disabled = true;";
			$html[] = "	}else{";
			$html[] = "		inquiry_confirm_button.disabled = false;";
			$html[] = "	}";
			$html[] = "}";
			$html[] = "</script>";
		}


		return implode("\n",$html);
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html  = 'サイズ:<input type="text" name="Column[config][uploadsize]" value="'.htmlspecialchars($this->uploadsize,ENT_QUOTES).'" size="3">KB&nbsp;';
		$html .= '拡張子:<input type="text" name="Column[config][extensions]" value="'.htmlspecialchars($this->extensions,ENT_QUOTES).'"><br>';
		$html .= '画像のリサイズ: width:<input type="text" name="Column[config][resize_w]" value="'.htmlspecialchars($this->resize_w, ENT_QUOTES) . '" size="5">px ';
		$html .= 'height:<input type="text" name="Column[config][resize_h]" value="'.htmlspecialchars($this->resize_h, ENT_QUOTES) . '" size="5">px(アスペクト比は維持)<br>';
		$html .= '同時アップロードファイル数:<input type="number" value="' . $this->upload_limit . '" style="width:50px;">';

		return $html;
	}

	/**
	 * 確認画面用
	 */
	function getView(){
		$values = self::getValues();
		if(is_array($values) && count($values)){
			$html = array();
			foreach($values as $v){
				if(isset($v["tmp_name"]) && strlen($v["tmp_name"])){
					$html[] = htmlspecialchars($v["name"] . " (".(int)($v["size"] / self::KB_SIZE)."KB)", ENT_QUOTES, "UTF-8");
				}else{	//エラー
					$html[] = "<span style=\"color:red;\">" . htmlspecialchars($v["name"], ENT_QUOTES, "UTF-8") . "のアップロードを失敗しました。</span>";
				}
			}
			return implode("<br>", $html);
		}

		return "";
	}

	/**
	 * データ投入用
	 */
	function getContent(){
		$values = self::getValues();
		if(is_array($values) && count($values)){
			$html = array();
			foreach($values as $v){
				if(isset($v["tmp_name"]) && strlen($v["tmp_name"])){
					$html[] = $v["name"] . " (".(int)($v["size"] / self::KB_SIZE)."KB)";
				}
			}
			return trim(implode("\n", $html));
		}

		return "";
	}

	/**
	 * 値が正常かどうかチェック
	 */
	function validate(){

		$id = $this->getColumnId();

		//アップロードした
		$values = array();
		if(isset($_FILES["data"]["size"]) && is_array($_FILES["data"]["size"][$id]) && count($_FILES["data"]["size"][$id]) && (int)$_FILES["data"]["size"][$id][0] > 0){
			if(!file_exists(SOY_INQUIRY_UPLOAD_TEMP_DIR)) mkdir(SOY_INQUIRY_UPLOAD_TEMP_DIR);

			for($i = 0; $i < count($_FILES["data"]["size"][$id]); $i++){
				if(!isset($_FILES["data"]["size"][$id][$i]) || (int)$_FILES["data"]["size"][$id][$i] === 0) continue;
				$v = array();
				$v["name"] = $_FILES["data"]["name"][$id][$i];
				$v["type"] = $_FILES["data"]["type"][$id][$i];
				$v["tmp_name"] = $_FILES["data"]["tmp_name"][$id][$i];
				$v["error"] = $_FILES["data"]["error"][$id][$i];
				$v["size"] = $_FILES["data"]["size"][$id][$i];

				//拡張子のチェック
				$pathinfo = pathinfo($v["name"]);
				$extensions = self::_shapeExtensions();
				if(count($extensions)){
					$res = false;
					foreach($extensions as $ext){	//大文字小文字関係なく拡張子を確かめる
						if(!$res && is_numeric(stripos($pathinfo["extension"], $ext))) $res = true;
					}

					// @ToDo 何らかのエラーを出力したい
					if(!$res) $v = self::setError($v);
				}

				//ファイルサイズチェック
				if(($this->uploadsize * self::KB_SIZE) < $v["size"]){
					// @ToDo 何らかのエラーを出力したい
					$v = self::setError($v);
				}

				//一時的にアップロードする
				if(strlen($v["tmp_name"])) {	//エラーがなければtmp_nameには何らかの文字列が入っている
					$path_to = SOY_INQUIRY_UPLOAD_TEMP_DIR . md5($v["name"] . time()) . "." . $pathinfo["extension"];
					for(;;){	//複数フォームを設置して、同名のファイルを送信する際に以前アップロードしたものが上書きされないようにファイル名を変更する
						if(!file_exists($path_to)) break;
						$path_to = SOY_INQUIRY_UPLOAD_TEMP_DIR . md5($v["name"] . rand(1, 10) . time()) . "." . $pathinfo["extension"];
					}
					$result = move_uploaded_file($v["tmp_name"], $path_to);

					if($result){
						$v["tmp_name"] = $path_to;	//tmp_nameを上書き
					}else{
						// @ToDo 何らかのエラーを出力したい
						$v = self::setError($v);
					}

					//$path_toにあるファイルをリサイズする
					if(strlen($v["tmp_name"]) && strpos($v["type"], "image") !== false && is_numeric($this->resize_w) && $this->resize_w > 0){

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
							$v["size"] = filesize($path_to);
						}
					}
				}

				$values[] = $v;
			}
		}else{	//アップロードしていない
			$this->setValue(self::getValues());
			return;
		}

		//必須チェック
		if($this->getIsRequire() && !count($values)){
			$this->setErrorMessage($this->getLabel()."を入力してください。");
			return false;
		}

		$this->setValue($values);
		$_POST["data"][$this->getColumnId()] = base64_encode(serialize($this->getValue()));
	}

	/**
	 * onSend
	 */
	function onSend($inquiry){
		$values = self::getValues();
		if(is_array($values) && count($values)){
			$new_dir = SOY_INQUIRY_UPLOAD_DIR . "/" . $this->getFormId() . "/" . date("Ym") . "/";
			if(!file_exists($new_dir)) mkdir($new_dir,0777,true);

			$commentDAO = SOY2DAOFactory::create("SOYInquiry_CommentDAO");

			foreach($values as $i => $v){
				if(!isset($v["size"]) || $v["size"] == 0) continue;
				$tmp_name = $v["tmp_name"];
				$new_name = str_replace(SOY_INQUIRY_UPLOAD_TEMP_DIR, $new_dir, $tmp_name);
				if(strpos($new_name, "//")) $new_name = str_replace("//", "/", $new_name);

				//同名のファイルがある場合は名前を変更する
				if(file_exists($new_name)){
					//拡張子を抜いて、ファイル名を少し変更する
					$ext = substr($new_name, strrpos($new_name, "."));
					$new_name = substr($new_name, 0, strrpos($new_name, "."));
					$new_name .= rand(100, 999) . $ext;
				}

				if(rename($tmp_name, $new_name)){
					$v["filepath"] = str_replace("\\","/",realpath($new_name));
					$v["filepath"] = str_replace(SOY_INQUIRY_UPLOAD_ROOT_DIR,"",$v["filepath"]);
					$values[$i] = $v;

					//コメントに追加する
					$content = $this->getLabel() . ":";
					$content .= '<a href="' . htmlspecialchars($v["filepath"], ENT_QUOTES, "UTF-8") . '">' . htmlspecialchars($v["name"], ENT_QUOTES, "UTF-8") . '</a>';

					$pathinfo = pathinfo($v["filepath"]);
					$extensions = self::_shapeExtensions();
					if(count($extensions)){
						$res = false;
						foreach($extensions as $ext){	//拡張子を大文字小文字関係なく調べる
							if(!$res && is_numeric(stripos($pathinfo["extension"], $ext))) $res = true;
						}

						if($res) $content .= '<br/><img src="' . htmlspecialchars($v["filepath"], ENT_QUOTES, "UTF-8") . '"/>';
					}

					$comment = new SOYInquiry_Comment();
					$comment->setInquiryId($inquiry->getId());
					$comment->setTitle($this->getLabel());
					$comment->setAuthor("-");
					$comment->setContent($content);

					$commentDAO->insert($comment);
				}
			}

			$this->setValue($values);
		}
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

	private function getValues(){
		$values = $this->getValue();
		if(is_string($values) && strlen($values)) $values = soy2_unserialize(base64_decode($values));
		if(!isset($values) || !is_array($values)) $values = array();
		return $values;
	}

	private function setError($v, $mes=null){
		$v["tmp_name"] = null;
		$v["size"] = 0;

		// @ToDo エラーメッセージをどうにかしたい 出力側は未実装
		if(strlen($mes)){
			$v["error_messege"] = $mes;
		}
		return $v;
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
