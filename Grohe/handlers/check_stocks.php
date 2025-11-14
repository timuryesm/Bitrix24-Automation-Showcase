<?php

// Получаем ID сделки из GET или структуры события
$dealId = isset($_GET['deal_id']) ? (int)$_GET['deal_id'] : 0;
if ($dealId <= 0 && isset($_REQUEST['data']['FIELDS']['ID'])) {
    $dealId = (int) $_REQUEST['data']['FIELDS']['ID'];
}

// Если ID сделки не найден, ничего не делаем
if ($dealId <= 0) {
    exit();
}

// Включаем отображение ошибок (при необходимости)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Адрес REST вебхука
$webhookUrl = 'https://crm.grohe.kz/rest/1/webhook/';

// Функция для вызова методов Bitrix через REST
function callBitrixMethod($method, $params = array())
{
    global $webhookUrl;
    $url = $webhookUrl . $method;
    $postData = http_build_query($params);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $response = curl_exec($ch);
    //$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код ответа
    curl_close($ch);

    return json_decode($response, true);
}

// Проверяем стадию сделки (по умолчанию считаем «Успех» = 'C1:WON')
$finalStageId = 'C1:WON';
$dealInfo = callBitrixMethod('crm.deal.get', ['id' => $dealId]);

if (empty($dealInfo['result'])) {
    exit();
}

$currentStage = $dealInfo['result']['STAGE_ID'];
// Если нужная стадия не установлена — выходим, чтобы не слать уведомления
if ($currentStage !== $finalStageId) {
    exit();
}

// Для коробочной версии Bitrix подключаем ядро
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
$_SERVER['DOCUMENT_ROOT'] = '/home/bitrix/www';
// Подключаем ядро Битрикс (ОБЯЗАТЕЛЬНО ДЛЯ КОРОБОЧНОЙ ВЕРСИИ!)
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Bitrix\Catalog\StoreTable;

// Загружаем модули
if (!Loader::includeModule('catalog')) {
    exit('Модуль catalog не загружен.');
}

// Порог остатка
$minQuantity = 5;

// Получаем «ответственных» за каждый склад (UF_CAT_STORE_1738047081)
$warehouseManagers = array();
$res = StoreTable::getList([
    'select' => ['ID', 'TITLE', 'UF_CAT_STORE_1738047081'], // Запрашиваем ID склада и кастомное поле
    'order'  => ['ID' => 'ASC'],
]);

while ($store = $res->fetch()) {
    $storeId = isset($store['ID']) ? (int)$store['ID'] : 0;
    $managerUserId = isset($store['UF_CAT_STORE_1738047081']) ? (int)$store['UF_CAT_STORE_1738047081'] : 0;

    if ($storeId > 0 && $managerUserId > 0) {
        $warehouseManagers[$storeId] = $managerUserId;
    }
}

// Запрашиваем товары на складах через REST API
$allStoreProducts = array();
$start = 0;
do {
    $storeProductsResponse = callBitrixMethod('catalog.storeproduct.list', array(
        'order'  => array('STORE_ID' => 'ASC'),
        'filter' => array(),
        'select' => array('ID', 'STORE_ID', 'PRODUCT_ID', 'AMOUNT'),
        'start'  => $start
    ));

    $resultData = isset($storeProductsResponse['result']) ? $storeProductsResponse['result'] : array();
    $allStoreProducts = array_merge($allStoreProducts, $resultData);

    $next = isset($storeProductsResponse['next']) ? $storeProductsResponse['next'] : false;
    if ($next !== false) {
        $start = $next;
    } else {
        break;
    }
} while (true);

foreach ($allStoreProducts['storeProducts'] as $item) {
    if (!isset($item['storeId'], $item['productId'], $item['amount'])) {
        continue;
    }

    $storeId   = (int)$item['storeId'];
    $productId = (int)$item['productId'];
    $amount    = isset($item['amount']) && $item['amount'] !== "" ? (int)$item['amount'] : 0;

    // Название склада
    $storeRes = StoreTable::getList([
        'select' => ['TITLE'],
        'filter' => ['ID' => $storeId]
    ]);
    $storeRow = $storeRes->fetch();
    $storeName = isset($storeRow['TITLE']) ? $storeRow['TITLE'] : "";

    // 🔹 Получаем название товара через REST API
    $productResponse = callBitrixMethod('catalog.product.get', array('id' => $productId));

// Проверяем, в каком поле API возвращает название
    if (isset($productResponse['result']['product']['name'])) {
        $productName = $productResponse['result']['product']['name'];
    } elseif (isset($productResponse['result']['name'])) {
        $productName = $productResponse['result']['name'];
    } else {
        $productName = "";
    }

    if ($amount <= $minQuantity) {
        if (!isset($warehouseManagers[$storeId])) {
            continue;
        }

        $managerUserId = $warehouseManagers[$storeId];

        // Отправляем уведомление
        $message = "🔴 Внимание! Остаток товара \"$productName\" на складе \"$storeName\" "
            . "упал до 5 шт. или ниже. Необходимо пополнить запас.";

        $notifyResponse = callBitrixMethod('im.notify.system.add', array(
            'USER_ID' => $managerUserId,
            'MESSAGE' => $message
        ));
    }
}
