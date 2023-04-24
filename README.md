# doge-twitter-php-bot
Monitors a Twitter account (for example Elon Musk) for tweets about Doge. Can send an e-mail notification or automatically buy Dogecoin on ByBit.

_This script is for educational purposes only_

The script can be run by opening doge-twitter-php-bot.php in your browser.
It is recommended to setup a Cronjob to automate checking Twitter every few minutes.
Complete the configuration in config.inc.php before running the script.

### Twitter

Obtain a **v1.1** Twitter API key as follows:

- Go to https://developer.twitter.com/en/portal/projects-and-apps and create a v1.1 Twitter API key. It is important to not use v2 as that has limited plan for frequently checking tweets.
- In your Twitter project go to the Keys and tokens section. Update config.inc.php with your API Key and Secret, and Access Token and Secret.
- Test by opening doge-twitter-php-bot.php in your browser. It will either output that a tweet containing the keyword was or was not made. Any errors will also be outputted.
- A good way to test the script is by making it temporarily monitor your own twitter account and then tweeting the keyword.

### E-mail

The script can send you e-mail notifications if the keyword was found in a tweet.

- Set $mail['enable'] to true in config.inc.php
- Make sure to enter from and to e-mail addresses.
- SMTP can be used for improved mail delivery.

### ByBit

The script can automatically open a contract on ByBit to long dogecoin with up to 50x leverage.

- Set $bybit['enable'] to true in config.inc.php
- If you do not have an account yet, signup using the referral url: https://www.bybit.com/invite?ref=N617WP for added benefits and a bonus.
- Make sure your ByBit account has derivate trading enabled and is set to 'unified' mode. Your default leverage setting will be used so make sure to set that as well.
- In ByBit go to: API. Create a key with at least the permissions: `Contract - Orders (derivates)` , `Contract - Positions (derivates)` and `Derivates API V3 - Trade` . Update config.inc.php accordingly.

### Cronjob

It is possible to automate the script by creating a cronjob for it:

- For example: `*/5 * * * * /usr/bin/wget -q --spider http{s}://{url}/doge-twitter-php-bot.php >/dev/null 2>&1`. Replace `{s}` and `{url}` with real values.
- In config.inc.php update the $twitter['within_time'] parameter to match the frequency of your cronjob. This prevents duplicate triggers. 

### Composer

The composer directories are fully committed to GIT. Usually the PHP script will work fine out-of-the-box. In case there are errors (for example PHP version mismatch) consider running `composer update`. 

