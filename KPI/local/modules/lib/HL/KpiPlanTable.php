<?php
namespace Kpi\Report\HL;

use Bitrix\Main\Entity;
use Bitrix\Main\SystemException;
use Bitrix\Highloadblock\HighloadBlockTable;

/**
 * Обёртка над HL-блоком, где храним план по KPI
 * (UF_STAGE_CODE, UF_REQUIRED_QUANTITY, UF_BONUS_MAX, и т.д.)
 */
class KpiPlanTable extends Entity\DataManager
{
    /**
     * ID HL-блока (укажите свой)
     */
    public static function getHlBlockId()
    {
        // Поменяйте на реальный ID вашего HL-блока
        return 1;
    }

    /**
     * Название таблицы в БД (нужно для наследования от DataManager)
     * Обычно HL-блок создаёт таблицу вида b_hlbd_[название], уточните её в админке
     */
    public static function getTableName()
    {
        return 'b_hlbd_kpi_plan';
    }

    /**
     * Компиляция сущности HL-блока
     */
    protected static function compileEntity()
    {
        $hlBlockId = static::getHlBlockId();
        $hlBlock = HighloadBlockTable::getById($hlBlockId)->fetch();
        if (!$hlBlock) {
            throw new SystemException("HL-блок с ID {$hlBlockId} не найден!");
        }

        return HighloadBlockTable::compileEntity($hlBlock);
    }

    /**
     * Переопределяем getEntity(), чтобы вернуть «скомпилированную» сущность
     */
    public static function getEntity()
    {
        static $entity;
        if (!$entity) {
            $entity = static::compileEntity();
        }
        return $entity;
    }

    /**
     * Карта полей (если хотим пользоваться ORM в полную силу)
     */
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new Entity\StringField('UF_STAGE_CODE'),
            new Entity\IntegerField('UF_REQUIRED_QUANTITY'),
            new Entity\IntegerField('UF_BONUS_MAX'),
        ];
    }
}