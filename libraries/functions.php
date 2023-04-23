<?php

function validate_twitter_config() {

}


function check_tweet_contains_keyword($twitter) {

	// Generate OAuth 1.0a Authorization header
	$oauth_nonce = md5(uniqid(rand(), true));
	$oauth_timestamp = time();
	$oauth_signature_method = 'HMAC-SHA1';
	$oauth_version = '1.0';
	
	// Build query
	$query = 'from:' . $twitter['username_to_monitor'];
	if ($twitter['include_retweets'] !== true) $query .= ' -filter:nativeretweets -filter:retweets';
	if ($twitter['include_quotes'] !== true) $query .= ' -filter:quote';
	if ($twitter['include_replies'] !== true) $query .= ' -filter:replies';
	if (!empty($twitter['within_time'])) $query .= ' within_time:' . $twitter['within_time'];
	
	$url = 'https://api.twitter.com/1.1/search/tweets.json';
	
	// Authentication
	$oauth_base_string = 'GET&' . rawurlencode($url) . '&'
		. rawurlencode('count=' . $twitter['limit_count']
		. '&oauth_consumer_key=' . $twitter['consumer_key']
		. '&oauth_nonce=' . $oauth_nonce
		. '&oauth_signature_method=' . $oauth_signature_method
		. '&oauth_timestamp=' . $oauth_timestamp
		. '&oauth_token=' . $twitter['access_token']
		. '&oauth_version=' . $oauth_version
		. '&q=' . rawurlencode($query));

	$oauth_signature_key = rawurlencode($twitter['consumer_secret']) . '&' . rawurlencode($twitter['access_token_secret']);
	$oauth_signature = base64_encode(hash_hmac('sha1', $oauth_base_string, $oauth_signature_key, true));

	$oauth_header = 'OAuth oauth_consumer_key="' . rawurlencode($twitter['consumer_key']) . '", '
		. 'oauth_nonce="' . rawurlencode($oauth_nonce) . '", '
		. 'oauth_signature="' . rawurlencode($oauth_signature) . '", '
		. 'oauth_signature_method="' . rawurlencode($oauth_signature_method) . '", '
		. 'oauth_timestamp="' . rawurlencode($oauth_timestamp) . '", '
		. 'oauth_token="' . rawurlencode($twitter['access_token']) . '", '
		. 'oauth_version="' . rawurlencode($oauth_version) . '"';

	$headers[] = 'Authorization: ' . $oauth_header;

	// Initialize cURL session
	$ch = curl_init();

	// Set cURL options
	curl_setopt($ch, CURLOPT_URL, $url . '?q=' . rawurlencode($query) . '&count=' . $twitter['limit_count']);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	// Send GET request
	$response = curl_exec($ch);
	
	// Check for errors
	if (curl_errno($ch) || empty($response) || isset($response['errors'])) {
		echo 'Twitter error! Please check your configuration.'; exit;
	} else {
		// Decode JSON response
		$json_response = json_decode($response, true);
	}
	
	if (!empty($json_response) && !isset($json_response['errors']) && isset($json_response['statuses'])) {
	
		$tweetContainsKeyword = false;
		foreach ($json_response['statuses'] as $tweet) {
            
			// Check if the response contains Dogecoin related tweets
			if (strpos(strtolower($tweet['text']), strtolower($twitter['keyword_to_monitor'])) !== false) {
				$tweetContainsKeyword = true;
        	}
		}
	} else {
		echo 'Twitter error! Please check your configuration.'; exit;
	}
   	
   	curl_close($ch);
   	
	return $tweetContainsKeyword;
}

function send_mail($mail, $message) {
	
	// Mailer
	$mailer = new PHPMailer\PHPMailer\PHPMailer;

	if ($mail['smtp'] == true) {
		$mailer->isSMTP();
		$mailer->SMTPAuth = true;
		$mailer->SMTPSecure = $mail['smtp_secure'];
		$mailer->Host = $mail['smtp_host'];
		$mailer->Port = $mail['smtp_port'];
		$mailer->Username = $mail['smtp_username'];
		$mailer->Password = $mail['smtp_password'];
	}
	$mailer->Timeout = 10;

	$mailer->From = $mail['from'];
	$mailer->addAddress($mail['to']);

	$mailer->WordWrap = 70;
	$mailer->Subject = $message;
	$mailer->Body = $message . '

---
This is an automated e-mail from your Twitter bot';

	// send mail
	if ($mailer->send()) {
		echo '<br /><br />E-mail sent succesfully.';
	} else {
		echo '<br /><br />Error sending e-mail! Please check your configuration.';
	}
}

function bybit_order($bybit) {

	$url = 'https://api.bybit.com';
	$ch = curl_init();

	$endpoint = '/contract/v3/private/order/create';
	$method = 'POST';
	$orderLinkId = uniqid();
	$params = '{"symbol" : "' . $bybit['derivate_symbol_to_buy'] . '", "side" : "Buy", "orderType" : "Market", "qty" : "' . $bybit['quantity_to_buy'] . '" , "timeInForce" : "GoodTillCancel" , "orderLinkId" : "' . $orderLinkId . '"}';

    $timestamp = time() * 1000;
    $params_for_signature = $timestamp . $bybit['api_key'] . "5000" . $params;
    $signature = hash_hmac('sha256', $params_for_signature, $bybit['secret_key']);
    
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url . $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_HTTPHEADER => array(
          "X-BAPI-API-KEY: " . $bybit['api_key'],
          "X-BAPI-SIGN: " . $signature,
          "X-BAPI-SIGN-TYPE: 2",
          "X-BAPI-TIMESTAMP: " . $timestamp,
          "X-BAPI-RECV-WINDOW: 5000",
          "Content-Type: application/json"
        ),
    ));
    
    $response = curl_exec($ch);
	
	// Check for errors
	if (curl_errno($ch) || empty($response)) {
		echo '<br /><br />Error placing order on ByBit! Please check your configuration.'; exit;
	} else {
		// Decode JSON response
		$json_response = json_decode($response, true);
	}
	
	if ($json_response['retMsg'] == 'OK' && 
		!empty($json_response['result']['orderId']) && 
		$json_response['result']['orderLinkId'] == $orderLinkId
		) {
		echo '<br /><br />ByBit order placed succesfully.';
	} else {
		echo '<br /><br />Error placing order on ByBit! Please check your configuration.'; exit;
	}
	
    curl_close($ch);
}
