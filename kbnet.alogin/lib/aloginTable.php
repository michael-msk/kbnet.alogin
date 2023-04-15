<?php

namespace Kbnet\Alogin;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;
use Bitrix\Main\Entity\DatetimeField;
use Bitrix\Main\Entity\Validator;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type;

Loc::loadMessages(__FILE__);
class AloginTable extends DataManager
{
// название таблицы
    public static function getTableName()
    {
        return 'b_kb_alogin';
    }
    // создаем поля таблицы
    public static function getMap()
    {
        return array(
            new IntegerField('ID', array(
                'autocomplete' => false,
                'primary' => true
            )),// autocomplite с первичным ключом
            new StringField('HASH', array(
                'required' => true,
                'title' => 'Hash',
                'size' => 32,
            )),//--
            new DatetimeField('DATE_START', array(
                'required' => true,
                'title' => 'Дата начала работы токена',
            )),//--
            new DatetimeField('DATE_END', array(
                'required' => false,
                'title' => 'Дата окончания работы токена',
            )),//--
            new IntegerField('QNT', array(
                'required' => false,
                'title' => 'Доступное кол-во авторизаций',
                'default_value' => -1,
            )),//-- Если значение -1 то кол-во авторизаций не ограниченно
            new DatetimeField('DATE_CREATED',array(
                'required' => true,
                'default_value' => new Type\Date(),
            )),//обязательное поле даты
        );
    }
}