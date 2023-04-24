<?php

function bybit_has_open_position($bybit) {

    $endpoint = '/unified/v3/private/position/list';
    $method = 'GET';
    $params = 'category=linear&symbol=' . $bybit['derivate_symbol_to_buy'];
    $json_response = bybit_request($bybit, $endpoint, $method, $params);

    if ($json_response['retCode'] === 0) {
        if (!empty($json_response['result']['list'])) {
            $open_position = false;
            foreach ($json_response['result']['list'] as $position) {
                if ($position['side'] == 'Buy' && $position['size'] > 0 && $position['positionValue'] > 0) {
                    $open_position = true;
                    break;
                }
            }
            if ($open_position === true) {
                echo '<br /><br />There is already an active position on ByBit. No new order placed.';
                return true;
            }
        }
        return false;

    } else {
        echo '<br /><br />Error checking position on ByBit! Please check your configuration.'; exit;
    }
}

function bybit_get_last_price($bybit) {

    $endpoint = '/derivatives/v3/public/tickers';
    $method = 'GET';
    $params = 'category=linear&symbol=' . $bybit['derivate_symbol_to_buy'];
    $json_response = bybit_request($bybit, $endpoint, $method, $params);

    if ($json_response['retCode'] === 0 &&
        !empty($json_response['result']['list']) &&
        !empty($json_response['result']['list'][0]['lastPrice']) &&
        $json_response['result']['list'][0]['symbol'] == $bybit['derivate_symbol_to_buy']
    ) {
        return $json_response['result']['list'][0]['lastPrice'];

    } else {
        echo '<br /><br />Error checking ticker on ByBit! Please check your configuration.'; exit;
    }
}

function bybit_order($bybit) {

    $last_price = null;
    if (
        ($bybit['stop_loss_percentage'] !== false && $bybit['stop_loss_percentage'] > 0) ||
        ($bybit['take_profit_percentage'] !== false && $bybit['take_profit_percentage'] > 0)
    ) {
        $last_price = bybit_get_last_price($bybit);
    }

    $endpoint = '/unified/v3/private/order/create';
    $method = 'POST';
    $orderLinkId = uniqid();
    $params = '{"category" : "linear", "symbol" : "' . $bybit['derivate_symbol_to_buy'] . '", "side" : "Buy", "orderType" : "Market", "qty" : "' . $bybit['quantity_to_buy'] . '" , "timeInForce" : "GoodTillCancel" , "orderLinkId" : "' . $orderLinkId . '"}';

    if ($bybit['stop_loss_percentage'] !== false && $bybit['stop_loss_percentage'] > 0) {
        $params = str_replace('}', ', "stopLoss" : "' . ($last_price - ($last_price / 100 * $bybit['stop_loss_percentage'])) . '"}', $params);
    }
    if ($bybit['take_profit_percentage'] !== false && $bybit['take_profit_percentage'] > 0) {
        $params = str_replace('}', ', "takeProfit" : "' . ($last_price + ($last_price / 100 * $bybit['take_profit_percentage'])) . '"}', $params);
    }

    $json_response = bybit_request($bybit, $endpoint, $method, $params);

    if ($json_response['retCode'] === 0 &&
		!empty($json_response['result']['orderId']) &&
		$json_response['result']['orderLinkId'] == $orderLinkId
		) {
		echo '<br /><br />ByBit order placed succesfully.';
	} else {
		echo '<br /><br />Error placing order on ByBit! Please check your configuration.'; exit;
	}
}

function bybit_request($bybit, $endpoint, $method, $params) {

    $url = 'https://api.bybit.com';
    $ch = curl_init();

    $timestamp = time() * 1000;
    $params_for_signature = $timestamp . $bybit['api_key'] . "5000" . $params;
    $signature = hash_hmac('sha256', $params_for_signature, $bybit['secret_key']);
    if ($method == 'GET') {
        $endpoint = $endpoint . "?" . $params;
    }

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
    if ($method == 'GET') {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }

    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch) || empty($response)) {
        curl_close($ch);
        echo '<br /><br />Error interacting with ByBit! Please check your configuration.'; exit;
    } else {
        // Decode JSON response
        $json_response = json_decode($response, true);
        curl_close($ch);
        return $json_response;
    }
}
