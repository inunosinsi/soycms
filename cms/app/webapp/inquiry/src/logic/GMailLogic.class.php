<?php

class GMailLogic extends SOY2LogicBase {

	function __construct(){
		SOY2::import("util.SOYInquiryUtil");
	}
	
	/**
	 * ライブラリ不要版：一通送信する
	 */
	function sendMail(string $sendTo, string $title, string $body, string $sendToName="", string $from="", string $fromName="", array $replyTo=array()){
		
		// OAuthクライアントID の秘密鍵ファイル (JSON形式を想定)
		$secretPath = SOYInquiryUtil::SOYINQUIRY_GMAIL_API_OAUTH_CLIENT_SECRET_FILEPATH;
		if(!file_exists($secretPath)) return false;
		$authConfig = json_decode(file_get_contents($secretPath), true);
		
		// webかinstalledかによって階層が違う場合があるため調整
		$cfg = $authConfig['web'] ?? $authConfig['installed'] ?? null;
		if(!$cfg) return false;

		// 認証トークンが見つからない場合は終了
		$tokenPath = SOYInquiryUtil::SOYINQUIRY_GMAIL_API_OAUTH_TOKEN_FILEPATH;
		if (!file_exists($tokenPath)) return false;
		$tokenData = json_decode(file_get_contents($tokenPath), true);

		// --- 1. トークンの有効期限チェックと更新 ---
		// Googleのトークンデータには通常 'created' と 'expires_in' が含まれる
		$isExpired = (isset($tokenData['created']) && isset($tokenData['expires_in'])) 
					 ? ($tokenData['created'] + $tokenData['expires_in'] - 30 < time()) 
					 : true;

		if ($isExpired) {
			if (!isset($tokenData['refresh_token'])) {
				throw new Exception("リフレッシュトークンがありません。再度ログインが必要です。");
			}

			// cURLでアクセストークンを更新
			$ch = curl_init('https://oauth2.googleapis.com/token');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
				'client_id'     => $cfg['client_id'],
				'client_secret' => $cfg['client_secret'],
				'refresh_token' => $tokenData['refresh_token'],
				'grant_type'    => 'refresh_token',
			]));
			$res = json_decode(curl_exec($ch), true);
			curl_close($ch);

			if (isset($res['access_token'])) {
				$tokenData['access_token'] = $res['access_token'];
				$tokenData['created'] = time();
				if(isset($res['expires_in'])) $tokenData['expires_in'] = $res['expires_in'];
				// 新しいトークンを保存
				file_put_contents($tokenPath, json_encode($tokenData));
			} else {
				return false;
			}
		}

		$accessToken = $tokenData['access_token'];

		// --- 2. メールメッセージの構築 (RFC 2822) ---
		$headers = [];
		if(strlen($fromName)){
			$headers[] = "From: =?utf-8?B?".base64_encode($fromName)."?= <".$from.">";
		} else {
			$headers[] = "From: me";
		}
		
		if(strlen($sendToName)){
			$headers[] = "To: =?utf-8?B?".base64_encode($sendToName)."?= <".$sendTo.">";
		} else {
			$headers[] = "To: ".$sendTo;
		}

		if(count($replyTo)){
			$headers[] = "Reply-To: ".implode(",", $replyTo);
		}

		$headers[] = "Subject: =?utf-8?B?" . base64_encode($title) . "?=";
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Type: text/plain; charset=utf-8";
		$headers[] = "Content-Transfer-Encoding: base64"; // 7bitよりbase64の方が日本語崩れが少ない

		$strRawMessage = implode("\r\n", $headers) . "\r\n\r\n" . base64_encode($body);
	
		// Base64URL エンコード
		$mime = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
	
		// --- 3. Gmail API 送信実行 ---
		try {
			$ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/me/messages/send');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"Authorization: Bearer $accessToken",
				"Content-Type: application/json",
			]);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['raw' => $mime]));

			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			return ($httpCode === 200);
		} catch (Exception $e) {
			// 必要に応じてログ出力など
			return false;
		}
	}	
}
