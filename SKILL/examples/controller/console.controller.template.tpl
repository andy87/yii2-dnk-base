<?php

declare(strict_types=1);

namespace {{controllerNamespace}};

use {{handlerControllerFqcn}};
use {{domainFqcn}};
use {{payloadFqcn}};

/**
 * Описание класса {{controllerClass}}.
 *
 * Console-контроллер домена {{domainName}}. Принимает command input,
 * создает payload, вызывает handler и возвращает exit code.
 *
 * {{handlerControllerFqcn}} — app-level базовый контроллер проекта,
 * который наследует andy87\yii2dnk\controllers\handlers\BaseConsoleController
 * и содержит общие console-хелперы. Все доменные console-контроллеры
 * проекта наследуют этот app-level класс.
 *
 * @method {{payloadClass}} getPayload(string $action, array $data = [])
 */
final class {{controllerClass}} extends {{handlerControllerClass}}
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Описание метода actionView.
     *
     * Назначение: обработать console-команду просмотра модели.
     *
     * @param int $id Идентификатор модели.
     * @return int Console exit code.
     * @throws \yii\base\InvalidConfigException Если domain, handler или payload mapping настроены некорректно.
     * @throws \Throwable Если handler завершается ошибкой.
     */
    public function actionView(int $id): int
    {
        return $this->display(
            $this->getHandler()->run(
                $this->getPayload({{domainClass}}::ACTION_VIEW, ['id' => $id])
            )
        );
    }
}
