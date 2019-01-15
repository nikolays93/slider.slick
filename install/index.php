<?
use \Bitrix\Main;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

Class NikolayS93SliderSlickModule extends CModule {
	const IBLOCKTYPE = 'media_content';
    const IBLOCKCODE = "slick";

	public $MODULE_ID = 'nikolays93:slider.slick';
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;

	function __construct()
	{
		$arModuleVersion = array();
		include(__DIR__ . "/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = Loc::getMessage("SLIDER_SLICK_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("SLIDER_SLICK_MODULE_DESC");

		$this->PARTNER_NAME = getMessage("SLIDER_SLICK_PARTNER_NAME");
		$this->PARTNER_URI = getMessage("SLIDER_SLICK_PARTNER_URI");

		/**
		 * Do not rewrite this files to include (when install)
		 */
		$this->exclusionAdminFiles = array(
			'..',
			'.',
			'menu.php',
			'operation_description.php',
			'task_description.php'
		);
	}

	static function isVersionD7()
	{
		return CheckVersion(Main\ModuleManager::getVersion('main'), '14.00.00');
	}

	static function GetPath($notDocumentRoot = false)
	{
		if ( $notDocumentRoot ) {
			return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
		}

		return dirname(__DIR__);
	}

	static function getSitesIdsArray()
	{
		$ids = Array();
		$rsSites = CSite::GetList($by = "sort", $order = "desc");
		while ($arSite = $rsSites->Fetch()) {
			$ids[] = $arSite["LID"];
		}

		return $ids;
	}

	function InstallDB($arParams = array())
	{
		$this->createNecessaryIblocks();
        $this->createNecessaryUserFields();
	}

	function UnInstallDB($arParams = array())
	{
		Main\Config\Option::delete($this->MODULE_ID);
		$this->deleteNecessaryIblocks();
        $this->deleteNecessaryUserFields();
	}

	function InstallEvents()
	{
		// Main\EventManager::getInstance()->registerEventHandler("catalog", "OnAfterIBlockElementUpdate", $this->MODULE_ID, '\Nick\Testovyymodul\EventHandlers\OnAfterIBlockElementUpdateHandler', "handler");
	}

	function UnInstallEvents()
	{
		// Main\EventManager::getInstance()->unRegisterEventHandler("catalog", "OnAfterIBlockElementUpdate", $this->MODULE_ID, '\Nick\Testovyymodul\EventHandlers\OnAfterIBlockElementUpdateHandler', "handler");
	}

	function InstallFiles($arParams = array())
	{
		$path = self::GetPath() . "/install/components";

		/**
		 * Move components to distr
		 */
		if ( Main\IO\Directory::isDirectoryExists($path) ) {
			CopyDirFiles($path, $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);
		}

		/**
		 * Move admin files to distr
		 */
		if ( Main\IO\Directory::isDirectoryExists($path = self::GetPath() . '/admin') ) {
			CopyDirFiles(self::GetPath() . "/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");

			if ( $dir = opendir($path) ) {
				while (false !== $item = readdir($dir)) {
					if ( in_array($item, $this->exclusionAdminFiles) ) continue;

					/**
					 * Rewrite to include this files
					 */
					file_put_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$item,
						'<'.'? require($_SERVER["DOCUMENT_ROOT"]."'.self::GetPath(true).'/admin/'.$item.'");?'.'>');
				}
				closedir($dir);
			}
		}

		/**
		 * Move custom needed files (from ./install/files/)
		 */
		if (Main\IO\Directory::isDirectoryExists($path = self::GetPath().'/install/files')){
			$this->copyArbitraryFiles();
		}

		return true;
	}

	/**
	 * Restore included files
	 */
	function UnInstallFiles()
	{
		Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . '/bitrix/components/' . $this->MODULE_ID . '/');

		if ( Main\IO\Directory::isDirectoryExists($path = self::GetPath() . '/admin') ) {
			DeleteDirFiles($_SERVER["DOCUMENT_ROOT"].self::GetPath().'/install/admin/', $_SERVER["DOCUMENT_ROOT"].'/bitrix/admin');

			if ( $dir = opendir($path) ) {
				while (false !== $item = readdir($dir)) {
					if ( in_array($item, $this->exclusionAdminFiles) ) continue;

					Main\IO\File::deleteFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item);
				}
				closedir($dir);
			}
		}

		if ( Main\IO\Directory::isDirectoryExists($path = self::GetPath() . '/install/files') ) {
			$this->deleteArbitraryFiles();
		}

		return true;
	}

	function copyArbitraryFiles()
	{
		$rootPath = $_SERVER["DOCUMENT_ROOT"];
		$localPath = self::GetPath() . '/install/files';

		$dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $object) {
			$destPath = $rootPath . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
			($object->isDir()) ? mkdir($destPath) : copy($object, $destPath);
		}
	}

	function deleteArbitraryFiles()
	{
		$rootPath = $_SERVER["DOCUMENT_ROOT"];
		$localPath = self::GetPath() . '/install/files';

		$dirIterator = new RecursiveDirectoryIterator($localPath, RecursiveDirectoryIterator::SKIP_DOTS);
		$iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $object) {
			if (!$object->isDir()){
				$file = str_replace($localPath, $rootPath, $object->getPathName());
				Main\IO\File::deleteFile($file);
			}
		}
	}

	/**
	 * Create important (required) iBlocks
	 */
	function createNecessaryIblocks()
	{
		$iblockType = $this->createIblockType();
		$iblock174ID = $this->createIblock(
			Array(
				"IBLOCK_TYPE_ID" => $iblockType,
				"ACTIVE" => "Y",
				"LID" => static::getSitesIdsArray(),
				"VERSION" => "1",
				"CODE" => static::IBLOCKCODE,
				"NAME" => Loc::getMessage("SLIDER_SLICK_IBLOCK_NAME"),
				"SORT" => "500",
				"LIST_PAGE_URL" => "#",
				"SECTION_PAGE_URL" => "#",
				"DETAIL_PAGE_URL" => "#",
				"INDEX_SECTION" => "N",
				"INDEX_ELEMENT" => "N",
				"FIELDS" => Array(
					"ACTIVE" => Array(
						"DEFAULT_VALUE" => "Y",
					),
					"PREVIEW_TEXT_TYPE" => Array(
						"DEFAULT_VALUE" => "text",
					),
					"PREVIEW_TEXT_TYPE_ALLOW_CHANGE" => Array(
						"DEFAULT_VALUE" => "N",
					),
					"DETAIL_TEXT_TYPE" => Array(
						"DEFAULT_VALUE" => "text",
					),
					"DETAIL_TEXT_TYPE_ALLOW_CHANGE" => Array(
						"DEFAULT_VALUE" => "N",
					),
				),
				"GROUP_ID" => Array('2' => 'R'),
			)
		);
	}

	function deleteNecessaryIblocks()
	{
		$this->removeIblockType();
	}

	function createNecessaryUserFields()
	{
		return true;
	}

	function deleteNecessaryUserFields()
	{
		return true;
	}

	function createNecessaryMailEvents()
	{
		return true;
	}

	function deleteNecessaryMailEvents()
	{
		return true;
	}

	function createIblockType()
	{
		global $DB, $APPLICATION;
		if( !CModule::IncludeModule("iblock") ) $APPLICATION->ThrowException(Loc::getMessage("SLIDER_SLICK_IBLOCK_MODULE_NOT_FOUND"));

		$iblockType = static::IBLOCKTYPE;
		/** @var CDBResult */
		$db_iblock_type = CIBlockType::GetList(
			Array("SORT" => "ASC"),
			Array("ID" => $iblockType)
		);

		if ( !$ar_iblock_type = $db_iblock_type->Fetch() ) {
			$arFieldsIBT = Array(
				'ID'       => $iblockType,
				'SECTIONS' => 'Y',
				'IN_RSS'   => 'N',
				'SORT'     => 500,
				'LANG'     => Array(
					'en' => Array(
						'NAME' => Loc::getMessage("SLIDER_SLICK_IBLOCK_TYPE_NAME_EN"),
					),
					'ru' => Array(
						'NAME' => Loc::getMessage("SLIDER_SLICK_IBLOCK_TYPE_NAME_RU"),
					)
				)
			);

			$obBlocktype = new CIBlockType;
			$DB->StartTransaction();
			$resIBT = $obBlocktype->Add($arFieldsIBT);

			if ( !$resIBT ) {
				$DB->Rollback();
				$APPLICATION->ThrowException(Loc::getMessage("SLIDER_SLICK_IBLOCK_TYPE_ALREADY_EXISTS"));
			}
			else {
				$DB->Commit();

				return $iblockType;
			}
		}
	}

	function removeIblockType()
	{
		global $APPLICATION, $DB;
		if( !CModule::IncludeModule("iblock") ) $APPLICATION->ThrowException(Loc::getMessage("SLIDER_SLICK_IBLOCK_MODULE_NOT_FOUND"));

        $iblockType = static::IBLOCKTYPE;

		$DB->StartTransaction();
		if ( !CIBlockType::Delete($iblockType) ) {
			$DB->Rollback();
			$APPLICATION->ThrowException(Loc::getMessage("SLIDER_SLICK_IBLOCK_TYPE_DELETION_ERROR"));
		}
		$DB->Commit();
	}

	function createIblock($params)
	{
		global $APPLICATION;
		if( !CModule::IncludeModule("iblock") ) $APPLICATION->ThrowException(Loc::getMessage("SLIDER_SLICK_IBLOCK_MODULE_NOT_FOUND"));

		$ib = new CIBlock;

		/** @var CDBResult */
		$resIBE = CIBlock::GetList(
			Array(),
			Array(
				'TYPE' => $params["IBLOCK_TYPE_ID"],
				'SITE_ID' => $params["SITE_ID"],
				"CODE" => $params["CODE"]
			)
		);

		/**
		 * Try create iblock
		 */
		if ( $ar_resIBE = $resIBE->Fetch() ) {
			$APPLICATION->ThrowException(Loc::getMessage("SLIDER_SLICK_IBLOCK_ALREADY_EXISTS"));

			return false;
		}
		else {
			$ID = $ib->Add($params);

			return $ID;
		}

		return false;
	}

	function DoInstall()
	{
		global $APPLICATION;

		if ( static::isVersionD7() ) {
			Main\ModuleManager::registerModule($this->MODULE_ID);

			$this->InstallDB();
			$this->createNecessaryMailEvents();
			$this->InstallEvents();
			$this->InstallFiles();
		}
		else {
			$APPLICATION->ThrowException(Loc::getMessage("SLIDER_SLICK_INSTALL_ERROR_VERSION"));
		}

		$APPLICATION->IncludeAdminFile(Loc::getMessage("SLIDER_SLICK_INSTALL"), self::GetPath() . "/install/step.php");
	}

	function DoUninstall()
	{
		global $APPLICATION;

		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();

		$this->UnInstallFiles();
		$this->deleteNecessaryMailEvents();
		$this->UnInstallEvents();

		if ($request["savedata"] != "Y") {
			$this->UnInstallDB();
		}

		Main\ModuleManager::unRegisterModule($this->MODULE_ID);

		$APPLICATION->IncludeAdminFile(Loc::getMessage("SLIDER_SLICK_UNINSTALL"), self::GetPath()."/install/unstep.php");
	}
}
