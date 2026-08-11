<?php

class UploadLogic extends SOY2LogicBase {

	private $topicId;
	private $postId;
	private $mypage;

	function __construct(){
		SOY2::import("module.plugins.bulletin_board.util.BulletinBoardUtil");
	}

	function uploadTmpFile($tmpName, $fileType){
		//古いファイルを削除
		self::_clean();

		$sesKey = self::_sessionKey();
		$files = $this->mypage->getAttribute($sesKey);
		if(!is_array($files)) $files = array();

		$ext = self::_extension($fileType);
		$path = self::_tmpDir() . md5($tmpName) . "." . $ext;
		$res = move_uploaded_file($tmpName, $path);
		if(is_bool($res) && !$res) return false;

		//jpgでない場合はjpgに変換
		if($ext !== "jpg") {
			$new = self::_convert($path, $ext);
			if(strlen($new)) $path = $new;
		}

		//拡張子を再取得
		$ext = substr($path, strrpos($path, ".") + 1);
		if($ext == "jpg"){
			self::_resize($path);	//リサイズ
			self::_optimize($path);	//最適化
		}

		//ファイル名のみを格納
		$files[] = trim(trim(substr($path, strrpos($path, "/")), "/"));
		$this->mypage->setAttribute($sesKey, $files);
		$this->mypage->save();

		return true;
	}

	function getTmpFilePathes(){
		$files = $this->mypage->getAttribute(self::_sessionKey());
		if(!is_array($files)) $files = array();

		if(!count($files)) return array();

		//画像が削除されていないか？調べる
		$tmps = array();
		$tmpDir = self::_tmpDir();
		foreach($files as $file){
			if(file_exists($tmpDir.$file)) $tmps[] = $file;
		}

		//削除画像の確認後に再度確認
		if(!count($tmps)) return array();

		$list = array();
		$tmpDir = self::_tmpDir(false);
		foreach($tmps as $file){
			$list[] = $tmpDir.$file;
		}
		return $list;
	}

	//画像ファイルを仮ディレクトリから各ポストのディレクトリに移動する
	function move($postId=null){
		$sesKey = self::_sessionKey();
		$files = $this->mypage->getAttribute($sesKey);
		if(!is_array($files) || !count($files)) return;

		if(is_null($postId)) $postId = $this->postId;

		$tmpDir = self::_tmpDir();
		$dir = self::_dir($this->mypage->getUserId(), $this->topicId, $postId);

		$i = self::_getLastIndex($postId);
		foreach($files as $file){
			if(!file_exists($tmpDir.$file)) continue;
			rename($tmpDir.$file, $dir.$i++.$file);	//ファイルの以降の際にファイル名の頭に連番を付ける→画像順にソート出来る
		}

		//ファイル名の変更	連番の付け直し
		self::align($postId);

		$this->mypage->clearAttribute($sesKey);
		$this->mypage->save();
	}

	function align($postId){
		$i = 0;
		$files = self::getFilePathes($postId, true);	//削除しているものとか関係なく出力する

		$sesKey = self::_sessionKey("_remove");
		$rmList = $this->mypage->getAttribute($sesKey);
		if(!is_array($rmList)) $rmList = array();

		$dir = self::_dir($this->mypage->getUserId(), $this->topicId, $postId);
		foreach($files as $file){
			$filename = BulletinBoardUtil::path2filename($file);
			$filepath = $dir.$filename;
			if(!file_exists($filepath)) continue;

			if(count($rmList) && is_numeric(array_search($filename, $rmList))){	//削除
				unlink($filepath);
			}else{	//リネーム
				rename($filepath, $dir.$i++.md5($file) . ".jpg");
			}
		}
		$this->mypage->clearAttribute($sesKey);
		$this->mypage->save();
	}

	function getFilePathes($postId, $isAll=false){
		$post = self::_postLogic()->getById($postId);
		$dir = self::_dir($post->getUserId(), $post->getTopicId(), $postId);
		$files = soy2_scandir($dir);
		if(!count($files)) return array();

		//削除リストから表示して良いファイルを調べる
		if(!$isAll){
			$sesKey = self::_sessionKey("_remove");
			$rmList = $this->mypage->getAttribute($sesKey);
			if(!is_array($rmList)) $rmList = array();
		}else{
			$rmList = array();
		}

		$list = array();
		$dir = self::_dir($post->getUserId(), $post->getTopicId(), $postId, false);
		foreach($files as $file){
			if(count($rmList) && is_numeric(array_search($file, $rmList))) continue;
			$list[] = $dir.$file;
		}
		return $list;
	}

	function remove($filename){
		//tmpの方を調べる
		$tmpDir = self::_tmpDir();
		if(file_exists($tmpDir . $filename)){
			unlink($tmpDir . $filename);
			return;
		}

		//dirの方を調べる removeの一覧を作成する moveの時に削除する
		$sesKey = self::_sessionKey("_remove");
		$rmList = $this->mypage->getAttribute($sesKey);
		if(!is_array($rmList)) $rmList = array();
		$rmList[] = $filename;
		$this->mypage->setAttribute($sesKey, $rmList);
	}

	//1週間前にtmpディレクトリにアップロードされたままの画像を削除する @ToDo cleanの期間も決めておきたい
	private function _clean(){
		$dir = self::_tmpDir();
		$files = soy2_scandir($dir);
		if(!count($files)) return;

		foreach($files as $file){
			$filepath = $dir . $file;
			if(!file_exists($filepath)) continue;

			if(filemtime($filepath) < strtotime("-7 day")){
				unlink($filepath);
			}
		}
	}

	//新規投稿と投稿の編集でキーを分ける
	private function _sessionKey($postfix=""){
		if(is_numeric($this->postId)){
			return BulletinBoardUtil::getEditUploadSessionKey($this->postId, $this->topicId, $this->mypage->getUserId()) . $postfix;
		}else{
			return BulletinBoardUtil::getUploadSessionKey($this->topicId, $this->mypage->getUserId()) . $postfix;
		}
	}

	private function _convert($path, $ext="png"){
		$new = substr($path, 0, strrpos($path, ".")) . ".jpg";
		switch($ext){
			case "png":
			case "gif":
			case "webp":
				if($ext == "gif"){
					$image = imagecreatefromgif($path);
				}else if($ext == "webp"){
					$image = imagecreatefromwebp($path);
				}else{	//png
					$image = imagecreatefrompng($path);
				}
				imagejpeg($image, $new, 100);
				imagedestroy($image);
				unlink($path);	//古い方を削除
				break;
			case "heic":
			case "heif":
				// @ToDo imageMagickを7にしておく必要がある
				exec("convert " . $path . " " . $new, $retval);
				unlink($path);	//heicの方を削除
				break;
			default:
				//他の拡張子も追加する
				return null;
		}
		return $new;
	}

	private function _extension($fileType){
		$ext = trim(substr($fileType, strpos($fileType, "/")), "/");
		if($ext == "jpeg") $ext = "jpg";
		if($ext == "heif") $ext = "heic";
		return $ext;
	}

	private function _resize($path){
		// 横640px以上は640pxにリサイズする	@ToDo そのうち設定画面を設けたい
		$info = getimagesize($path);
		if($info[0] > 640) {
			soy2_resizeimage($path, $path, 640);
		}

		//縦の方も調べる
		$info = getimagesize($path);
		if($info[1] > 640) {
			$resizeW = (int)($info[0] * (640 / $info[1]));
			soy2_resizeimage($path, $path, $resizeW);
		}
	}

	private function _optimize($path){
		//guetzliがある場合
		exec("guetzli --quality 84 " . $path . " " . $path);
	}

	private function _getLastIndex($postId){
		$images = self::getFilePathes($postId);
		if(!count($images)) return 0;

		$cnt = count($images);
		$lastImage = array_pop($images);
		$lastImageFileName = BulletinBoardUtil::path2filename($lastImage);

		if($cnt >= 10){	//二桁の場合
			$i = (int)substr($lastImageFileName, 0, 2);
			if($i === 0) $i = (int)substr($lastImageFileName, 0, 1);
		}else{
			$i = (int)substr($lastImageFileName, 0, 1);	//最初の一文字に
		}

		return $i + 1;
	}

	private function _tmpDir($isFullpath=true){
		if($isFullpath){
			$dir = SOYSHOP_SITE_DIRECTORY . ".tmp/";
		}else{
			$dir = "/" . SOYSHOP_ID . "/.tmp/";
		}

		if($isFullpath && (!file_exists($dir) || !is_dir($dir))) mkdir($dir);
		return $dir;
	}

	//画像ファイルを保管するディレクトリ
	private function _dir($userId, $topicId, $postId, $isFullpath=true){
		if($isFullpath){
			$dir = SOYSHOP_SITE_DIRECTORY . "files/board/";
		}else{
			$dir = "/" . SOYSHOP_ID . "/files/board/";
		}

		if($isFullpath && (!file_exists($dir) || !is_dir($dir))) mkdir($dir);

		$dir .= $userId . "/";
		if($isFullpath && (!file_exists($dir) || !is_dir($dir))) mkdir($dir);

		$dir .= $topicId . "/";
		if($isFullpath && (!file_exists($dir) || !is_dir($dir))) mkdir($dir);

		$dir .= $postId . "/";
		if($isFullpath && (!file_exists($dir) || !is_dir($dir))) mkdir($dir);

		return $dir;
	}

	private function _postLogic(){
		static $logic;
		if(is_null($logic)) $logic = SOY2Logic::createInstance("module.plugins.bulletin_board.logic.PostLogic");
		return $logic;
	}

	function setTopicId($topicId){
		$this->topicId = $topicId;
	}

	function setPostId($postId){
		$this->postId = $postId;
	}

	function setMypage($mypage){
		$this->mypage = $mypage;
	}
}
