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
		$dataFile = Path::clean(JPATH_SITE . $template . $this->params->get('paramsFile'));
		if ( !file_exists($dataFile) )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_PARSING_FAILED'), 'danger');

			return;
		}
		$minify = false;

		// Gather template parameters.
		// $table has all the params so lets fetch it.
		$data = json_decode($table->params);

		// params file should live with template.
		include_once $dataFile;

		if ( empty($css) )
		{
			// Since $css is missing give up.
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_MISSING_CSS'), 'danger');

			return;
		}

		// export created css file(s) - $css comes from include.
		$minify = $this->doWrite($css, $template);

		$result = $this->doPrepend($template, $minify);

		if ( $result === true )
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_OK'), 'message');
		}

		// Exit back to CMS
		return;
	}

	public function doWrite($css, $template)
	{
		$minify = $this->params->get('Minify');

		// What template?
		$cssIn             = $this->params->get('cssIn');
		$cssIn_noExtension = substr($cssIn, 0, strrpos($cssIn, "."));
		$cssExtension      = $minify ? '.min.css' : '.css';
		$cssIn             = Path::clean(JPATH_SITE . $template . '/css/' . $cssIn_noExtension . $cssExtension);

		// Delete existing bos.css file.
		if ( File::exists($cssIn) )
		{
			File::delete($cssIn);
		}

		// Check for Minimize
		if ( $minify == true )
		{
			// If minimize compress bos.css into boss.min.css
			$minifier     = new Minify\CSS($cssIn);
			$minifiedPath = $cssIn;
			$minifier->minify($minifiedPath);
		}
		elseif(file_put_contents($cssIn, $css) === false)
		{
			$this->app->enqueueMessage(JText::_('PLG_BEARSONSAVE_WRITE_CSS_FAILED'), 'danger');

			return false;
		}

		return true;
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

	public function doPrepend($template, $minify)
	{
		/* if custom.css exists we need to prepend our @import to the first line.
		* else just create it with our line being first.
		*/

		// Let's make some var's.
		$customCss         = Path::clean(JPATH_SITE . $template . 'css/custom.css');
		$backupCss         = Path::clean(JPATH_SITE . $template . 'css/.backup.custom.css');
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

		// Ok, it exists so time to backup.
		if ( $this->doBackup($backupCss, $customCss) === false )
		{
			return false;
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
		$data = implode($lines);

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
