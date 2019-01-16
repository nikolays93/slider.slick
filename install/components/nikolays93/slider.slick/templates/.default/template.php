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
/** @var $arrSlickProps array params for lib */

if( 0 >= intval($arResult['COUNT']) ) return;

$this->setFrameMode(true);

$this->addExternalCss($componentPath . "/assets/slick/slick.css");
$this->addExternalJS ($componentPath . "/assets/slick/slick.min.js");
// $this->addExternalJS ($componentPath . "/assets/slick/initialize.js");
$slickID = randString(6);


?>
<div class="slick-list" id="slick-<?= $slickID ?>">
<?foreach ($arResult['IBLOCKS'] as $iblock):
foreach($iblock["ITEMS"] as $arItem):
	$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
	$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));

	$name = $arItem["NAME"];
	$img = sprintf('<img src="%s" width="%s" height="%s" alt="%s" title="%s" />',
		$arItem["PREVIEW_PICTURE"]["SRC"],
		$arItem["PREVIEW_PICTURE"]["WIDTH"],
		$arItem["PREVIEW_PICTURE"]["HEIGHT"],
		$arItem["PREVIEW_PICTURE"]["ALT"],
		$arItem["PREVIEW_PICTURE"]["TITLE"]);
	?>
	<div class="item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">

		<div class="thumbnail">
			<?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])) {
				echo ( $arItem['SLICK_LINK'] ) ? sprintf('<a href="%s">%s</a>', $arItem['SLICK_LINK'], $img) : $img;
			}?>
		</div>

		<div class="description">
			<?if($arParams["DISPLAY_NAME"]!="N" && $arItem["NAME"]) {
				echo sprintf('<div class="item-title">%s</div>',
					$arItem['SLICK_LINK'] ? sprintf('<a href="%s">%s</a>', $arItem['SLICK_LINK'], $name) : $name);
			}?>

			<?if($arParams["DISPLAY_DATE"]!="N" && $arItem["DISPLAY_ACTIVE_FROM"]):?>
				<small class="date-time"><?echo $arItem["DISPLAY_ACTIVE_FROM"]?></small>
			<?endif?>

			<?if($arParams["DISPLAY_PREVIEW_TEXT"]!="N" && $arItem["PREVIEW_TEXT"]):?>
				<div class="text"><?echo $arItem["PREVIEW_TEXT"];?></div>
			<?endif;?>
		</div>

	</div>
<?endforeach;endforeach;?>
</div><!-- .slick-list -->

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$( '#slick-<?= $slickID ?>' ).slick(<?=json_encode( $arResult['SlickProps'] );?>);
	});
</script>
