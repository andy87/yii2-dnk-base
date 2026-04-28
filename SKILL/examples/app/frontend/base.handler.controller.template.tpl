<?php

declare(strict_types=1);

namespace {{handlerControllerNamespace}};

use {{commonHandlerControllerFqcn}} as CommonBaseHandlerController;

/**
 * Базовый frontend web-контроллер приложения для DNK flow.
 *
 * Наследует общие transport helpers из common-level BaseHandlerController.
 * Frontend-специфичные helpers добавляются здесь.
 */
abstract class BaseHandlerController extends CommonBaseHandlerController
{
}
