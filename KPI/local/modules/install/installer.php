<?php
namespace kpi\report;

use Bitrix\Main\ModuleManager;

class Installer extends \CModule
{
    public function __construct()
    {
        $this->MODULE_ID = 'kpi.report';
        $this->MODULE_VERSION = '1.0.0';
        $this->MODULE_VERSION_DATE = '2025-03-12';
        $this->MODULE_NAME = 'KPI Report';
        $this->MODULE_DESCRIPTION = 'Модуль для отчёта по KPI';
    }

    public function DoInstall()
    {
        // Регистрируем модуль
        ModuleManager::registerModule($this->MODULE_ID);

        // Можно добавить здесь создание таблиц, HL-блоков, пунктов меню
        // ...
        return true;
    }

    public function DoUninstall()
    {
        // Удалить всё, что мы создали (при необходимости)
        // ...
        // Отменяем регистрацию модуля
        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }
}
