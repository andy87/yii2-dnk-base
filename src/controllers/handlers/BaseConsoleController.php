<?php

declare(strict_types=1);

namespace andy87\yii2dnk\controllers\handlers;

use andy87\yii2dnk\domain\BaseDomain;
use andy87\yii2dnk\viewModels\BaseViewModel;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Базовый консольный контроллер для DNK flow.
 *
 * Принимает аргументы команды, создаёт payload и handler через реестр домена,
 * вызывает handler и выводит результат в stdout через display().
 * Не содержит бизнес-логику.
 */
abstract class BaseConsoleController extends Controller
{
    use ControllerDomainTrait;

    /**
     * Явно указанный класс реестра домена.
     * Если пустая строка — определяется автоматически через DomainAwareTrait.
     *
     * @var class-string<BaseDomain>|''
     */
    protected const DOMAIN = '';

    /**
     * Выводит результат handler в stdout и возвращает код завершения.
     *
     * Если результат false — возвращает код ошибки.
     * Если результат — BaseViewModel, преобразует в массив через release().
     * Если результат не null и не true — выводит JSON в stdout.
     *
     * @param BaseViewModel|array|bool|null $result Результат выполнения handler.
     * @return int Код завершения консольной команды.
     */
    protected function display(BaseViewModel|array|bool|null $result): int
    {
        if ($result === false) {
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if ($result instanceof BaseViewModel) {
            $result = $result->release();
        }

        if ($result !== null && $result !== true) {
            $this->stdout(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL);
        }

        return ExitCode::OK;
    }
}
