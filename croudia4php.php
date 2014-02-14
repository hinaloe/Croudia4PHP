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
    public $httphead;
	
	public function __construct($c_id, $c_secret) {
		$this -> client_id = $c_id;
		$this -> client_secret = $c_secret;
	}

    /**
     * GET request
     * 
     * @return  object|array
     * @param   string  $url        request URL
     * @param   array   $paramas    request content
     */
    public function get($url,$params){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
        $opts["http"] = array(
            "method" => "GET",
            "header" => $headers,
            "content" => http_build_query($params),
            "ignore_errors" => true,
        );
        $res = file_get_contents($url, false, stream_context_create($opts));
        $this -> httphead = $http_response_header;
        return json_decode($res);
    }
    
    /**
     * POST request
     * 
     * @return  object|array
     * @param   string  $url        request URL
     * @param   array   $paramas    request content
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
	
	public function getAuthorizeURL(){
		return "https://api.croudia.com/oauth/authorize?response_type=code&client_id=".$this -> client_id;
	}
	
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

	public function refreshAccessToken(){
		$params = array(
			"grant_type" => "refresh_token", 
			"client_id" => $this -> client_id, 
			"client_secret" => $this -> client_secret, 
			"refresh_token" => $this -> refresh_token,
            "ignore_errors" => true,
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
	
	public function GET_statuses_public_timeline($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "GET", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params)
		);
		$res = file_get_contents("https://api.croudia.com/statuses/public_timeline.json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		return json_decode($res);
	}
	
	public function GET_statuses_home_timeline($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "GET", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params)
		);
		$res = file_get_contents("https://api.croudia.com/statuses/home_timeline.json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		return json_decode($res);
	}
	
	public function GET_statuses_user_timeline($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "GET", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params)
		);
		$res = file_get_contents("https://api.croudia.com/statuses/user_timeline.json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		return json_decode($res);
	}
	
	public function GET_statuses_mentions($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "GET", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params)
		);
		$res = file_get_contents("https://api.croudia.com/statuses/mentions.json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		return json_decode($res);
	}
	
	public function POST_statuses_update($params = array()){
		$res = self::post("https://api.croudia.com/statuses/update.json", $params);
		return $res;
	}
	
	
	public function POST_statuses_update_with_media($params = array(),$fname){

		$boundary = '---------------------------'.time();


		
		$data = '';

		foreach($params as $key => $value) {

			$data .= "--$boundary" . "\r\n";

			$data .= 'Content-Disposition: form-data; name="' . $key .'"' . "\r\n" . "\r\n";

			$data .= $value . "\r\n";

		}
		
		//upload_file
		
			$data .= "--$boundary" . "\r\n";

			$data .= sprintf('Content-Disposition: form-data; name="%s"; filename="%s"%s', 'media', $_FILES[$fname]['name'], "\r\n");

			$data .= 'Content-Type: '. $_FILES[$fname]['type'] . "\r\n\r\n";

			$data .= file_get_contents($_FILES[$fname]['tmp_name']) . "\r\n";

		$data .= "--$boundary--" . "\r\n";

		$headers = array(
			"Authorization: Bearer ".$this -> access_token, 
			"Content-type: multipart/form-data; boundary=" . $boundary,
			'Content-Length: '.strlen($data)

		);
		$opts["http"] = array(
			"method" => "POST", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => $data//http_build_query($params)
		);
		
		//var_dump($opts);
		
		$res = @file_get_contents("https://api.croudia.com/statuses/update_with_media.json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		$this -> res = $res;
		return json_decode($res);
	}
	
	public function POST_statuses_destroy($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "POST", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params),
   "ignore_errors" => true
		);
		$id = $params["id"];
		$res = file_get_contents("https://api.croudia.com/statuses/destroy/".$id.".json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		return json_decode($res);
	}
	
	
	public function GET_statuses_show($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "GET", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params)
		);
		$id = $params["id"];
		$res = file_get_contents("https://api.croudia.com/statuses/show/".$id.".json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		return json_decode($res);
	}
	
	public function GET_secret_mails($params = array()){
		$res = self::get("https://api.croudia.com/secret_mails.json", $params);
		return $res;
	}

	public function GET_secret_mails_sent($params = array()){
		$res = self::get("https://api.croudia.com/secret_mails/sent.json", $params);
		return $res;
	}

	public function POST_secret_mails_new($params = array()){
		$res = self::post("https://api.croudia.com/secret_mails/new.json", $params);
		return $res;
	}

	public function POST_secret_mails_destroy($params = array()){
		$id = $params["id"];
		$res = self::post("https://api.croudia.com/secret_mails/destroy/".$id.".json", $params);
		return $res;
	}

	public function GET_secret_mails_show($params = array()){
		$id = $params["id"];
		$res = self::get("https://api.croudia.com/secret_mails/show/".$id.".json", $params);
		return $res;
	}

	public function GET_users_show($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "GET", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params)
		);
		
		$res = file_get_contents("https://api.croudia.com/users/show.json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		return json_decode($res);
	}
	
    public function GET_users_lookup($params = array()){
		$res = self::get("https://api.croudia.com/users/lookup.json", $params);
		return $res;
	}

	public function GET_account_verify_credentials($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "GET", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params)
		);
		
		$res = file_get_contents("https://api.croudia.com/account/verify_credentials.json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		return json_decode($res);
	}

	public function POST_account_update_profile_image($params = array(),$fname){

		$boundary = '---------------------------'.time();
		
		$data = '';

		foreach($params as $key => $value) {

			$data .= "--$boundary" . "\r\n";

			$data .= 'Content-Disposition: form-data; name="' . $key .'"' . "\r\n" . "\r\n";

			$data .= $value . "\r\n";

		}
		
		//upload_file
		
			$data .= "--$boundary" . "\r\n";

			$data .= sprintf('Content-Disposition: form-data; name="%s"; filename="%s"%s', 'image', $_FILES[$fname]['name'], "\r\n");

			$data .= 'Content-Type: '. $_FILES[$fname]['type'] . "\r\n\r\n";

			$data .= file_get_contents($_FILES[$fname]['tmp_name']) . "\r\n";

		$data .= "--$boundary--" . "\r\n";

		$headers = array(
			"Authorization: Bearer ".$this -> access_token, 
			"Content-type: multipart/form-data; boundary=" . $boundary,
			'Content-Length: '.strlen($data)

		);
		$opts["http"] = array(
			"method" => "POST", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => $data//http_build_query($params)
		);
		
		//var_dump($opts);
		
		$res = @file_get_contents("https://api.croudia.com/account/update_profile_image.json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		$this -> res = $res;
		return json_decode($res);
	}

	public function POST_account_update_cover_image($params = array(),$fname){

		$boundary = '---------------------------'.time();
		
		$data = '';

		foreach($params as $key => $value) {

			$data .= "--$boundary" . "\r\n";

			$data .= 'Content-Disposition: form-data; name="' . $key .'"' . "\r\n" . "\r\n";

			$data .= $value . "\r\n";

		}
		
		//upload_file
		
			$data .= "--$boundary" . "\r\n";

			$data .= sprintf('Content-Disposition: form-data; name="%s"; filename="%s"%s', 'image', $_FILES[$fname]['name'], "\r\n");

			$data .= 'Content-Type: '. $_FILES[$fname]['type'] . "\r\n\r\n";

			$data .= file_get_contents($_FILES[$fname]['tmp_name']) . "\r\n";

		$data .= "--$boundary--" . "\r\n";

		$headers = array(
			"Authorization: Bearer ".$this -> access_token, 
			"Content-type: multipart/form-data; boundary=" . $boundary,
			'Content-Length: '.strlen($data)

		);
		$opts["http"] = array(
			"method" => "POST", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => $data//http_build_query($params)
		);
		
		//var_dump($opts);
		
		$res = @file_get_contents("https://api.croudia.com/account/update_cover_image.json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		$this -> res = $res;
		return json_decode($res);
	}

	public function POST_account_update_profile($params = array()){
		$res = self::post("https://api.croudia.com/account/update_profile.json", $params);
		return $res;
	}

	public function POST_friendships_create($params = array()){
		$res = self::post("https://api.croudia.com/friendships/create.json", $params);
		return $res;
	}

	public function POST_friendships_destroy($params = array()){
		$res = self::post("https://api.croudia.com/friendships/destroy.json", $params);
		return $res;
	}

	public function GET_friendships_show($params = array()){
		$res = self::get("https://api.croudia.com/friendships/show.json", $params);
		return $res;
	}

	public function GET_friendships_lookup($params = array()){
		$res = self::get("https://api.croudia.com/friendships/lookup.json", $params);
		return $res;
	}

	public function GET_friends_ids($params = array()){
		$res = self::get("https://api.croudia.com/friends/ids.json", $params);
		return $res;
	}

	public function GET_followers_ids($params = array()){
		$res = self::get("https://api.croudia.com/followers/ids.json", $params);
		return $res;
	}

	public function GET_friends_list($params = array()){
		$res = self::get("https://api.croudia.com/friends/list.json", $params);
		return $res;
	}

	public function GET_followers_list($params = array()){
		$res = self::get("https://api.croudia.com/followers/list.json", $params);
		return $res;
	}


    public function GET_favorites($params = array()){
		$res = self::get("https://api.croudia.com/favorites.json", $params);
		return $res;
    }	
	
	public function POST_favorites_create($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "POST", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params)
		);
		$id = $params["id"];
		$res = file_get_contents("https://api.croudia.com/favorites/create/".$id.".json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		return json_decode($res);
	}
	
	public function POST_favorites_destroy($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "POST", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params)
		);
		$id = $params["id"];
		$res = file_get_contents("https://api.croudia.com/favorites/destroy/".$id.".json", false, stream_context_create($opts));
		$this -> httphead =  $http_response_header;
		return json_decode($res);
	}
    
	public function POST_statuses_spread($params = array()){
		$id = $params["id"];
		$res = self::post("https://api.croudia.com/statuses/spread/".$id.".json", $params);
		return $res;
	}
	
	public function GET_trends_place($params = array()){
		$headers = array(
			"Content-type: application/x-www-form-urlencoded", 
			"Authorization: Bearer ".$this -> access_token
		);
		$opts["http"] = array(
			"method" => "GET", 
			"header"  =>  implode("\r\n", $headers), 
			"content" => http_build_query($params)
		);
		$res = file_get_contents("https://api.croudia.com/trends/place.json", false, stream_context_create($opts));
		$this -> httphead=  $http_response_header;
		return json_decode($res);
	}


	
}