<?php

declare(strict_types=1);

namespace {{controllerNamespace}};

use {{handlerControllerFqcn}};
use {{domainFqcn}};
use {{createPayloadFqcn}};
use {{deletePayloadFqcn}};
use {{indexPayloadFqcn}};
use {{updatePayloadFqcn}};
use {{viewPayloadFqcn}};
use andy87\yii2dnk\viewModels\crud\BaseFormResource;
use yii\web\Response;

/**
 * Описание класса {{controllerClass}}.
 *
 * Backend CRUD-контроллер домена {{domainName}}.
 * Экшены содержат только transport-логику: payload, handler, redirect и alert.
 *
 * @method {{indexPayloadClass}} getPayload(string $action, array $data = [])
 * @method {{createPayloadClass}} getPayload(string $action, array $data = [])
 * @method {{updatePayloadClass}} getPayload(string $action, array $data = [])
 * @method {{viewPayloadClass}} getPayload(string $action, array $data = [])
 * @method {{deletePayloadClass}} getPayload(string $action, array $data = [])
 */
final class {{controllerClass}} extends {{handlerControllerClass}}
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Описание метода actionIndex.
     *
     * Назначение: отобразить список моделей через DNK handler.
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
     * Описание метода actionCreate.
     *
     * Назначение: отобразить форму создания или перенаправить после сохранения.
     *
     * @return string|Response HTML или HTTP redirect.
     * @throws \yii\base\InvalidConfigException Если domain, handler или payload mapping настроены некорректно.
     * @throws \Throwable Если handler завершается ошибкой.
     */
    public function actionCreate(): string|Response
    {
        $result = $this->getHandler()->run(
            $this->getPayload({{domainClass}}::ACTION_CREATE, [
                'data' => $this->request->post(),
                'submitted' => $this->request->isPost,
            ])
        );

        if ($result instanceof BaseFormResource && $result->saved) {
            $this->successAlert('Запись создана.');

            return $this->redirectToModel($result->model);
        }

        return $this->display($result);
    }

    /**
     * Описание метода actionUpdate.
     *
     * Назначение: отобразить форму обновления или перенаправить после сохранения.
     *
     * @param int $id Идентификатор модели.
     * @return string|Response HTML или HTTP redirect.
     * @throws \yii\base\InvalidConfigException Если domain, handler или payload mapping настроены некорректно.
     * @throws \Throwable Если handler завершается ошибкой.
     */
    public function actionUpdate(int $id): string|Response
    {
        $result = $this->getHandler()->run(
            $this->getPayload({{domainClass}}::ACTION_UPDATE, [
                'id' => $id,
                'data' => $this->request->post(),
                'submitted' => $this->request->isPost,
            ])
        );

        if ($result instanceof BaseFormResource && $result->saved) {
            $this->successAlert('Запись обновлена.');

            return $this->redirectToModel($result->model);
        }

        return $this->display($result);
    }

    /**
     * Описание метода actionView.
     *
     * Назначение: отобразить карточку модели через DNK handler.
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

    /**
     * Описание метода actionDelete.
     *
     * Назначение: удалить модель через DNK handler.
     * При успехе — redirect на index с success alert.
     * При неудаче — redirect на update с error alert.
     *
     * @param int $id Идентификатор модели.
     * @return Response HTTP redirect.
     * @throws \yii\base\InvalidConfigException Если domain, handler или payload mapping настроены некорректно.
     * @throws \Throwable Если handler завершается ошибкой.
     */
    public function actionDelete(int $id): Response
    {
        $result = $this->getHandler()->run(
            $this->getPayload({{domainClass}}::ACTION_DELETE, ['id' => $id])
        );

        if ($result === false) {
            $this->errorAlert('Ошибка удаления записи.');

            return $this->redirect(['update', 'id' => $id]);
        }

        $this->successAlert('Запись удалена.');

        return $this->redirect(['index']);
    }
}
