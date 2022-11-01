<?php

$db = [
    'host' => 'localhost',
    'user' => 'dariaunb_tr',
    'pass' => '07amotrc77A!',
    'db' => 'dariaunb_tr',
];
$dsn = "mysql:host={$db['host']};dbname={$db['db']};charset=utf8";
$opt = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

$pdo = new PDO($dsn, $db['user'], $db['pass'], $opt);

function get_chat_id($chat_id)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM chat WHERE chat_id = ?");
    $stmt->execute([$chat_id]);
    return $stmt->fetch();
}

function add_chat_id($chat_id, $first_name, $lang)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO chat (chat_id, first_name, lang) VALUES (?, ?, ?)");
    return $stmt->execute([$chat_id, $first_name, $lang]);
}

function update_chat($chat_id, $lang)
{
    global $pdo;
    $stmt = $pdo->prepare("UPDATE chat SET lang = ? WHERE chat_id = ?");
    return $stmt->execute([$lang, $chat_id]);
}
