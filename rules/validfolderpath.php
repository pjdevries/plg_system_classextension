<?php
/**
 * @package    Class Extension System Plugin
 *
 * @author     Pieter-Jan de Vries/Obix webtechniek <pieter@obix.nl>
 * @copyright  Copyright © 2020 Obix webtechniek. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.obix.nl
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

class JFormRuleValidFolderPath extends \JFormRule
{
	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
	{
		$path = JPATH_ROOT . '/' . trim($value, '\\/');

		if (is_dir($path))
		{
			// Path exists and is a folder.
			return true;
		}

		if (file_exists($path))
		{
			// Path exists but is not a folder.
			$element->addAttribute(
				'message',
				Text::_('PLG_SYSTEM_CLASS_EXTENSION_EXTENSIONS_PATH_NOT_A_FOLDER')
			);

			return false;
		}

		if (!(int) $input->get('params')->extensionRootPathCreate)
		{
			// Path does not exist but auto creation is not requested.
			$element->addAttribute(
				'message',
				Text::_('PLG_SYSTEM_CLASS_EXTENSION_EXTENSIONS_PATH_MAY_NOT_CREATE')
			);

			return false;
		}

		// Try to create the path.
		try
		{
			if (@mkdir($path, 0755, true))
			{
				return true;
			}

			$lastError = error_get_last();
			$message = $lastError ?  $lastError['message'] : Text::_('PLG_SYSTEM_CLASS_EXTENSION_UNKNOWN_ERROR');
			$element->addAttribute(
				'message',
				Text::sprintf(
					'PLG_SYSTEM_CLASS_EXTENSION_EXTENSIONS_PATH_CREATION_ERROR',
					$message
				)
			);

			return false;
		}
		catch (\Exception $e)
		{
			$element->addAttribute(
				'message',
				Text::sprintf(
					'PLG_SYSTEM_CLASS_EXTENSION_EXTENSIONS_PATH_CREATION_ERROR',
					$e->getMessage()
				)
			);
		}
	}
}