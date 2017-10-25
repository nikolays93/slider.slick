<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => "Slick Слайдер",
	"DESCRIPTION" => "Показывает перелистывающиеся изображения",
	"ICON" => "/images/news_all.gif",
	"SORT" => 50,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID"   => "slider",
			"NAME" => 'Slick Слайдер',
			"SORT" => 10,
		)
	),
);
