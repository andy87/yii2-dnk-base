<?php

declare(strict_types=1);

namespace andy87\yii2dnk\controllers\handlers;

use andy87\yii2dnk\BasePayload;
use andy87\yii2dnk\DomainAwareTrait;
use andy87\yii2dnk\domain\BaseDomain;
use andy87\yii2dnk\domain\BaseHandler;
use yii\base\InvalidConfigException;

/**
 * Общие методы getHandler() и getPayload() для web/console контроллеров DNK.
 *
 * Устраняет дублирование между BaseWebController и BaseConsoleController.
 *
 * @see BaseWebController
 * @see BaseConsoleController
 */
trait ControllerDomainTrait
{
    use DomainAwareTrait;

    /**
     * Создаёт экземпляр handler домена через реестр.
     *
     * Получает handler через domainClass()::create() и проверяет
     * что он наследует BaseHandler.
     *
     * @return BaseHandler Экземпляр handler домена.
     * @throws InvalidConfigException Если domainClass() не резолвится или определение handler невалидно.
     */
    protected function getHandler(): BaseHandler
    {
        $handler = static::domainClass()::create(BaseDomain::HANDLER);

        if (!$handler instanceof BaseHandler) {
            throw new InvalidConfigException(sprintf(
                'Handler for "%s" must extend "%s".',
                static::class,
                BaseHandler::class
            ));
        }

        return $handler;
    }

    /**
     * Создаёт payload для действия контроллера через реестр домена.
     *
     * Делегирует создание domainClass()::createPayload() с передачей
     * идентификатора действия и входных данных.
     *
     * @param string $action Идентификатор действия контроллера (например 'view').
     * @param array<string, mixed> $data Данные запроса для заполнения payload.
     * @return BasePayload Созданный экземпляр payload.
     * @throws InvalidConfigException Если domainClass() не резолвится, mapping payload невалиден или Yii DI не создал payload.
     */
    protected function getPayload(string $action, array $data = []): BasePayload
    {
        return static::domainClass()::createPayload($action, $data);
    }
}
