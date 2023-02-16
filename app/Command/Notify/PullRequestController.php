<?php

namespace App\Command\Notify;

use Minicli\Command\CommandController;

class PullRequestController extends CommandController
{
    public function handle(): void
    {
        //notify slack primary channel about a new PR for autodocs
        $url= "https://github.com/chainguard-dev/edu/pulls/" . getenv('INPUT_PR_NUMBER');
        $message = sprintf("A new automated pull request has been submitted and awaits review: %s", $url);

        $this->getApp()->runCommand(['autodocs', 'notify', 'slack', 'message=' . $message]);
    }
}
