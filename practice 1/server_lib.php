<?php

function executeCommand(string $command): string
{
    $allowedCommands = [
        'ls' => 'ls',
        'ps' => 'ps',
        'whoami' => 'whoami',
        'id' => 'id',
        'pwd' => 'pwd',
        'date' => 'date',
        'uptime' => 'uptime',
        'uname' => 'uname -a',
        'hostname' => 'hostname',
        'free' => 'free -h',
        'df' => 'df -h'
    ];

    $command = trim($command);

    if ($command === '') {
        return '';
    }

    if (!array_key_exists($command, $allowedCommands)) {
        return "bash: {$command}: command not found. Available commands: " . implode(', ', array_keys($allowedCommands));
    }

    return shell_exec($allowedCommands[$command] ?? "Command has not output.");
}

