<?php

class UserLogic extends SOY2LogicBase{

    function remove($userId){
    	$userDao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

    	try{
    		$user = $userDao->getById($userId);
    	}catch(Exception $e){
    		return false;
    	}

    	$mailAddress = $user->getMailAddress();

    	//ユーザが存在していた場合
    	if(isset($mailAddress)){
			$i = 0;
			$res = false;
			do{
				try{
					$deleteAddress = $mailAddress . "_delete_" . $i;
					$userDao->getByMailAddress($deleteAddress);
					$i++;
				}catch(Exception $e){
					$res = true;
				}
			}while(!$res);

			$accountId = $user->getAccountId();
			$deleteAccountId = null;
			if(!is_null($accountId)){
				$i = 0;
				$res = false;
				do{
					try{
						$deleteAccountId = $accountId . "_delete_" . $i;
						$userDao->getByAccountId($deleteAccountId);
						$i++;
					}catch(Exception $e){
						$res = true;
					}
				}while(!$res);
			}

    		$user->setName("(削除)" . $user->getName());
    		$user->setMailAddress($deleteAddress);
			$user->setUserCode(null);
    		$user->setAccountId($deleteAccountId);
    		$user->setIsDisabled(SOYShop_User::USER_IS_DISABLED);

    		try{
    			$userDao->update($user);
    			$res = true;
    		}catch(Exception $e){
				$res = false;
    		}
    	}

    	if($res){
    		$loginDao = SOY2DAOFactory::create("user.SOYShop_AutoLoginSessionDAO");
    		try{
    			$loginDao->deleteByUserId($userId);
    			return true;
    		}catch(Exception $e){
    			return false;
    		}
    	}
    }

	/** プロフィール **/

	/**
	 * プロフィール用のアカウントを作成する。諸々の値のハッシュ
	 * @return string profile_id
	 */
	function createProfileId(SOYShop_User $user){
		$hash = $user->getId() . md5($user->getName(). $user->getMailAddress());
		return substr($hash, 0, 20);
	}

	/**
	 * プロフィールページに表示するための画像サイズ
	 * @param object SOYShop_User
	 * @return int width
	 */
	function getDisplayImage(SOYShop_User $user){
		$width = 0;	//画像が存在していなかったときは0px
		$path = $user->getAttachmentsPath() . $user->getImagePath();
    	$imageExists = is_readable($path) && is_file($path) && strlen($user->getImagePath());
    	if($imageExists){
			$image_size = getimagesize($path);
			$width = ($image_size[0] > 480) ? 480 : $image_size[0];
    	}
    	return $width;
	}

    function uploadFile($file, $tmp, $userId, $isResize, $resizeWidth, $resizeHeight = null){

		SOYShopPlugin::load("soyshop.upload.image");
		$new = SOYShopPlugin::invoke("soyshop.upload.image", array(
			"mode" => "profile",
			"pathinfo" => pathinfo($file)
		))->getName();

		if(is_null($new)) $new = self::getUniqueFileName($file);

		$path = $this->makeDirectory($userId) . $new;
		@move_uploaded_file($tmp, $path);

		//リサイズ
		if($isResize && self::_checkSizeBeforeResize(getimagesize($path), $resizeWidth)){
			soy2_resizeimage($path, $path, $resizeWidth);
		}

		return $new;
	}

	function uploadTmpFile($file, $tmp, $userId, $isResize, $resizeWidth, $resizeHeight = null){
    	$new = self::getUniqueFileName($file);
		$path = $this->makeTmpDirectory() . $new;
		@move_uploaded_file($tmp, $path);

		//リサイズ
		if($isResize && self::_checkSizeBeforeResize(getimagesize($path),$resizeWidth)){
			soy2_resizeimage($path, $path, $resizeWidth);
		}

		return $new;
	}

	private function getUniqueFileName($file){
		$fileType = substr($file, strrpos($file, "."));
		return md5($file . time()) . $fileType;
	}

	function _checkSizeBeforeResize($image, $resize_width){
		return (isset($image[0]) && ($image[0] - $resize_width));
	}

	function makeDirectory($userId){
		SOY2::import("domain.user.SOYShop_User");
		$user = new SOYShop_User();
		$user->setId($userId);
		$dir = $user->getAttachmentsPath();//なければ作成、「/」で終わる
		return $dir;
	}
	function makeTmpDirectory(){
		SOY2::import("domain.user.SOYShop_User");
		$user = new SOYShop_User();
		$dir = $user->getTmpPath();
		return $dir;
	}

	function deleteFile($file,$userId){
		if(strlen($file)){
			SOY2::import("domain.user.SOYShop_User");
			$user = new SOYShop_User();
			$user->setId($userId);
			$path = $user->getAttachmentsPath() . $file;
			if(file_exists($path)){
				unlink($path);
			}
		}
	}

	function tmpAllDelete(){
		SOY2::import("domain.user.SOYShop_User");
		$user = new SOYShop_User();
		$tmpDir = $user->getTmpPath();

		$res = opendir(".");

		$dir = dir($tmpDir);
		while($file=$dir->read()){
			if(strlen($file) > 2){
				@unlink($tmpDir . $file);
			}
		}
		$dir->close();
	}
}
