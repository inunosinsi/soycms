<?php

class PasswordUtil {

	//ハッシュ化の繰り返し回数
	const STRETCH = 50000;

	/**
	 * パスワードが正しいかチェックする
	 *
	 * @param String 入力されたパスワード
	 * @param String 保存されているハッシュを含む文字列（algo/salt/hash または algo/salt/hash/stretch）
	 */
	public static function checkPassword($input, $stored){
		if(strpos($stored, "sha512/") === 0 OR strpos($stored, "md5/") === 0 ){
			if(substr_count($stored, '/') == 2){
				//1.3.4aまで
				list($algo, $salt, $hash) = explode("/", $stored);
				return ( $stored == self::hashString($input, $salt, $algo, null) );
			}else{
				list($algo, $salt, $hash, $stretch) = explode("/", $stored);
				return ( $stored == self::hashString($input, $salt, $algo, $stretch) );
			}
		}else{
			//1.2.4cとの互換性のため
			return ( $stored == crypt($input, $stored) );
		}
	}

	/**
	 * 新規にパスワードをハッシュ化する
	 *
	 * @param String ハッシュ化する文字列
	 * @return String ハッシュ化された文字列（algo/salt/hash/stretch）
	 *
	 * 1.2.5以降はSHA512もしくはMD5
	 * 1.2.4cではcryptを使っていたので上記の形式以外はcryptを使う。
	 * 1.2.4b以前との互換性はない（バグがあったので1.2.4cでパスワードを再設定している）。
	 *
	 * データベース保存先のuser_passwordは、MySQLではVARCHAR(255)ということに注意（SQLiteではVARCHAR）。
	 * md5: 16進数表記で32文字
	 * sha512: 16進数表記で128文字
	 */
	public static function hashPassword($rawPassword){
		//saltは乱数をmd5にしたもの
		$salt = md5(mt_rand());

		$stretch = self::STRETCH;
		//変えたい人は変えられるようにしておく
		if(defined("SOYCMS_PASSWORD_HASH_STRETCH") && is_int(SOYCMS_PASSWORD_HASH_STRETCH)){
			$stretch = SOYCMS_PASSWORD_HASH_STRETCH;
		}

		if(function_exists("hash")){
			// hash関数があればSHA512で
			return self::hashString($rawPassword, $salt, "sha512", $stretch);
		}else{
			// なければMD5
			return self::hashString($rawPassword, $salt, "md5", $stretch);
		}
	}

	/**
	 * 文字列をハッシュ化する。algo/salt/hash または algo/salt/hash/stretchの形式で返す。
	 *
	 * UNIX標準の$6$salt$hashにすべきだったか？
	 *
	 * @param String ハッシュ化する文字列
	 * @param String ハッシュ化の際のsalt
	 * @param String ハッシュ化アルゴリズム
	 * @param Integer 繰り返し回数（stretch, round) 1.3.4aまではなし
	 * @return String ハッシュ化された文字列（algo/salt/hash または algo/salt/hash/stretch）
	 */
	private static function hashString($string, $salt, $algo, $stretch){

		if(isset($stretch)){
			$hash = self::hashStringWithStretch($string, $salt, $algo, $stretch);
			return "$algo/$salt/$hash/$stretch";
		}else{
			//1.3.4aまで
			$hash = self::hashStringWithStretch($string, $salt, $algo, 1);
			return "$algo/$salt/$hash";
		}
	}

	/**
	 * 文字列を繰り返しハッシュ化する
	 *
	 * @param String ハッシュ化する文字列
	 * @param String ハッシュ化の際のsalt
	 * @param String ハッシュ化アルゴリズム
	 * @param Integer 繰り返し回数（stretch, round)
	 * @return String ハッシュ化された文字列そのもの
	 */
	private static function hashStringWithStretch($string, $salt, $algo, $stretch){
		$algo = strtolower($algo);
		$stretch = max(1,(int)$stretch);

		$hash = "";
		if($algo == "md5"){
			//md5はhashが使えないときための保険
			for($i=0;$i<$stretch;++$i){
				$hash = md5($hash.$salt.$string);
			}
		}else{
			for($i=0;$i<$stretch;++$i){
				$hash = hash($algo, $hash.$salt.$string);
			}
		}

		return $hash;
	}


}
