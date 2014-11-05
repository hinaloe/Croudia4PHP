<?php

/**
 * Croudia4PHP
 *
 * @author  ikr7
 * @package Croudia4PHP
 */

class Croudia4PHP {
    /**
     * Options
     *
     * @var string
     * @var string
     * @var string
     * @var string
     */
        private $client_id;
        private $client_secret;
        private $access_token;
        private $refresh_token;
        public  $httphead;

        public function __construct($client_id, $client_secret) {
                $this -> client_id = $client_id;
                $this -> client_secret = $client_secret;
        }

    /**
     * GET request
     *
     * @return  object|array
     * @param   string  $url        request URL
     * @param   array   $params    request content
     */
    public function get($url,$params){
        $headers = array(
          "Content-type: application/x-www-form-urlencoded",
          "Authorization: Bearer ".$this -> access_token
        );
        $opts["http"] = array(
          "method" => "GET",
          "header" => $headers,
        //  "content" => http_build_query($params),
          "ignore_errors" => true,
        );
        $url .= $params ?  "?".http_build_query($params) : "" ;
        $res = file_get_contents($url, false, stream_context_create($opts));
        $this -> httphead = $http_response_header;
        return json_decode($res);
    }

    /**
     * POST request
     *
     * @return  object|array
     * @param   string  $url        request URL
     * @param   array   $params    request content
     */
    public function post($url,$params){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded",
			"Authorization: Bearer ".$this -> access_token
		);
        $opts["http"] = array(
            "method" => "POST",
            "header" => $headers,
            "content" => http_build_query($params),
            "ignore_errors" => true,
        );
        $res = file_get_contents($url, false, stream_context_create($opts));
        $this -> httphead = $http_response_header;
        return json_decode($res);
    }

    /**
     * POST requrest with media
     *
     * @param string $url
     * @param array $params
     * @param string|array $media
     * @param string|array $media_to
     * @return array|stdClass $res
     */
    public function post_with_media($url,array $params,$media,$media_to){
        $boundary = '--------------------' . sha1(mt_rand() . microtime());
        $medias = array();
        if(is_string($media)){
            if(isset($_FILES[$media])){
                $medias[0] = array(
                    "filename" => $_FILES[$media]["name"],
                    "type" => $_FILES[$media]["type"],
                    "path" => $_FILES[$media]["tmp_name"],
                    "name" => $media_to,
                );

            }else{
                $medias[0] = array(
                    "path" => $media,
                    "type" => "application/octet-stream",
                    "name" => $media_to,
                );
                $medias["filename"] = md5(mt_rand() . microtime());
            }

        }elseif(is_string($media["tmp_name"])){
            $medias[0] = array(
                "filename" => $media["name"],
                "type" => $media["type"],
                "path" => $media["tmp_name"],
                "name" => $media_to,
            );

        }elseif(is_array($media)){
            foreach($media as $key => $value){
                if(is_string($value)){
                    if(is_array($media_to)){
                        if(isset($media_to[$key])){
                            $name = $media_to[$key];
                        }else{
                            $name = reset($media_to);
                        }
                    }elseif(is_string($media_to)){
                        $name = $media_to;
                    }else{
                        $name = "media";
                    }
                    $medias[] = array(
                        "path" => $value,
                        "type" => "application/octet-stream",
                        "filename" => md5(mt_rand() . microtime()),
                        "name" => $name,
                    );

                }elseif(is_array($value)){
                    if(isset($value["tmp_name"])){
                        if(is_array($media_to)){
                            if(isset($media_to[$key])){
                                $name = $media_to[$key];
                            }else{
                                $name = reset($media_to);
                            }
                        }elseif(is_string($media_to)){
                            $name = $media_to;
                        }else{
                            $name = "media";
                        }
                        $medias[] = array(
                            "path" => $value["tmp_name"],
                            "type" => $value["type"],
                            "filename" => $value["name"],
                            "name" => $name,
                        );
                    }elseif(isset($value["path"])){
                        $medias[] = array(
                            "path" => $value["path"],
                            "type" => "application/octet-stream",
                            "filename" => md5(mt_rand() . microtime()),
                            "name" => $value["name"],
                        );
                    }
                }
            }
        }
        $lines = array();
        foreach($params as $key => $value) {
            array_push(
                $lines,
                "--{$boundary}",
                "Content-Disposition: form-data; name=\"{$key}\"",
                "Content-Type: application/octet-stream",
                "",
                $value
            );

        }
        foreach($medias as $key => $value) {
            $content = @file_get_contents($value["path"]);
            array_push(
                $lines,
                "--{$boundary}",
                "Content-Disposition: form-data; name=\"" . $value['name'] . "\"; filename=\"" . $value['filename'] . "\"",
                "Content-Type: " . $value['type'],
                "",
                $content
            );
        }
        $lines[] = "--{$boundary}--";
        $data = implode("\r\n", $lines);

        $headers = array(
                        "Authorization: Bearer ".$this -> access_token,
                        "Content-type: multipart/form-data; boundary=" . $boundary,
                        'Content-Length: '.strlen($data)

        );
        $opts["http"] = array(
                "method" => "POST",
                "header"  =>  implode("\r\n", $headers),
                "content" => $data,
                "ignore_errors" => true,
        );

        $res = file_get_contents($url, false, stream_context_create($opts));
        $this -> httphead =  $http_response_header;
		return json_decode($res);
    }

        /**
         * Get authorize url
         *
         * @return string   authorize URL
         *
         */
        public function getAuthorizeURL(){
                return "https://api.croudia.com/oauth/authorize?response_type=code&client_id=".$this -> client_id;
        }
        /**
         * set Access Token
         *
         * @return string   access_token
         * @param string $code authorization_code
         */
        public function setAccessToken($code){
                $params = array(
                        "grant_type" => "authorization_code",
                        "client_id" => $this -> client_id,
                        "client_secret" => $this -> client_secret,
                        "code" => $code
                );

                $opts["http"] = array(
                        "method" => "POST",
                        "header"  => "Content-type: application/x-www-form-urlencoded",
                        "content" => http_build_query($params),
                        "ignore_errors" => true,

                );

                $res = file_get_contents("https://api.croudia.com/oauth/token", false, stream_context_create($opts));
                if(isset( json_decode($res) ->error)){
                        return json_decode($res);
                }
                $access_token = json_decode($res)  -> access_token;
                $refresh_token = json_decode($res)  -> refresh_token;
                $this -> access_token = $access_token;
                $this -> refresh_token = $refresh_token;
                return $this -> access_token;
        }

        /**
         * refresh Access Token
         *
         * @return string access_token
         *
         */
        public function refreshAccessToken(){
                $params = array(
                        "grant_type" => "refresh_token",
                        "client_id" => $this -> client_id,
                        "client_secret" => $this -> client_secret,
                        "refresh_token" => $this -> refresh_token,
                );

                $opts["http"] = array(
                        "method" => "POST",
                        "header"  => "Content-type: application/x-www-form-urlencoded",
                        "content" => http_build_query($params)
                );

                $res = file_get_contents("https://api.croudia.com/oauth/token", false, stream_context_create($opts));
                $access_token = json_decode($res)  -> access_token;
                $refresh_token = json_decode($res)  -> refresh_token;
                $this -> access_token = $access_token;
                $this -> refresh_token = $refresh_token;
                return $this -> access_token;
        }

        /**
         *  public timeline
         *
         * @param array $params
         * @return array $res
         *
         * @link https://developer.croudia.com/docs/01_statuses_public_timeline
         */
        public function GET_statuses_public_timeline($params = array()){
                $res = self::get("https://api.croudia.com/statuses/public_timeline.json", $params);
                return $res;
        }

        /**
         *  home timeline
         *
         * @param array $params
         * @return array $res
         *
         * @link https://developer.croudia.com/docs/02_statuses_home_timeline
         */
        public function GET_statuses_home_timeline($params = array()){
                $res = self::get("https://api.croudia.com/statuses/home_timeline.json", $params);
                return $res;
        }

        /**
         * Get User Timeline
         *
         * @param array $params
         * @return array $res
         *
         * @link https://developer.croudia.com/docs/03_statuses_user_timeline
         */
        public function GET_statuses_user_timeline($params = array()){
                $res = self::get("https://api.croudia.com/statuses/user_timeline.json", $params);
                return $res;
        }

        /**
         * Mentions
         *
         * @param array $params
         * @return array $res
         *
         * @link https://developer.croudia.com/docs/04_statuses_mentions
         */
        public function GET_statuses_mentions($params = array()){
                $res = self::get("https://api.croudia.com/statuses/mentions.json", $params);
                return $res;
        }

        /**
         * Update status
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/11_statuses_update
         */
        public function POST_statuses_update($params = array()){
                $res = self::post("https://api.croudia.com/statuses/update.json", $params);
                return $res;
        }

        /**
         * update status with media
         *
         * @param array $params
         * @param  array|string $fname filename
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/14_statuses_update_with_media
         */
        public function POST_statuses_update_with_media($params = array(),$fname){
                $res = self::post_with_media("https://api.croudia.com/statuses/update_with_media.json", $params, $fname , "media");
                return $res;
        }

        /**
         * destroy status
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/12_statuses_destroy
         */
        public function POST_statuses_destroy($params = array()){
                $id = $params["id"];
                $res = self::post("https://api.croudia.com/statuses/destroy/".$id.".json", $params);
                return $res;
        }


        /**
         * Retrieve a status
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/13_statuses_show
         */
        public function GET_statuses_show($params = array()){
                $id = $params["id"];
                $res = self::get("https://api.croudia.com/statuses/show/".$id.".json", $params);
                return $res;
        }

        /**
         * Get incoming secret mails
         *
         * @param array $params
         * @return array $res
         *
         * @link https://developer.croudia.com/docs/21_secret_mails
         */
        public function GET_secret_mails($params = array()){
                $res = self::get("https://api.croudia.com/secret_mails.json", $params);
                return $res;
        }

        /**
         * Get outgoing secret mails
         *
         * @param array $params
         * @return array $res
         *
         * @link https://developer.croudia.com/docs/22_secret_mails_sent
         */
        public function GET_secret_mails_sent($params = array()){
                $res = self::get("https://api.croudia.com/secret_mails/sent.json", $params);
                return $res;
        }

        /**
         * Send a new secret mail
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/23_secret_mails_new
         */
        public function POST_secret_mails_new($params = array()){
                $res = self::post("https://api.croudia.com/secret_mails/new.json", $params);
                return $res;
        }

        /**
         * Send a new secret mail with media
         *
         * @param array $params
         * @param  array|string $fname filename
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/26_secret_mails_new_with_media
         */
        public function POST_secret_mails_new_with_media($params = array(),$fname){
                $res = self::post_with_media("https://api.croudia.com/secret_mails/new_with_media.json", $params, $fname , "media");
                return $res;
        }


        /**
         * destroy a secret mail
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/24_secret_mails_destroy
         */
        public function POST_secret_mails_destroy($params = array()){
                $id = $params["id"];
                $res = self::post("https://api.croudia.com/secret_mails/destroy/".$id.".json", $params);
                return $res;
        }

        /**
         * Get a secret mail
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/25_secret_mails_show
         */
        public function GET_secret_mails_show($params = array()){
                $id = $params["id"];
                $res = self::get("https://api.croudia.com/secret_mails/show/".$id.".json", $params);
                return $res;
        }

        /**
         * Retrieve a user
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/31_users_show
         */
        public function GET_users_show($params = array()){
                $res = self::get("https://api.croudia.com/users/show.json", $params);
                return $res;
        }

        /**
         * Lookup Users
         *
         * @param array $params
         * @return array $res
         *
         * @link https://developer.croudia.com/docs/32_users_lookup
         */
        public function GET_users_lookup($params = array()){
                $res = self::get("https://api.croudia.com/users/lookup.json", $params);
                return $res;
        }

        /**
         * Retrieve the Authenticated User
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/35_account_verify_credentials
         */
        public function GET_account_verify_credentials($params = array()){
                $res = self::get("https://api.croudia.com/account/verify_credentials.json", $params);
                return $res;
        }

        /**
         * Update profile image
         *
         * @param array $params
         * @param array|String $fname
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/36_account_update_profile_image
         */
        public function POST_account_update_profile_image($params = array(),$fname){
                $res = self::post_with_media("https://api.croudia.com/account/update_profile_image.json", $params, $fname , "image");
                return $res;
        }

        /**
         * Update cover image
         *
         * @param array $params
         * @param array|String $fname
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/39_account_update_cover_image
         */
        public function POST_account_update_cover_image($params = array(),$fname){
                $res = self::post_with_media("https://api.croudia.com/account/update_cover_image.json", $params, $fname , "image");
                return $res;
        }

        /**
         * Update profile
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/37_account_update_profile
         */
        public function POST_account_update_profile($params = array()){
                $res = self::post("https://api.croudia.com/account/update_profile.json", $params);
                return $res;
        }

        /**
         * Follow a user
         *
         * @param array $params
         * @return stdClass
         *
         * @link https://developer.croudia.com/docs/41_friendships_create
         */
        public function POST_friendships_create($params = array()){
                $res = self::post("https://api.croudia.com/friendships/create.json", $params);
                return $res;
        }

        /**
         * Unfollow a user
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/42_friendships_destroy
         */
        public function POST_friendships_destroy($params = array()){
                $res = self::post("https://api.croudia.com/friendships/destroy.json", $params);
                return $res;
        }

        /**
         * Show relationship between two users
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/44_friendships_show
         */
        public function GET_friendships_show($params = array()){
                $res = self::get("https://api.croudia.com/friendships/show.json", $params);
                return $res;
        }

        /**
         * Lookup friendships between the current user and others
         *
         * @param array $params
         * @return array $res
         *
         * @link https://developer.croudia.com/docs/47_friendships_lookup
         */
        public function GET_friendships_lookup($params = array()){
                $res = self::get("https://api.croudia.com/friendships/lookup.json", $params);
                return $res;
        }

        /**
         * Friend ids of specified user
         *
         * @param array $params
         * @return stdClass $array
         *
         * @link https://developer.croudia.com/docs/48_friends_ids
         */
        public function GET_friends_ids($params = array()){
                $res = self::get("https://api.croudia.com/friends/ids.json", $params);
                return $res;
        }

        /**
         * Follower ids of specified user
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/49_followers_ids
         */
        public function GET_followers_ids($params = array()){
                $res = self::get("https://api.croudia.com/followers/ids.json", $params);
                return $res;
        }

        /**
         * Friends of specified user
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/40_friends_list
         */
        public function GET_friends_list($params = array()){
                $res = self::get("https://api.croudia.com/friends/list.json", $params);
                return $res;
        }

        /**
         * Followers of specified user
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/43_followers_list
         */
        public function GET_followers_list($params = array()){
                $res = self::get("https://api.croudia.com/followers/list.json", $params);
                return $res;
        }


        /**
         * List of favorited statuses
         *
         * @param array $params
         * @return array $res
         *
         * @link https://developer.croudia.com/docs/51_favorites
         */
        public function GET_favorites($params = array()){
                $res = self::get("https://api.croudia.com/favorites.json", $params);
                return $res;
        }

        /**
         * Favorite a status
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/52_favorites_create
         */
        public function POST_favorites_create($params = array()){
                $id = $params["id"];
                $res = self::post("https://api.croudia.com/favorites/create/".$id.".json", $params);
                return $res;
        }

        /**
         * Unfavorite a status
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/53_favorites_destroy
         */
        public function POST_favorites_destroy($params = array()){
                $id = $params["id"];
                $res = self::post("https://api.croudia.com/favorites/destroy/".$id.".json", $params);
                return $res;
        }

        /**
         * Spread a status
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/61_statuses_spread
         */
        public function POST_statuses_spread($params = array()){
                $id = $params["id"];
                $res = self::post("https://api.croudia.com/statuses/spread/".$id.".json", $params);
                return $res;
        }

        /**
         * Share (formerly reply with quote) a status
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/110_statuses_share
         */
        public function POST_statuses_share($params = array()){
                $res = self::post("https://api.croudia.com/statuses/share.json", $params);
                return $res;
        }

        /**
         * Share (formerly reply with quote) a status with media
         *
         * @param array $params
         * @param array|String $fname filename
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/111_statuses_share_with_media
         */
        public function POST_statuses_share_with_media($params = array(),$fname){
                $res = self::post_with_media("https://api.croudia.com/statuses/share_with_media.json", $params, $fname , "media");
                return $res;
        }



        /**
         * Retrieve the current trends
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/100_trends_place
         */
        public function GET_trends_place($params = array()){
                $res = self::get("https://api.croudia.com/trends/place.json", $params);
                return $res;
        }

        /**
         * mute the specified user
         *
         * @param array $params
         * @return stdClass $res UserObject
         *
         * @link https://developer.croudia.com/docs/71_mutes_users_create
         */
        public function POST_mutes_users_create ($params = array()){
          return self::post("https://api.croudia.com/mutes/users/create.json", $params);
        }

        /**
         * unmute the specified user
         *
         * @param array $params
         * @return stdClass UserObject
         *
         * @link https://developer.croudia.com/docs/72_mutes_users_destroy
         */
        public function POST_mutes_users_destroy ( $params = array() ){
          return self::post("https://api.croudia.com/mutes/users/destroy.json", $params);
        }

        /**
         *
         *
         * @param array $params
         * @return stdClass
         *
         * @link https://developer.croudia.com/docs/74_mutes_users_list
         */
        public function GET_mutes_users_list ( $params = array() ){
          return self::get("https://api.croudia.com/mutes/users/list.json", $params);
        }

        /**
         *
         *
         * @param array $params
         * @return stdClass
         *
         * @link https://developer.croudia.com/docs/75_mutes_users_ids
         */
        public function GET_mutes_users_ids ( $params = array() ){
          return self::get("https://api.croudia.com/mutes/users/ids.json", $params);
        }

        /**
         * Search for statuses
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/81_search
         */
        public function GET_search_voices($params = array()){
                $res = self::get("https://api.croudia.com/search/voices.json", $params);
                return $res;
        }

        /**
         * Search for users
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/82_users_search
         */
        public function GET_users_search($params = array()){
                $res = self::get("https://api.croudia.com/users/search.json", $params);
                return $res;
        }

        /**
         * Search for users with profile
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/83_profile_search
         */
        public function GET_profile_search($params = array()){
                $res = self::get("https://api.croudia.com/profile/search.json", $params);
                return $res;
        }

        /**
         * Search for statuses the authenticated user has favorited
         *
         * @param array $params
         * @return stdClass $res
         *
         * @link https://developer.croudia.com/docs/84_search_favorites
         */
        public function GET_search_favorites($params = array()){
                $res = self::get("https://api.croudia.com/search/favorites.json", $params);
                return $res;
        }


}
