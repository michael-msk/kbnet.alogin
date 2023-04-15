<?php

namespace Kbnet\Alogin;

use Bitrix\Main\Mail\Event;
use Bitrix\Main\Mail\EventType;
use Bitrix\Main\Context;

class install
{
    const NAME_EVENT_SEND_LINK = 'SEND_LINK_AUTO_LOGIN';

    public static function addEventType()
    {
        $siteId = Context::getCurrent()->getSite();

        //-- не нашел описания на D7
        //-- проверяем наличие почтового события и создаем его по необходимости
        $rsEt = \CEventType::GetByID(self::NAME_EVENT_SEND_LINK, $siteId);
        if (!($arET = $rsEt->Fetch()))
        {
            $et = new CEventType;
            $description = '
                
        ';
            $et->Add(array(
                "LID"           => $siteId,
                "EVENT_NAME"    => self::NAME_EVENT_SEND_LINK,
                "NAME"          => 'Отправка пользователю на почту ссылки для автоматической авторизации',
                "DESCRIPTION"   => $description,
            ));
        }
        //-- проверяем наличие почтового шаблона
        



        $resEventTyte = EventType::getList([
            //'select' => [],
            //'filter' => ['HASH' => $hash],
        ]);

        if ($arEventTyte = $resEventTyte->fetch())
        {
            return $arEventTyte;
        } else {
            return 'Ошибка';
        }

        /*
        Event::send(array(
            "EVENT_NAME" => "TEST",
            "LID" => "s1",
            "C_FIELDS" => array(
                "EMAIL" => $email,
                "USER_ID" => 1
            ),
        ));

*/
    }
}