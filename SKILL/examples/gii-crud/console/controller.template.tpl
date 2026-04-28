<?php

declare(strict_types=1);

namespace {{controllerNamespace}};

use {{handlerControllerFqcn}};
use {{domainFqcn}};
use {{viewPayloadFqcn}};

/**
 * Описание класса {{controllerClass}}.
 *
 * Console controller домена {{domainName}}. Команды создают payload и вызывают handler.
 *
 * @method {{viewPayloadClass}} getPayload(string $action, array $data = [])
 */
final class {{controllerClass}} extends {{handlerControllerClass}}
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Описание метода actionView.
     *
     * Назначение: вывести модель в stdout через DNK handler.
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
