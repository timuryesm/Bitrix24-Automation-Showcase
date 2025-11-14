<?php
namespace Kpi\Report;

use Bitrix\Main\Loader;
use Bitrix\Crm\DealTable;
use Bitrix\Voximplant\StatisticTable as VoxCalls; // если используете телефонию
use Kpi\Report\HL\KpiPlanTable;                  // наш класс HL-блока
use Kpi\Report\Timeman\WorkHours;                // класс учёта рабочего времени (при необходимости)

class Main
{
    /**
     * Сформировать отчёт KPI по заданному пользователю и периоду
     * (Дата "от" и "до"). Возвращает массив с деталями (rows, percent, bonus и т.д.).
     */
    public static function getKpiReport($userId, $dateFrom, $dateTo)
    {
        // Подключаем модули, если нужны
        Loader::includeModule('crm');
        Loader::includeModule('highloadblock');
        // Если используете телефонию:
        // Loader::includeModule('voximplant');
        // Если нужен учёт рабочего времени:
        // Loader::includeModule('timeman');

        // 1. Получаем план KPI из HL-блока
        $planData = self::getPlanData();

        // 2. Набор «этапов» или метрик, которые нужно считать
        $stages = [
            'TAKE_TO_WORK'    => 'Взять в работу',
            'MEETING_SUCCESS' => 'Встреча (успешно)',
            'CALLS_MADE'      => 'Звонки',
        ];

        $rows = [];
        foreach ($stages as $stageCode => $title) {
            // План
            $required = (float)($planData[$stageCode]['UF_REQUIRED_QUANTITY'] ?? 0);

            // Факт
            $fact = self::getFactValue($stageCode, $dateFrom, $dateTo, $userId);

            // % выполнения
            $percent = ($required > 0) ? ($fact / $required) * 100 : 0;

            // Бонус по этапу (по шкале)
            $stageBonus = self::calculateBonus($percent);

            $rows[] = [
                'STAGE_CODE' => $stageCode,
                'TITLE'      => $title,
                'REQUIRED'   => $required,
                'FACT'       => $fact,
                'PERCENT'    => round($percent, 2),
                'BONUS'      => $stageBonus,
            ];
        }

        // 3. Итоги по всем этапам
        $totalPercent = 0;
        foreach ($rows as $row) {
            $totalPercent += $row['PERCENT'];
        }
        if (count($rows) > 0) {
            $totalPercent = $totalPercent / count($rows); // средний процент
        }
        $totalPercent = round($totalPercent, 2);

        $totalBonus = self::calculateBonus($totalPercent);

        // 4. Оклад по рабочим часам (если нужно)
        // Допустим, ставка у нас 1000 (тенге/руб./у.е.) в час (просто пример):
        $hourlyRate = 1000;
        $hoursWorked = WorkHours::getUserWorkHours($userId, $dateFrom, $dateTo);
        $salary = $hoursWorked * $hourlyRate;
        $salary = round($salary, 2);

        // 5. Общая сумма (оклад + бонус)
        $totalSalary = $salary + $totalBonus;

        // Формируем итоговую структуру
        return [
            'ROWS' => $rows,               // детальная информация по каждому этапу
            'TOTAL_PERCENT' => $totalPercent,
            'TOTAL_BONUS'   => $totalBonus,
            'HOURS_WORKED'  => $hoursWorked,
            'SALARY'        => $salary,
            'SALARY_TOTAL'  => $totalSalary,
        ];
    }

    /**
     * Получаем "план" из HL-блока KPI (используем KpiPlanTable).
     */
    protected static function getPlanData()
    {
        $data = [];
        $res = KpiPlanTable::getList([
            'select' => ['*']
        ]);
        while ($row = $res->fetch()) {
            $stageCode = $row['UF_STAGE_CODE'];
            $data[$stageCode] = $row;
        }
        return $data;
    }

    /**
     * Определяем факт (кол-во) в зависимости от кода метрики.
     */
    protected static function getFactValue($stageCode, $dateFrom, $dateTo, $userId)
    {
        switch ($stageCode) {
            case 'TAKE_TO_WORK':
                // Пример: количество сделок, попавших в стадию C1:NEW
                return self::getDealsCount('C1:NEW', $dateFrom, $dateTo, $userId);

            case 'MEETING_SUCCESS':
                // Пример: сделки с успехом (C1:WON) или конкретная стадия
                return self::getDealsCount('C1:WON', $dateFrom, $dateTo, $userId);

            case 'CALLS_MADE':
                // Количество звонков (телефония)
                return self::getCallsCount($dateFrom, $dateTo, $userId);
        }

        return 0;
    }

    /**
     * Пример: получаем количество сделок, у которых STAGE_ID = $stageId и созданы за период
     */
    protected static function getDealsCount($stageId, $dateFrom, $dateTo, $userId)
    {
        // Пример для CRM сделок
        $filter = [
            '=STAGE_ID'      => $stageId,
            '>=DATE_CREATE'  => $dateFrom,
            '<=DATE_CREATE'  => $dateTo,
            '=ASSIGNED_BY_ID' => $userId
        ];
        return DealTable::getCount($filter);
    }

    /**
     * Пример: считаем звонки через модуль Voximplant
     * (нужен Loader::includeModule('voximplant'))
     */
    protected static function getCallsCount($dateFrom, $dateTo, $userId)
    {
        if (Loader::includeModule('voximplant')) {
            $filter = [
                '>=CALL_START_DATE' => $dateFrom,
                '<=CALL_START_DATE' => $dateTo,
                '=PORTAL_USER_ID' => $userId,
            ];
            return VoxCalls::getCount($filter);
        }
        // Если не используете телефонию - вернуть заглушку или 0
        return 0;
    }

    /**
     * Расчёт бонуса на основе процента выполнения, согласно шкале:
     * >= 100% => 100000
     * 80-99%  => 65000
     * 65-79%  => 50000
     * 50-64%  => 25000
     * < 50%   => 10000
     */
    public static function calculateBonus($percent)
    {
        if ($percent >= 100) {
            return 100000;
        } elseif ($percent >= 80) {
            return 65000;
        } elseif ($percent >= 65) {
            return 50000;
        } elseif ($percent >= 50) {
            return 25000;
        } else {
            return 10000;
        }
    }
}