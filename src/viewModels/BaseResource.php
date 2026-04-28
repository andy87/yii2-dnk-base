<?php

declare(strict_types=1);

namespace andy87\yii2dnk\viewModels;

/**
 * Базовый resource для API/REST-ответов DNK flow.
 *
 * Класс не добавляет поведения поверх BaseViewModel, но фиксирует назначение
 * выходной модели как transport/resource DTO для API-ответов.
 */
abstract class BaseResource extends BaseViewModel
{
}
