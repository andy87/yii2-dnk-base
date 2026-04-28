<?php

declare(strict_types=1);

namespace andy87\yii2dnk\interfaces;

/**
 * Маркерный интерфейс для доменных search-моделей.
 *
 * DNK не требует отдельный BaseSearchModel: поисковая модель обычно
 * наследуется от боевой ActiveRecord-модели домена. Интерфейс можно
 * использовать в проекте для явной маркировки таких моделей.
 */
interface SearchModelInterface
{
}
