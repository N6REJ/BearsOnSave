<?php
/**
 * @package    bearsonsave
 *
 * @author     Bear <troy@hallhome.us>
 * @copyright  Nov 02 2019
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://hallhome.us
 */

/** @var string $check */

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
	/*
	  Application object

	  @var    JApplicationCms

	  @since  3.8.0
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
	/*	public function onExtensionAfterInstall($eid, $isNew)
		{

			// @Todo run on install ONLY
			if ($isNew)
			{
				$db = JFactory::getDbo();
				$db->setQuery("UPDATE #__extensions SET `enabled` = 1 WHERE `extension_id` = $eid AND `type` = 'plugin'");
				$db->execute();
			}
		}
	*/

	/**
	 * onAfterSave.
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @var string $css
	 * @since   1.0.0
	 */
	public function onExtensionAfterSave($context, $table)
	{
		if ( $context !== 'com_templates.style' || $table->client_id )
		{
			return;
		}

		// @TODO we gotta find out what site template it is and its name/location
		$dataFile = Path::clean(JPATH_SITE . '/templates/' . $table->template . '/' . $this->params->get('paramsFile'));

		if ( !file_exists($dataFile) )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_PARSING_FAILED'), 'danger');

			return;
		}

		// Gather template parameters.
		// $table has all the params so lets fetch it.
		$data = json_decode($table->params);

		// params file should live with template.
		include_once $dataFile;

		// Check for Minimize
		if ( $this->params->get('Minimize') )
		{
			$Minimize = doMinimize($css);
		};

		// export created css file(s).
		$cssIn = $this->doWrite($css, $table);

		$result = $this->doPrepend($table, $css, $cssIn);

		// Exit back to CMS
		return;
	}

	public function doMinimize($css)
	{

		if ( $this->params->get('Minimize') )
		{
			$this->app->enqueueMessage($data, 'warning');
		}

		// If minimize compress params.css into params.min.css
		return;
	}


	public function doWrite($css, $table)
	{
		/* Write css file(s). */

		// What template?
		$cssIn = PATH::clean(JPATH_SITE . '/templates/' . $table->template . '/css/' . $this->params->get('cssIn'));

		// Delete existing bos.css file.
		if ( File::exists($cssIn) )
		{
			File::delete($cssIn);
		}

		// write css file
		if ( file_put_contents($cssIn, $css) === false )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_CSS_FAILED'), 'danger');

			return false;
		}

		return $cssIn;
	}


	public function doBackup($backupCss, $customCss)
	{
		// Since custom.css exists we need to be very careful!
		// backup existing custom.css to '.backup.custom.css' just to CYA
		File::copy($customCss, $backupCss);

		// Is it saved?
		if ( !file_exists($backupCss) )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_BACKUP_FAILED'), 'danger');

			return false;
		}

		return true;
	}

	public function doPrepend($table, $css, $cssIn)
	{
		/* if custom.css exists we need to prepend our @import to the first line.
		* else just create it with our line being first.
		*/

		// Let's make some var's.
		$customCss = Path::clean(JPATH_SITE . '/templates/' . $table->template . '/css/custom.css');
		$backupCss = Path::clean(JPATH_SITE . '/templates/' . $table->template . '/css/.backup.custom.css');
		$import    = '@import "' . '/templates/' . $table->template . '/css/' . $this->params->get('cssIn') . '";';

		if ( file_exists($customCss) === false )
		{
			// No custom.css so lets create one.
			if ( file_put_contents($customCss, $import) === false )
			{
				$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_CUSTOMCSS_FAILED'), 'danger');

				return false;
			}
		}

		// Ok, it exists so time to backup.
		if ( $this->doBackup($backupCss, $customCss) === false )
		{
			return false;
		}

		// get existing custom.css data.
		$data = file_get_contents($customCss);
		if ( $data === false )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_READ_CUSTOMCSS_FAILED'), 'danger');

			return false;
		}

		// Loop through our array, & see if we've already added the @import before.
		$lines = file($customCss);
		foreach ( $lines as $line_num => $line )
		{
			// use '|||' as unique EOL delimiter
			if ( strpos($line, $import) !== false || strpos($line, '|||') !== false )
			{
				// Nothing to be done, it's already there
				return true;
			}
		}
		// ok, lets add the @import. use ' /* BOS @import ||| */' as unique EOL delimiter
		$output = $import . " /* BOS @import ||| */\n" . $data;

		// Now write the new file.
		if ( file_put_contents($customCss, $output) === false )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_CUSTOMCSS_FAILED'), 'danger');

			return false;
		}

		return true;
	}

	/* ============= END OF CLASS ================== */
}
