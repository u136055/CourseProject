<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="body-blog">

<?
$APPLICATION->IncludeComponent(
		"bitrix:blog.new_posts.list", 
		"", 
		Array(
			"MESSAGE_PER_PAGE"		=> $arParams["MESSAGE_COUNT"],
			"PATH_TO_BLOG"		=>	$arParams["PATH_TO_BLOG"],
			"PATH_TO_POST"		=>	$arParams["PATH_TO_POST"],
			"PATH_TO_GROUP_BLOG_POST"		=>	$arParams["PATH_TO_GROUP_BLOG_POST"],
			"PATH_TO_GROUP_BLOG"		=>	$arParams["PATH_TO_GROUP_BLOG"],
			"PATH_TO_USER"		=>	$arParams["PATH_TO_USER"],
			"PATH_TO_BLOG_CATEGORY"	=> 	$arParams["PATH_TO_BLOG_CATEGORY"],
			"PATH_TO_GROUP_BLOG_CATEGORY"	=> 	$arParams["PATH_TO_GROUP_BLOG_CATEGORY"],
			"PATH_TO_SMILE"		=>	$arParams["PATH_TO_SMILE"],
			"CACHE_TYPE"		=>	$arParams["CACHE_TYPE"],
			"CACHE_TIME"		=>	$arParams["CACHE_TIME"],
			"BLOG_VAR"			=>	$arParams["VARIABLE_ALIASES"]["blog"],
			"POST_VAR"			=>	$arParams["VARIABLE_ALIASES"]["post_id"],
			"USER_VAR"			=>	$arParams["VARIABLE_ALIASES"]["user_id"],
			"PAGE_VAR"			=>	$arParams["VARIABLE_ALIASES"]["page"],
			"DATE_TIME_FORMAT"	=> $arParams["DATE_TIME_FORMAT"],
			"GROUP_ID" 			=> $arParams["GROUP_ID"],
			"SET_TITLE" 		=> $arResult["SET_TITLE"],
			"NAV_TEMPLATE"		=> $arParams["NAV_TEMPLATE"],
			"USE_SOCNET" 		=> $arParams["USE_SOCNET"],
			"SEO_USER"			=> $arParams["SEO_USER"],
			"NAME_TEMPLATE" 	=> $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" 		=> $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" 	=> $arParams["PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_SONET_USER_PROFILE" 	=> $arParams["PATH_TO_SONET_USER_PROFILE"],
			"PATH_TO_MESSAGES_CHAT" 		=> $arParams["PATH_TO_MESSAGES_CHAT"],
			"PATH_TO_VIDEO_CALL" 			=> $arParams["PATH_TO_VIDEO_CALL"],
			"POST_PROPERTY_LIST"			=> $arParams["POST_PROPERTY_LIST"],
			"USE_SHARE" 		=> $arParams["USE_SHARE"],
			"SHARE_HIDE" 		=> $arParams["SHARE_HIDE"],	
			"SHARE_TEMPLATE" 	=> $arParams["SHARE_TEMPLATE"],
			"SHARE_HANDLERS" 	=> $arParams["SHARE_HANDLERS"],
			"SHARE_SHORTEN_URL_LOGIN"		=> $arParams["SHARE_SHORTEN_URL_LOGIN"],
			"SHARE_SHORTEN_URL_KEY" 		=> $arParams["SHARE_SHORTEN_URL_KEY"],			
			"SHOW_RATING" => $arParams["SHOW_RATING"],
			"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
			"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
			"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
			"RATING_TYPE" => $arParams["RATING_TYPE"],
		),
		$component 
	);
?>
</div>