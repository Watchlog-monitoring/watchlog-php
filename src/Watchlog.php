<?php

namespace MetricsTracker;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Watchlog
{
    // Define the URL as a class constant
    private const URL = 'http://localhost:3774';
    private $client;

    // Constructor to initialize the Guzzle HTTP client
    public function __construct()
    {
        $this->client = new Client();
    }

    // Method to send the metric to the server
    private function sendMetric($method, $metric, $value = 1)
    {
        // Ensure the value is numeric
        if (!is_numeric($value)) {
            return;
        }

        // Prepare the data for the request
        $data = [
            'query' => [
                'method' => $method,
                'metric' => $metric,
                'value' => $value,
            ]
        ];

        // Send an asynchronous GET request to the server
        $this->client->requestAsync('GET', self::URL, $data)
            ->then(
                function ($response) {
                    // Handle response if needed
                },
                function ($exception) {
                    // Handle exceptions
                    echo "Error in sendMetric: {$exception->getMessage()}\n";
                }
            )->wait(); // Wait for the asynchronous request to complete
    }

    // Public method to increment a metric
    public function increment($metric, $value = 1)
    {
        $this->sendMetric('increment', $metric, $value);
    }

    // Public method to decrement a metric
    public function decrement($metric, $value = 1)
    {
        $this->sendMetric('decrement', $metric, $value);
    }

    // Public method to set a gauge value for a metric
    public function gauge($metric, $value)
    {
        $this->sendMetric('gauge', $metric, $value);
    }

    // Public method to set a percentage value for a metric
    public function percentage($metric, $value)
    {
        if (is_numeric($value) && $value >= 0 && $value <= 100) {
            $this->sendMetric('percentage', $metric, $value);
        }
    }

    // Public method to set a system byte value for a metric
    public function systembyte($metric, $value)
    {
        $this->sendMetric('systembyte', $metric, $value);
    }
}
