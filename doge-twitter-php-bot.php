<?php

// Autoload
require_once __DIR__.'/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include config and functions
require('config.inc.php');
require('libraries/functions.php');

// Get tweets
validate_twitter_config($twitter);
$tweet_contains_keyword = check_tweet_contains_keyword($twitter);

// Set and output message
echo $message = ($tweet_contains_keyword === true) ? 
	$twitter['username_to_monitor'] .' tweeted about ' . $twitter['keyword_to_monitor'] . '!' :
	$twitter['username_to_monitor'] .' did not tweet about ' . $twitter['keyword_to_monitor'] . '.';

// Send e-mails if needed
if ($tweet_contains_keyword === true && $mail['enable'] === true) {
	send_mail($mail, $message);
}

// Place ByBit trade if needed
if ($tweet_contains_keyword === true && $bybit['enable'] === true) {
	bybit_order($bybit);
}

