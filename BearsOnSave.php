<?php
/**
 * @package    bearsonsave
 *
 * @author     Bear <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

/**
 * bearsonsave plugin.
 *
 * @package   bearsonsave
 * @since     1.0.0
 */
class plgExtensionBearsOnSave extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;


	/**
	 * onAfterInstall.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onExtensionAfterInstall()
	{
		// @Todo run AfterSave so that the params.css file is created
		// Create "DoMinimize" param to find out if user wants files compressed or not.
	}

	/**
	 * onAfterSave.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onExtensionAfterSave()
	{
		// Check for DoMinimize in plugin params.
//var_dump("triggers properly!");
		// Temp message to show its triggering.
		Factory::getApplication()->enqueueMessage('HEY! that tickles!!', 'info');

		// Gather template parameters.

		// process params just like is currently done.

		// export created css into "params.css" file.
		$this->AfterWrite();

		// Exit back to CMS
		return;
	}

	public function AfterWrite()
	{
		// Trap errors.

		return;
	}

	public function DoMinimize()
	{
		// Check for minimize in plugin params.

		// If minimize compress params.css into params.min.css

		// Trap errors.
		return;
	}
}
