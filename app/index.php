<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Swoole\Http\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

$host = getenv('HOST');
$hostname = getenv('HOSTNAME');
$port = getenv('PORT');

// be local if there is no ENV
if (getenv('ENV') === false) {
    putenv('ENV=local');
}

$server = new Server($host, $port);

// a swoole server is evented just like express
$server->on('start', function (Server $server) use ($hostname, $port) {
    echo sprintf('Swoole http server is started at http://%s:%s' . PHP_EOL, $hostname, $port);
});

// handle all requests with this response
$server->on('request', function (Request $request, Response $response) use ($twig) {
    $request_method = $request->server['request_method'];

    if ($request_method !== 'POST') {
        $response->header('Content-Type', 'application/json');
        $response->header('Status', '405 Method Not Allowed');
        return $response->end(json_encode([
            'data' => [
                'error' => 'Method Not Allowed',
                'message' => 'Only POST requests are allowed.',
            ],
        ]));
    }

    $request_uri = $request->server['request_uri'];

    // populate the global state with the request info
    $_SERVER['REQUEST_URI'] = $request_uri;
    $_SERVER['REQUEST_METHOD'] = $request_method;
    $_SERVER['REMOTE_ADDR'] = $request->server['remote_addr'];
    $_GET = $request->get ?? [];
    $_FILES = $request->files ?? [];
    $_POST = $request->post ?? [];

    // form-data and x-www-form-urlencoded work out of the box so we handle JSON POST here
    $isJson = $request->header['content-type'] === 'application/json';
    if ($isJson) {
        $body = $request->rawContent();
        $_POST = empty($body) ? [] : json_decode($body, true);
    }

    $_GET = array_merge([
        'template' => 'json',
        'content-type' => 'json',
    ], $_GET);

    $template = $_GET['template'] ?? 'json';
    $contentType = $_GET['content-type'] ?? 'application/json';

    $response->header('Status', '200 OK');
    $response->header('Content-Type', $contentType);

    $output = (new Render)($template . '.twig', $_POST);

    $url = $_GET['callback'] ?? null;
    if ($url) {
        // use a coroutine for the HTTP request
        go(function () use ($url, $contentType, $output) {
            (new Callback)($url, $contentType, $output);
        });
    }

    // write the JSON string out
    $response->end($output);
});

$server->on('close', function(Server $server, $connectionId) {
    echo 'connection close: ' . $connectionId . PHP_EOL;
});

$server->start();
