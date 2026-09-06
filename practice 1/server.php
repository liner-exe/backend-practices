<?php
session_start();

require_once ('server_lib.php');

if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

$cmd = trim($_POST['cmd'] ?? '');

if ($cmd !== '') {
    if ($cmd === 'clear') {
        $_SESSION['history'] = [];
    } else {
        $output = executeCommand($cmd);
        $_SESSION['history'][] = [
            'cmd' => $cmd,
            'output' => $output
        ];
    }

    header("Location: server.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Командная строка</title>
    <style>
        body {
            margin: 0;
            font-family: 'Consolas', monospace;
            font-size: 1.2rem;
        }

        .terminal {
            display: flex;
            flex-direction: column;
            min-height: 100dvh;
            background: #2b2b2b;
            gap: 4px;
        }

        .input-line {
            display: flex;
            align-items: center;
        }

        .cmd-label {
            color: #4ade80;
        }

        .cmd-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .text-cmd {
            color: #4ade80;
        }

        .cursor {
            display: inline-block;
            width: 1ch;
            height: 1em;
            background: #fff;
            animation: blink 1s step-end infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
    </style>
</head>
<body class="terminal" onclick="document.getElementById('command').focus();">
<div style="">
    <?php foreach ($_SESSION['history'] as $entry): ?>
        <div style="color: #4ade80; margin: 0;">liner-exe@server ~ $&nbsp;<?= htmlspecialchars($entry['cmd']) ?></div>
        <pre style="color: #fff; margin: 0;"><?= htmlspecialchars($entry['output']) ?></pre>
    <?php endforeach; ?>

    <div>
        <form method="POST" class="input-line">
            <label for="command" class="cmd-label">liner-exe@server ~ $&nbsp;</label>
            <span id="input-cmd" class="text-cmd"></span>
            <input id="command" name="cmd" class="cmd-input" type="text" size="1" autofocus autocomplete="off"
                   oninput="document.getElementById('input-cmd').textContent = this.value;">
            <span class="cursor"></span>
        </form>
    </div>
</div>
</body>
</html>
