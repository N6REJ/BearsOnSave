<?php
/**
 * @package    bearsonsave
 *
 * @author     Bear <troy@hallhome.us>
 * @copyright  Nov 02 2019
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://hallhome.us
 */
defined('_JEXEC') or die;
/** @var string $check */

/** @var string $minifier */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\File;
use MatthiasMullie\Minify;

// Load minify
require_once __DIR__ . '/vendor/autoload.php';



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

// Lets set some basic vars
		$template = '/templates/' . $table->template . '/';

		// @TODO we gotta find out what site template it is and its name/location
		$paramsFile = Path::clean(JPATH_SITE . $template . $this->params->get('paramsFile'));
		if ( !file_exists($paramsFile) )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_PARSING_FAILED'), 'danger');

			return;
		}
		$minify = false;

		// Gather template parameters.
		// $table has all the params so lets fetch it.
		$params = json_decode($table->params);

		// params file should live with template.
		include_once $paramsFile;

		// Get variable used in params file
		$variableName = $this->params->get('variableName');
		// Assign the variable requested to $css
		$css = $$variableName;

		if ( empty($css) )
		{
			// Since $css is missing give up.
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_MISSING_CSS'), 'danger');

			return;
		}

		// export created css file(s) - $css comes from include.
		$result = $this->doWrite($css, $template);

		$result = $this->doPrepend($template);

		if ( $result === true )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_OK'), 'message');
		}

		if ( $this->params->get('Static') )
		{

			$result = $this->doStatic($template, $params);
			if ( $result === true )
			{
				$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_STATIC_OK'), 'message');
			}
		}

		// Exit back to CMS
		return;
	}

	public function doWrite($css, $template)
	{
		$minify = $this->params->get('Minify');

		// What css filename should we use?
		$cssIn             = $this->params->get('cssIn');
		$cssIn_noExtension = substr($cssIn, 0, strrpos($cssIn, "."));
		$rootPath          = Path::clean(JPATH_SITE . $template . '/css/' . $cssIn_noExtension);
		$sourcePath        = $rootPath . '.css';


		// Delete existing bos.css file(s).
		if ( file_exists($sourcePath) )
		{
			File::delete($sourcePath);
		}
		if ( file_exists($rootPath . '.min.css') )
		{
			File::delete($rootPath . '.min.css');
		}

		// Save non-minified .css file
		if ( file_put_contents($sourcePath, $css) === false )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_CSS_FAILED'), 'danger');

			return false;
		}
		// Check for Minimize
		if ( $minify == true )
		{
			// If minimize compress bos.css into boss.min.css
			$minifier     = new Minify\CSS($sourcePath);
			$minifiedPath = $rootPath . '.min.css';
			$minifier->minify($minifiedPath);
			if ( file_exists($minifiedPath) === false )
			{
				$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_MINIFY_CSS_FAILED'), 'danger');
			}

			return false;
		}

		return true;
	}


	public function doBackup($backupCss, $params)
	{
		// Since custom.css exists we need to be very careful!
		// backup existing custom.css to '.backup.custom.css' just to CYA
		if ( file_put_contents($backupCss, $params) === false )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_BACKUP_FAILED'), 'danger');

			return false;
		}

		return true;
	}

	public function doPrepend($template)
	{
		/* if custom.css exists we need to prepend our @import to the first line.
		* else just create it with our line being first.
		*/

		// Let's make some var's.
		$customCss         = Path::clean(JPATH_SITE . $template . 'css/custom.css');
		$backupCss         = Path::clean(JPATH_SITE . $template . 'css/.backup.custom.css');
		$minify            = $this->params->get('Minify');
		$cssIn             = $this->params->get('cssIn');
		$cssIn_noExtension = substr($cssIn, 0, strrpos($cssIn, "."));
		$cssExtension      = $minify ? '.min.css' : '.css';
		$import            = '@import "' . $template . 'css/' . $cssIn_noExtension . $cssExtension . '";';

		if ( file_exists($customCss) === false )
		{
			// No custom.css so lets create one.
			if ( file_put_contents($customCss, $import) === false )
			{
				$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_CUSTOMCSS_FAILED'), 'danger');

				return false;
			}
		}

		// get existing custom.css data.
		$lines = file($customCss);
		if ( $lines === false )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_READ_CUSTOMCSS_FAILED'), 'danger');

			return false;
		}
		// Let's see if we've already added the @import before.
		foreach ( $lines as $line_num => $line )
		{
			// use '|||' as unique EOL delimiter
			if ( strpos($line, $import) !== false || strpos($line, '|||') !== false )
			{
				// It's there so delete it.
				$lines[$line_num] = '';
			}
		}

		// Convert custom.css back into a string like before.
		$params = implode($lines);

		// ok, lets add the @import. use ' /* BOS @import ||| */' as unique EOL delimiter
		$output = $import . " /* BOS @import ||| */\n" . $params;


		// Ok, it exists so time to backup.
		if ( $this->doBackup($backupCss, $params) === false )
		{
			return false;
		}

		// Now write the new file.
		if ( file_put_contents($customCss, $output) === false )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_CUSTOMCSS_FAILED'), 'danger');

			return false;
		}

		return true;
	}

	public function doStatic($template, $params)
	{
		// Set basic variables
		$path    = JPATH_SITE . $template;
		$fileOut = Path::clean($path . $this->params->get('staticOut'));

		// Read vars from input file.
		include_once Path::clean($path . $this->params->get('staticIn'));


		if ( file_put_contents($fileOut, $output) === false )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_STATIC_FAILED'), 'danger');
		}
	}
	/* ============= END OF CLASS ================== */
}
