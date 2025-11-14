<?php
// Подключаем пролог админки Bitrix
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';

// Проверка прав (опционально)
if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm("Доступ запрещён");
}

// Подключаем наш модуль
\Bitrix\Main\Loader::includeModule('kpi.report');

// Импортируем основной класс
use Kpi\Report\Main as KpiMain;

$APPLICATION->SetTitle("Отчёт по KPI");

// Получаем входные параметры (дата, пользователь и т.д.)
$dateFrom = $_GET['DATE_FROM'] ?? date('Y-m-01');   // по умолчанию 1-е число текущего месяца
$dateTo   = $_GET['DATE_TO']   ?? date('Y-m-d');    // по умолчанию сегодня
$userId   = $_GET['USER_ID']   ?? $USER->GetID();   // по умолчанию текущий пользователь

// Форма фильтра
?>
    <form method="GET">
        <label>Дата с: <input type="date" name="DATE_FROM" value="<?=htmlspecialchars($dateFrom)?>"></label>
        <label>Дата по: <input type="date" name="DATE_TO" value="<?=htmlspecialchars($dateTo)?>"></label>
        <label>UserID: <input type="text" name="USER_ID" value="<?=htmlspecialchars($userId)?>"></label>
        <input type="submit" value="Показать отчёт">
    </form>
<?php

// Получаем данные отчёта
$report = KpiMain::getKpiReport($userId, $dateFrom, $dateTo);

?>
    <h2>Отчёт по KPI для пользователя #<?= htmlspecialchars($userId) ?></h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>Метрика (этап)</th>
            <th>План</th>
            <th>Факт</th>
            <th>% выполнения</th>
            <th>Бонус</th>
        </tr>
        <?php foreach ($report['ROWS'] as $row): ?>
            <tr>
                <td><?=htmlspecialchars($row['TITLE'])?></td>
                <td><?=htmlspecialchars($row['REQUIRED'])?></td>
                <td><?=htmlspecialchars($row['FACT'])?></td>
                <td><?=htmlspecialchars($row['PERCENT'])?>%</td>
                <td><?=htmlspecialchars($row['BONUS'])?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p><strong>Средний % выполнения:</strong> <?=$report['TOTAL_PERCENT']?>%</p>
    <p><strong>Бонус (итого):</strong> <?=$report['TOTAL_BONUS']?></p>

    <p><strong>Отработано часов:</strong> <?=$report['HOURS_WORKED'] ?? 0?></p>
    <p><strong>Оклад (часовая ставка * кол-во часов):</strong> <?=$report['SALARY'] ?? 0?></p>

    <p><strong>Итоговая сумма (оклад + бонус):</strong> <?=$report['SALARY_TOTAL'] ?? 0?></p>

<?php
// Подключаем эпилог админки
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';