<?php
namespace Kpi\Report\Timeman;

use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\Timeman\Model\EntriesTable;

/**
 * Упрощённый пример для подсчёта отработанных часов
 * (включите модуль timeman)
 */
class WorkHours
{
    /**
     * Возвращает количество часов, отработанных userId за период
     * [dateFrom; dateTo], используя записи из Time Man
     */
    public static function getUserWorkHours($userId, $dateFrom, $dateTo)
    {
        if (!Loader::includeModule('timeman')) {
            return 0;
        }

        // Перевод строковых дат в тип Bitrix Date
        $dFrom = new Date($dateFrom, 'Y-m-d');
        $dTo = new Date($dateTo, 'Y-m-d');

        $filter = [
            '>=DATE_START' => $dFrom,
            '<=DATE_START' => $dTo,
            '=USER_ID' => $userId,
        ];

        $totalSeconds = 0;

        // EntriesTable - стандартный класс в модуле timeman
        $res = EntriesTable::getList([
            'select' => ['ID', 'DURATION'],
            'filter' => $filter,
        ]);
        while ($row = $res->fetch()) {
            $totalSeconds += (int)$row['DURATION'];
        }

        // Переводим секунды в часы
        $hours = $totalSeconds / 3600;
        return round($hours, 2);
    }
}