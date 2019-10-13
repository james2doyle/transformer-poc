<?php

namespace App;

use Swoole\Coroutine\Http\Client;

/**
 * Return an instance of a twig renderer
 */
class Callback
{
    /**
     * Make the POST request
     *
     * @param string $url
     * @param string $contentType
     * @param string $output
     *
     */
    public function __invoke(string $url, string $contentType, string $output)
    {
        $urlParts = parse_url($url);
        // couldnt figure out how to access this as a class
        $hostIp = swoole_async_dns_lookup_coro($urlParts['host'], 10);
        $port = isset($urlParts['port']) ? ':' . $urlParts['port'] : '80';

        $cli = new Client($hostIp, (int)$port);
        $cli->setHeaders([
            'Host' => $urlParts['host'],
            'User-Agent' => 'transformer',
            'Accept' => 'text/html,application/xhtml+xml,application/xml',
            'Content-Type' => $contentType,
            'Accept-Encoding' => 'gzip',
        ]);
        // $cli->setDefer();

        $uri = sprintf('/%s?%s', $urlParts['path'], $urlParts['query']);

        $cli->post($uri, $output);
        $cli->close();
    }
}
