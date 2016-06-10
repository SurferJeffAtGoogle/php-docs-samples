<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// create the Silex application
$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__ ];
# Global list to storage messages received by this instance.
$app['messages'] = array();

$app['pubsub'] = function ($app) {
    // Google Client to retry in the event of a 503 Backend Error
    $retryConfig = ['retries' => 2 ];
    $client = new \Google_Client(['retry' => $retryConfig ]);
    $client->useApplicationDefaultCredentials();
    $client->addScope(Google_Service_Pubsub::PUBSUB);
    $pubsub = new \Google_Service_Pubsub($client);

    // Create the topic and subscription on first run.
    $topic = new \Google_Service_Pubsub_Topic();
    $topic->setName($app['pubsub.topic_name']);
    try {
        $createResult = $pubsub->projects_topics->create($topic->getName(),
            $topic);
    } catch (\Google_Service_Exception $e) {
        // 409 is ok.  The topic already exists.
        if ($e->getCode() != 409) throw $e;
    }
    $subscription = new \Google_Service_Pubsub_Subscription();
    $subscription->setName($app['pubsub.subscription_name']);
    $subscription->setTopic($topic->getName());
    $config = new \Google_Service_Pubsub_PushConfig();
    $project_id = $app['google.project_id'];
    $config->setPushEndpoint("https://$project_id.appspot.com/pubsub/push");
    $subscription->setPushConfig($config);
    try {
        $createResult = $pubsub->projects_subscriptions->create(
            $subscription->getName(), $subscription);
    } catch (\Google_Service_Exception $e) {
        // 409 is ok.  The topic already exists.
        if ($e->getCode() != 409) throw $e;
    }
    return $pubsub;
};

$app->get('/', function (Application $app) {
    $pubsub = $app['pubsub'];
    return $app['twig']->render('index.html.twig', [
        'messages' => $app['messages'],
        'posted' => false]);
});

$app->post('/', function (Application $app, Request $request) {
    $messageText = $request->get('payload');
    $message = new Google_Service_Pubsub_PubsubMessage();
    $message->setData(base64_encode($messageText));
    $request = new Google_Service_Pubsub_PublishRequest();
    $request->setMessages([$message]);
    /** @var \Google_Service_Pubsub $pubsub */
    $pubsub = $app['pubsub'];
    $pubsub->projects_topics->publish($app['pubsub.topic_name'], $request);

    return $app['twig']->render('index.html.twig', [
        'messages' => $app['messages'], 'posted' => true]);
});

$app->post('/pubsub/push', function (Application $app, Request $request) {
    if ($request->get('token') != $app['pubsub.verification_token']) {
        return new Response("Invalid Request", Response::HTTP_BAD_REQUEST);
    }
    $envelope = json_decode($request->getContent());
    $payload = base64_decode($envelope['message']['data']);
    array_push($app['messages'], $payload);
    return 'OK';
});


return $app;
