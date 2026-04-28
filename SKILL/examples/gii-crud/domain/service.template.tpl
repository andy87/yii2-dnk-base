<?php

declare(strict_types=1);

namespace {{serviceNamespace}};

use andy87\yii2dnk\domain\BaseService;
use {{dataProviderFqcn}};
use {{domainFqcn}};
use {{modelFqcn}};
use yii\data\ActiveDataProvider;

/**
 * Описание класса {{serviceClass}}.
 *
 * CRUD-сервис домена {{domainName}}.
 * Использует Repository для чтения, Producer для создания моделей,
 * Killer для удаления и DataProvider для index/search.
 */
final class {{serviceClass}} extends BaseService
{
    protected const DOMAIN = {{domainClass}}::class;

    /**
     * Описание метода search.
     *
     * Назначение: подготовить ActiveDataProvider для index view.
     * SearchModel доступна через $this->getSearchModel() после вызова.
     *
     * @param array<string, mixed> $params Query/request параметры поиска.
     * @return ActiveDataProvider DataProvider для списка.
     * @throws \yii\base\InvalidConfigException Если data provider настроен некорректно.
     */
    public function search(array $params): ActiveDataProvider
    {
        /** @var {{dataProviderClass}} $builder */
        $builder = $this->getDataProvider();

        return $builder->search($params);
    }

    /**
     * Описание метода getById.
     *
     * Назначение: получить модель по id или выбросить NotFoundException.
     *
     * @param int $id Идентификатор модели.
     * @return {{modelClass}} Найденная модель.
     * @throws \Throwable Если repository не нашёл модель или настроен некорректно.
     */
    public function getById(int $id): {{modelClass}}
    {
        /** @var {{modelClass}} $model */
        $model = $this->getRepository()->findOrFail($id);

        return $model;
    }

    /**
     * Описание метода createForm.
     *
     * Назначение: создать runtime-модель формы и при POST сохранить новую запись.
     *
     * @param array<string, mixed> $data POST-данные формы.
     * @param bool $submitted Флаг отправки формы.
     * @return array{model: {{modelClass}}, saved: bool} Модель формы и статус сохранения.
     * @throws \yii\base\InvalidConfigException Если producer настроен некорректно.
     */
    public function createForm(array $data, bool $submitted): array
    {
        /** @var {{modelClass}} $model */
        $model = $this->getProducer()->createFormModel($submitted ? $data : []);

        if (!$submitted) {
            return ['model' => $model, 'saved' => false];
        }

        return ['model' => $model, 'saved' => $model->save()];
    }

    /**
     * Описание метода updateForm.
     *
     * Назначение: загрузить существующую модель и при POST сохранить изменения.
     *
     * @param int $id Идентификатор модели.
     * @param array<string, mixed> $data POST-данные формы.
     * @param bool $submitted Флаг отправки формы.
     * @return array{model: {{modelClass}}, saved: bool} Модель формы и статус сохранения.
     * @throws \Throwable Если repository не нашёл модель или настроен некорректно.
     */
    public function updateForm(int $id, array $data, bool $submitted): array
    {
        $model = $this->getById($id);

        if (!$submitted) {
            return ['model' => $model, 'saved' => false];
        }

        $model->load($data);

        return ['model' => $model, 'saved' => $model->save()];
    }

    // Update использует прямой $model->save(), т.к. Producer по дизайну
    // не предоставляет generic update. Для update-сценариев save() возвращает
    // bool — нормальный поток (не исключение).

    /**
     * Описание метода deleteById.
     *
     * Назначение: удалить модель по id.
     *
     * @param int $id Идентификатор модели.
     * @return bool True если удаление прошло успешно.
     * @throws \Throwable Если модель не найдена или удаление завершилось ошибкой.
     */
    public function deleteById(int $id): bool
    {
        return $this->getKiller()->delete($this->getById($id));
    }
}
