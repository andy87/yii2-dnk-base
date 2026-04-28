<?php

declare(strict_types=1);

namespace {{handlerControllerNamespace}};

use andy87\yii2dnk\BaseModel;
use andy87\yii2dnk\controllers\handlers\BaseWebController;
use Yii;
use yii\web\Response;

/**
 * Описание класса BaseHandlerController.
 *
 * Базовый web-контроллер приложения для DNK flow.
 * Наследует runtime-логику payload -> handler -> display из пакета
 * и содержит transport-level helpers: redirect и flash-alert.
 *
 * Это app-level класс — все доменные web-контроллеры наследуют его.
 * Он наследует andy87\yii2dnk\controllers\handlers\BaseWebController из пакета.
 */
abstract class BaseHandlerController extends BaseWebController
{
    /**
     * Записать успешный flash-alert.
     *
     * @param string $message Текст сообщения.
     * @return void
     */
    protected function successAlert(string $message): void
    {
        Yii::$app->session->setFlash('success', $message);
    }

    /**
     * Записать flash-alert ошибки.
     *
     * @param string $message Текст сообщения.
     * @return void
     */
    protected function errorAlert(string $message): void
    {
        Yii::$app->session->setFlash('error', $message);
    }

    /**
     * Перенаправить на view-страницу модели.
     *
     * @param BaseModel $model Сохранённая или найденная модель.
     * @param string $route Route действия просмотра.
     * @return Response HTTP redirect response.
     */
    protected function redirectToModel(BaseModel $model, string $route = 'view'): Response
    {
        return $this->redirect([$route, 'id' => $model->getPrimaryKey()]);
    }
}
