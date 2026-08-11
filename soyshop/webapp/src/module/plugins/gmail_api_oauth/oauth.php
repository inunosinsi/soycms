<?php
// SOY2 などの CMS 環境を想定
session_start();

// クライアント情報の読み込み
$secretPath = GmailApiOAuthUtil::getClientSecretJsonPath();
$authConfig = json_decode(file_get_contents($secretPath), true);
$cfg = $authConfig['web'] ?? $authConfig['installed'] ?? null;

if (!$cfg) exit("Client Secret JSON が正しくありません。");

$clientId     = $cfg['client_id'];
$clientSecret = $cfg['client_secret'];
$redirectUri  = GmailApiOAuthUtil::getCallbackUrl();
$tokenPath    = GmailApiOAuthUtil::getGmailConfigFilePath();

// 1. Google の認証画面へ飛ばすフェーズ
if (!isset($_GET['code'])) {
    $params = [
        'client_id'     => $clientId,
        'redirect_uri'  => $redirectUri,
        'response_type' => 'code',
        'scope'         => 'https://www.googleapis.com/auth/gmail.send',
        'access_type'   => 'offline',       // これがないと refresh_token がもらえない
        'prompt'        => 'consent select_account' // 確実に同意画面を出してトークンを得る
    ];

    $authUrl = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params);
    
    header('Location: ' . $authUrl);
    exit;

// 2. 認証後に戻ってきた 'code' を 'token' に交換するフェーズ
} else {
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code'          => $_GET['code'],
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri'  => $redirectUri,
        'grant_type'    => 'authorization_code',
    ]));

    $response = curl_exec($ch);
    $token = json_decode($response, true);
    curl_close($ch);

    if (isset($token['access_token'])) {
        // 今後の自動更新のために「作成日時」を付与して保存
        $token['created'] = time();
        file_put_contents($tokenPath, json_encode($token));
        echo "Successed! Token has been saved.";
    } else {
        echo "Failed to get token: " . htmlspecialchars($response);
    }
    exit;
}
