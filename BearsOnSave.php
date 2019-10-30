<?php
/**
 * @package    bearsonsave
 *
 * @author     Bear <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

/** @var string $check */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\File;
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
		// Show file compressed in plugin dialog so user is aware of it

		// Create the css for the first time
		//$this->onExtensionAfterSave($context, $table, $isNew);
	}

	/**
	 * onAfterSave.
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @var string $css
	 * @since   1.0.0
	 */
	public function onExtensionAfterSave($context, $table, $isNew)
	{
		if ( $context !== 'com_templates.style' || $table->client_id )
		{
			return;
		}
		// @TODO we gotta find out what site template it is and its name/location
		// Gather template parameters.
		$data = $this->DoGetParams($table);

// ========  WORKS UP TO HERE


		// process params just like is currently done.
		// params file should live with template.
		@require_once Path::clean(JPATH_SITE . '/templates/' . $table->template . '/' . $this->params->get('getData'));
		if ( empty($css) )
		{
			Factory::getApplication()->enqueueMessage('PLG_BEARSONSAVE_PARSING_FAILED', 'danger');

			return;
		}

		// Check for DoMinimize
		$this->DoMinimize($this->params->get('DoMinimize'));

		// export created css file(s).
		$file = $this->DoWrite($css, $table);

		$result = $this->DoPrepend($table, $css, $file);

		if ( $result === true )
		{

			// bos.css written successfully
			Factory::getApplication()->enqueueMessage('PLG_BEARSONSAVE_WRITE_OK', 'success');
		}

		// Exit back to CMS
		return;
	}

	public function DoWrite($css, $table)
	{
		/* Write css file(s). */

		// What template?
		$file = Path::clean(JPATH_SITE . '/templates/' . $table->template . '/css/bos.css');

		// Delete existing bos.css file.
		if ( File::exists($file) )
		{
			File::delete($file);
		}

		// write css file
		if ( file_put_contents($file, $css) === false )
		{
			Factory::getApplication()->enqueueMessage('PLG_BEARSONSAVE_WRITE_FAILED', 'danger');

			return false;
		}

		return $file;
	}

	public function DoGetParams($table)
	{
		// $table has all the params so lets fetch it.
		$data = json_decode($table->params);

		return $data;
	}

	public function DoBackup($backupCss, $customCss)
	{
		// Since custom.css exists we need to be very careful!
		// backup existing custom.css to '.backup.custom.css' just to CYA
		File::copy($customCss, $backupCss);

		// Is it saved?
		if ( !file_exists($backupCss) )
		{
			Factory::getApplication()->enqueueMessage('PLG_BEARSONSAVE_WRITE_BACKUP_FAILED', 'danger');

			return false;
		}

		return true;
	}

	public function DoPrepend($table, $css, $file)
	{
		/* if custom.css exists we need to prepend our @import to the first line.
		* else just create it with our line being first.
		*/

		// Let's make some var's.
		$customCss = Path::clean(JPATH_SITE . '/templates/' . $table->template . '/css/custom.css');
		$backupCss = Path::clean(JPATH_SITE . '/templates/' . $table->template . '/css/.backup.custom.css');
		$import    = '@import "' . (Path::clean($this->params->get('filename'))) . '";';

		if ( file_exists($customCss) === false )
		{
			// No custom.css so lets create one.
			if ( file_put_contents($customCss, $import) === false )
			{
				Factory::getApplication()->enqueueMessage('PLG_BEARSONSAVE_WRITE_CUSTOMCSS_FAILED', 'danger');

				return false;
			}
		}

		// Ok, it exists so time to backup.
		if ( $this->DoBackup($backupCss, $customCss) === false )
		{
			return false;
		}

		// get existing custom.css data.
		$data = file_get_contents($customCss);
		if ( $data === false )
		{
			Factory::getApplication()->enqueueMessage('PLG_BEARSONSAVE_READ_CUSTOMCSS_FAILED', 'danger');

			return false;
		}
		if ( strpos($data, $import) !== false )
		{
			// Nothing to be done, it's already there
			return true;
		}

		// ok, lets add the @import.
		$output = $import . "\n" . $data;

		// Now write the new file.
		if ( file_put_contents($customCss, $output) === false )
		{
			Factory::getApplication()->enqueueMessage('PLG_BEARSONSAVE_WRITE_CUSTOMCSS_FAILED', 'danger');

			return false;
		}

		return true;
	}

	public function DoMinimize($check)
	{

		if ( $this->params->get('DoMinimize') )
		{
			Factory::getApplication()->enqueueMessage($data, 'warning');
		}

		// If minimize compress params.css into params.min.css
		return;
	}

	/* ============= END OF CLASS ================== */
}
