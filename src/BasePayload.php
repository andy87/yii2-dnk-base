<?php

declare(strict_types=1);

namespace andy87\yii2dnk;

use andy87\yii2dnk\exceptions\ValidationException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

/**
 * Базовый DTO входных данных для DNK действий.
 *
 * Payload содержит только входные данные конкретного действия контроллера.
 * Не читает request самостоятельно и не содержит бизнес-логику.
 *
 * Загрузка данных выполняется явно через loadData() или createPayload():
 * - BaseDomain::createPayload() — создаёт payload через Yii::createObject(),
 *   загружает данные через loadData() и валидирует через validateOrFail().
 * - BasePayload::fromArray() — статическая фабрика, создаёт и загружает данные.
 */
abstract class BasePayload extends Model
{
    /**
     * @param array<string, mixed> $config Конфигурация Yii-объекта.
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
    }

    /**
     * Создаёт экземпляр payload из массива данных.
     *
     * Статический фабричный метод для удобного создания payload
     * без явного использования new.
     *
     * @param array<string, mixed> $data Входные данные для заполнения payload.
     * @return static Созданный экземпляр payload.
     * @throws InvalidConfigException Если Yii DI не смог создать payload.
     */
    public static function fromArray(array $data): static
    {
        $payload = Yii::createObject(static::class);

        if (!$payload instanceof static) {
            throw new InvalidConfigException(sprintf(
                'Payload "%s" must be instance of "%s".',
                $payload::class,
                static::class
            ));
        }

        return $payload->loadData($data);
    }

    /**
     * Загружает данные в payload через Yii Model::load().
     *
     * Использует пустой formName для работы с request-массивами.
     * Возвращает $this для цепочки вызовов.
     *
     * @param array<string, mixed> $data Входные данные.
     * @param string $formName Имя формы. По умолчанию пустая строка.
     * @return static Текущий payload.
     * @throws ValidationException Если загрузка привела к ошибке типов.
     */
    public function loadData(array $data, string $formName = ''): static
    {
        try {
            $this->load($data, $formName);
        } catch (Throwable $exception) {
            throw new ValidationException(
                ['payload' => [$exception->getMessage()]],
                'Payload load failed.',
                422,
                $exception
            );
        }

        return $this;
    }

    /**
     * Валидирует payload и выбрасывает ValidationException при ошибках.
     *
     * @param array<int, string>|null $attributeNames Атрибуты для проверки.
     * @param bool $clearErrors Очищать ли старые ошибки перед проверкой.
     * @return static Валидный payload.
     * @throws ValidationException Если payload не прошёл валидацию.
     */
    public function validateOrFail(?array $attributeNames = null, bool $clearErrors = true): static
    {
        if (!$this->validate($attributeNames, $clearErrors)) {
            throw new ValidationException($this->getErrors());
        }

        return $this;
    }
}
