#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Универсальный renderer DNK skill templates.
 *
 * Скрипт предназначен для smoke-проверки и bootstrap-генерации DNK templates
 * в любом Yii2-проекте без привязки к конкретному workspace.
 */

$skillRoot = dirname(__DIR__);
$options = parseOptions($argv);

if (isset($options['help'])) {
    printHelp();
    exit(0);
}

$domainName = normalizeDomainName((string) ($options['domain'] ?? 'TestItem'));
$baseNamespace = normalizeNamespace((string) ($options['namespace'] ?? 'app\\GeneratedDnk'));
$templateRoot = normalizePath((string) ($options['template-root'] ?? $skillRoot . '/examples'));
$outputRoot = normalizePath((string) ($options['output'] ?? getcwd() . '/generated'));
$map = buildPlaceholderMap($domainName, $baseNamespace, $options);

if (isset($options['map-json'])) {
    $map = array_merge($map, loadMapJson((string) $options['map-json']));
}

if (!is_dir($templateRoot)) {
    fwrite(STDERR, "Template root not found: {$templateRoot}\n");
    exit(1);
}

if (isset($options['clean'])) {
    removeDirectory($outputRoot);
}

if (!is_dir($outputRoot)) {
    mkdir($outputRoot, 0775, true);
}

$count = renderTemplates($templateRoot, $outputRoot, $map);

if (isset($options['source-stub'])) {
    writeSourceStub($outputRoot, $map);
}

assertNoUnresolvedPlaceholders($outputRoot);

if (isset($options['lint'])) {
    lintPhpFiles($outputRoot);
}

echo "Rendered {$count} DNK templates into {$outputRoot}\n";

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
 * Печатает краткую справку по renderer-у.
 *
 * @return void
 */
function printHelp(): void
{
    echo <<<'HELP'
DNK template renderer

Usage:
  php scripts/generate-dnk-templates.php --output=/path/generated --domain=OrderItem --namespace=app\DomainSmoke --clean --source-stub --lint

Options:
  --template-root=PATH             Root with *.tpl files. Default: ../examples relative to this script.
  --output=PATH                    Output directory. Default: ./generated.
  --domain=PascalName              Domain basename. Default: TestItem.
  --namespace=Vendor\App           Base namespace for generated classes. Default: app\GeneratedDnk.
  --controller-namespace=NS        Override controller namespace.
  --handler-controller-namespace=NS Override app-level BaseHandlerController namespace.
  --common-handler-controller-fqcn=FQCN Override common BaseHandlerController FQCN.
  --map-json=PATH                  JSON object with placeholder overrides, e.g. {"{{domainName}}":"OrderItem"}.
  --source-stub                    Add minimal <Domain>Source model for syntax smoke.
  --clean                          Remove output directory before rendering.
  --lint                           Run php -l for generated PHP files.
  --help                           Show this help.

HELP;
}

/**
 * Нормализует имя домена к PascalCase-подобному идентификатору.
 *
 * @param string $domainName Raw имя домена.
 * @return string Валидное имя без небезопасных символов.
 */
function normalizeDomainName(string $domainName): string
{
    $domainName = preg_replace('/[^A-Za-z0-9_]/', '', $domainName) ?: 'TestItem';

    if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $domainName) !== 1) {
        fwrite(STDERR, "Invalid domain name: {$domainName}\n");
        exit(1);
    }

    return $domainName;
}

/**
 * Нормализует PHP namespace/FQCN без начального и конечного slash.
 *
 * @param string $namespace Raw namespace.
 * @return string Нормализованный namespace.
 */
function normalizeNamespace(string $namespace): string
{
    $namespace = trim($namespace, "\\ \t\n\r\0\x0B");

    if ($namespace === '' || preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\\\\[A-Za-z_][A-Za-z0-9_]*)*$/', $namespace) !== 1) {
        fwrite(STDERR, "Invalid namespace: {$namespace}\n");
        exit(1);
    }

    return $namespace;
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
 * Строит default placeholder map для DNK templates.
 *
 * @param string $domainName Имя домена.
 * @param string $baseNamespace Базовый namespace проекта.
 * @param array<string, string|bool> $options CLI options.
 * @return array<string, string> Placeholder map.
 */
function buildPlaceholderMap(string $domainName, string $baseNamespace, array $options): array
{
    $modelNamespace = "{$baseNamespace}\\Models";
    $domainNamespace = "{$baseNamespace}\\Domain";
    $payloadNamespace = "{$domainNamespace}\\Payload";
    $resourceNamespace = "{$baseNamespace}\\ViewModels";
    $controllerNamespace = normalizeNamespace((string) ($options['controller-namespace'] ?? "{$baseNamespace}\\Controllers"));
    $handlerControllerNamespace = normalizeNamespace((string) ($options['handler-controller-namespace'] ?? $controllerNamespace));
    $commonHandlerControllerFqcn = normalizeNamespace((string) ($options['common-handler-controller-fqcn'] ?? "{$baseNamespace}\\Common\\BaseHandlerController"));

    $classes = [
        'model' => $domainName,
        'source' => "{$domainName}Source",
        'search' => "{$domainName}Search",
        'domain' => "{$domainName}Domain",
        'handler' => "{$domainName}Handler",
        'service' => "{$domainName}Service",
        'repository' => "{$domainName}Repository",
        'producer' => "{$domainName}Producer",
        'killer' => "{$domainName}Killer",
        'queryStorage' => "{$domainName}QueryStorage",
        'dataProvider' => "{$domainName}DataProvider",
        'controller' => "{$domainName}Controller",
        'payload' => "{$domainName}ViewPayload",
        'resource' => "{$domainName}ViewResource",
        'indexPayload' => "{$domainName}IndexPayload",
        'createPayload' => "{$domainName}CreatePayload",
        'updatePayload' => "{$domainName}UpdatePayload",
        'viewPayload' => "{$domainName}ViewPayload",
        'deletePayload' => "{$domainName}DeletePayload",
        'indexResource' => "{$domainName}IndexResource",
        'createResource' => "{$domainName}CreateResource",
        'updateResource' => "{$domainName}UpdateResource",
        'viewResource' => "{$domainName}ViewResource",
    ];

    return [
        '{{actionId}}' => 'view',
        '{{commonHandlerControllerFqcn}}' => $commonHandlerControllerFqcn,
        '{{controllerClass}}' => $classes['controller'],
        '{{controllerNamespace}}' => $controllerNamespace,
        '{{createPayloadClass}}' => $classes['createPayload'],
        '{{createPayloadFqcn}}' => "{$payloadNamespace}\\{$classes['createPayload']}",
        '{{createResourceClass}}' => $classes['createResource'],
        '{{createResourceFqcn}}' => "{$resourceNamespace}\\{$classes['createResource']}",
        '{{dataProviderClass}}' => $classes['dataProvider'],
        '{{dataProviderFqcn}}' => "{$domainNamespace}\\{$classes['dataProvider']}",
        '{{dataProviderNamespace}}' => $domainNamespace,
        '{{deletePayloadClass}}' => $classes['deletePayload'],
        '{{deletePayloadFqcn}}' => "{$payloadNamespace}\\{$classes['deletePayload']}",
        '{{domainClass}}' => $classes['domain'],
        '{{domainFqcn}}' => "{$domainNamespace}\\{$classes['domain']}",
        '{{domainName}}' => $domainName,
        '{{domainNamespace}}' => $domainNamespace,
        '{{handlerClass}}' => $classes['handler'],
        '{{handlerControllerClass}}' => 'BaseHandlerController',
        '{{handlerControllerFqcn}}' => "{$handlerControllerNamespace}\\BaseHandlerController",
        '{{handlerControllerNamespace}}' => $handlerControllerNamespace,
        '{{handlerFqcn}}' => "{$domainNamespace}\\{$classes['handler']}",
        '{{handlerNamespace}}' => $domainNamespace,
        '{{indexPayloadClass}}' => $classes['indexPayload'],
        '{{indexPayloadFqcn}}' => "{$payloadNamespace}\\{$classes['indexPayload']}",
        '{{indexResourceClass}}' => $classes['indexResource'],
        '{{indexResourceFqcn}}' => "{$resourceNamespace}\\{$classes['indexResource']}",
        '{{killerClass}}' => $classes['killer'],
        '{{killerFqcn}}' => "{$domainNamespace}\\{$classes['killer']}",
        '{{killerNamespace}}' => $domainNamespace,
        '{{modelClass}}' => $classes['model'],
        '{{modelFqcn}}' => "{$modelNamespace}\\{$classes['model']}",
        '{{modelNamespace}}' => $modelNamespace,
        '{{payloadClass}}' => $classes['payload'],
        '{{payloadFqcn}}' => "{$payloadNamespace}\\{$classes['payload']}",
        '{{payloadNamespace}}' => $payloadNamespace,
        '{{producerClass}}' => $classes['producer'],
        '{{producerFqcn}}' => "{$domainNamespace}\\{$classes['producer']}",
        '{{producerNamespace}}' => $domainNamespace,
        '{{queryStorageClass}}' => $classes['queryStorage'],
        '{{queryStorageFqcn}}' => "{$domainNamespace}\\{$classes['queryStorage']}",
        '{{queryStorageNamespace}}' => $domainNamespace,
        '{{repositoryClass}}' => $classes['repository'],
        '{{repositoryFqcn}}' => "{$domainNamespace}\\{$classes['repository']}",
        '{{repositoryNamespace}}' => $domainNamespace,
        '{{resourceClass}}' => $classes['resource'],
        '{{resourceFqcn}}' => "{$resourceNamespace}\\{$classes['resource']}",
        '{{resourceNamespace}}' => $resourceNamespace,
        '{{searchClass}}' => $classes['search'],
        '{{searchFqcn}}' => "{$modelNamespace}\\{$classes['search']}",
        '{{searchNamespace}}' => $modelNamespace,
        '{{serviceClass}}' => $classes['service'],
        '{{serviceFqcn}}' => "{$domainNamespace}\\{$classes['service']}",
        '{{serviceNamespace}}' => $domainNamespace,
        '{{sourceClass}}' => $classes['source'],
        '{{sourceFqcn}}' => "{$modelNamespace}\\{$classes['source']}",
        '{{updatePayloadClass}}' => $classes['updatePayload'],
        '{{updatePayloadFqcn}}' => "{$payloadNamespace}\\{$classes['updatePayload']}",
        '{{updateResourceClass}}' => $classes['updateResource'],
        '{{updateResourceFqcn}}' => "{$resourceNamespace}\\{$classes['updateResource']}",
        '{{viewPayloadClass}}' => $classes['viewPayload'],
        '{{viewPayloadFqcn}}' => "{$payloadNamespace}\\{$classes['viewPayload']}",
        '{{viewResourceClass}}' => $classes['viewResource'],
        '{{viewResourceFqcn}}' => "{$resourceNamespace}\\{$classes['viewResource']}",
    ];
}

/**
 * Загружает JSON map overrides.
 *
 * @param string $path Путь к JSON-файлу.
 * @return array<string, string> Placeholder overrides.
 */
function loadMapJson(string $path): array
{
    $data = json_decode((string) file_get_contents($path), true);

    if (!is_array($data)) {
        fwrite(STDERR, "Invalid map JSON: {$path}\n");
        exit(1);
    }

    foreach ($data as $key => $value) {
        if (!is_string($key) || !is_string($value)) {
            fwrite(STDERR, "Map JSON must be object<string,string>: {$path}\n");
            exit(1);
        }
    }

    return $data;
}

/**
 * Рендерит все *.tpl файлы в output directory.
 *
 * @param string $templateRoot Директория templates.
 * @param string $outputRoot Директория результата.
 * @param array<string, string> $map Placeholder map.
 * @return int Количество отрендеренных templates.
 */
function renderTemplates(string $templateRoot, string $outputRoot, array $map): int
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($templateRoot, FilesystemIterator::SKIP_DOTS)
    );
    $count = 0;

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'tpl') {
            continue;
        }

        $relativePath = substr($file->getPathname(), strlen($templateRoot) + 1);
        $targetPath = $outputRoot . '/' . preg_replace('/\.template\.tpl$/', '.php', $relativePath);

        if ($targetPath === $outputRoot . '/' . $relativePath) {
            $targetPath = preg_replace('/\.tpl$/', '.php', $targetPath);
        }

        $targetDirectory = dirname((string) $targetPath);
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0775, true);
        }

        $rendered = strtr((string) file_get_contents($file->getPathname()), $map);
        $rendered = str_replace("\r\n", "\n", $rendered);
        file_put_contents((string) $targetPath, $rendered);
        $count++;
    }

    return $count;
}

/**
 * Добавляет минимальный Source model stub для syntax smoke.
 *
 * @param string $outputRoot Директория результата.
 * @param array<string, string> $map Placeholder map.
 * @return void
 */
function writeSourceStub(string $outputRoot, array $map): void
{
    $modelNamespace = $map['{{modelNamespace}}'];
    $sourceClass = $map['{{sourceClass}}'];
    $stubPath = $outputRoot . '/domain/models/' . $sourceClass . '.php';
    $targetDirectory = dirname($stubPath);

    if (!is_dir($targetDirectory)) {
        mkdir($targetDirectory, 0775, true);
    }

    $sourceStub = <<<PHP
<?php

declare(strict_types=1);

namespace {$modelNamespace};

use andy87\\yii2dnk\\BaseModel;

/**
 * Минимальная Gii Source-модель для синтаксической проверки generated templates.
 */
class {$sourceClass} extends BaseModel
{
    /**
     * Возвращает имя тестовой таблицы.
     *
     * @return string Имя таблицы.
     */
    public static function tableName(): string
    {
        return 'test_item';
    }

    /**
     * Возвращает атрибуты без обращения к схеме БД.
     *
     * @return array<int, string> Список атрибутов.
     */
    public function attributes(): array
    {
        return ['id'];
    }
}

PHP;

    file_put_contents($stubPath, $sourceStub);
}

/**
 * Проверяет, что в результате не осталось DNK placeholders.
 *
 * @param string $outputRoot Директория результата.
 * @return void
 */
function assertNoUnresolvedPlaceholders(string $outputRoot): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($outputRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());
        if (preg_match('/\{\{[A-Za-z0-9_]+}}/', $content) === 1) {
            fwrite(STDERR, "Unresolved DNK placeholder in {$file->getPathname()}\n");
            exit(1);
        }
    }
}

/**
 * Запускает php -l по сгенерированным PHP-файлам.
 *
 * @param string $outputRoot Директория результата.
 * @return void
 */
function lintPhpFiles(string $outputRoot): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($outputRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $command = escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname());
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            fwrite(STDERR, implode("\n", $output) . "\n");
            exit($exitCode);
        }
    }
}

/**
 * Удаляет директорию рекурсивно.
 *
 * @param string $directory Путь директории.
 * @return void
 */
function removeDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo) {
            continue;
        }

        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }

    rmdir($directory);
}
