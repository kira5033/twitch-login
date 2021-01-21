# TWITCH OIDC 試作範例

參考 [TWITCH 文件](https://dev.twitch.tv/docs/)實作一個簡易登入套件  

## 說明

```php=
$TwitchProvider = new Twitch_Login(CLIENT_ID,CLIENT_SECRET);
```

1. 產生登入連結
```php=
// 可以產生一個UUID作為$state，可以callback回來做驗證
$TwitchProvider->createOAuthUrl(CALLBACK_URL, $state)
```
2. 在`callback.php`取得`Access Token`及`ID Token`
```php
// 這裡可以丟入$state做檢查
$result = $TwitchProvider->catchResponse()->Authorization(CALLBACK_URL, $state);
if($result){
    // success
    $id_token = $Line->getUserIdToken();
    $access_token = $Line->getUserAccessToken();
}else{
    // failed
}
```
3. 取得 `JWT` 資料
```php
$TwitchProvider->getUserInfoEndpoint($access_token$);
$this->getUserId(); // Twtich User Id
$this->getUserEmail();
$this->getUserPicture();
$this->getUserDisplayName();
```

## 其他

1. 取得使用者資訊
```php
$TwitchProvider->getUser($access_token$,$user_id);
```

2. 撤除 `Access Token`
```php
$TwitchProvider->revoke($access_token);
```
