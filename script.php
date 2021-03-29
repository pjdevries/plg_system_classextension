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

use Joomla\CMS\Factory;

/**
 * ClassExtension script file.
 *
 * @package   Class Extension System Plugin
 * @since     1.0.0
 */
class plgSystemClassExtensionInstallerScript
{
    /**
     * Constructor
     *
     * @param JAdapterInstance $adapter The object responsible for running this script
     */
    public function __construct(JAdapterInstance $adapter)
    {
    }

    /**
     * Called before any type of action
     *
     * @param string $route Which action is happening (install|uninstall|discover_install|update)
     * @param JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function preflight($route, JAdapterInstance $adapter)
    {
    }

    /**
     * Called after any type of action
     *
     * @param string $route Which action is happening (install|uninstall|discover_install|update)
     * @param JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function postflight($route, JAdapterInstance $adapter)
    {
        // Enable plugin on first installation only.
        if ($route === 'install') {
            $db = Factory::getDbo();
            $query = sprintf('UPDATE %s SET %s = 1 WHERE %s = %s AND %s = %s',
                $db->quoteName('#__extensions'),
                $db->quoteName('enabled'),
                $db->quoteName('type'), $db->quote('plugin'),
                $db->quoteName('name'), $db->quote('PLG_SYSTEM_CLASS_EXTENSION')
            );
            $db->setQuery($query);
            $db->execute();
        }
    }

    /**
     * Called on installation
     *
     * @param JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function install(JAdapterInstance $adapter)
    {
    }

    /**
     * Called on update
     *
     * @param JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function update(JAdapterInstance $adapter)
    {
    }

    /**
     * Called on uninstallation
     *
     * @param JAdapterInstance $adapter The object responsible for running this script
     */
    public function uninstall(JAdapterInstance $adapter)
    {
    }
}
