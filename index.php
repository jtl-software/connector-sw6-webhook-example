<?php

const TOKEN = 'token';

function generateHmac($data, $key)
{
    $hash = hash_hmac('sha256', $data, $key, true);
    return base64_encode($hash);
}

function modifyData($data)
{
    $data = json_decode($data, true);

    list($controller, $action) = \explode('.', $data['method']);
    if (\strtolower($controller) === 'product') {
        if (\strtolower($action) === 'pull') {
            foreach ($data['params'] as $key => $product) {
                foreach ($product['i18ns'] as $i18nKey => $i18n) {
                    $data['params'][$key]['i18ns'][$i18nKey]['name'] = 'Modified Pull: ' . $i18n['name'];
                }
            }
        } elseif (\strtolower($action) === 'push') {
            foreach ($data['params'] as $key => $product) {
                foreach ($product['i18ns'] as $i18nKey => $i18n) {
                    $data['params'][$key]['i18ns'][$i18nKey]['name'] = 'Modified Push: ' . $i18n['name'];
                    $data['params'][$key]['i18ns'][$i18nKey]['description'] = 'Modified Push: foo bar';
                }
            }
        }
    }
    return json_encode($data);
}


$hmac = $_SERVER['HTTP_X_CONNECTOR_HMAC_SHA256'];

$data = $_REQUEST['jtlrpc'];
$generatedHmac = generateHmac($data, TOKEN);

if (!hash_equals($hmac, $generatedHmac)) {
    echo 'Invalid HMAC';
    http_response_code(401);
    exit;
}

$data = modifyData($data);

$newHmac = generateHmac($data, TOKEN);

header('X-CONNECTOR-HMAC-SHA256: ' . $newHmac);
header('Content-Type: application/json');
echo $data;
