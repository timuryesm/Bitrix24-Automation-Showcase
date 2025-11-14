<?php
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    'kpi.report',
    [
        // Основной класс логики
        'Kpi\\Report\\Main' => 'lib/Main.php',

        // HL-блок плана
        'Kpi\\Report\\HL\\KpiPlanTable' => 'lib/HL/KpiPlanTable.php',

        // (Опционально) Лог сохранения итогов
        'Kpi\\Report\\Reports\\KpiReportTable' => 'lib/Reports/KpiReportTable.php',

        // (Опционально) Учёт рабочего времени
        'Kpi\\Report\\Timeman\\WorkHours' => 'lib/Timeman/WorkHours.php',
    ]
);