#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Синхронизирует Cursor skill с master-source DNK skill.
 *
 * Master-source остаётся SKILL/SKILL.md внутри пакета andy87/yii2-dnk-base.
 * Cursor skill получает полную копию, чтобы агент не зависел от перехода
 * по относительным ссылкам и не работал с урезанным индексом.
 */

$options = parseOptions($argv);

if (isset($options['help'])) {
    printHelp();
    exit(0);
}

$skillRoot = dirname(__DIR__);
$workspaceRoot = dirname(__DIR__, 4);
$source = normalizePath((string) ($options['source'] ?? $skillRoot . '/SKILL.md'));
$target = normalizePath((string) ($options['target'] ?? $workspaceRoot . '/.cursor/skills/yii2-dnk/SKILL.md'));
$priority = (string) ($options['priority'] ?? 'high');

if (!is_file($source)) {
    fwrite(STDERR, "Source SKILL.md not found: {$source}\n");
    exit(1);
}

$content = (string) file_get_contents($source);
$content = str_replace("\r\n", "\n", $content);
$cursorContent = buildCursorSkillContent($content, $source, $priority);
$targetDirectory = dirname($target);

if (!is_dir($targetDirectory)) {
    mkdir($targetDirectory, 0775, true);
}

file_put_contents($target, $cursorContent);

echo "Cursor skill synced: {$target}\n";

/**
 * Разбирает CLI options формата --key=value и --flag.
 *
 * @param array<int, string> $argv Аргументы CLI.
 * @return array<string, string|bool> Parsed options.
 */
function parseOptions(array $argv): array
{
    $options = [];

    foreach (array_slice($argv, 1) as $argument) {
        if (!str_starts_with($argument, '--')) {
            continue;
        }

        $argument = substr($argument, 2);
        if (str_contains($argument, '=')) {
            [$key, $value] = explode('=', $argument, 2);
            $options[$key] = $value;

            continue;
        }

        $options[$argument] = true;
    }

    return $options;
}

/**
 * Печатает краткую справку по sync script.
 *
 * @return void
 */
function printHelp(): void
{
    echo <<<'HELP'
DNK Cursor skill sync

Usage:
  php scripts/sync-cursor-skill.php
  php scripts/sync-cursor-skill.php --source=/path/SKILL.md --target=/path/.cursor/skills/yii2-dnk/SKILL.md --priority=high

Options:
  --source=PATH    Master SKILL.md. Default: ../SKILL.md relative to this script.
  --target=PATH    Cursor SKILL.md output. Default: <workspace>/.cursor/skills/yii2-dnk/SKILL.md.
  --priority=VALUE Cursor skill priority metadata. Default: high.
  --help           Show this help.

HELP;
}

/**
 * Нормализует filesystem path.
 *
 * @param string $path Raw путь.
 * @return string Нормализованный путь без trailing slash.
 */
function normalizePath(string $path): string
{
    return rtrim($path, DIRECTORY_SEPARATOR);
}

/**
 * Собирает Cursor skill content из master content.
 *
 * @param string $content Содержимое master SKILL.md.
 * @param string $source Путь master-файла.
 * @param string $priority Cursor priority metadata.
 * @return string Содержимое Cursor skill.
 */
function buildCursorSkillContent(string $content, string $source, string $priority): string
{
    if (!str_starts_with($content, "---\n")) {
        return generatedNotice($source) . ltrim($content);
    }

    $closingPosition = strpos($content, "\n---\n", 4);

    if ($closingPosition === false) {
        return generatedNotice($source) . ltrim($content);
    }

    $frontmatter = substr($content, 4, $closingPosition - 3);
    $body = substr($content, $closingPosition + 5);

    if (preg_match('/^priority:/m', $frontmatter) !== 1) {
        $frontmatter = rtrim($frontmatter) . "\npriority: {$priority}\n";
    }

    return "---\n"
        . $frontmatter
        . "---\n\n"
        . generatedNotice($source)
        . ltrim($body);
}

/**
 * Возвращает marker, объясняющий происхождение generated Cursor skill.
 *
 * @param string $source Путь master-файла.
 * @return string Markdown notice.
 */
function generatedNotice(string $source): string
{
    return "<!-- Generated from {$source}. Do not edit manually; run shared/yii2/dnk/scripts/sync-cursor-skill.php. -->\n\n";
}
