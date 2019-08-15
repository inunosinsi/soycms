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
				$html[] = htmlspecialchars($v["name"], ENT_QUOTES, "UTF-8") . "(".(int)($v["size"] / self::KB_SIZE)."KB)";
				$html[] = "<br>";
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
		if(count($values)){
			$html = array();
			foreach($values as $v){
				$html[] = htmlspecialchars($v["name"] . " (".(int)($v["size"] / self::KB_SIZE)."KB)", ENT_QUOTES, "UTF-8");
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
		if(count($values)){
			$html = array();
			foreach($values as $v){
				$html[] = $v["name"] . " (".(int)($v["size"] / self::KB_SIZE)."KB)";
			}
			return trim(implode("\n", $html));
		}

		return "";
	}

	/**
	 * onSend
	 */
	function onSend($inquiry){
		$values = self::getValues();
		if(count($values)){
			$new_dir = SOY_INQUIRY_UPLOAD_DIR . "/" . $this->getFormId() . "/" . date("Ym") . "/";
			if(!file_exists($new_dir)) mkdir($new_dir,0777,true);

			$commentDAO = SOY2DAOFactory::create("SOYInquiry_CommentDAO");

			foreach($values as $i => $v){
				$tmp_name = $v["tmp_name"];
				$new_name = str_replace(SOY_INQUIRY_UPLOAD_TEMP_DIR, $new_dir, $tmp_name);

				if(rename($tmp_name, $new_name)){
					$v["filepath"] = str_replace("\\","/",realpath($new_name));
					$v["filepath"] = str_replace(SOY_INQUIRY_UPLOAD_ROOT_DIR,"",$v["filepath"]);
					$values[$i] = $v;

					//コメントに追加する
					$content = $this->getLabel() . ":";
					$content .= '<a href="' . htmlspecialchars($v["filepath"], ENT_QUOTES, "UTF-8") . '">' . htmlspecialchars($v["name"], ENT_QUOTES, "UTF-8") . '</a>';

					$pathinfo = pathinfo($v["filepath"]);
					if(in_array($pathinfo["extension"], array("jpg", "jpeg", "gif", "png"))){
						$content .= '<br/><img src="' . htmlspecialchars($v["filepath"], ENT_QUOTES, "UTF-8") . '"/>';
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

				$check = true;

				//拡張子のチェック
				$pathinfo = pathinfo($v["name"]);
				$extensions = explode(",", $this->extensions);
				if(!in_array($pathinfo["extension"], $extensions)){
					// @ToDo 何らかのエラーを出力したい
					$check = false;
				}

				//ファイルサイズチェック
				if(($this->uploadsize * self::KB_SIZE) < $v["size"]){
					// @ToDo 何らかのエラーを出力したい
					$check = false;
				}

				//一時的にアップロードする
				if($check) {
					$path_to = SOY_INQUIRY_UPLOAD_TEMP_DIR . md5($v["name"] . time()) . "." . $pathinfo["extension"];
					$result = move_uploaded_file($v["tmp_name"], $path_to);
					$v["tmp_name"] = $path_to;	//tmp_nameを上書き

					if(!$result){
						// @ToDo 何らかのエラーを出力したい
						$check = false;
					}

					//$path_toにあるファイルをリサイズする
					if(strpos($v["type"], "image") !== false && is_numeric($this->resize_w) && $this->resize_w > 0){
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

					if($check) $values[] = $v;
				}
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

	private function getValues(){
		$values = $this->getValue();
		if(is_string($values) && strlen($values)) $values = soy2_unserialize(base64_decode($values));
		if(!isset($values) || !is_array($values)) $values = array();
		return $values;
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
