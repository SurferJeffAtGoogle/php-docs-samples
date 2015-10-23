<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
require_once __DIR__.'/vendor/autoload.php';

/**
 * Create an authorized client that we will use to invoke BigQuery.
 *
 * @return Google_Service_Bigquery
 *
 * @throws Exception
 */
function createAuthorizedClient()
{
    $json_credentials_path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
    if (!$json_credentials_path) {
        throw new Exception('Set the environment variable '.
            'GOOGLE_APPLICATION_CREDENTIALS to the path to your .json file.');
    }
    $contents = file_get_contents($json_credentials_path);
    $json_array = json_decode($contents, true);
    $credentials = new Google_Auth_AssertionCredentials(
        $json_array['client_email'],
        [Google_Service_Bigquery::BIGQUERY],
        $json_array['private_key']
    );
    $client = new Google_Client();
    $client->setAssertionCredentials($credentials);
    if ($client->getAuth()->isAccessTokenExpired()) {
        $client->getAuth()->refreshTokenWithAssertion();
    }
    $service = new Google_Service_Bigquery($client);

    return $service;
}

/**
 * Get all the rows, page by page, for a given job.
 *
 * @return Generator
 */
function getRows(
    Google_Service_Bigquery $bigquery,
    $projectId,
    $jobId,
    $rowsPerPage = null)
{
    $pageToken = null;
    do {
        $page = $bigquery->jobs->getQueryResults($projectId, $jobId, array(
            'pageToken' => $pageToken,
            'maxResults' => $rowsPerPage,
        ));
        $rows = $page->getRows();
        if ($rows) foreach ($rows as $row) yield $row;
        $pageToken = $page->getPageToken();
    } while ($pageToken);
}

/**
 * Use the sychronous API to execute a query.  Returns null if job timed out.
 * @return array|null
 */
function syncQuery(
    Google_Service_Bigquery $bigquery,
    $projectId,
    $queryString,
    $timeout = 10000)
{
    $request = new Google_Service_Bigquery_QueryRequest();
    $request->setQuery($queryString);
    $request->setTimeoutMs($timeout);
    $response = $bigquery->jobs->query($projectId, $request);
    if (!$response->getJobComplete())
        return null;
    return $response->getRows() ? $response->getRows() : array();
}

/**
 * Use the asynchronous API to execute a query.
 *
 * @return Google_Service_Bigquery_Job
 */
function asyncQuery(
    Google_Service_Bigquery $bigquery,
    $projectId,
    $queryString,
    $batch = false)
{
    $query = new Google_Service_Bigquery_JobConfigurationQuery();
    $query->setQuery($queryString);
    $query->setPriority($batch ? 'BATCH' : 'INTERACTIVE');
    $config = new Google_Service_Bigquery_JobConfiguration();
    $config->setQuery($query);
    $job = new Google_Service_Bigquery_Job();
    $job->setConfiguration($config);
    return $bigquery->jobs->insert($projectId, $job);
}

/**
 * Wait until a job completes.
 *
 * @param Google_Service_Bigquery $bigquery
 * @param $projectId
 * @param $jobId
 * @param $intervalMs  How long should we sleep between checks?
 * @return Google_Service_Bigquery_Job
 */
function pollJob(
    Google_Service_Bigquery $bigquery,
    $projectId,
    $jobId,
    $intervalMs)
{
    while (true) {
        $job = $bigquery->jobs->get($projectId, $jobId);
        if ($job->getStatus()->getState() == 'DONE')
            return $job;
        usleep(1000 * $intervalMs);
    }
}

/**
 * List the datasets.  Never return null.
 *
 * @param Google_Service_Bigquery $bigquery
 * @param $projectId
 * @return array
 */
function listDatasets(Google_Service_Bigquery $bigquery, $projectId)
{
    $datasets = $bigquery->datasets->listDatasets($projectId);
    return $datasets->getDatasets() ? $datasets->getDatasets() : array();
}

/**
 * List the projects.  Never return null.
 *
 * @param Google_Service_Bigquery $bigquery
 * @return array
 */
function listProjects(Google_Service_Bigquery $bigquery)
{
    $projects = $bigquery->projects->listProjects();
    return $projects->getProjects() ? $projects->getProjects() : array();
}

