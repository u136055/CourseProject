<?php
use Bitrix\Main\Localization\Loc;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
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
$storage = \Bitrix\Disk\Driver::getInstance()->getStorageByUserId($arResult['VARIABLES']['user_id']);
if(!$storage)
{
	$storage = \Bitrix\Disk\Driver::getInstance()->addUserStorage($arResult['VARIABLES']['user_id']);
}

$arResult['VARIABLES']['STORAGE'] = $storage;
?>
<?
$pageId = "user_files";
include("util_menu.php");
include("util_profile.php");
?>

<div class="bx-disk-container posr">
	<table style="width: 100%;" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				<div class="bx-disk-interface-toolbar-container">
					<?php
					$APPLICATION->IncludeComponent(
						'bitrix:disk.folder.toolbar',
						'',
						array(
							'STORAGE' => $arResult['VARIABLES']['STORAGE'],
							'FOLDER' => $arResult['VARIABLES']['STORAGE']->getRootObject(),

							'URL_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_TRASHCAN_LIST'], array('TRASH_PATH' => '', 'user_id' => $arResult['VARIABLES']['user_id'])),
							'URL_TO_FOLDER_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_DISK'], array('PATH' => '', 'user_id' => $arResult['VARIABLES']['user_id'])),
							'PATH_TO_EXTERNAL_LINK_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_EXTERNAL_LINK_LIST'], array('user_id' => $arResult['VARIABLES']['user_id'])),
							'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK'], array('user_id' => $arResult['VARIABLES']['user_id'])),
							'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),
							'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),
							'PATH_TO_TRASHCAN_LIST' => CComponentEngine::makePathFromTemplate($arResult['PATH_TO_USER_TRASHCAN_LIST'], array('user_id' => $arResult['VARIABLES']['user_id'])),

							'MODE' => 'external_link_list'
						),
						$component
					);
					?>
				</div>
				<?php
				$APPLICATION->IncludeComponent(
					'bitrix:disk.external.link.list',
					'',
					array_merge($arResult, array(
						'STORAGE' => $arResult['VARIABLES']['STORAGE'],

						'PATH_TO_FOLDER_LIST' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK'], array('user_id' => $arResult['VARIABLES']['user_id'])),
						'PATH_TO_FOLDER_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),
						'PATH_TO_FILE_VIEW' => CComponentEngine::MakePathFromTemplate($arResult['PATH_TO_USER_DISK_FILE'], array('user_id' => $arResult['VARIABLES']['user_id'])),

					)),
					$component
				);?>
			</td>
			<td class="bx-disk-table-sidebar-cell" style="">
				<div id="bx_disk_empty_select_section" class="bx-disk-sidebar-section">
					<div class="bx-disk-info-panel">
						<div class="bx-disk-info-panel-relative tac">
							<div class="bx-disk-info-panel-icon-empty"><br></div>
							<div class="bx-disk-info-panel-empty-text">
								<?= Loc::getMessage('DISK_VIEW_SMALL_DETAIL_SIDEBAR') ?>
							</div>
						</div>
					</div>
				</div>

				<div id="disk_info_panel"></div>
			</td>
		</tr>
	</table>
</div>

