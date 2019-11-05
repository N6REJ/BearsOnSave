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

/** @var string $minifier */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\File;
use MatthiasMullie\Minify;

$path = JPATH_PLUGINS . '/extension/bearsonsave/vendor/matthiasmullie';
require_once $path . '/minify/src/Minify.php';
require_once $path . '/minify/src/CSS.php';
require_once $path . '/minify/src/JS.php';
require_once $path . '/minify/src/Exception.php';
require_once $path . '/minify/src/Exceptions/BasicException.php';
require_once $path . '/minify/src/Exceptions/FileImportException.php';
require_once $path . '/minify/src/Exceptions/IOException.php';
require_once $path . '/path-converter/src/ConverterInterface.php';
require_once $path . '/path-converter/src/Converter.php';

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
			$this->app->enqueueMessage(JJText::_('PLG_BEARSONSAVE_PARSING_FAILED'), 'danger');

			return;
		}
		// Gather template parameters.
		// $table has all the params so lets fetch it.
		$data = json_decode($table->params);

		// params file should live with template.
		include_once $dataFile;

		// Check for Minimize
		if ( $this->params->get('Minify') )
		{
			$result = $this->doMinify($table);
		}

		// export created css file(s).
		$cssIn = $this->doWrite($css, $table);

		$result = $this->doPrepend($table, $css, $cssIn);

		if ( $result === true )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_OK'), 'message');
		}

		// Exit back to CMS
		return;
	}

	public function doMinify($table)
	{
		// If minimize compress bos.css into boss.min.css
		$sourcePath   = Path::clean(JPATH_SITE . '/templates/' . $table->template . '/css/' . $this->params->get('cssIn'));
		$minifier     = new Minify\CSS($sourcePath);
		$minifiedPath = substr($sourcePath, 0, strrpos($sourcePath, ".")) . '.min.css';
		$minifier->minify($minifiedPath);

		return true;
	}


	public function doWrite($css, $table)
	{
		/* Write css file(s). */

		// What template?
		$cssIn = '/templates/' . $table->template . '/css/' . $this->params->get('cssIn');

		// Delete existing bos.css file.
		if ( File::exists($cssIn) )
		{
			File::delete($cssIn);
		}

		// write css file
		if ( file_put_contents(Path::clean(JPATH_SITE . $cssIn), $css) === false )
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
		$import    = '@import "' . $cssIn . '";';

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
