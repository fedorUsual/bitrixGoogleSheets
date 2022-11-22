<?php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
require_once __DIR__ . '/classes/GoogleSheetsConnector.php';
require_once __DIR__ . '/classes/FormCreator.php';
require_once __DIR__ . '/classes/SignParamsActions.php';
require_once __DIR__ . '/classes/FieldsRefactor.php';
require_once __DIR__ . '/classes/DataHandler.php';

use GoogleSheetsApp\FieldsRefactor;
use GoogleSheetsApp\GoogleSheetsConnector;
use GoogleSheetsApp\FormCreator;
use GoogleSheetsApp\SignParamsActions;
use GoogleSheetsApp\DataHandler;
global $APPLICATION;
$APPLICATION->SetTitle('Настройка Google таблиц');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
<?php
$newGoogle = new FormCreator();
$newGoogle->htmlEntityFormCreate();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php';