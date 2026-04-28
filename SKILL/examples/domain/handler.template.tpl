<?php

declare(strict_types=1);

namespace {{handlerNamespace}};

use andy87\yii2dnk\BasePayload;
use andy87\yii2dnk\domain\BaseHandler;
use andy87\yii2dnk\viewModels\BaseViewModel;
use {{domainFqcn}};
use {{payloadFqcn}};
use {{resourceFqcn}};
use {{serviceFqcn}};
use yii\base\InvalidConfigException;

/**
 * Описание класса {{handlerClass}}.
 *
 * Handler домена {{domainName}}. Оркестрирует use-case и делегирует
 * бизнес-логику сервису.
 */
final class {{handlerClass}} extends BaseHandler
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Описание метода provider.
     *
     * Назначение: диспетчеризовать payload к конкретному обработчику сценария.
     *
     * @param BasePayload $payload Входные данные действия.
     * @param BaseViewModel|null $viewModel Выходная модель, созданная по domain mapping.
     * @return BaseViewModel|bool|array|null Результат действия.
     * @throws InvalidConfigException Если payload не поддерживается.
     */
    protected function provider(BasePayload $payload, ?BaseViewModel $viewModel = null): BaseViewModel|bool|array|null
    {
        return match ($payload::class) {
            {{payloadClass}}::class => $this->processView($payload, $viewModel),
            default => throw new InvalidConfigException('Unsupported payload: ' . $payload::class),
        };
    }

    /**
     * Описание метода processView.
     *
     * Назначение: обработать сценарий просмотра модели.
     *
     * @param {{payloadClass}} $payload Входные данные просмотра.
     * @param BaseViewModel|null $viewModel Выходная модель просмотра.
     * @return {{resourceClass}} Заполненная выходная модель.
     * @throws InvalidConfigException Если view model настроена некорректно.
     * @throws \Throwable Если сервисный слой завершился ошибкой.
     */
    private function processView({{payloadClass}} $payload, ?BaseViewModel $viewModel): {{resourceClass}}
    {
        if (!$viewModel instanceof {{resourceClass}}) {
            throw new InvalidConfigException('Invalid view model for payload: ' . $payload::class);
        }

        /** @var {{serviceClass}} $service */
        $service = $this->getService();
        $viewModel->model = $service->getById($payload->id);

        return $viewModel;
    }
}
