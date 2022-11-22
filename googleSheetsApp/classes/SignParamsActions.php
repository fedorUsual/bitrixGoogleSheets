<?php
namespace GoogleSheetsApp;

use GoogleSheetsApp\FormCreator;

/**
 * класс для обработки аякс запросов
 */
class SignParamsActions
{
    /**
     * @var string путь до файла настроек, должен находится в одной директории с данным классом
     */
    private static $configFilepath = __DIR__ . '/configs_';

    public static function action($actionName)
    {
        return self::$actionName();
    }

    /**
     * @return string записывает данные о выбранном типе сущности и формирует относительно этого
     * форму для заполнения соответствия заголовков таблицы и полей сущности CRM
     */
    public static function signEntityType()
    {
        $entityType = $_POST['entity_type'];
        $resultArr = json_decode(file_get_contents(self::$configFilepath . mb_strtolower($entityType) . '.json'), true);
        $resultArr['spreadsheet_id'] = $_POST['spreadsheet_id'];
        $resultArr['list_name'] = $_POST['list_name'];
        $resultArr['entity_type'] = $entityType;
        file_put_contents(self::$configFilepath . mb_strtolower($entityType) . '.json', json_encode($resultArr));
        $fieldForm = new FormCreator();
        return $fieldForm->getFieldsForm($entityType);
    }

    /**
     * @return void записыввает массив соответствия заголовков таблицы и полей сущности CRM
     */
    private static function signEntityAndTableFields()
    {
        $entityType = $_POST['entity_type'];
        $entityTableFields = $_POST['fields'];
        $entityIdField = array_filter($_POST['fields'], function ($v) {
            return $v['columnName'] === 'ID';
        });
        if (count($entityIdField) <= 0) {
            return ['result' => 'error', 'text' => 'Поле ID сущности обязательно должно присутствовать в таблице, иначе соотнесение данных CRM и гугл таблицы будет невозможно!'];
        }

        $result = json_decode(file_get_contents(self::$configFilepath . mb_strtolower($entityType) . '.json'), true);
        $result['entity_table_fields'] = $entityTableFields;
        file_put_contents(self::$configFilepath . mb_strtolower($entityType) . '.json', json_encode($result));
        return ['result' => 'ok', 'text' => ''];
    }

    /**
     * @return mixed возвращает параметры гугл таблицы ее id и имя активного листа
     */
    public function getTableParams()
    {
        $entityType = $_POST['entity_type'];
        $res = file_get_contents(self::$configFilepath . mb_strtolower($entityType) . '.json');
        $res = json_decode($res, true);
        $result = [
            'spreadsheet_id' => $res['spreadsheet_id'],
            'list_name' => $res['list_name']
        ];
        return $res;
    }
}