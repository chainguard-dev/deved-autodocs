<?php

test('command signature shows help', function () {
    $app = getApp();
    $app->runCommand(['autodocs']);
})->expectOutputRegex("/help/");

test('help command is loaded and shows commands available', function () {
    $app = getApp();
    $app->runCommand(['autodocs', 'help']);
})->expectOutputRegex("/build/");

test('the "build" command outputs example message', function () {
    $app = getApp();
    $app->runCommand(['autodocs', 'build']);
})->expectOutputRegex("/Example/");

