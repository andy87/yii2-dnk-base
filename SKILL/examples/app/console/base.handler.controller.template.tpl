<?php

declare(strict_types=1);

namespace {{handlerControllerNamespace}};

use andy87\yii2dnk\controllers\handlers\BaseConsoleController;

/**
 * Описание класса BaseHandlerController.
 *
 * Базовый console-контроллер приложения для DNK flow.
 * Наследует runtime-логику payload -> handler -> display из пакета.
 *
 * Это app-level класс — все доменные console-контроллеры наследуют его.
 * Он наследует andy87\yii2dnk\controllers\handlers\BaseConsoleController из пакета.
 */
abstract class BaseHandlerController extends BaseConsoleController
{
    /**
     * Описание метода successLine.
     *
     * Назначение: вывести успешное сообщение в stdout.
     *
     * @param string $message Текст сообщения.
     * @return void
     */
    protected function successLine(string $message): void
    {
        $this->stdout($message . PHP_EOL);
    }
}
