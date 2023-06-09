<?php

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
        curl_close($ch);
		echo 'Twitter error! Please check your configuration.'; exit;
	} else {
		// Decode JSON response
		$json_response = json_decode($response, true);
        curl_close($ch);
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
   	
	return $tweetContainsKeyword;
}
