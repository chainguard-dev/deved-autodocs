<?php

namespace App\Command\Notify;

use Minicli\Command\CommandController;
use Minicli\Curly\Client;

class PullRequestController extends CommandController
{
    public function handle(): void
    {
        $message = "A new automated pull request has been submitted and awaits review.";
        if (getenv('PR_CHANGED')) {
            $message .= "\nNumber of files changed: " . getenv('PR_CHANGED');
        }

        $url = "https://github.com/chainguard-dev/edu/pulls";
        $prUrl = getenv('PR_URL');
        //query api url for more info
        $client = new Client();
        $details = $client->get($prUrl);

        if ($details['code'] === 200) {
            $urlDetails = json_decode($details['body'], 1);
            $url = $urlDetails['html_url'];
        }
          
        $message .= "\nPull request URL: " . $url;
        
        $this->getApp()->runCommand(['autodocs', 'notify', 'slack', 'message=' . $message]);
    }
}
