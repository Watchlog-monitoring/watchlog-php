<?php

namespace MetricsTracker;

use GuzzleHttp\Client;

class Watchlog
{
    private Client $client;
    private string $agentUrl;
    private static ?bool $isK8s = null;
    private static ?string $cachedUrl = null;

    public function __construct()
    {
        $this->client   = new Client();
        $this->agentUrl = self::getServerUrl();
    }

    private static function isRunningInK8s(): bool
    {
        if (self::$isK8s !== null) {
            return self::$isK8s;
        }

        $tokenPath = '/var/run/secrets/kubernetes.io/serviceaccount/token';
        if (is_file($tokenPath)) {
            return self::$isK8s = true;
        }

        if (is_readable('/proc/1/cgroup')) {
            $content = @file_get_contents('/proc/1/cgroup');
            if (strpos($content, 'kubepods') !== false) {
                return self::$isK8s = true;
            }
        }

        $ip = @gethostbyname('kubernetes.default.svc.cluster.local');
        if ($ip !== 'kubernetes.default.svc.cluster.local' && filter_var($ip, FILTER_VALIDATE_IP)) {
            return self::$isK8s = true;
        }

        return self::$isK8s = false;
    }

    private static function getServerUrl(): string
    {
        if (self::$cachedUrl !== null) {
            return self::$cachedUrl;
        }

        self::$cachedUrl = self::isRunningInK8s()
            ? 'http://watchlog-node-agent.monitoring.svc.cluster.local:3774'
            : 'http://127.0.0.1:3774';

        return self::$cachedUrl;
    }

    private function sendMetric(string $method, string $metric, $value = 1): void
    {
        if (!is_numeric($value) || $metric === '') {
            return;
        }

        $options = [
            'query'   => [
                'method' => $method,
                'metric' => $metric,
                'value'  => $value,
            ],
            'timeout' => 1,
        ];

        // ارسال async بدون لاگ خطا
        $this->client
            ->requestAsync('GET', $this->agentUrl, $options)
            ->then(fn($response) => null, fn($exception) => null);
    }

    public function increment(string $metric, $value = 1): void
    {
        if ($value > 0) {
            $this->sendMetric('increment', $metric, $value);
        }
    }

    public function decrement(string $metric, $value = 1): void
    {
        if ($value > 0) {
            $this->sendMetric('decrement', $metric, $value);
        }
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
        if (is_numeric($value) && $value > 0) {
            $this->sendMetric('systembyte', $metric, $value);
        }
    }
}
