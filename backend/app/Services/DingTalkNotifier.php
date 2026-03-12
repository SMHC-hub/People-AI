<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DingTalkNotifier
{
    protected string $webhookUrl;

    protected ?string $secret;

    public function __construct()
    {
        $this->webhookUrl = config('services.dingtalk.webhook_url', '');
        $this->secret = config('services.dingtalk.secret');
    }

    /**
     * Send a text message via DingTalk webhook.
     */
    public function sendText(string $content, array $atMobiles = [], bool $isAtAll = false): bool
    {
        return $this->send([
            'msgtype' => 'text',
            'text' => [
                'content' => $content,
            ],
            'at' => [
                'atMobiles' => $atMobiles,
                'isAtAll' => $isAtAll,
            ],
        ]);
    }

    /**
     * Send a markdown message via DingTalk webhook.
     */
    public function sendMarkdown(string $title, string $text, array $atMobiles = [], bool $isAtAll = false): bool
    {
        return $this->send([
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => $title,
                'text' => $text,
            ],
            'at' => [
                'atMobiles' => $atMobiles,
                'isAtAll' => $isAtAll,
            ],
        ]);
    }

    /**
     * Send an action card message via DingTalk webhook.
     */
    public function sendActionCard(string $title, string $text, string $singleTitle, string $singleURL): bool
    {
        return $this->send([
            'msgtype' => 'actionCard',
            'actionCard' => [
                'title' => $title,
                'text' => $text,
                'singleTitle' => $singleTitle,
                'singleURL' => $singleURL,
            ],
        ]);
    }

    /**
     * Send a notification about attendance anomalies.
     */
    public function notifyAnomalies(array $anomalies): bool
    {
        $count = count($anomalies);
        $title = "Attendance Anomaly Alert";
        $text = "## {$title}\n\n";
        $text .= "**{$count} anomalies detected**\n\n";

        foreach (array_slice($anomalies, 0, 5) as $anomaly) {
            $text .= "- Employee: {$anomaly['employee_name']}, Date: {$anomaly['date']}, Score: {$anomaly['score']}\n";
        }

        if ($count > 5) {
            $text .= "\n... and ".($count - 5)." more\n";
        }

        return $this->sendMarkdown($title, $text, [], false);
    }

    /**
     * Send a notification about a generated report.
     */
    public function notifyReportGenerated(string $reportTitle, string $downloadUrl): bool
    {
        return $this->sendActionCard(
            'Report Generated',
            "## Report Ready\n\nThe report **{$reportTitle}** has been generated and is ready for download.",
            'Download Report',
            $downloadUrl
        );
    }

    /**
     * Send the webhook request.
     */
    protected function send(array $payload): bool
    {
        if (empty($this->webhookUrl)) {
            Log::warning('DingTalk webhook URL is not configured');

            return false;
        }

        $url = $this->buildUrl();

        try {
            $response = Http::timeout(10)
                ->post($url, $payload);

            if ($response->successful() && $response->json('errcode') === 0) {
                return true;
            }

            Log::error('DingTalk notification failed', [
                'status' => $response->status(),
                'response' => $response->json(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('DingTalk notification error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Build the webhook URL with signature if secret is configured.
     */
    protected function buildUrl(): string
    {
        if (empty($this->secret)) {
            return $this->webhookUrl;
        }

        $timestamp = (int) (microtime(true) * 1000);
        $stringToSign = $timestamp."\n".$this->secret;
        $sign = urlencode(base64_encode(hash_hmac('sha256', $stringToSign, $this->secret, true)));

        $separator = str_contains($this->webhookUrl, '?') ? '&' : '?';

        return "{$this->webhookUrl}{$separator}timestamp={$timestamp}&sign={$sign}";
    }
}
