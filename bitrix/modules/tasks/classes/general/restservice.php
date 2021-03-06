<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 */

if(!CModule::IncludeModule('rest'))
{
	return;
}

/**
 * This class is for internal use only, not a part of public API.
 * It can be changed at any time without notification.
 *
 * @access private
 */
final class CTaskRestService extends IRestService
{
	const SCOPE_NAME_NORMAL     = 'task';
	const SCOPE_NAME_EXTENDED   = 'tasks_extended';
	const TASKS_LIMIT_PAGE_SIZE = 50;	// Max nPageSize (NAV_PARAMS) for GetLists()
	const TASKS_LIMIT_TOP_COUNT = 50;	// Max nTopCount (NAV_PARAMS) for GetLists()

	// private const
	const delimiter = '____x____';

	// Must be lower-case list
	private static $arAllowedClasses = array(
		'ctaskitem',
		'ctaskelapseditem',
		'ctaskcommentitem',
		'ctaskchecklistitem',
		'ctaskplannermaintance',

		/*deprecated*/
		'ctaskcomments',
		'ctasks'
	);

	// will be inited later:
	private static $inited = false;
	private static $arMethodsMetaInfo = array();
	private static $arAllowedMethods;
	private static $arClassAliases = array();
	private static $arManifests = array();


	public static function onRestServiceBuildDescription()
	{
		if ( ! self::$inited )
			self::_init();

		$arFunctionsMap = array(
			CRestUtil::EVENTS => array(
				'OnTaskAdd'    => array('tasks', 'OnTaskAdd', array('CTaskRestService', 'onEventFilter')),
				'OnTaskUpdate' => array('tasks', 'OnTaskUpdate', array('CTaskRestService', 'onEventFilter')),
				'OnTaskDelete' => array('tasks', 'OnTaskDelete', array('CTaskRestService', 'onEventFilter'))
			)
		);

		foreach (self::$arAllowedMethods as $className => $arMethods)
		{
			$aliasClassName = null;
			if (isset(self::$arClassAliases[$className]))
				$aliasClassName = self::$arClassAliases[$className];

			foreach ($arMethods as $methodName)
			{
				$aliasMethodName   = null;
				if (isset(self::$arMethodsMetaInfo[$className][$methodName]['alias']))
					$aliasMethodName = self::$arMethodsMetaInfo[$className][$methodName]['alias'];

				$transitMethodName = $className . self::delimiter . $methodName;

				$arPublicNames = array('task.' . $className . '.' . $methodName);

				if ($aliasMethodName !== null)
					$arPublicNames[] = 'task.' . $className . '.' . $aliasMethodName;

				// Is class alias exists?
				if ($aliasClassName !== null)
				{
					$arPublicNames[] = 'task.' . $aliasClassName . '.' . $methodName;

					if ($aliasMethodName !== null)
						$arPublicNames[] = 'task.' . $aliasClassName . '.' . $aliasMethodName;
				}

				foreach ($arPublicNames as $publicMethodName)
					$arFunctionsMap[$publicMethodName] = array('CTaskRestService', $transitMethodName);
			}
		}

		return array(
			self::SCOPE_NAME_NORMAL   => $arFunctionsMap,
			self::SCOPE_NAME_EXTENDED => array(
				'tasks_extended.meta.setAnyStatus'  => array('CTaskRestService', 'tasks_extended_meta_setAnyStatus'),
				'tasks_extended.meta.occurInLogsAs' => array('CTaskRestService', 'tasks_extended_meta_occurInLogsAs')
			)
		);
	}


	public static function tasks_extended_meta_occurInLogsAs($args)
	{
		$arMessages  = array();
		$parsedReturnValue = null;
		$withoutExceptions = false;

		try
		{
			if ( ! (
				CTasksTools::IsAdmin()
				|| CTasksTools::IsPortalB24Admin()
			))
			{
				throw new TasksException('Only root can do this', TasksException::TE_ACCESS_DENIED);
			}

			CTaskAssert::assert(
				is_array($args) 
				&& (count($args) == 1)
			);

			$userId = array_pop($args);

			CTasksTools::setOccurAsUserId($userId);

			$parsedReturnValue = CTasksTools::getOccurAsUserId();
			$withoutExceptions = true;
		}
		catch (CTaskAssertException $e)
		{
			$arMessages[] = array(
				'id'   => 'TASKS_ERROR_ASSERT_EXCEPTION',
				'text' => 'TASKS_ERROR_ASSERT_EXCEPTION'
			);
		}
		catch (TasksException $e)
		{
			$errCode = $e->getCode();
			$errMsg  = $e->getMessage();

			if ($e->GetCode() & TasksException::TE_FLAG_SERIALIZED_ERRORS_IN_MESSAGE)
				$arMessages = unserialize($errMsg);
			else
			{
				$arMessages[] = array(
					'id'   => 'TASKS_ERROR_EXCEPTION_#' . $errCode,
					'text' => 'TASKS_ERROR_EXCEPTION_#' . $errCode 
						. '; ' . $errMsg
						. '; ' . TasksException::renderErrorCode($e)
				);
			}
		}
		catch (Exception $e)
		{
			$errMsg = $e->getMessage();
			if ($errMsg !== '')
				$arMessages[] = array('text' => $errMsg, 'id' => 'TASKS_ERROR');
		}

		if ($withoutExceptions)
			return ($parsedReturnValue);
		else
		{
			self::_emitError($arMessages);
			throw new Exception();
		}
	}


	public static function tasks_extended_meta_setAnyStatus($args)
	{
		$arMessages  = array();
		$parsedReturnValue = null;
		$withoutExceptions = false;

		try
		{
			CTaskAssert::assert(
				is_array($args) 
				&& (count($args) == 2)
			);

			$statusId = array_pop($args);
			$taskId   = array_pop($args);

			CTaskAssert::assertLaxIntegers($statusId, $taskId);

			$taskId   = (int) $taskId;
			$statusId = (int) $statusId;

			if ( ! in_array(
				$statusId,
				array(
					CTasks::STATE_PENDING,
					CTasks::STATE_IN_PROGRESS,
					CTasks::STATE_SUPPOSEDLY_COMPLETED,
					CTasks::STATE_COMPLETED,
					CTasks::STATE_DEFERRED
				),
				true	// forbid type casting
			))
			{
				throw new TasksException('Invalid status given', TasksException::TE_WRONG_ARGUMENTS);
			}

			$oTask = CTaskItem::getInstance($taskId, 1);	// act as Admin
			$oTask->update(array('STATUS' => $statusId));

			$parsedReturnValue = null;

			$withoutExceptions = true;
		}
		catch (CTaskAssertException $e)
		{
			$arMessages[] = array(
				'id'   => 'TASKS_ERROR_ASSERT_EXCEPTION',
				'text' => 'TASKS_ERROR_ASSERT_EXCEPTION'
			);
		}
		catch (TasksException $e)
		{
			$errCode = $e->getCode();
			$errMsg  = $e->getMessage();

			if ($e->GetCode() & TasksException::TE_FLAG_SERIALIZED_ERRORS_IN_MESSAGE)
				$arMessages = unserialize($errMsg);
			else
			{
				$arMessages[] = array(
					'id'   => 'TASKS_ERROR_EXCEPTION_#' . $errCode,
					'text' => 'TASKS_ERROR_EXCEPTION_#' . $errCode 
						. '; ' . $errMsg
						. '; ' . TasksException::renderErrorCode($e)
				);
			}
		}
		catch (Exception $e)
		{
			$errMsg = $e->getMessage();
			if ($errMsg !== '')
				$arMessages[] = array('text' => $errMsg, 'id' => 'TASKS_ERROR');
		}

		if ($withoutExceptions)
			return ($parsedReturnValue);
		else
		{
			self::_emitError($arMessages);
			throw new Exception();
		}
	}


	public static function onEventFilter($arParams, $arHandler)
	{
		if ( ! isset($arHandler['EVENT_NAME']) )
			$arHandler['EVENT_NAME'] = '$arHandler[\'EVENT_NAME\'] is not set';

		$arEventFields = array(
			'FIELDS_BEFORE'        => 'undefined',
			'FIELDS_AFTER'         => 'undefined',
			'IS_ACCESSIBLE_BEFORE' => 'undefined',
			'IS_ACCESSIBLE_AFTER'  => 'undefined'
		);

		$taskId = (int) $arParams[0];

		CTaskAssert::assert($taskId >= 1);

		switch (strtolower($arHandler['EVENT_NAME']))
		{
			case 'ontaskadd':
				$arEventFields['FIELDS_BEFORE']        = 'undefined';
				$arEventFields['FIELDS_AFTER']         =  array('ID' => $taskId);
				$arEventFields['IS_ACCESSIBLE_BEFORE'] = 'N';
				$arEventFields['IS_ACCESSIBLE_AFTER']  = 'undefined';
			break;

			case 'ontaskupdate':
				$arEventFields['FIELDS_BEFORE']        =  array('ID' => $taskId);
				$arEventFields['FIELDS_AFTER']         =  array('ID' => $taskId);
				$arEventFields['IS_ACCESSIBLE_BEFORE'] = 'undefined';
				$arEventFields['IS_ACCESSIBLE_AFTER']  = 'undefined';
			break;

			case 'ontaskdelete':
				$arEventFields['FIELDS_BEFORE']        =  array('ID' => $taskId);
				$arEventFields['FIELDS_AFTER']         = 'undefined';
				$arEventFields['IS_ACCESSIBLE_BEFORE'] = 'undefined';
				$arEventFields['IS_ACCESSIBLE_AFTER']  = 'N';
			break;

			default:
				throw new Exception(
					'tasks\' RPC event handler: onEventFilter: '
					. 'not allowed $arHandler[\'EVENT_NAME\']: ' 
					. $arHandler['EVENT_NAME']
				);
			break;
		}

		return ($arEventFields);
	}


	public static function __callStatic($transitMethodName, $args)
	{
		global $APPLICATION, $USER;

		$APPLICATION->resetException();

		if ( ! self::$inited )
			self::_init();

		$arFuncNameParts = explode(self::delimiter, $transitMethodName, 2);
		$className  = $arFuncNameParts[0];
		$methodName = $arFuncNameParts[1];

		$returnValue = null;
		$arMessages  = array();
		$parsedReturnValue = null;

		$withoutExceptions = false;
		try
		{
			if ( ! in_array($className, self::$arAllowedClasses, true) )
				throw new Exception('Unknown REST-method signature given');

			$methodArgs = array();

			foreach ($args[0] as $value)
				$methodArgs[] = $value;

			/** @noinspection PhpUndefinedMethodInspection */
			list($returnValue, $dbResult) = $className::runRestMethod(
				(int) $USER->getId(),
				$methodName,
				$methodArgs,
				self::getNavData($args[1])
			);

			$parsedReturnValue = self::_parseReturnValue($className, $methodName, $returnValue);

			if ($dbResult !== null)
				$parsedReturnValue = self::setNavData($parsedReturnValue, $dbResult);

			$withoutExceptions = true;
		}
		catch (CTaskAssertException $e)
		{
			$arMessages[] = array(
				'id'   => 'TASKS_ERROR_ASSERT_EXCEPTION',
				'text' => 'TASKS_ERROR_ASSERT_EXCEPTION'
			);
		}
		catch (TasksException $e)
		{
			$errCode = $e->getCode();
			$errMsg  = $e->getMessage();

			if ($e->GetCode() & TasksException::TE_FLAG_SERIALIZED_ERRORS_IN_MESSAGE)
				$arMessages = unserialize($errMsg);
			else
			{
				$arMessages[] = array(
					'id'   => 'TASKS_ERROR_EXCEPTION_#' . $errCode,
					'text' => 'TASKS_ERROR_EXCEPTION_#' . $errCode 
						. '; ' . $errMsg
						. '; ' . TasksException::renderErrorCode($e)
				);
			}
		}
		catch (Exception $e)
		{
			$errMsg = $e->getMessage();
			if ($errMsg !== '')
				$arMessages[] = array('text' => $errMsg, 'id' => 'TASKS_ERROR');
		}

		if ($withoutExceptions)
			return ($parsedReturnValue);
		else
		{
			self::_emitError($arMessages);
			throw new Exception();
		}
	}


	protected static function getNavData($start)
	{
		return array(
			'nPageSize' => self::TASKS_LIMIT_PAGE_SIZE,
			'iNumPage'  => intval($start / self::TASKS_LIMIT_PAGE_SIZE) + 1
		);
	}


	private static function _init()
	{
		self::$arAllowedMethods = array();

		foreach (self::$arAllowedClasses as $className)
		{
			$arManifest = $className::getManifest();
			self::$arAllowedMethods[$className] = array_map(
				'strtolower',
				array_keys($arManifest['REST: available methods'])
			);
			self::$arMethodsMetaInfo[$className] = $arManifest['REST: available methods'];

			if (isset($arManifest['REST: shortname alias to class']))
			{
				$aliasClassName = $arManifest['REST: shortname alias to class'];
				self::$arClassAliases[$className] = $aliasClassName;
			}

			self::$arManifests[$className] = $arManifest;
		}

		self::$inited = true;
	}


	private static function _emitError($arMessages = array())
	{
		global $APPLICATION;

		if (empty($arMessages))
		{
			$arMessages[] = array(
				'id'   => 'TASKS_ERROR_UNKNOWN',
				'text' => 'TASKS_ERROR_UNKNOWN'
			);
		}

		$e = new CAdminException($arMessages);
		$APPLICATION->throwException($e);
	}


	/**
	 * This function is for internal use only, not a part of public API
	 *
	 * @access private
	 */
	private static function _parseReturnValue($className, $methodName, $returnValue)
	{
		CTaskAssert::assert(isset(self::$arMethodsMetaInfo[$className][$methodName]));
		$parsedValue = null;

		$arDateFields = array();
		if (isset(self::$arManifests[$className]['REST: date fields']))
			$arDateFields = self::$arManifests[$className]['REST: date fields'];

		$arMethodMetaInfo = self::$arMethodsMetaInfo[$className][$methodName];

		// Function returns an array of file ids?
		if (
			isset($arMethodMetaInfo['fileIdsReturnValue'])
			&& ($arMethodMetaInfo['fileIdsReturnValue'] === true)
			&& is_array($returnValue)
		)
		{
			$parsedValue = array();
			foreach ($returnValue as &$fileId)
				$parsedValue[] = '/bitrix/components/bitrix/tasks.task.detail/show_file.php?fid=' . (int) $fileId;
			unset($fileId);
		}
		else if (is_array($returnValue) && isset($arMethodMetaInfo['allowedKeysInReturnValue']))
		{
			if (
				isset($arMethodMetaInfo['collectionInReturnValue'])
				&& ($arMethodMetaInfo['collectionInReturnValue'] === true)
			)
			{
				$parsedValue = array();
				foreach ($returnValue as $valueToBeFiltered)
				{
					$parsedValue[] = self::_filterArrayByAllowedKeys(	// Additionally converts datetime fields
						$valueToBeFiltered,
						$arMethodMetaInfo['allowedKeysInReturnValue'],
						$arDateFields
					);
				}

			}
			else
			{
				$parsedValue = self::_filterArrayByAllowedKeys(	// Additionally converts datetime fields
					$returnValue,
					$arMethodMetaInfo['allowedKeysInReturnValue'],
					$arDateFields
				);
			}
		}
		else
			$parsedValue = $returnValue;

		return ($parsedValue);
	}


	private static function _filterArrayByAllowedKeys($arData, $arAllowedKeys, $arDateFields = array())
	{
		$filteredData = array();

		foreach ($arAllowedKeys as $allowedKey)
		{
			if (array_key_exists($allowedKey, $arData))
			{
				// Additionally convert datetime fields
				if (in_array((string) $allowedKey, $arDateFields, true))
					$filteredData[$allowedKey] = CRestUtil::convertDateTime($arData[$allowedKey]);
				else
					$filteredData[$allowedKey] = $arData[$allowedKey];
			}
		}

		return ($filteredData);
	}


	/**
	 * This function is for internal use only, not a part of public API
	 *
	 * @access private
	 */
	public static function _parseRestParams($className, $methodName, $inArgs)
	{
		CTaskAssert::assert(is_array($inArgs) && isset(self::$arMethodsMetaInfo[$className][$methodName]));

		$arMethodMetaInfo     = self::$arMethodsMetaInfo[$className][$methodName];
		$arAllowedParams      = $arMethodMetaInfo['params'];
		$mandatoryParamsCount = $arMethodMetaInfo['mandatoryParamsCount'];

		$arDateFields = array();
		if (isset(self::$arManifests[$className]['REST: date fields']))
			$arDateFields = self::$arManifests[$className]['REST: date fields'];

		$outArgs = array();
		foreach ($arAllowedParams as $paramIndex => $paramMetaInfo)
		{
			// No more params given?
			if ( ! array_key_exists($paramIndex, $inArgs) )
			{
				// Set default value, if need
				if (array_key_exists('defaultValue', $paramMetaInfo))
					$inArgs[$paramIndex] = $paramMetaInfo['defaultValue'];
				elseif ($paramIndex < $mandatoryParamsCount)	// Expected mandatory param?
				{
					throw new TasksException(
						'Param #' . $paramIndex . ' (' . $paramMetaInfo['description'] . ')'
						. ' expected by method ' . $className . '::' . $methodName . '(), but not given.',
						TasksException::TE_WRONG_ARGUMENTS
					);
				}
				else
					break;		// no more params to be processed
			}

			// for "galvanic isolation" of input/output
			$paramValue = $inArgs[$paramIndex];

			// Check param type
			/** @noinspection PhpUnusedLocalVariableInspection */
			$isCorrectValue = false;
			switch ($paramMetaInfo['type'])
			{
				case 'boolean':
					if (($paramValue === '0') || ($paramValue === 0))
						$paramValue = false;
					elseif (($paramValue === '1') || ($paramValue === 1))
						$paramValue = true;

					$isCorrectValue = is_bool($paramValue);
				break;

				case 'array':
					$isCorrectValue = is_array($paramValue);
				break;

				case 'string':
					$isCorrectValue = is_string($paramValue);
				break;

				case 'integer':
					$isCorrectValue = CTaskAssert::isLaxIntegers($paramValue);
				break;

				default:
					throw new TasksException(
						'Internal error: unknown param type: ' . $paramMetaInfo['type'],
						TasksException::TE_UNKNOWN_ERROR
					);
				break;
			}

			if ( ! $isCorrectValue )
			{
				throw new TasksException(
					'Param #' . $paramIndex . ' (' . $paramMetaInfo['description'] . ')'
					. ' for method ' . $className . '::' . $methodName . '()'
					. ' expected to be of type "' . $paramMetaInfo['type'] . '",'
					. ' but given something else.',
					TasksException::TE_WRONG_ARGUMENTS
				);
			}

			if (isset($paramMetaInfo['allowedKeys']))
			{
				CTaskAssert::assert(is_array($paramValue));	// ensure that $paramValue is array
				/** @var $paramValue array */

				foreach (array_keys($paramValue) as $key)
				{
					if (isset($paramMetaInfo['allowedKeyPrefixes']))
						$keyWoPrefix = str_replace($paramMetaInfo['allowedKeyPrefixes'], '', $key);
					else
						$keyWoPrefix = $key;

					if ( ! in_array((string) $keyWoPrefix, $paramMetaInfo['allowedKeys'], true) )
					{
						throw new TasksException(
							'Param #' . $paramIndex . ' (' . $paramMetaInfo['description'] . ')'
							. ' for method ' . $className . '::' . $methodName . '()'
							. ' must not contain key "' . $key . '".',
							TasksException::TE_WRONG_ARGUMENTS
						);
					}

					// Additionally convert datetime fields from ISO 8601
					if (in_array((string) $keyWoPrefix, $arDateFields, true) && !in_array($paramValue[$key], array('asc', 'desc'))/*it could be sorting*/)
						$paramValue[$key] = (string) CRestUtil::unConvertDateTime($paramValue[$key]);
				}
			}

			if (isset($paramMetaInfo['allowedValues']))
			{
				CTaskAssert::assert(is_array($paramValue));

				foreach ($paramValue as $value)
				{
					if ( ($value !== null) && ( ! is_bool($value) ) )
						$value = (string) $value;

					if ( ! in_array($value, $paramMetaInfo['allowedValues'], true) )
					{
						throw new TasksException(
							'Param #' . $paramIndex . ' (' . $paramMetaInfo['description'] . ')'
							. ' for method ' . $className . '::' . $methodName . '()'
							. ' must not contain value "' . $value . '".',
							TasksException::TE_WRONG_ARGUMENTS
						);
					}
				}
			}

			// "galvanic isolation" of input/output
			$outArgs[] = $paramValue;
		}

		if (count($inArgs) > count($arAllowedParams))
		{
			throw new TasksException(
				'Too much params(' . count($inArgs) . ') given for method ' . $className . '::' . $methodName . '()'
				. ', but expected not more than ' . count($arAllowedParams) . '.',
				TasksException::TE_WRONG_ARGUMENTS
			);
		}

		return ($outArgs);
	}
}
