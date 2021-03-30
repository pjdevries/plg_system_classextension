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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;

/**
 * ClassExtension plugin.
 *
 * @package   Class Extension System Plugin
 * @since     1.0.0
 */
class plgSystemClassExtension extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  1.0.0
	 */
	protected $db;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

    /**
     * Base path for class etension files.
     *
     * @var string
     */
    private $extensionRootPath = '';

    const EXTENSION = 'ExtensionBase';

	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		$this->extensionRootPath = JPATH_SITE . '/templates/' . $this->app->getTemplate() . '/class_extensions';
	}

    /**
     * Listener for the 'onAfterInitialise' event
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onAfterRoute(): void
    {
        // Initialise extended (core) classses.
        $this->extendClasses();
    }

    /**
     * Initialise extended core classes.
     */
    private function extendClasses(): void
    {
        // File path of the class extension specifications.
        $classExtensionSepecificationFile = $this->extensionRootPath . '/class_extensions.json';

        // If no specification file exists, we're done already.
        if (!file_exists($classExtensionSepecificationFile))
        {
            return;
        }

        // Read json encoded file and decode into array of \stdClass objects.
        // The file contains an array of objects with the following attributes:
        // - "file": the path of the file, relative to the website root,
        //           containing the original class definition to be extended.
        // - "class": the name of the original class to be extended.
        $classExtensions = json_decode(file_get_contents($classExtensionSepecificationFile));

        foreach ($classExtensions as $extensionSpecs)
        {
            $this->extend($extensionSpecs);
        }
    }

    private function extend(\stdClass $extensionSpecs): void
    {
        $className = $extensionSpecs->class;

        // Remove root path ...
        $originalClassFile = preg_replace('#^' . preg_quote(JPATH_ROOT) . '#',
            '', $extensionSpecs->file);
        // ... and leading/trailing slashes from original file path.
        $originalClassFile = trim($originalClassFile, '\\/');

        // If the extension specifications only applies to a specific route,
        // we expect a name to be used as part of the path name (see below).
        // This implies that the route specs name must be filename safe.
        if (($hasRoute = isset($extensionSpecs->route)) && !isset($extensionSpecs->route->name))
        {
            throw new \RuntimeException(Text::_('PLG_SYSTEM_CLASS_EXTENSION_MISSING_ROUTE_NAME'));
        }

        // If the extension specifications only applies to a specific route,
        // we check if the current route matches the route specs.
        if ($hasRoute && !$this->isEnRoute($extensionSpecs->route))
        {
            return;
        }

        // Extract path without filename from original file path.
        // We use this for the path of the copied original and
        // for extended class file.
        $classExtensionDir = dirname($originalClassFile);

        // For route specific extensions we append the route specs name.
        if ($hasRoute)
        {
            $classExtensionDir .= '/' . $extensionSpecs->route->name;
        }

        // Both the the name of the extended class and its filename are the same as
        // the name of the original class. The file is located next to the copy of
        // the original class file.
        $extendedClassFile = sprintf("%s/%s/%s.php",
            $this->extensionRootPath, $classExtensionDir, $className);

        // If no extended class file exists, we're done already.
        if (!file_exists($extendedClassFile))
        {
            return;
        }

        // The class to be extended is copied to a file with the name of the
        // original class and 'ExtensionBase' appended. The directory path of the
        // copy is the same as the original path relative to the website root,
        // but now relative to the extension root directory.
        $toBeExtendedClassFile = sprintf("%s/%s/%s%s.php",
            $this->extensionRootPath, $classExtensionDir, $className, self::EXTENSION);

        // Make original file path absolute.
        $orgiginalClassFile = JPATH_ROOT . '/' . $originalClassFile;

        // If no copy of the original class file exists or if it has changed since
        // the copy was made, we make a fresh copy.
        if (!file_exists($toBeExtendedClassFile) || filemtime($orgiginalClassFile) > filemtime($toBeExtendedClassFile))
        {
            $orgFileContents = file_get_contents($orgiginalClassFile);

	        static $replacement = '$1' . self::EXTENSION;
	        $pattern = '/\b(' . $className . ')\b/';
	        $ExtensionBaseContents = preg_replace($pattern, $replacement, $orgFileContents);

            file_put_contents($toBeExtendedClassFile, $ExtensionBaseContents);
        }

        // Now we first include the copy of the original class file, making the original
        // class available with a derived name (i.e with 'ExtensionBase' appended).
        include_once $toBeExtendedClassFile;

        // Next we include overriding class, which has the name of the original class
        // and extends the original class by refering to the derived name of the copy.
        // The overriding class is now loaded and readily available.
        include_once $extendedClassFile;
    }

    private function isEnRoute(\stdClass $extensionSpecsRoute): bool
    {
        static $routeElements = [
            'option',
            'view',
            'layout',
            'task'
        ];

        foreach ($routeElements as $element)
        {
            if (isset($extensionSpecsRoute->{$element}) && !$this->app->input->getCmd($element))
            {
                return false;
            }
        }

        return true;
    }
}
