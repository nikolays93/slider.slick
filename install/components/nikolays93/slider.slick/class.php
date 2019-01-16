<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Loader;

class sliderSlickComponent extends CBitrixComponent
{
    protected $arrFilter;

    function __construct($component = null)
    {
        parent::__construct($component);

        if( !Loader::includeModule( 'iblock' ) ) {
            $this->errors[] = GetMessage("IBLOCK_MODULE_NOT_INSTALLED");
        }
    }

    function onPrepareComponentParams($arParams)
    {
        if( !isset( $arParams["CACHE_TIME"] ) ) {
            $arParams["CACHE_TIME"] = 36000000;
        }

        $arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);

        /**
         * Iblock to array
         */
        if( !is_array($arParams["IBLOCKS"]) ) {
            $arParams["IBLOCKS"] = array($arParams["IBLOCKS"]);
        }

        /**
         * Unset empty values
         * @var [type]
         */
        foreach($arParams["IBLOCKS"] as $key => $val) {
            if( ! $val ) unset( $arParams["IBLOCKS"][ $key ] );
        }

        /**
         * Sort && Orders
         */
        $arParams["IBLOCK_SORT_BY"] = trim($arParams["IBLOCK_SORT_BY"]);

        $allowIBlockSortBy = array("SORT","NAME","ID");

        if( ! in_array($arParams["IBLOCK_SORT_BY"], $allowIBlockSortBy) ) {
            $arParams["SORT_BY1"] = "SORT";
        }

        $arParams["IBLOCK_SORT_ORDER"] = strtoupper( $arParams["IBLOCK_SORT_ORDER"] );

        if( $arParams["IBLOCK_SORT_ORDER"] != "DESC" ) {
            $arParams["IBLOCK_SORT_ORDER"] = "ASC";
        }

        $arParams["SORT_BY1"] = trim( $arParams["SORT_BY1"] );

        if( strlen($arParams["SORT_BY1"]) <= 0 ) $arParams["SORT_BY1"] = "ACTIVE_FROM";

        if( ! preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER1"]) ) {
            $arParams["SORT_ORDER1"] = "DESC";
        }

        if( strlen($arParams["SORT_BY2"]) <= 0 ) $arParams["SORT_BY2"] = "SORT";

        if( ! preg_match('/^(asc|desc|nulls)(,asc|,desc|,nulls){0,1}$/i', $arParams["SORT_ORDER2"]) ) {
            $arParams["SORT_ORDER2"] = "ASC";
        }

        /**
         * Prepare property code
         */
        if( ! is_array( $arParams["FIELD_CODE"] ) ) $arParams["FIELD_CODE"] = array();

        foreach( $arParams["FIELD_CODE"] as $key => $val ) {
            if( "" === $val ) unset($arParams["FIELD_CODE"][ $key ]);
        }

        if( ! is_array($arParams["PROPERTY_CODE"]) ) $arParams["PROPERTY_CODE"] = array();

        foreach($arParams["PROPERTY_CODE"] as $k => $v) {
            if( "" === $v ) unset($arParams["PROPERTY_CODE"][ $k ]);
        }

        /**
         * Set arrFilter
         */
        if(strlen($arParams["FILTER_NAME"]) <= 0 || ! preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]) ) {
            $this->arrFilter = array();
        }
        else {
            global ${$arParams["FILTER_NAME"]};

            $this->arrFilter = ${$arParams["FILTER_NAME"]};
            if( ! is_array($this->arrFilter) ) {
                $this->arrFilter = array();
            }
        }

        $arParams["NEWS_COUNT"] = intval($arParams["NEWS_COUNT"]);
        if( $arParams["NEWS_COUNT"] <= 0 ) {
            $arParams["NEWS_COUNT"] = 5;
        }

        // $arParams["IBLOCK_URL"] = trim( $arParams["IBLOCK_URL"] );
        // $arParams["DETAIL_URL"] = trim( $arParams["DETAIL_URL"] );

        $arParams["ACTIVE_DATE_FORMAT"] = trim( $arParams["ACTIVE_DATE_FORMAT"] );
        if( strlen($arParams["ACTIVE_DATE_FORMAT"]) <= 0 ) {
            $arParams["ACTIVE_DATE_FORMAT"] = $DB->DateFormatToPHP( CSite::GetDateFormat("SHORT") );
        }

        return $arParams;
    }

    function executeComponent()
    {
        global $APPLICATION, $USER;

        /**
         * Show errors
         */
        if( !empty($this->arResult['ERRORS']) ) {
            // maybe $this->includeComponentTemplate('error'); for include ./template/error.php?
            $this->includeComponentTemplate();
            return;
        }

        $this->arResult['IBLOCKS'] = array();
        $this->arResult['COUNT'] = 0;

        $groups = $this->arParams["CACHE_GROUPS"]==="N" ? false : $USER->GetGroups();

        /**
         * Start cache for current user groups
         */
        if( $this->startResultCache(false, $groups) ) {
            $arSelect = array_merge( $arParams["FIELD_CODE"], array(
                "ID",
                "IBLOCK_ID",
                "IBLOCK_SECTION_ID",
                "NAME",
                "ACTIVE_FROM",
                "TIMESTAMP_X",
                "DETAIL_PAGE_URL",
                "PREVIEW_TEXT",
                "PREVIEW_PICTURE",
                "PREVIEW_LINK",
            ) );

            /**
             * Add custom properties
             */
            $bGetProperty = count($arParams["PROPERTY_CODE"]) > 0;
            if( $bGetProperty ) {
                $arSelect[] = "PROPERTY_*";
            }
            $bGetProperty = true;
            $arSelect[] = "PROPERTY_SLICK_LINK";

            /**
             * Where
             */
            $this->arrFilter["IBLOCK_TYPE"] = $arParams["IBLOCK_TYPE"];
            $this->arrFilter["ACTIVE"] = "Y";
            $this->arrFilter["ACTIVE_DATE"] = "Y";
            $this->arrFilter["CHECK_PERMISSIONS"] = "Y";

            $arOrder = array(
                $this->arParams["SORT_BY1"] => $this->arParams["SORT_ORDER1"],
                $this->arParams["SORT_BY2"] => $this->arParams["SORT_ORDER2"],
            );

            if( !array_key_exists("ID", $arOrder) ) {
                $arOrder["ID"] = "DESC";
            }

            $arIBlockOrder = array(
                $this->arParams["IBLOCK_SORT_BY"] => $this->arParams["IBLOCK_SORT_ORDER"],
            );

            if( ! array_key_exists("ID", $arIBlockOrder) ) {
                $arIBlockOrder["ID"] = "DESC";
            }

            /** @var CDBResult [description] */
            $rsIBlocks = CIBlock::GetList( $arIBlockOrder, array(
                "LID" => SITE_ID,
                "ACTIVE" => "Y",
                "ID" => $this->arParams["IBLOCKS"],
            ) );

            // While Iblocks
            while( $arIBlock = $rsIBlocks->GetNext() )
            {
                $arButtons = CIBlock::GetPanelButtons( $arIBlock["ID"], 0, 0, array(
                    "SECTION_BUTTONS" => false,
                    "SESSID" => false
                ) );

                $arIBlock["ADD_ELEMENT_LINK"] = $arButtons["edit"]["add_element"]["ACTION_URL"];
                $arIBlock["~LIST_PAGE_URL"] = str_replace(
                    array("#SERVER_NAME#", "#SITE_DIR#", "#IBLOCK_TYPE_ID#", "#IBLOCK_ID#", "#IBLOCK_CODE#", "#IBLOCK_EXTERNAL_ID#", "#CODE#"),
                    array(SITE_SERVER_NAME, SITE_DIR, $arIBlock["IBLOCK_TYPE_ID"], $arIBlock["ID"], $arIBlock["CODE"], $arIBlock["EXTERNAL_ID"], $arIBlock["CODE"]),
                    strlen($this->arParams["IBLOCK_URL"])? trim($this->arParams["~IBLOCK_URL"]): $arIBlock["~LIST_PAGE_URL"]
                );
                $arIBlock["~LIST_PAGE_URL"] = preg_replace("'/+'s", "/", $arIBlock["~LIST_PAGE_URL"]);
                $arIBlock["LIST_PAGE_URL"] = htmlspecialcharsbx($arIBlock["~LIST_PAGE_URL"]);

                $arIBlock["ITEMS"] = array();

                $this->arrFilter["IBLOCK_ID"] = $arIBlock["ID"];
                $rsItem = CIBlockElement::GetList($arOrder, $this->arrFilter, false, array(
                    "nTopCount" => $this->arParams["NEWS_COUNT"]
                ), $arSelect );
                $rsItem->SetUrlTemplates( $this->arParams["DETAIL_URL"] );

                // While Items.. Lets go!
                while( $obItem = $rsItem->GetNextElement() )
                {
                    $arItem = $obItem->GetFields();

                    $arButtons = CIBlock::GetPanelButtons(
                        $arItem["IBLOCK_ID"],
                        $arItem["ID"],
                        0,
                        array("SECTION_BUTTONS" => false, "SESSID" => false)
                    );

                    $arItem["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
                    $arItem["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

                    $arItem["DISPLAY_ACTIVE_FROM"] = "";
                    if( strlen($arItem["ACTIVE_FROM"]) > 0 ) {
                        $arItem["DISPLAY_ACTIVE_FROM"] = CIBlockFormatProperties::DateFormat(
                            $this->arParams["ACTIVE_DATE_FORMAT"],
                            MakeTimeStamp( $arItem["ACTIVE_FROM"], CSite::GetDateFormat() )
                        );
                    }

                    $ipropValues = new Bitrix\Iblock\InheritedProperty\ElementValues($arItem["IBLOCK_ID"], $arItem["ID"]);
                    $arItem["IPROPERTY_VALUES"] = $ipropValues->getValues();

                    \Bitrix\Iblock\Component\Tools::getFieldImageData(
                        $arItem,
                        array('PREVIEW_PICTURE'),
                        \Bitrix\Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
                        'IPROPERTY_VALUES'
                    );

                    /**
                     * Fill fields
                     */
                    $arItem["FIELDS"] = array();
                    foreach($this->arParams["FIELD_CODE"] as $code) {
                        if( array_key_exists($code, $arItem) ) {
                            $arItem["FIELDS"][ $code ] = $arItem[ $code ];
                        }
                    }

                    if( $bGetProperty ) {
                        $arItem["PROPERTIES"] = $obItem->GetProperties();
                    }

                    $arItem["DISPLAY_PROPERTIES"] = array();

                    foreach($this->arParams["PROPERTY_CODE"] as $pid)
                    {
                        $prop = &$arItem["PROPERTIES"][$pid];
                        if( (is_array($prop["VALUE"]) && count($prop["VALUE"])>0)
                            || (!is_array($prop["VALUE"]) && strlen($prop["VALUE"])>0) )
                        {
                            $arItem["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arItem, $prop, "news_out");
                        }
                    }

                    $arItem['SLICK_LINK'] = '';
                    if( $this->arParams['USE_LINKS'] != "N" ) {
                        $arItem['SLICK_LINK'] = !empty( $arItem['PROPERTY_SLICK_LINK_VALUE'] )
                            ? $arItem["PROPERTY_SLICK_LINK_VALUE"] : $arItem["DETAIL_PAGE_URL"];
                    }

                    $arIBlock["ITEMS"][] = $arItem;
                    $this->arResult['COUNT']++;
                }

                $this->arResult["IBLOCKS"][] = $arIBlock;
            }

            /**
             * Prepare slick props
             */
            foreach ($this->arParams as $param => $value)
            {
                $pos = strpos($param, 'SLICK_');
                if( 0 === $pos ) {
                    if( is_numeric($value) ) {
                        $value = (float) $value;
                    }
                    else {
                        switch ($value) {
                            case 'Y': $value = true; break;
                            case 'N': $value = false; break;
                            default: $value = htmlspecialchars_decode($value); break;
                        }
                    }

                    $this->arResult['SlickProps'][ str_replace('SLICK_', '', $param) ] = $value;
                }

                if( 0 === $pos || 1 === $pos ) {
                    unset( $this->arParams[ $param ] );
                }
            }

            $this->setResultCacheKeys(array(
                "ID",
                "IBLOCK_TYPE_ID",
                "LIST_PAGE_URL",
                "NAV_CACHED_DATA",
                "NAME",
                "SECTION",
                "ELEMENTS",
                "IPROPERTY_VALUES",
                "ITEMS_TIMESTAMP_X",
            ));
            $this->includeComponentTemplate();
        }
    }
}
