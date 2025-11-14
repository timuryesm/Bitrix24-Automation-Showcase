<?php
namespace Kpi\Report\Reports;

use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

/**
 * Если нужно хранить результаты готовых отчётов/логов в своей таблице
 * Придётся предварительно создать таблицу в БД (через миграцию или вручную),
 * например:
 * CREATE TABLE kpi_report_log(
 *   ID int auto_increment primary key,
 *   USER_ID int,
 *   DATE_FROM date,
 *   DATE_TO date,
 *   TOTAL_PERCENT float,
 *   TOTAL_BONUS float,
 *   CREATED_AT datetime
 * );
 */
class KpiReportTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'kpi_report_log'; // название вашей таблицы
    }

    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new Entity\IntegerField('USER_ID'),
            new Entity\DateField('DATE_FROM'),
            new Entity\DateField('DATE_TO'),
            new Entity\FloatField('TOTAL_PERCENT'),
            new Entity\FloatField('TOTAL_BONUS'),
            new Entity\DatetimeField('CREATED_AT', [
                'default_value' => function(){
                    return new DateTime();
                },
            ]),
        ];
    }
}