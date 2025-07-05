<?php

namespace MetricsTracker;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Watchlog
{
    /** @var string آدرس APM agent */
    private string $agentUrl;

    /** @var Client کلاینت Guzzle */
    private Client $client;

    public function __construct()
    {
        // اگر KUBERNETES_SERVICE_HOST ست شده باشد، در کوبرنیتیز اجرا می‌شویم
        $isKubernetes = getenv('KUBERNETES_SERVICE_HOST') !== false;

        // تنظیم آدرس Agent بر اساس محیط
        $this->agentUrl = $isKubernetes
            ? 'http://watchlog-node-agent:3774'
            : 'http://127.0.0.1:3774';

        // در صورت نیاز می‌توانید اینجا base_uri را هم روی client ست کنید:
        // $this->client = new Client(['base_uri' => $this->agentUrl]);
        $this->client = new Client();
    }

    /**
     * درخواست ارسال متریک
     *
     * @param string $method
     * @param string $metric
     * @param mixed  $value
     */
    private function sendMetric(string $method, string $metric, $value = 1): void
    {
        if (!is_numeric($value)) {
            return;
        }

        $options = [
            'query' => [
                'method' => $method,
                'metric' => $metric,
                'value'  => $value,
            ],
            'timeout' => 1, // ثانیه
        ];

        $this->client
            ->requestAsync('GET', $this->agentUrl, $options)
            ->then(
                fn($response) => null,
                fn($exception) => error_log("Watchlog sendMetric error: " . $exception->getMessage())
            )
            ->wait();
    }

    public function increment(string $metric, $value = 1): void
    {
        $this->sendMetric('increment', $metric, $value);
    }

    public function decrement(string $metric, $value = 1): void
    {
        $this->sendMetric('decrement', $metric, $value);
    }

    public function gauge(string $metric, $value): void
    {
        $this->sendMetric('gauge', $metric, $value);
    }

    public function percentage(string $metric, $value): void
    {
        if (is_numeric($value) && $value >= 0 && $value <= 100) {
            $this->sendMetric('percentage', $metric, $value);
        }
    }

    public function systembyte(string $metric, $value): void
    {
        $this->sendMetric('systembyte', $metric, $value);
    }
}
