<?php

namespace App;

use Minicli\App;
use Minicli\Curly\Client;
use Minicli\ServiceInterface;

class SlackNotifier
{
    public Client $client;
    public array $channels;

    /**
     * ex: $channels['docs'] = unique_hook_per_channel
     * @param array $channels
     * @return void
     */
    public function __construct(array $channels)
    {
        $this->channels = $channels;
        $this->client = new Client();
    }

    public function send(string $message, string $channel): array
    {
        $slackEndpoint = $this->channels[$channel];

        return $this->client->post(
            $slackEndpoint,
            ['text' => $message, 'type' => 'mrkdwn'],
            ['Content-type: application/json']
        );
    }
}
