<?php
//-- файл с описанием модуля

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config as Conf;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Application;
use \Bitrix\Main\ModuleManager;

use \Kbnet\Alogin\AloginTable;

Loc::loadMessages(__FILE__);
Class kbnet_alogin extends CModule
{
    var $exclusionAdminFiles;

	function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__."/version.php");

        $this->exclusionAdminFiles = array(
            '..',
            '.',
            'menu.php',
            // '_description.php', //-- файлы раздела admin, которые не нужно копировать в раздел административный раздел bitrix
        );

        $this->MODULE_ID = 'kbnet.alogin';
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("KB_ALOGIN_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("KB_ALOGIN_MODULE_DESC");

		$this->PARTNER_NAME = Loc::getMessage("KB_ALOGIN_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("KB_ALOGIN_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS='Y';
        $this->MODULE_GROUP_RIGHTS = "Y";
	}

    //Определяем место размещения модуля
    public function GetPath($notDocumentRoot=false)
    {
        if($notDocumentRoot)
            return str_ireplace(Application::getDocumentRoot(),'',dirname(__DIR__));
        else
            return dirname(__DIR__);
    }

    //Проверяем что система поддерживает D7
    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    function InstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
            AloginTable::getEntity()->createDbTable();
        }

        /*
        $path = $this->GetPath().'/bd/mysql/install.sql';
        if (file_exists($path))
        {
            $connection = Application::getConnection();
            $sql = file_get_contents($path);
            try {
                $connection->queryExecute($sql);

            } catch (SystemException $exception) {
                $this->error = $exception->getMessage();
                return $this->error;
            }
        } else {
            throw new IO\FileNotFoundException($path);
        }

        return true;
        */
    }

    function UnInstallDB()
    {
        if (Loader::includeModule($this->MODULE_ID)) {
            if (Application::getConnection()->isTableExists(Base::getInstance('\Kbnet\Alogin\AloginTable')->getDBTableName())) {
                $connection = Application::getInstance()->getConnection();
                $connection->dropTable(AloginTable::getTableName());
            }
        }

        /*
        $path = $this->GetPath().'/bd/mysql/uninstall.sql';
        if (file_exists($path))
        {
            $connection = Application::getConnection();
            $sql = file_get_contents($path);
            try {
                $connection->queryExecute($sql);

            } catch (SystemException $exception) {
                $this->error = $exception->getMessage();
                return $this->error;
            }
        } else {
            throw new IO\FileNotFoundException($path);
        }

        return true;
        */
    }

	function InstallEvents()
	{
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler('main', 'OnBeforeProlog', $this->MODULE_ID, '\Kbnet\Alogin\Event', 'OnBeforePrologHandler');
	}

	function UnInstallEvents()
	{
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler('main', 'OnBeforeProlog', $this->MODULE_ID, '\Kbnet\Alogin\Event', 'OnBeforePrologHandler');
        /*
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler($this->MODULE_ID, 'TestEventD7', $this->MODULE_ID, '\Academy\D7\Event', 'eventHandler');
        */
	}

	function InstallFiles($arParams = array())
	{
        /*
        $path=$this->GetPath()."/install/components";

        if(\Bitrix\Main\IO\Directory::isDirectoryExists($path))
            CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
        else
            throw new \Bitrix\Main\IO\InvalidPathException($path);

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin'))
        {
            CopyDirFiles($this->GetPath() . "/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"); //если есть файлы для копирования
            if ($dir = opendir($path))
            {
                while (false !== $item = readdir($dir))
                {
                    if (in_array($item,$this->exclusionAdminFiles))
                        continue;
                    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$this->MODULE_ID.'_'.$item,
                        '<'.'? require($_SERVER["DOCUMENT_ROOT"]."'.$this->GetPath(true).'/admin/'.$item.'");?'.'>');
                }
                closedir($dir);
            }
        }
        */

        return true;
	}

	function UnInstallFiles()
	{
        /*
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/academy/');

        if (\Bitrix\Main\IO\Directory::isDirectoryExists($path = $this->GetPath() . '/admin')) {
            DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . $this->GetPath() . '/install/admin/', $_SERVER["DOCUMENT_ROOT"] . '/bitrix/admin');
            if ($dir = opendir($path)) {
                while (false !== $item = readdir($dir)) {
                    if (in_array($item, $this->exclusionAdminFiles))
                        continue;
                    \Bitrix\Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item);
                }
                closedir($dir);
            }
        }
        */
		return true;
	}

	function DoInstall()
	{
        if ($this->isVersionD7()) {

            ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();

        } else {
            throw new SystemException(Loc::getMessage("KB_ALOGIN_INSTALL_ERROR_VERSION"));
        }

	}

	function DoUninstall()
	{
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();
        ModuleManager::unRegisterModule($this->MODULE_ID);

	}

    function GetModuleRightList()
    {
        return array(
            "reference_id" => array("D","K","S","W"),
            "reference" => array(
                "[D] ".Loc::getMessage("KB_ALOGIN_DENIED"),
                "[K] ".Loc::getMessage("KB_ALOGIN_READ_COMPONENT"),
                "[S] ".Loc::getMessage("KB_ALOGIN_WRITE_SETTINGS"),
                "[W] ".Loc::getMessage("KB_ALOGIN_FULL"))
        );
    }
}
?>