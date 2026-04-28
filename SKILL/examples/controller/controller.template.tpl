<?php

declare(strict_types=1);

namespace {{controllerNamespace}};

use {{handlerControllerFqcn}};
use {{domainFqcn}};
use {{payloadFqcn}};
use yii\web\Response;

/**
 * Описание класса {{controllerClass}}.
 *
 * Web-контроллер домена {{domainName}}. Принимает request, создает payload,
 * вызывает handler и возвращает transport-level response.
 *
 * {{handlerControllerFqcn}} — app-level базовый контроллер проекта,
 * который наследует andy87\yii2dnk\controllers\handlers\BaseWebController
 * и содержит общие transport-хелперы (redirect, alert и т.д.).
 * Все доменные контроллеры проекта наследуют этот app-level класс.
 *
 * @method {{payloadClass}} getPayload(string $action, array $data = [])
 */
final class {{controllerClass}} extends {{handlerControllerClass}}
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Описание метода actionView.
     *
     * Назначение: обработать HTTP-запрос просмотра модели.
     *
     * @param int $id Идентификатор модели.
     * @return string|Response HTML или JSON response.
     * @throws \yii\base\InvalidConfigException Если domain, handler или payload mapping настроены некорректно.
     * @throws \Throwable Если handler или Yii response завершается ошибкой.
     */
    public function actionView(int $id): string|Response
    {
        return $this->display(
            $this->getHandler()->run(
                $this->getPayload({{domainClass}}::ACTION_VIEW, ['id' => $id])
            )
        );
    }
}
