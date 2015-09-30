<?php

require_once __DIR__ . '/vendor/autoload.php';

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
        [Google_Service_Bigquery::BIGQUERY],
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

function executeQuery($querySql, Google_Service_Bigquery $bigquery,
                      $projectId)
{
    $request = new Google_Service_Bigquery_QueryRequest();
    $request->setQuery($querySql);
    $response = $bigquery->jobs->query($projectId, $request);
    return $response->getRows();
}

function printResults($rows)
{
    echo "\nQuery Results:\n------------\n";
    foreach ($rows as $row) {
        foreach ($row['f'] as $field) {
            printf('%-50s', $field['v']);
        }
        printf("\n");
    }
}

function main()
{
    global $argc, $argv;
    $bigquery = createAuthorizedClient();
    $projectId = '';
    if ($projectId) {
    } elseif ($argc > 1) {
        $projectId = $argv[1];
    } else {
        echo "Enter the project ID: ";
        $projectId = trim(fgets(STDIN));
    }
    $querySql = 'SELECT TOP(corpus, 10) as title, COUNT(*) as unique_words ' .
        'FROM [publicdata:samples.shakespeare]';
    $rows = executeQuery($querySql, $bigquery, $projectId);
    printResults($rows);
}

main();