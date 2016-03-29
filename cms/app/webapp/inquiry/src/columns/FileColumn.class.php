<?php

class FileColumn extends SOYInquiry_ColumnBase{

	private $extensions = "jpg,jpeg,gif,png";
	private $uploadsize = 500;	//KB

	const KB_SIZE = 1024;

	/**
	 * ユーザに表示するようのフォーム
	 */
	function getForm($attr = array()){

		$value = $this->getValue();

		//アップロードされていた場合
		if(is_array($value)){

			$new_value = base64_encode(serialize($this->getValue()));

			$html = array();
			$html[] = htmlspecialchars($value["name"], ENT_QUOTES, "UTF-8") . "(".(int)($value["size"] / self::KB_SIZE)."KB)";
			$html[] = '<input type="hidden" name="data['.$this->getColumnId().']" value="'.$new_value.'" />';

			return implode("\n",$html);
		}

		$attributes = array();

		foreach($attr as $key => $value){
			$attributes[] = htmlspecialchars($key, ENT_QUOTES, "UTF-8") . "=\"".htmlspecialchars($value, ENT_QUOTES, "UTF-8")."\"";
		}

		$html = array();
		$html[] = "<input type=\"file\" name=\"data[".$this->getColumnId()."]\" value=\"".htmlspecialchars($this->getValue(), ENT_QUOTES, "UTF-8")."\"" . implode(" ",$attributes) . " />";

		return implode("\n",$html);
	}

	/**
	 * 設定画面で表示する用のフォーム
	 */
	function getConfigForm(){
		$html  = 'サイズ:<input type="text" name="Column[config][uploadsize]" value="'.htmlspecialchars($this->uploadsize,ENT_QUOTES).'" size="3"/>KB&nbsp;';
		$html .= '拡張子:<input type="text" name="Column[config][extensions]" value="'.htmlspecialchars($this->extensions,ENT_QUOTES).'" /><br>';

		return $html;
	}

	/**
	 * 確認画面用
	 */
	function getView(){
		$value = $this->getValue();

		if(is_array($value) && isset($value["name"]) && isset($value["size"])){
			$html = $value["name"] . " (".(int)($value["size"] / self::KB_SIZE)."KB)";
			return htmlspecialchars($html, ENT_QUOTES, "UTF-8");
		}

		return "";
	}

	/**
	 * データ投入用
	 */
	function getContent(){
		$value = $this->getValue();

		if(is_array($value) && isset($value["name"]) && isset($value["size"])){

			$html = array();
			$html[] = $value["name"] . " (".(int)($value["size"] / self::KB_SIZE)."KB)";

			return implode("\n",$html);
		}

		return "";
	}

	/**
	 * onSend
	 */
	function onSend($inquiry){

		$value = $this->getValue();

		if(is_array($value)){
			$tmp_name = $value["tmp_name"];

			$new_dir = SOY_INQUIRY_UPLOAD_DIR . "/" . $this->getFormId() . "/" . date("Ym") . "/";
			if(!file_exists($new_dir))mkdir($new_dir,0777,true);

			$new_name = str_replace(SOY_INQUIRY_UPLOAD_TEMP_DIR, $new_dir,$tmp_name);

			if(rename($tmp_name,$new_name)){
				$value["filepath"] = str_replace("\\","/",realpath($new_name));
				$value["filepath"] = str_replace(SOY_INQUIRY_UPLOAD_ROOT_DIR,"",$value["filepath"]);
				$this->setValue($value);

				//コメントに追加する
				$content = $this->getLabel() . ":";
				$content .= '<a href="'.htmlspecialchars($value["filepath"],ENT_QUOTES,"UTF-8").'">'.htmlspecialchars($value["name"],ENT_QUOTES,"UTF-8").'</a>';

				$pathinfo = pathinfo($value["filepath"]);
				if(in_array($pathinfo["extension"],array("jpg","jpeg","gif","png"))){
					$content .= '<br/><img src="'.htmlspecialchars($value["filepath"],ENT_QUOTES,"UTF-8").'"/>';
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

		$value = $this->getValue();

		$tmp = @unserialize(base64_decode($value));

		//二回目のPOST
		if(is_array($tmp) && isset($tmp["tmp_name"])
			&& file_exists($tmp["tmp_name"]) && is_readable($tmp["tmp_name"])){

			$this->setValue($tmp);
			return;
		}


		$id = $this->getColumnId();


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

		$pathinfo = pathinfo($name);

		//拡張子チェック
		$extensions = explode(",", $this->extensions);
		if(!in_array($pathinfo["extension"], $extensions)){
			$this->setErrorMessage($this->getLabel()."の形式が不正です。");
			return false;
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
		$result = move_uploaded_file($tmp_name,$path_to);

		//一時アップロードに失敗した場合
		if(!$result){
			$this->setErrorMessage("アップロードに失敗しました。");
			return false;
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

	/**
	 * 保存された設定値を渡す
	 */
	function setConfigure($config){
		SOYInquiry_ColumnBase::setConfigure($config);

		$this->uploadsize  = (isset($config["uploadsize"]) && is_numeric($config["uploadsize"])) ? (int)$config["uploadsize"] : 500;
		$this->extensions = (isset($config["extensions"]) && strlen($config["extensions"])) ? $config["extensions"] : "jpg,gif,png";
	}
	
	function getConfigure(){
		$config = parent::getConfigure();
		$config["uploadsize"] = $this->uploadsize;
		$config["extensions"] = $this->extensions;
		return $config;
	}

	function getLinkagesSOYMailTo() {
		return array(
			SOYMailConverter::SOYMAIL_NONE  => "連携しない",
		);
	}
}
?>