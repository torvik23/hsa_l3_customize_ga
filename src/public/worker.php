<?php
require_once dirname(__DIR__).'/vendor/autoload.php';

$currencyUri = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange?json&valcode=USD';
$googleAnalyticsUri = 'http://www.google-analytics.com/collect';
$trackingId ='UA-212165674-1';

$client = new \GuzzleHttp\Client();

try {
    $currencyResponse = $client->get($currencyUri);
    if (!$currencyResponse->getBody()) {
        throw new \Exception('Body is empty');
    }

    $content = json_decode($currencyResponse->getBody()->getContents(),true);
    $rateInfo = end($content);
    if (empty($rateInfo)) {
        throw new \Exception('Rate is empty');
    }
    $currentRate = $rateInfo['rate'] ?? 0.0000;
} catch (\Exception $exception) {
    echo sprintf(
        "An error occurred during currency rate request.\n%s",
        $exception->getMessage()
    );
    exit;
}

$formData = [
    'v' => '1',  # API Version.
    'tid' => $trackingId,  # Tracking ID
    'cid' => '765e0be0-6d7c-4f64-89e1-1ba047882791', // Client ID
    't' => 'transaction',  # Event hit type.
    'ti' => time(), // Transaction ID
    'cd1' => 'UAH/USD', //Custom Dimension
    'cm1' => $currentRate, //Custom Metrics
    'ta' => 'UAH/USD', // Transaction Affiliation
    'tr' => $currentRate, // Transaction Revenue
];

try {
    $gaResponse = $client->request('POST', $googleAnalyticsUri, ['form_params' => $formData]);
    echo "\n\nCurrency rate was sent successfully.\n Status Code: " . $gaResponse->getStatusCode() . "\n\n";
} catch (\Exception $exception) {
    echo sprintf(
        "An error occurred during the currency rate update request to Google Analytics.\n%s",
        $exception->getMessage()
    );
}
