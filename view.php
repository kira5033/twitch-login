<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=10.0, user-scalable=yes">
    <title>LINE Login</title>
</head>
<body>

<img alt="small" src="<?=$TwitchProvider->getUserPicture()?>"><br>
UserId： <?=$TwitchProvider->getUserId()?> <br>
UserName： <?=$TwitchProvider->getUserLoginName()?> <br>
UserDisplayName： <?=$TwitchProvider->getUserDisplayName()?> <br>
AccessToken： <?=$access_token?><br>
ID Token： <?=$id_token?><br>

<button type="button" onclick="location.href = '/?act=logout'">Logout</button>

</body>
</html>
