<?php

namespace App\Command\Notify;

use App\SlackNotifier;
use Minicli\Command\CommandController;
use Minicli\Curly\Client;

class SlackController extends CommandController
{
    public function handle(): void
    {
        if (getenv('AUTODOCS_SLACK_PRIMARY') == null) {
            throw new \Exception("Missing AUTODOCS_SLACK_PRIMARY environment variable with channel endpoint.");
        }

        $notifier = new SlackNotifier([
            'primary' => getenv('AUTODOCS_SLACK_PRIMARY'),
            'secondary' => getenv('AUTODOCS_SLACK_SECONDARY'),
            'general' => getenv('AUTODOCS_SLACK_GENERAL'),
        ]);

        if (!$this->hasParam("message")) {
            throw new \Exception("You must provide a message parameter with your notification message.");
        }

        $message = $this->getParam("message");
        $response = $notifier->send($message, $this->getParam('channel') ?? 'primary');

        if ($response['code'] !== 200) {
            throw new \Exception("There was an error with the request.");
        }

        $this->getPrinter()->success("Message sent.");
    }
}
