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


/*************************************************************************
	Processing of received parameters
*************************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 180;


$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
file_put_contents(__DIR__."/log.txt", print_r($arParams, true));

if($arParams["IBLOCK_ID"] > 0 && $this->StartResultCache(false, ($arParams["CACHE_GROUPS"]==="N"? false: $USER->GetGroups())))
{
    file_put_contents(__DIR__."/log1.txt", print_r($arParams, true));
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return;
	}
	//SELECT
	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"CODE",
		"IBLOCK_SECTION_ID",
		"NAME",
		"DETAIL_PICTURE",
		"DETAIL_PAGE_URL",
	);
	//WHERE
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ACTIVE_DATE" => "Y",
		"ACTIVE"=>"Y",
		"CHECK_PERMISSIONS"=>"Y",
	);
	if($arParams["PARENT_SECTION"]>0)
	{
		$arFilter["SECTION_ID"] = $arParams["PARENT_SECTION"];
		$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
	}
	//ORDER BY
	$arSort = false;
	//EXECUTE
	$rsIBlockElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
	$rsIBlockElement->SetUrlTemplates($arParams["DETAIL_URL"]);
	while($arr = $rsIBlockElement->fetch())
	{
        $arr["PROPERTIES"] = [];
        $arVacancies[$arr['ID']] = $arr;
		//$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($arResult["IBLOCK_ID"], $arResult["ID"]); //непонятное место
		//$arResult["IPROPERTY_VALUES"] = $ipropValues->getValues();
	}
    CIBlockElement::GetPropertyValuesArray($arVacancies, $arFilter['IBLOCK_ID'], $arFilter);
	file_put_contents(__DIR__."/arVac.txt", print_r($arVacancies, true));

    //получаем имена разделов
    $arSec = [];
    foreach($arVacancies as $arItem){
		if(!array_key_exists($arItem['IBLOCK_SECTION_ID'], $arSec)){
			$arSec[$arItem['IBLOCK_SECTION_ID']]['ID'] = $arItem['IBLOCK_SECTION_ID'];
			$Ids[] = $arItem['IBLOCK_SECTION_ID'];
		}
    }

	$res = [];
	$rsSecNames = CIBlockSection::GetList(
		Array("SORT"=>"ASC"),
		['@ID' => $Ids, 'IBLOCK_ID' => $arParams["IBLOCK_ID"]],
		false,
		['ID', 'NAME'],
		false
	);
	while($res = $rsSecNames->fetch()){
		$arSec[$res['ID']]['NAME'] = $res['NAME'];
	}

	file_put_contents(__DIR__."/arSec.txt", print_r($arSec, true));

	//формируем arResult с учётом задания
	$arResult = [];
	foreach($arSec as $secItem){
		$arResult[$secItem['ID']]['ID'] = $secItem['ID'];
		$arResult[$secItem['ID']]['SECTION_NAME'] = $secItem['NAME'];
		$arResult[$secItem['ID']]['VACANCIES'] = [];
		foreach($arVacancies as $vacItem){
			if($vacItem['IBLOCK_SECTION_ID'] == $secItem['ID']){
				$arResult[$secItem['ID']]['VACANCIES'][] = $vacItem;
			}
		}
	}

    if($arResult)
    {
        $this->SetResultCacheKeys(array(
		));
		$this->IncludeComponentTemplate();
    }
	else
	{
		$this->AbortResultCache();
	}
    file_put_contents(__DIR__."/arResult.txt", print_r($arResult, true));
}
?>