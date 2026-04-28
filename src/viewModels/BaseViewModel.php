<?php

declare(strict_types=1);

namespace andy87\yii2dnk\viewModels;

use yii\base\Model;

/**
 * Базовый DTO выходных данных для DNK действий.
 *
 * View model описывает структуру данных ответа для view/API.
 * Не содержит бизнес-логику. Handler заполняет публичные свойства,
 * контроллер рендерит или сериализует результат.
 */
abstract class BaseViewModel extends Model
{
    /**
     * @param array<string, mixed> $config Конфигурация Yii-объекта.
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * Метод для получения данных для view model в виде массива.
     *
     * @param array<string, mixed> $params Дополнительные параметры для объединения с данными view model.
     * @return array<string, mixed> Массив данных view model.
     */
    public function release(array $params = []): array
    {
        $data = $this->toArray();

        return array_merge($data, $params);
    }
}
