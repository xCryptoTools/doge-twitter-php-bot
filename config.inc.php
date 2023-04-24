<?php

/**
 * Doge Twitter PHP bot configuration.
 * Make sure to complete the config before running the script.
 */

declare(strict_types=1);

/************************************************************
 * 
 * Twitter config
 * 
 ************************************************************/

// Replace these with your own Twitter API v1.1 credentials
// See README for instructions
$twitter['consumer_key'] = '';
$twitter['consumer_secret'] = '';
$twitter['access_token'] = '';
$twitter['access_token_secret'] = '';

// Username and keyword to monitor
$twitter['username_to_monitor'] = 'elonmusk';
$twitter['keyword_to_monitor'] = 'doge';

// Tweet filters
$twitter['include_retweets'] = false; // Include retweets? [true/false]
$twitter['include_quotes'] = false; // Include quotes? [true/false]
$twitter['include_replies'] = false; // Include replies? [true/false]

$twitter['within_time'] = '60min'; // Example: '10min'. Can be set to false.
$twitter['limit_count'] = 10; // Limit amount of tweets. Example: 1


/************************************************************
 * 
 * E-mail config
 * 
 ************************************************************/

$mail['enable'] = false; // Send an e-mail when tweet trigger is met? [true/false]

$mail['to'] = ''; // Send e-mail to this address
$mail['from'] = ''; // Sender e-mail address, make sure this matches server/smtp to prevent spam marking

$mail['smtp'] = true; // Set to true to use SMTP, set to false for php mail
// If smtp is true, configure the following:
$mail['smtp_secure'] = 'tls'; // Usually 'tls'
$mail['smtp_host'] = 'smtp.gmail.com';
$mail['smtp_port'] = 587;
$mail['smtp_username'] = '';
$mail['smtp_password'] = '';


/************************************************************
 * 
 * ByBit config
 * 
 ************************************************************/

$bybit['enable'] = false; // Auto buy on ByBit if trigger is met? [true/false]

// ByBit API v3 credentials
// See README for instructions
$bybit['api_key'] = '';
$bybit['secret_key'] = '';

// Symbol and quantity to buy
$bybit['derivate_symbol_to_buy'] = 'DOGEUSDT'; // Symbol to buy
$bybit['quantity_to_buy'] = 1000;

$bybit['only_buy_if_not_active'] = true; // Only buy if there is no active trade of this ticker. Can prevent double trades.

