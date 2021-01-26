<?php
$token = '225392d1-8384-447a-8a5b-e8e373f16f8b';
$postcode = @$_REQUEST['postcode'];
$number = @$_REQUEST['huisnummer'];
// $number = intval(preg_replace('/[^0-9]+/', '', $number));

if (!$number || 0 === preg_match('/^[1-9]{1}[0-9]{3}[\s]{0,1}[a-z]{2}$/i', $postcode)) {
    http_response_code(500);
    return;
}

$uri = sprintf("http://json.api-postcode.nl?postcode=%s&number=%s", $postcode, $number);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $uri);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [sprintf('Token: %s', $token)]);
$serverOutput = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    return;
}

curl_close($curl);
header('Content-Type: application/json');
header("Content-Disposition: inline; filename=\"{$postcode}-{$number}.json\"");

echo $serverOutput;

// curl -H 'Token:225392d1-8384-447a-8a5b-e8e373f16f8b' 'http://json.api-postcode.nl?postcode=1851PJ&number=40'