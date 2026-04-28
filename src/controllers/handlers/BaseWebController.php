<?php

declare(strict_types=1);

namespace andy87\yii2dnk\controllers\handlers;

use andy87\yii2dnk\BasePayload;
use andy87\yii2dnk\domain\BaseDomain;
use andy87\yii2dnk\exceptions\ValidationException;
use andy87\yii2dnk\viewModels\BaseTemplateResource;
use andy87\yii2dnk\viewModels\BaseViewModel;
use yii\base\InvalidConfigException;
use yii\web\Controller;
use yii\web\Response;

/**
 * Базовый web-контроллер для DNK flow.
 *
 * Принимает HTTP-запрос, создаёт payload и handler через реестр домена,
 * вызывает handler и возвращает response через display().
 * Не содержит бизнес-логику.
 */
abstract class BaseWebController extends Controller
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
     * Создаёт payload из текущего HTTP request и валидирует его.
     *
     * Для GET-запроса берёт query params. Для остальных HTTP-методов
     * объединяет query params и body params, затем делегирует создание
     * payload в domain registry через getPayload().
     *
     * @param string $action Идентификатор действия контроллера.
     * @param array<string, mixed>|null $data Явные данные. Если null — из request.
     * @return BasePayload Валидный payload.
     * @throws InvalidConfigException Если domainClass() не резолвится, mapping payload невалиден или Yii DI не создал payload.
     * @throws ValidationException Если payload не прошёл валидацию.
     */
    protected function getPayloadFromRequest(string $action, ?array $data = null): BasePayload
    {
        if ($data === null) {
            $request = $this->request;
            $data = $request->isGet
                ? $request->get()
                : array_merge($request->get(), $request->getBodyParams());
        }

        return $this->getPayload($action, $data);
    }

    /**
     * Конвертирует результат handler в HTTP-ответ.
     *
     * BaseTemplateResource рендерится как HTML-view.
     * Остальные BaseViewModel сериализуются в JSON через release().
     *
     * @param BaseViewModel|array|bool|null $result Результат выполнения handler.
     * @param int|null $statusCode HTTP status code для JSON-ответа.
     * @param string|null $view Имя view для BaseTemplateResource.
     * @return string|Response HTML-рендер или JSON-ответ.
     */
    protected function display(BaseViewModel|array|bool|null $result, ?int $statusCode = null, ?string $view = null): string|Response
    {
        if ($result instanceof BaseTemplateResource) {
            return $this->displayView($result, $view);
        }

        if ($result instanceof BaseViewModel) {
            return $this->displayJson($result->release(), $statusCode);
        }

        return $this->displayJson($result, $statusCode);
    }

    /**
     * Рендерит BaseViewModel в HTML view.
     *
     * Использует TEMPLATE из BaseTemplateResource или action id как имя view.
     * Распаковывает поля resource в переменные view и передаёт сам resource
     * как переменную $resource.
     *
     * @param BaseViewModel $result Выходная модель.
     * @param string|null $view Имя view. Если null — из TEMPLATE или action id.
     * @param array<string, mixed> $params Дополнительные параметры view.
     * @return string HTML.
     */
    protected function displayView(BaseViewModel $result, ?string $view = null, array $params = []): string
    {
        $template = $view ?? ($result instanceof BaseTemplateResource ? $result::TEMPLATE : $this->action->id);

        return $this->render($template, array_merge(['resource' => $result], $result->release($params)));
    }

    /**
     * Возвращает JSON response с optional HTTP status code.
     *
     * @param array|bool|null $result Данные ответа.
     * @param int|null $statusCode HTTP status code.
     * @return Response JSON response.
     */
    protected function displayJson(array|bool|null $result, ?int $statusCode = null): Response
    {
        $response = $this->asJson($result);

        if ($statusCode !== null) {
            $response->setStatusCode($statusCode);
        }

        return $response;
    }

    /**
     * Возвращает JSON response с ошибкой transport/API уровня.
     *
     * @param array|string $errors Ошибка или список ошибок.
     * @param int $statusCode HTTP status code.
     * @return Response JSON response.
     */
    protected function displayProblem(array|string $errors, int $statusCode = 400): Response
    {
        return $this->displayJson(['errors' => $errors], $statusCode);
    }
}
