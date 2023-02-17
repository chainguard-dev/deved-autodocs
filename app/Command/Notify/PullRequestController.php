<?php

namespace App\Command\Notify;

use Minicli\Command\CommandController;

class PullRequestController extends CommandController
{
    public function handle(): void
    {
        //notify slack primary channel about a new PR for autodocs
        $url = getenv('PR_URL') ?? "https://github.com/chainguard-dev/edu/pulls";
        $message = "A new automated pull request has been submitted and awaits review.";
        if (getenv('PR_CHANGED')) {
            $message .= "\nNumber of files changed: " . getenv('PR_CHANGED');
        }
        $message .= "\nPull request URL: " . $url;
        
        $this->getApp()->runCommand(['autodocs', 'notify', 'slack', 'message=' . $message]);
    }
}
