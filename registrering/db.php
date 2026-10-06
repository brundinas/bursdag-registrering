<?php

function db(): PDO
{
    $pdo = new PDO('sqlite:' . __DIR__ . '/data/registrering.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS aktiviteter (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            aktivitet TEXT NOT NULL,
            beskrivelse TEXT
        )
    ');
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS gjest (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            navn TEXT NOT NULL UNIQUE
        )
    ');
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS paamelding (
            idNavn INTEGER NOT NULL REFERENCES gjest(id) ON DELETE CASCADE,
            idAktivitet INTEGER NOT NULL REFERENCES aktiviteter(id) ON DELETE CASCADE,
            PRIMARY KEY (idNavn, idAktivitet)
        )
    ');

    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
