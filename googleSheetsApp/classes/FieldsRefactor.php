<?php
namespace GoogleSheetsApp;

/**
 * класс преобразователь пользовательских полей
 */
class FieldsRefactor
{
    /**
     * @var string[] $fieldTypes типы данных пользовательских полей, которые может обрабатывать класс
     */
    private static $fieldTypes = [
        'double', // число
        'integer', // целове число
        'enumeration', // список
        'boolean', // Да/Нет
        'date', // число
        'datetime', // число
        'money', // деньги
        'string', // строка
        'url', // ссылка
        'file', // файл
        'address', // адрес
        'iblock_element', // привязка к элементу инфорблока
        'crm', // привязка к элементу crm
        'employee', // привязка к сотруднику
    ];

    public static function refactor(array $fieldType, $fieldValue)
    {
        if (!in_array($fieldType['TYPE'], self::$fieldTypes))
        {
            return 'Error this class does not work with this type of field';
        }
        else
        {
            $result = '';
            if (is_array($fieldValue))
            {
                foreach ($fieldValue as $val)
                {
                    if ($fieldType['TYPE'] == 'double') {
                        $result .= self::doubleRefactor($val);
                    } elseif ($fieldType['TYPE'] == 'enumeration') {
                        $result .= self::enumerationRefactor($val);
                    } elseif ($fieldType['TYPE'] == 'boolean') {
                        $result .= self::booleanRefactor($val);
                    } elseif ($fieldType['TYPE'] == 'date') {
                        $result .= self::dateRefacror($val);
                    } elseif ($fieldType['TYPE'] == 'datetime') {
                        $result .= self::dateTimeRefactor($val);
                    } elseif ($fieldType['TYPE'] == 'money') {
                        $result .= self::moneyRefactor($val);
                    } elseif ($fieldType['TYPE'] == 'url') {
                        $result .= self::urlRefactor($val);
                    } elseif ($fieldType['TYPE'] == 'file') {
                        $result .= self::fileRefactor($val);
                    } elseif ($fieldType['TYPE'] == 'iblock_element') {
                        $result .= self::iblockRefactor($val);
                    } elseif ($fieldType['TYPE'] == 'crm') {
                        $result .= self::crmRefactor($fieldType['NAME'], $val);
                    } else {
                        $result .= self::stringRefactor($val);
                    }
                }
            }
            else
            {
                if ($fieldType['TYPE'] == 'double') {
                    $result = self::doubleRefactor($fieldValue);
                } elseif ($fieldType['TYPE'] == 'enumeration') {
                    $result = self::enumerationRefactor($fieldValue);
                } elseif ($fieldType['TYPE'] == 'boolean') {
                    $result = self::booleanRefactor($fieldValue);
                } elseif ($fieldType['TYPE'] == 'date') {
                    $result = self::dateRefacror($fieldValue);
                } elseif ($fieldType['TYPE'] == 'datetime') {
                    $result = self::dateTimeRefactor($fieldValue);
                } elseif ($fieldType['TYPE'] == 'money') {
                    $result = self::moneyRefactor($fieldValue);
                } elseif ($fieldType['TYPE'] == 'url') {
                    $result = self::urlRefactor($fieldValue);
                } elseif ($fieldType['TYPE'] == 'file') {
                    $result = self::fileRefactor($fieldValue);
                } elseif ($fieldType['TYPE'] == 'iblock_element') {
                    $result = self::iblockRefactor($fieldValue);
                } elseif ($fieldType['TYPE'] == 'employee') {
                    $result = self::userRefactor($fieldValue);
                } elseif ($fieldType['TYPE'] == 'crm') {
                    $result = self::crmRefactor($fieldType['NAME'], $fieldValue);
                } else {
                    $result = self::stringRefactor($fieldValue);
                }
            }

            return $result;
        }
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed возвращает преобразованное значение пользовательского поля
     */
    private static function doubleRefactor($value)
    {
        return $value;
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed возвращает преобразованное значение пользовательского поля
     */
    private static function enumerationRefactor($value)
    {
        if (!empty($value)) {
            $result = \CUserFieldEnum::GetList([], ['ID' => $value])->GetNext()['VALUE'];
        }
        return html_entity_decode($result);
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed возвращает преобразованное значение пользовательского поля
     */
    private static function booleanRefactor($value)
    {
        $result = '';
        if (!empty($value)) {
            $result = ($value != false ? 'Да' : 'Нет');
        }
        return $result;
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed возвращает преобразованное значение пользовательского поля
     */
    private static function dateRefacror($value)
    {
        $result = (!empty($value) ? date('d.m.Y', strtotime($value)) : '');
        return $result;
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed возвращает преобразованное значение пользовательского поля
     */
    private static function dateTimeRefactor($value)
    {
        $result = (!empty($value) ? date('d.m.Y H:i:s', strtotime($value)) : '');
        return $result;
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed возвращает преобразованное значение пользовательского поля
     */
    private static function moneyRefactor($value)
    {
        $result = '';
        if (!empty($value)) {
            $result = stristr($value, '|', true);
        }

        return self::doubleRefactor($result);
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed возвращает гиперссылку для вставки в таблицу
     */
    private static function urlRefactor($value)
    {
        $result = '';
        if (!empty($value)) {
            $result = self::linkMaker($value);
        }
        return $result;
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed возвращает гиперссылку на файл, для вставки в таблицу
     */
    private static function fileRefactor($value)
    {
        $result = '';
        if (!empty($value)) {
            $file = \CFile::GetFileArray($value);
            if ($_SERVER['HTTPS'] == 'on') {
                $http = 'https://';
            } else {
                $http = 'http://';
            }
            $result = self::linkMaker($http . $_SERVER['SERVER_NAME'] . $file);
        }
        return $result;
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed|string возвращает название элемента инфоблока
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function iblockRefactor($value)
    {
        $result = '';
        if (!empty($value)) {
            $result = \Bitrix\Iblock\ElementTable::getList(['filter' => ['ID' => (int)$value]])->fetch();
        }
        return $result['NAME'];
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed возвращает ФИО пользователя
     */
    private static function userRefactor($value)
    {
        $result = '';
        if (!empty($value)) {
            $user = \CUser::GetByID($value)->GetNext();
            $result = false;
            if ($user) {
                $result = $user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME'];
            }
        }
        return $result;
    }

    /**
     * @param $fieldName string символьный код поля
     * @param $value string значение поля
     * @return string заголовок сущности crm
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function crmRefactor($fieldName, $value)
    {
        $result = '';
        if (!empty($value)) {
            $entities = [];
            $resField = \Bitrix\Main\UserFieldTable::getList(['filter' => ['FIELD_NAME' => $fieldName]]);
            while ($item = $resField->fetch()) {
                foreach ($item['SETTINGS'] as $entityType => $include) {
                    if ($include == 'Y') {
                        $entities[] = $entityType;
                    }
                }
            }

            $result = '';
            if (count($entities) === 1) {
                $result = self::getCrmFieldValByTypeAndId(current($entities), $value);
            } elseif (count($entities) > 1) {
                list($entityAbbr, $entityId) = explode('_', $value);
                $type = \CCrmOwnerTypeAbbr::ResolveName($entityAbbr);
                $result = self::getCrmFieldValByTypeAndId($type, $entityId);
            }
        }
        return $result;
    }

    /**
     * @param $value string начальное значение пользовательского поля
     * @return mixed возвращает преобразованное значение пользовательского поля
     */
    private static function stringRefactor($value)
    {
        return html_entity_decode($value);
    }

    /**
     * @param $type string тип сущности CRM
     * @param $id string ID сущности CRM
     * @return string заголовок сущности CRM
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function getCrmFieldValByTypeAndId($type, $id)
    {
        $result = '';
        if ($type === 'LEAD') {
            $resVal = \Bitrix\Crm\LeadTable::getList(['filter' => ['ID' => $id], 'select' => ['ID', 'TITLE']])->fetch();
            $result = $resVal['TITLE'];
        } elseif ($type === 'DEAL') {
            $resVal = \Bitrix\Crm\DealTable::getList(['filter' => ['ID' => $id], 'select' => ['ID', 'TITLE']])->fetch();
            $result = $resVal['TITLE'];
        } elseif ($type === 'CONTACT') {
            $resVal = \Bitrix\Crm\ContactTable::getList(['filter' => ['ID' => $id], 'select' => ['ID', 'TITLE']])->fetch();
            $result = $resVal['TITLE'];
        } elseif ($type === 'COMPANY') {
            $resVal = \Bitrix\Crm\CompanyTable::getList(['filter' => ['ID' => $id], 'select' => ['ID', 'TITLE']])->fetch();
            $result = $resVal['TITLE'];
        }

        return html_entity_decode($result);
    }

    /**
     * @param $link string ссылка для гиперссылки в таблице
     * @param $text string текст для гиперссылки
     * @return string возвращает гиперссылку для вставки в таблицу
     */
    public static function linkMaker($link, $text = '')
    {
        $result = '=ГИПЕРССЫЛКА("' .  $link . '"; "' . (!empty(htmlspecialchars_decode($text)) ? $text : $link) . '")';
        return $result;
    }

}