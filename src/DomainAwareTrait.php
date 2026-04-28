<?php

declare(strict_types=1);

namespace andy87\yii2dnk;

use andy87\yii2dnk\domain\BaseDomain;
use yii\base\InvalidConfigException;

/**
 * Определяет класс реестра домена для DNK-компонентов.
 *
 * Trait предоставляет механизм разрешения класса домена:
 * 1. Проверяет константу DOMAIN в использующем классе — если задана явно, использует её.
 * 2. Если константа пуста — угадывает имя по short class name, убирая суффикс
 *    (Handler, Repository, Producer и т.д.) и добавляя 'Domain'.
 * 3. Поиск угаданного класса идёт снизу вверх по namespace, пока не найдётся
 *    существующий класс, наследующий BaseDomain.
 *
 * Поддерживаемые суффиксы для автоопределения:
 * ConsoleController, WebController, Controller, ActiveDataProvider,
 * QueryStorage, Repository, Producer, Service, Handler, Payload,
 * Resource, ViewModel, Killer.
 */
trait DomainAwareTrait
{
    /**
     * Возвращает класс реестра домена для текущего наследника.
     *
     * Сначала проверяет явное объявление через DOMAIN, затем угадывает.
     * Бросает исключение если резолвленный класс не наследует BaseDomain.
     *
     * @return class-string<BaseDomain> FQCN класса реестра домена.
     * @throws InvalidConfigException Если резолвленный класс не наследует BaseDomain.
     */
    public static function domainClass(): string
    {
        $domainClass = static::declaredDomainClass() ?: static::guessDomainClass();

        if (!is_subclass_of($domainClass, BaseDomain::class)) {
            throw new InvalidConfigException(sprintf(
                'Class "%s" must extend "%s". Resolved for "%s".',
                $domainClass,
                BaseDomain::class,
                static::class
            ));
        }

        return $domainClass;
    }

    /**
     * Возвращает явно заданный класс домена из константы DOMAIN.
     *
     * Проверяет existence константы DOMAIN в текущем классе
     * и возвращает её значение как строку.
     *
     * @return class-string<BaseDomain>|'' FQCN домена или пустая строка если не задан.
     */
    private static function declaredDomainClass(): string
    {
        $constantName = static::class . '::DOMAIN';

        if (!defined($constantName)) {
            return '';
        }

        $domainClass = constant($constantName);

        return is_string($domainClass) ? $domainClass : '';
    }

    /**
     * Угадывает класс домена по имени текущего класса.
     *
     * Убирает известный суффикс из short class name (например 'ItemHandler' -> 'ItemDomain'),
     * затем ищет класс с таким именем снизу вверх по уровням namespace.
     * Если класс не найден — возвращает fallback-имя в текущем namespace.
     *
     * @return class-string<BaseDomain> Угаданный FQCN класса домена.
     */
    private static function guessDomainClass(): string
    {
        $class = static::class;
        $parts = explode('\\', $class);
        $shortName = (string) array_pop($parts);
        $domainShortName = self::domainShortName($shortName);
        $fallback = ($parts === [] ? '' : implode('\\', $parts) . '\\') . $domainShortName;

        for ($length = count($parts); $length >= 0; $length--) {
            $namespace = implode('\\', array_slice($parts, 0, $length));
            $candidate = ($namespace === '' ? '' : $namespace . '\\') . $domainShortName;

            if (class_exists($candidate) && is_subclass_of($candidate, BaseDomain::class)) {
                return $candidate;
            }
        }

        return $fallback;
    }

    /**
     * Вычисляет ожидаемое short-имя класса домена по short-имени DNK-класса.
     *
     * Перебирает массив поддерживаемых суффиксов, находит совпадение
     * и заменяет суффикс на 'Domain'. Если ни один суффикс не найден —
     * добавляет 'Domain' к исходному имени.
     *
     * Примеры: ItemHandler -> ItemDomain, ItemWebController -> ItemDomain,
     * Foo -> FooDomain.
     *
     * @param string $shortName Short-имя текущего класса (без namespace).
     * @return string Ожидаемое short-имя класса домена.
     */
    private static function domainShortName(string $shortName): string
    {
        $suffixes = [
            'ConsoleController',
            'WebController',
            'Controller',
            'ActiveDataProvider',
            'QueryStorage',
            'Repository',
            'Producer',
            'Payload',
            'Service',
            'Handler',
            'Resource',
            'ViewModel',
            'Killer',
        ];

        foreach ($suffixes as $suffix) {
            if (str_ends_with($shortName, $suffix)) {
                return substr($shortName, 0, -strlen($suffix)) . 'Domain';
            }
        }

        return $shortName . 'Domain';
    }
}
