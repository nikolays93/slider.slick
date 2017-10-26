<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

if( !isset($arResult["IBLOCKS"]) || ! is_array($arResult["IBLOCKS"]) )
	return;

$this->addExternalCss($componentPath . "/assets/slick/slick.css");
$this->addExternalJS($componentPath . "/assets/slick/slick.min.js");

?>
<style type="text/css">
/* Default Icons */
.slick-loading .slick-list
{
	background: #fff url('<?=$componentPath;?>/assets/slick/ajax-loader.gif') center center no-repeat;
}
@font-face
{
	font-family: 'slick';
	font-weight: normal;
	font-style: normal;

	src: url('<?=$componentPath;?>/assets/slick/fonts/slick.eot');
	src: url('<?=$componentPath;?>/assets/slick/fonts/slick.eot?#iefix') format('embedded-opentype'),
	url('<?=$componentPath;?>/assets/slick/fonts/slick.woff') format('woff'),
	url('<?=$componentPath;?>/assets/slick/fonts/slick.ttf') format('truetype'),
	url('<?=$componentPath;?>/assets/slick/fonts/slick.svg#slick') format('svg');
}

</style>
<?php
foreach ($arResult["IBLOCKS"] as $arIBlock) {
	echo "<section id='slick-slider-{$arIBlock['ID']}'>";
	// Добавить кнопку "Добавить элемент"
	$this->AddEditAction('iblock_'.$arIBlock['ID'], $arIBlock['ADD_ELEMENT_LINK'], CIBlock::GetArrayByID($arIBlock["ID"], "ELEMENT_ADD"));

	$i = 0;
	foreach($arIBlock["ITEMS"] as $arItem) { ?>
		<div id="<?=$this->GetEditAreaId($arItem['ID']);?>"<?if($i) echo ' style="display: none;"';?>>
			<?php
			// Добавить кнопки "Изменить", "Удалить"
			// $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
			// $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => 'Вы уверены?'));

				bx_get_image( $arItem['PREVIEW_PICTURE'] );
				bx_get_image( $arItem['DETAIL_PICTURE'] );
			?>
			<!-- <a href="<? // =$arItem["DETAIL_PAGE_URL"]?>"><? // =$arItem["NAME"]?></a> -->
		</div>
		<?php
		$i++;
	}
	echo "</section>";
}

if( ! (string)$props = json_encode((array)$props) ) {
	$props = '';
}


?>
<? // =$props;?>
<script type="text/javascript">
	BX.message({ BLOCK_ID: '#slick-slider-<?=$arIBlock['ID'];?>' });
</script>
<?