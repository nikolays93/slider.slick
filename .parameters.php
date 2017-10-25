<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockTypes = CIBlockParameters::GetIBlockTypes();
$arIBlocks = array();

$IBlockType = $arCurrentValues["IBLOCK_TYPE"] != "-" ? $arCurrentValues["IBLOCK_TYPE"] : '';
$db_iblock = CIBlock::GetList(
	Array("SORT"=>"ASC"),
	Array(
		"SITE_ID" => $_REQUEST["site"],
		"TYPE" => $IBlockType,
		)
	);

while( $arRes = $db_iblock->Fetch() ) {
	$arIBlocks[ $arRes["ID"] ] = sprintf('[%s] %s',
		$arRes["ID"],
		$arRes["NAME"]
		);

    $arProperty_LNS = array();

    if( count($arCurrentValues["IBLOCKS"]) == 1 ) {
        $rsProp = CIBlockProperty::GetList(
            Array(
                "sort" => "asc",
                "name" => "asc",
                ),
            Array(
                "ACTIVE" => "Y",
                "IBLOCK_ID" => $arCurrentValues["IBLOCKS"][0],
                )
            );

        while ( $arr = $rsProp->Fetch() ) {
            $arProperty[ $arr["CODE"] ] = sprintf('[%s] %s',
                $arRes["CODE"],
                $arRes["NAME"]
                );

            if ( in_array( $arr["PROPERTY_TYPE"], array("L", "N", "S") ) ) {
                $arProperty_LNS[ $arr["CODE"] ] = sprintf('[%s] %s',
                    $arr["CODE"],
                    $arr["NAME"]
                    );
            }
        }
    }
}

$arSorts = Array(
	"ASC" => "По возрастанию",
	"DESC" => "По убыванию",
);

$arSortFields = Array(
        "ID"          => 'По ID',
        "NAME"        => 'По названию',
        "ACTIVE_FROM" => 'Дата начала активности',
        "SORT"        => "Сортировка",
        "TIMESTAMP_X" => "Дата последнего изменения",
	);

$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => "Тип информационных блоков",
			"TYPE" => "LIST",
			"VALUES" => $arIBlockTypes,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCKS" => Array(
			"PARENT" => "BASE",
			"NAME" => "Код информационного блока",
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"MULTIPLE" => "Y",
			"REFRESH" => "Y",
		),
		"NEWS_COUNT" => Array(
			"PARENT" => "BASE",
			"NAME" => "Количество показываемых слайдов",
			"TYPE" => "STRING",
			"DEFAULT" => "1",
		),
		"IBLOCK_SORT_BY" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Поле для сортировки информационных блоков",
			"TYPE" => "LIST",
			"VALUES" => Array(
                "SORT" => "Сортировка",
                "NAME" => "Имя",
                "ID"   => "ID",
			),
			"DEFAULT" => "SORT",
		),
		"IBLOCK_SORT_ORDER" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Направление для сортировки информационных блоков",
			"TYPE" => "LIST",
			"DEFAULT" => "ASC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_BY1" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Поле для первой сортировки новостей",
			"TYPE" => "LIST",
			"DEFAULT" => "ACTIVE_FROM",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_ORDER1" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Направление для первой сортировки новостей",
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_BY2" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Поле для второй сортировки новостей",
			"TYPE" => "LIST",
			"DEFAULT" => "SORT",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_ORDER2" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Направление для второй сортировки новостей",
			"TYPE" => "LIST",
			"DEFAULT" => "ASC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		),
		"FIELD_CODE" => CIBlockParameters::GetFieldCode("Поля", "DATA_SOURCE"),
		"PROPERTY_CODE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Свойства",
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "Y",
		),
		"FILTER_NAME" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Имя массива со значениями фильтра для фильтрации элементов",
			"TYPE" => "STRING",
			"DEFAULT" => "arrFilter",
		),
		// "IBLOCK_URL" => CIBlockParameters::GetPathTemplateParam(
		// 	"LIST",
		// 	"IBLOCK_URL",
		// 	"УРЛ",
		// 	"",
		// 	"URL_TEMPLATES"
		// ),
		// "DETAIL_URL" => CIBlockParameters::GetPathTemplateParam(
		// 	"DETAIL",
		// 	"DETAIL_URL",
		// 	"Детальный УРЛ",
		// 	"",
		// 	"URL_TEMPLATES"
		// ),
		"ACTIVE_DATE_FORMAT" => CIBlockParameters::GetDateFormat("Формат показа даты", "ADDITIONAL_SETTINGS"),
		"CACHE_TIME"  =>  Array("DEFAULT"=>36000000),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => "Учитывать права доступа",
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
	),
);
?>
