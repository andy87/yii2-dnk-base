#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Линтит PHP-файлы пакета через встроенный `php -l`.
 *
 * Скрипт намеренно не требует внешних dev-зависимостей, чтобы `composer lint`
 * работал в минимальном окружении пакета.
 */

$root = dirname(__DIR__);
$paths = [
    $root . '/src',
    $root . '/tests',
    $root . '/scripts',
    $root . '/SKILL/scripts',
];

exit(lintPaths($paths));

/**
 * Выполняет PHP lint для всех файлов в указанных путях.
 *
 * @param array<int, string> $paths Список файлов или директорий для проверки.
 * @return int Код завершения: 0 при успехе, 1 при ошибках lint-а.
 */
function lintPaths(array $paths): int
{
    $failed = false;

    foreach (collectPhpFiles($paths) as $file) {
        $command = PHP_BINARY . ' -l ' . escapeshellarg($file);
        exec($command, $output, $code);

        foreach ($output as $line) {
            echo $line . PHP_EOL;
        }

        if ($code !== 0) {
            $failed = true;
        }
    }

    return $failed ? 1 : 0;
}

/**
 * Собирает PHP-файлы из списка файлов и директорий.
 *
 * @param array<int, string> $paths Список файлов или директорий.
 * @return array<int, string> Отсортированный список PHP-файлов.
 */
function collectPhpFiles(array $paths): array
{
    $files = [];

    foreach ($paths as $path) {
        if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $files[] = $path;
            continue;
        }

        if (!is_dir($path)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
    }

    sort($files);

    return $files;
}
