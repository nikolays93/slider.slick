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
        "LIB" => array(
            "NAME" => "Параметры библиотеки",
            "SORT" => "350",
            )
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
			"NAME" => "Макс. количество слайдов",
			"TYPE" => "STRING",
			"DEFAULT" => "5",
		),
		"IBLOCK_SORT_BY" => Array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => "Поле для сортировки слайдов",
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

        "INFINITE" => array(
            "PARENT" => "LIB",
            "NAME" => "Infinite",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
            "DESCRIPTION" => 'Infinite looping',
            ),
        "SLIDESTOSHOW" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Slides To Show',
            'DESC'    => 'slides to show at a time',
            "DEFAULT" => "1",
            "TYPE" => "NUMBER",
            ),
        "SLIDESTOSCROLL" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Slides To Scroll',
            'DESC'    => 'slides to scroll at a time',
            "DEFAULT" => "1",
            'TYPE'    => 'NUMBER'
            ),
        "AUTOPLAY" => array(
            "PARENT" => "LIB",
            "NAME" => 'Auto Play',
            'DESC'  => 'Enables auto play of slides',
            'TYPE'  => 'CHECKBOX',
            ),
        "AUTOPLAYSPEED" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Auto Play Speed',
            'DESC'    => 'Auto play change interval',
            'DEFAULT' => '3000',
            "TYPE" => "NUMBER",
            ),
        "DOTS" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Dots',
            'DESC'    => 'Current slide indicator dots',
            "TYPE" => "CHECKBOX",
            'data-show' => 'dotsClass'
            ),
        "DOTSCLASS" => array(
            "PARENT" => "LIB",
           "NAME"   => 'Dots Class',
           'DESC'    => 'Class for slide indicator dots container',
           'DEFAULT' => 'slick-dots',
           "TYPE" => "text",
           ),
        "ARROWS" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Arrows',
            'DESC'    => 'Enable Next/Prev arrows',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            'data-show' => 'prevArrow, nextArrow'
            ),
        "PREVARROW" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Prev Arrow',
            'DESC'    => '(html | jQuery selector) | object (DOM node | jQuery object)   Allows you to select a node or customize the HTML for the "Previous" arrow. (May use %object%)',
            'DEFAULT' => '<button type="button" class="slick-prev">Previous</button>',
            "TYPE" => "text",
            ),
        "NEXTARROW" => array(
            "PARENT" => "LIB",
           "NAME"   => 'Next Arrow',
           'DESC'    => '(html | jQuery selector) | object (DOM node | jQuery object) Allows you to select a node or customize the HTML for the "Next" arrow. (May use %object%)',
           'DEFAULT' => '<button type="button" class="slick-next">Next</button>',
           "TYPE" => "text",
           ),
        "SPEED" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Speed',
            'DESC'    => 'Transition speed',
            'DEFAULT' => '300',
            "TYPE" => "NUMBER",
            ),
        "CENTERMODE" => array(
            "PARENT" => "LIB",
            "NAME" => 'Center Mode',
            'DESC'  => 'Enables centered view with partial prev/next slides. Use with odd numbered slidesToShow counts.',
            "TYPE" => "CHECKBOX",
            'data-show' => 'centerPadding',
            ),
        "CENTERPADDING" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Center Padding',
            'DESC'    => 'Side padding when in center mode. (px or %)',
            'DEFAULT' => '50px',
            "TYPE" => "text",
            ),
        "FADE" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Fade',
            'DESC'    => 'Enables fade',
            "TYPE" => "CHECKBOX",
            ),
        "VARIABLEWIDTH" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Variable Width',
            'DESC'    => 'Disables automatic slide width calculation',
            "TYPE" => "CHECKBOX",
            ),
        "ADAPTIVEHEIGHT" => array(
            "PARENT" => "LIB",
            "NAME" => 'AdaptiveHeight',
            'DESC'  => 'Adapts slider height to the current slide',
            "TYPE" => "CHECKBOX",
            ),
        "CSSEASE" => array(
            "PARENT" => "LIB",
            "NAME"   => 'CSS Ease',
            'DESC'    => 'CSS3 easing',
            'DEFAULT' => 'ease',
            "TYPE" => "text",
            ),
        "ACCESSIBILITY" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Accessibility',
            'DESC'    => 'Enables tabbing and arrow key navigation',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            ),
        "CUSTOMPAGING" => array(
            "PARENT" => "LIB",
            "NAME" => 'Custom Paging',
            'DESC'  => '(use %function_name%) Custom paging templates. See source for use example.',
            "TYPE" => "text",
            ),
        "DRAGGABLE" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Draggable',
            'DESC'    => 'Enables desktop dragging',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            ),
        "EASING" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Easing',
            'DESC'    => 'animate() fallback easing',
            'DEFAULT' => 'linear',
            "TYPE" => "text",
            ),
        "EDGEFRICTION" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Edge Friction',
            'DESC'    => 'Resistance when swiping edges of non-infinite carousels',
            'DEFAULT' => '0.15',
            "TYPE" => "NUMBER",
            ),
        "MOBILEFIRST" => array(
            "PARENT" => "LIB",
           "NAME"   => 'Mobile First',
           'DESC'    => 'Responsive settings use mobile first calculation',
           "TYPE" => "CHECKBOX",
           ),
        "INITIALSLIDE" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Initial Slide',
            'DESC'    => 'Slide to start on',
            'DEFAULT' => '0',
            "TYPE" => "NUMBER",
            ),
        "LAZYLOAD" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Lazy Load',
            'DESC'    => 'Accepts \'ondemand\' or \'progressive\' for lazy load technique. \'ondemand\' will load the image as soon as you slide to it, \'progressive\' loads one image after the other when the page loads.',
            'DEFAULT' => 'ondemand',
            "TYPE" => "text",
            ),
        "PAUSEONFOCUS" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Pause On Focus',
            'DESC'    => 'Pauses autoplay when slider is focussed',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            ),
        "PAUSEONHOVER" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Pause On Hover',
            'DESC'    => 'Pauses autoplay on hover',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            ),
        "PAUSEONDOTSHOVER" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Pause On Dots Hover',
            'DESC'    => 'Pauses autoplay when a dot is hovered',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            ),
        "RESPONDTO" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Respond To',
            'DESC'    => 'Width that responsive object responds to. Can be \'window\', \'slider\' or \'min\' (the smaller of the two).
            responsive  array   null    Array of objects containing breakpoints and settings objects (see example). Enables settings at given breakpoint. Set settings to "unslick" instead of an object to disable slick at a given breakpoint.',
            'DEFAULT' => 'window',
            "TYPE" => "text",
            ),
        "ROWS" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Rows',
            'DESC'    => 'Setting this to more than 1 initializes grid mode. Use slidesPerRow to set how many slides should be in each row.',
            "DEFAULT" => "Y",
            "TYPE" => "NUMBER",
            ),
        "SLIDE" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Slide',
            'DESC'    => 'Slide element query',
            'DEFAULT' => '',
            "TYPE" => "text",
            ),
        "SLIDESPERROW" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Slides Per Row',
            'DESC'    => 'With grid mode initialized via the rows option, this sets how many slides are in each grid row.',
            "DEFAULT" => "Y",
            "TYPE" => "NUMBER",
            ),
        "SWIPE" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Swipe',
            'DESC'    => 'Enables touch swipe',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            ),
        "SWIPETOSLIDE" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Swipe To Slide',
            'DESC'    => 'Swipe to slide irrespective of slidesToScroll',
            "TYPE" => "CHECKBOX",
            ),
        "TOUCHMOVE" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Touch Move',
            'DESC'    => 'Enables slide moving with touch',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            ),
        "TOUCHTHRESHOLD" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Touch Threshold',
            'DESC'    => 'To advance slides, the user must swipe a length of (1/touchThreshold) * the width of the slider.',
            'DEFAULT' => '5',
            "TYPE" => "NUMBER",
            ),
        "USECSS" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Use CSS',
            'DESC'    => 'Enable/Disable CSS Transitions',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            ),
        "USETRANSFORM" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Use Transform',
            'DESC'    => 'Enable/Disable CSS Transforms',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            ),
        "VERTICAL" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Vertical',
            'DESC'    => 'Vertical slide direction',
            "TYPE" => "CHECKBOX",
            ),
        "VERTICALSWIPING" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Vertical Swiping',
            'DESC'    => 'Changes swipe direction to vertical',
            "TYPE" => "CHECKBOX",
            ),
        "RTL" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Right To Left',
            'DESC'    => 'Change the slider\'s direction to become right-to-left',
            "TYPE" => "CHECKBOX",
            ),
        "WAITFORANIMATE" => array(
            "PARENT" => "LIB",
            "NAME"   => 'Wait For Animate',
            'DESC'    => 'Ignores requests to advance the slide while animating',
            'DEFAULT' => 'on',
            "TYPE" => "CHECKBOX",
            ),
        "ZINDEX" => array(
            "PARENT" => "LIB",
            "NAME"   => 'zIndex',
            'DESC'    => 'Set the zIndex values for slides, useful for IE9 and lower',
            'DEFAULT' => '1000',
            "TYPE" => "NUMBER",
            ),

        ),
);
