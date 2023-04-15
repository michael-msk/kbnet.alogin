<?php

namespace Kbnet\Alogin;

use \Bitrix\Main\Application;
use \Bitrix\Main\Web\Uri;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Diag;
use \Bitrix\Main\Type\DateTime;
use function Sodium\add;

class event
{
    const ACTION_NAME_HASH = 'alogin';
    public static function OnBeforeEventAddHandler(&$event, &$lid, &$arFields)
    {
        /*
        if (in_array($event, explode(',', COption::GetOptionString('asd.autologin', 'event_types'))))
        {
            $arUserMarkers = explode(',', COption::GetOptionString('asd.autologin', 'user_id_marker'));
            $hash = self::GetHash();
            foreach ($arUserMarkers as $marker)
            {
                $marker = trim($marker);
                if (check_email($arFields[$marker]))
                {
                    if ($arUser = CUser::GetList($by='id', $order='asc', array('=EMAIL' => trim($arFields[$marker])))->Fetch())
                        $UID = $arUser['ID'];
                }
                elseif ($arFields[$marker] > 0)
                {
                    $UID = intval($arFields[$marker]);
                }

                if ($UID > 0)
                {
                    $arFields['AUTOLOGIN_HASH'] = $hash.$UID;
                    $GLOBALS['DB']->Query("INSERT INTO b_asd_autologin (ID, HASH) VALUES($UID, '$hash');");
                    $events = GetModuleEvents('asd.autologin', 'OnAfterInsertHash');
                    while ($arEvent = $events->Fetch())
                    {
                        ExecuteModuleEventEx($arEvent, array(array(
                            'EVENT' => $event,
                            'LID' => $lid,
                            'HASH' => $hash,
                            'USER_ID' => $UID,
                            'EVENT_FIELDS' => $arFields)));
                    }
                    break;
                }

            }
        }*/
    }

    public static function OnBeforePrologHandler()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        $hash = $request->get(self::ACTION_NAME_HASH);

        if (strlen($hash) > 0)
        {
            Diag\Debug::writeToFile($hash, "hash", "alogin.log");

            $resHash = AloginTable::getList([
                'select' => ['ID', 'HASH', 'QNT', 'DATE_START', 'DATE_END'],
                'filter' => ['HASH' => $hash],
            ]);

            if ($arHash = $resHash->fetch())
            {
                if (self::isAccessHash($arHash))
                {
                    self::authorization($arHash['ID']);
                    if ($arHash['QNT'] > 1)
                    {
                        self::reducingСounter($arHash['ID'], $arHash['QNT']);
                    }
                } else {
                    self::deleteUserHash($arHash['ID']);
                }
            }

            $uriString = $request->getRequestUri();
            $uri = new Uri($uriString);
            $uri->deleteParams(array(self::ACTION_NAME_HASH));
            $redirect = $uri->getUri();
            LocalRedirect($redirect);
        }
    }
    public static function OnUserDeleteHandler($userId)
    {
        self::deleteUserHash($userId);
    }

    private static function GetHash()
    {
        return md5(md5(time()).uniqid());
    }

    public static function authorization($userId)
    {
        if  (self::accessRights($userId))
        {
            $GLOBALS['USER']->Authorize($userId, Option::get('kbnet.alogin', 'authorize_save') == 'Y');
        }
    }

    public static function reducingСounter($userId, $qnt)
    {
        if ($qnt > 1)
        {
            $qnt -= 1;
            AloginTable::update($userId, ['QNT' => $qnt]);
        } else {
            self::deleteUserHash($userId);
        }
    }

    public static function getUserHash($userId)
    {
        $resUserHash = AloginTable::getList([
            'select' => ['ID', 'HASH', 'QNT', 'DATE_START', 'DATE_END'],
            'filter' => ['ID' => $userId],
        ]);

        if ($arUserHash = $resUserHash->fetch())
        {
            //-- проверяем действующий ли он
            return $arUserHash;
        }
        return false;
    }

    public static function isAccessHash(array $arHash)
    {
        $curDate = new DateTime();

        if (($arHash['DATE_START'] <= $curDate) && ($curDate <= $arHash['DATE_END']))
        {
            if ($arHash['QNT'] == -1)
            {
                return true;
            } else {
                if ($arHash['QNT'] > 0)
                {
                    return true;
                }
            }
        }
        return false;
    }
    public static function newUserHash($userId, $qnt = -1, $dateStart = null, $dateEnd = null)
    {
        $arHash = self::getUserHash($userId);

        if (false !== $arHash)
        {
            if (!self::isAccessHash($arHash))
            {
                self::deleteUserHash($userId);
                $arHash = false;
            }
        }

        if (false === $arHash)
        {
            $hash = self::GetHash();

            if (is_null($dateStart))
            {
                $dateStart = new DateTime();
            }
            if (is_null($dateEnd))
            {
                $interval = (string)self::getCountDayAccess().'D';
                $dateEnd = (clone $dateStart)->add($interval);
            }

            $arFields = [
                'ID' => $userId,
                'HASH' => $hash,
                'DATE_START' => $dateStart,
                'DATE_END' => $dateEnd,
                'QNT' => $qnt,
            ];

            if (AloginTable::add($arFields)) {
                return $hash;
            }
        }

        return false;
    }

    public static function deleteUserHash($userId)
    {
        AloginTable::delete($userId);
    }

    public static function deleteAllHash($userId)
    {

    }

    public static function accessRights($userId)
    {
        $arGroups = self::getUserGroups($userId);

        if (in_array(1, $arGroups))
        {
            return false;
        }
        return true;
    }

    public static function getUserGroups($userId): array
    {
        $arResult = [];

        $result = \Bitrix\Main\UserGroupTable::getList(array(
            'filter' => array('USER_ID' => $userId, 'GROUP.ACTIVE' => 'Y'),
            'select' => array('GROUP_ID'), // выбираем идентификатор группы и символьный код группы
            //'order' => array('GROUP.C_SORT' => 'ASC'), // сортируем в соответствии с сортировкой групп
        ));

        while ($arGroup = $result->fetch())
        {
            $arResult[] = $arGroup['GROUP_ID'];
        }

        return $arResult;
    }

    public static function getCountDayAccess()
    {
        return 30;
    }
}