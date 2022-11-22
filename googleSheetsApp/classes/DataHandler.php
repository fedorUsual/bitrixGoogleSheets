<?php
namespace GoogleSheetsApp;
/**
 * класс для
 */

class DataHandler
{
    private string $entityType;
    private array $configs;
    private array $hasStagesEntities = ['LEAD', 'DEAL'];
    private $connector;

    public function __construct($entityType)
    {
        \CModule::IncludeModule('main');
        \CModule::IncludeModule('crm');

        $this->entityType = $entityType;
        $connector = new GoogleSheetsConnector($entityType);
        $this->connector = $connector;
        $this->configs = $this->connector::$configs;
    }

    private function getLeadInfo($leadId)
    {
        $lead = \CCrmLead::GetList([], ['ID' => $leadId])->GetNext();
        return $lead;
    }

    private function getDealInfo($dealId)
    {
        $deal = \CCrmDeal::GetList([], ['ID' => $dealId])->GetNext();
        return $deal;
    }

    private function getCompanyInfo($companyId)
    {
        $company = \CCrmCompany::GetList([], ['ID' => $companyId])->GetNext();
        return $company;
    }

    private function getContactInfo($contactId)
    {
        $contact = \CCrmContact::GetList([], ['ID' => $contactId])->GetNext();
        return $contact;
    }

    public function getEntityInfo($entityId)
    {
        if ($this->entityType === 'LEAD') {
            return $this->getLeadInfo($entityId);
        }
        if ($this->entityType === 'DEAL') {
            return $this->getDealInfo($entityId);
        }
        if ($this->entityType === 'COMPANY') {
            return $this->getCompanyInfo($entityId);
        }
        if ($this->entityType === 'CONTACT') {
            return $this->getContactInfo($entityId);
        }
        return false;
    }

    public function userFieldHandler($userId)
    {
        $user = \CUser::GetByID($userId)->GetNext();
        $userName = false;
        if ($user) {
            $userName = $user['LAST_NAME'] . ' ' . $user['NAME'] . ' ' . $user['SECOND_NAME'];
        }
        return $userName;
    }

    public function getStageName($stageId)
    {
        if (!in_array($this->entityType, $this->hasStagesEntities)) {
            return false;
        } else {
            // получаем данные по стадии сделки
            $curStage = '';
            $resStage = \Bitrix\Crm\StatusTable::getList(['filter' => ['STATUS_ID' => $stageId]]);
            while ($item = $resStage->fetch()) {
                $curStage = $item['NAME'];
            }
            return $curStage;
        }
    }

    public function fillValues($entityId, $operationType = '')
    {
        $entityData = $this->getEntityInfo($entityId);
        $rowNum = '';
        $row = [];
        foreach ($this->configs['entity_table_fields'] as $key => $field) {
            $fieldValue = $entityData[$field['columnName']];
            if (strpos($field['columnName'], 'UF_') === 0) {
                $res = \Bitrix\Main\UserFieldTable::getList(['filter' => ['FIELD_NAME' => $field['columnName']]]);
                $fieldType = '';
                while ($item = $res->fetch()) {
                    $fieldType = $item['USER_TYPE_ID'];
                }
                $refactor = new FieldsRefactor();
                $fieldData = $refactor::refactor(['TYPE' => $fieldType, 'NAME' => $field['columnName']], $fieldValue);
                $this->configs['entity_table_fields'][$key]['VALUE'] = $fieldData;
            }
            else
            {
                if ($field['columnName'] == 'ID') {
                    $this->configs['entity_table_fields'][$key]['VALUE'] = $fieldValue;
                }
                if ($field['columnName'] == 'TITLE') {
                    $this->configs['entity_table_fields'][$key]['VALUE'] = html_entity_decode($fieldValue);
                }
                if ($field['columnName'] == 'ASSIGNED_BY_ID') {
                    $this->configs['entity_table_fields'][$key]['VALUE'] = $this->userFieldHandler($fieldValue);
                }
                if ($field['columnName'] == 'STAGE_ID') {
                    $this->configs['entity_table_fields'][$key]['VALUE'] = $this->getStageName($fieldValue);
                }
                if ($field['columnName'] == 'ITERATOR') {
                    $iteratorColumn = $this->connector->getIteratorColumnKey();
                    if ($operationType === 'add') {
                        $this->configs['entity_table_fields'][$key]['VALUE'] = $this->connector->getAddedRowNum($iteratorColumn)+1;
                    } elseif ($operationType === 'update') {
                        $row = $this->connector->searchEntityRow($entityId);
                        $rowData = current($row);
                        $rowNum = key($row);
                        $this->configs['entity_table_fields'][$key]['VALUE'] = $rowData[$iteratorColumn];
                    }
                }
            }
        }

        $resultArr = [];
        foreach ($this->configs['entity_table_fields'] as $field) {
            $resultArr[] = $field['VALUE'];
        }

        $result = $this->getFormattedData($resultArr);

        if ($operationType === 'add' || count($row) <= 0) {
            $result = $this->connector->appendData($result);
        } elseif ($operationType === 'update') {
            $this->connector->updateData($result, $rowNum+1);
        }
        return $result;
    }

    public function getFormattedData($data) {
        foreach ($data as $key => $value) {
            $data[$key] = str_replace(',', ' ', $value);
        }

        $ret = implode(',',$data);

        $retDate = explode(',', $ret);
        $result = [];

        foreach ($retDate as $item) {
            if (is_numeric($item) && preg_match('/\./', $item) === 1) {
                $result[] = str_replace('.', ',', $item);
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }
}