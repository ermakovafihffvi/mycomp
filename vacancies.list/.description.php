<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("T_IBLOCK_DESC_VAC_LIST"),
	"DESCRIPTION" => GetMessage("T_IBLOCK_DESC_VAC_DESC"),
	"CACHE_PATH" => "Y",
	"SORT" => 40,
	"PATH" => array(
		"ID" => "content",
		"CHILD" => array(
			"ID" => "vacansies",
			"NAME" => GetMessage("T_IBLOCK_DESC_VAC"),
			"SORT" => 20,
		)
	),
);

?>