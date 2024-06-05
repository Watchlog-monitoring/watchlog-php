<?php

namespace MetricsTracker;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Watchlog
{
    private $url;
    private $client;

    public function __construct($url = 'https://localhost:3775')
    {
        $this->url = $url;
        $this->client = new Client();
    }

    private function sendMetric($method, $metric, $value = 1)
    {
        try {
            if (is_numeric($value)) {
                $data = [
                    'query' => [
                        'method' => $method,
                        'metric' => $metric,
                        'value' => $value,
                    ]
                ];
                $this->client->requestAsync('GET', $this->url, $data)
                    ->then(
                        function ($response) {
                            // Handle response if needed
                        },
                        function ($exception) {
                            echo "Error in sendMetric: {$exception->getMessage()}\n";
                        }
                    )->wait();
            }
        } catch (RequestException $e) {
            echo "Error in sendMetric: {$e->getMessage()}\n";
        }
    }

    public function increment($metric, $value = 1)
    {
        $this->sendMetric('increment', $metric, $value);
    }

    public function decrement($metric, $value = 1)
    {
        $this->sendMetric('decrement', $metric, $value);
    }

    public function gauge($metric, $value)
    {
        $this->sendMetric('gauge', $metric, $value);
    }

    public function percentage($metric, $value)
    {
        if (is_numeric($value) && $value >= 0 && $value <= 100) {
            $this->sendMetric('percentage', $metric, $value);
        }
    }

    public function systembyte($metric, $value)
    {
        $this->sendMetric('systembyte', $metric, $value);
    }
}
