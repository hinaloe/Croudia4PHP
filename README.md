#Croudia4PHP

Croudia API をPHPで扱うためのライブラリです。  
とりあえず[Croudia REST API 1.0](http://developer.croudia.com/docs/api10)とかexample見ればわかるはず。


##つかいかた

example見ればわかる

##メソッド一覧

### Auth
getAuthorizeURL()…認証URLを返します  
setAccessToken()…callbackに渡される code というGETパラメータを渡してアクセストークンをCroudia4PHPオブジェクトにセットします  
refreshAccessToken()…アクセストークンを更新する 各メソッドでAPIを叩いて$c4p -> httphead[0] に"HTTP/1.1 401 Authorization Required"が入っているときに叩くといいとか

### Timeline
GET_statuses_public_timeline()  
GET_statuses_home_timeline()  
GET_statuses_user_timeline()  
GET_statuses_mentions()  
 
### statuses
POST_statuses_update()  
POST_statuses_update_with_media(array,file)  
POST_statuses_destroy()
GET_statuses_show()

### secret mail
GET_secret_mails()
GET_secret_mails_sent()
POST_secret_mails_new()
POST_secret_mails_destroy()
GET_secret_mails_show()

### user
GET_users_show()
GET_users_lookup()

### settings
GET_account_verify_credentials()
POST_account_update_profile_image(array,file)
POST_account_update_cover_image(array,file)
POST_account_update_profile()

### follow
POST_friendships_create()
POST_friendships_destroy()
GET_friendships_show()
GET_friendships_lookup()
GET_friends_ids()
GET_followers_ids()
GET_friends_list()
GET_followers_list()

### favorites
GET_favorites()
POST_favorites_create()  
POST_favorites_destroy()  

### spread
POST_statuses_spread()

### Trends
GET_trends_place()

### Search API
function GET_search_voices()
function GET_users_search()
function GET_profile_search()
function GET_search_favorites()

このへんは[Croudia REST API 1.0](http://developer.croudia.com/docs/api10) を見て察してくれ

とりあえずパラメーターは配列にして引数に指定してやったらいいけど`(array,file)`ってなってるやつは`$_FILES`に格納されたのをそのまま使う。`$_FILES[name]`ならfilsにnameを指定してやるなり

ローカルファイル等を扱う際は`$_FILES`に配列突っ込めば使える(?)

```php
$_FILES[$name] = array (
  "name" => "ファイル名",
  "type" => "mime-type(ex. image/png)",
  "tmp_name"=>"ファイルへのパス。",
);  

```

↓これでも大丈夫のはず

```php
POST_statuses_update_with_media(array("status"=>"test"),"c:\\path\\to\\img.png");
```


ここらへんの仕様はころころ変わりそう(今後のマルチファイルうｐ対応の可能性。)
