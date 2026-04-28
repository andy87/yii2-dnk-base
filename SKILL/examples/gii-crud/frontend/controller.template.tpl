<?php

declare(strict_types=1);

namespace {{controllerNamespace}};

use {{handlerControllerFqcn}};
use {{domainFqcn}};
use {{indexPayloadFqcn}};
use {{viewPayloadFqcn}};
use yii\web\Response;

/**
 * Описание класса {{controllerClass}}.
 *
 * Frontend controller домена {{domainName}}. По умолчанию показывает index/view
 * через DNK handler, без бизнес-логики на уровне controller.
 *
 * @method {{indexPayloadClass}} getPayload(string $action, array $data = [])
 * @method {{viewPayloadClass}} getPayload(string $action, array $data = [])
 */
final class {{controllerClass}} extends {{handlerControllerClass}}
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Описание метода actionIndex.
     *
     * Назначение: отобразить список моделей.
     *
     * @return string|Response HTML или HTTP response.
     * @throws \yii\base\InvalidConfigException Если domain, handler или payload mapping настроены некорректно.
     * @throws \Throwable Если handler завершается ошибкой.
     */
    public function actionIndex(): string|Response
    {
        return $this->display(
            $this->getHandler()->run(
                $this->getPayload({{domainClass}}::ACTION_INDEX, ['params' => $this->request->queryParams])
            )
        );
    }

    /**
     * Описание метода actionView.
     *
     * Назначение: отобразить карточку модели.
     *
     * @param int $id Идентификатор модели.
     * @return string|Response HTML или HTTP response.
     * @throws \yii\base\InvalidConfigException Если domain, handler или payload mapping настроены некорректно.
     * @throws \Throwable Если handler завершается ошибкой.
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
