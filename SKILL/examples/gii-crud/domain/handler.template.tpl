<?php

declare(strict_types=1);

namespace {{handlerNamespace}};

use andy87\yii2dnk\BasePayload;
use andy87\yii2dnk\domain\BaseHandler;
use andy87\yii2dnk\viewModels\BaseViewModel;
use {{createPayloadFqcn}};
use {{createResourceFqcn}};
use {{deletePayloadFqcn}};
use {{domainFqcn}};
use {{indexPayloadFqcn}};
use {{indexResourceFqcn}};
use {{serviceFqcn}};
use {{updatePayloadFqcn}};
use {{updateResourceFqcn}};
use {{viewPayloadFqcn}};
use {{viewResourceFqcn}};
use yii\base\InvalidConfigException;

/**
 * Описание класса {{handlerClass}}.
 *
 * CRUD-handler домена {{domainName}}. Диспетчеризует payload к сценариям,
 * вызывает методы service и заполняет resource для view.
 */
final class {{handlerClass}} extends BaseHandler
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Описание метода provider.
     *
     * Назначение: сопоставить payload с CRUD-сценарием.
     *
     * @param BasePayload $payload Входные данные действия.
     * @param BaseViewModel|null $viewModel Resource, созданный по domain mapping.
     * @return BaseViewModel|bool|array|null Результат действия.
     * @throws InvalidConfigException Если payload не поддерживается.
     */
    protected function provider(BasePayload $payload, ?BaseViewModel $viewModel = null): BaseViewModel|bool|array|null
    {
        return match ($payload::class) {
            {{indexPayloadClass}}::class => $this->processIndex($payload, $viewModel),
            {{createPayloadClass}}::class => $this->processCreate($payload, $viewModel),
            {{updatePayloadClass}}::class => $this->processUpdate($payload, $viewModel),
            {{viewPayloadClass}}::class => $this->processView($payload, $viewModel),
            {{deletePayloadClass}}::class => $this->processDelete($payload),
            default => throw new InvalidConfigException('Unsupported payload: ' . $payload::class),
        };
    }

    /**
     * Описание метода processIndex.
     *
     * Назначение: подготовить resource для index view.
     *
     * @param {{indexPayloadClass}} $payload Входные данные index.
     * @param BaseViewModel|null $viewModel Resource index.
     * @return {{indexResourceClass}} Заполненный resource.
     * @throws InvalidConfigException Если view model настроена некорректно.
     */
    private function processIndex({{indexPayloadClass}} $payload, ?BaseViewModel $viewModel): {{indexResourceClass}}
    {
        if (!$viewModel instanceof {{indexResourceClass}}) {
            throw new InvalidConfigException('Invalid index resource.');
        }

        /** @var {{serviceClass}} $service */
        $service = $this->getService();
        $viewModel->dataProvider = $service->search($payload->params);
        $viewModel->searchModel = $service->getSearchModel();

        return $viewModel;
    }

    /**
     * Описание метода processCreate.
     *
     * Назначение: подготовить или сохранить форму создания.
     *
     * @param {{createPayloadClass}} $payload Входные данные create.
     * @param BaseViewModel|null $viewModel Resource create.
     * @return {{createResourceClass}} Заполненный resource.
     * @throws InvalidConfigException Если view model настроена некорректно.
     */
    private function processCreate({{createPayloadClass}} $payload, ?BaseViewModel $viewModel): {{createResourceClass}}
    {
        if (!$viewModel instanceof {{createResourceClass}}) {
            throw new InvalidConfigException('Invalid create resource.');
        }

        /** @var {{serviceClass}} $service */
        $service = $this->getService();
        $result = $service->createForm($payload->data, $payload->submitted);
        $viewModel->model = $result['model'];
        $viewModel->saved = $result['saved'];

        return $viewModel;
    }

    /**
     * Описание метода processUpdate.
     *
     * Назначение: подготовить или сохранить форму обновления.
     *
     * @param {{updatePayloadClass}} $payload Входные данные update.
     * @param BaseViewModel|null $viewModel Resource update.
     * @return {{updateResourceClass}} Заполненный resource.
     * @throws InvalidConfigException Если view model настроена некорректно.
     */
    private function processUpdate({{updatePayloadClass}} $payload, ?BaseViewModel $viewModel): {{updateResourceClass}}
    {
        if (!$viewModel instanceof {{updateResourceClass}}) {
            throw new InvalidConfigException('Invalid update resource.');
        }

        /** @var {{serviceClass}} $service */
        $service = $this->getService();
        $result = $service->updateForm($payload->id, $payload->data, $payload->submitted);
        $viewModel->model = $result['model'];
        $viewModel->saved = $result['saved'];

        return $viewModel;
    }

    /**
     * Описание метода processView.
     *
     * Назначение: подготовить resource для detail view.
     *
     * @param {{viewPayloadClass}} $payload Входные данные view.
     * @param BaseViewModel|null $viewModel Resource view.
     * @return {{viewResourceClass}} Заполненный resource.
     * @throws InvalidConfigException Если view model настроена некорректно.
     */
    private function processView({{viewPayloadClass}} $payload, ?BaseViewModel $viewModel): {{viewResourceClass}}
    {
        if (!$viewModel instanceof {{viewResourceClass}}) {
            throw new InvalidConfigException('Invalid view resource.');
        }

        /** @var {{serviceClass}} $service */
        $service = $this->getService();
        $viewModel->model = $service->getById($payload->id);

        return $viewModel;
    }

    /**
     * Описание метода processDelete.
     *
     * Назначение: удалить модель.
     * Возвращает bool для controller-level обработки (redirect, flash-alert).
     * Не предназначен для display() — controller сам формирует response.
     *
     * @param {{deletePayloadClass}} $payload Входные данные delete.
     * @return bool True если удаление прошло успешно.
     */
    private function processDelete({{deletePayloadClass}} $payload): bool
    {
        /** @var {{serviceClass}} $service */
        $service = $this->getService();

        return $service->deleteById($payload->id);
    }
}
