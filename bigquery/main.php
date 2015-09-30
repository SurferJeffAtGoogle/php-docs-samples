<?php

require_once __DIR__.'/vendor/autoload.php';

/**
 * @return Google_Service_Bigquery
 * @throws Exception
 */
function createAuthorizedClient()
{
    $json_credentials_path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
    if (!$json_credentials_path) {
        throw new Exception('Set the environment variable ' .
            'GOOGLE_APPLICATION_CREDENTIALS to the path to your .json file.');
    }
    $contents = file_get_contents($json_credentials_path);
    $json_a = json_decode($contents, true);
    $credentials = new Google_Auth_AssertionCredentials(
        $json_a['client_email'],
        array(Google_Service_Bigquery::BIGQUERY),
        $json_a['private_key']
    );
    $client = new Google_client();
    $client->setAssertionCredentials($credentials);
    if ($client->getAuth()->isAccessTokenExpired()) {
        $client->getAuth()->refreshTokenWithAssertion();
    }
    $service = new Google_Service_Bigquery($client);
    return $service;
}

$client = createAuthorizedClient();