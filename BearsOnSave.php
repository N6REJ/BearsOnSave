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
		$this->onExtensionAfterSave();
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
		$data  = $this->DoGetParams($table);

		// process params just like is currently done.
		@include_once 'params.php';

		if ( !empty($css) )
		{
			Factory::getApplication()->enqueueMessage($css, 'success');
		}

		// Check for DoMinimize
		$this->DoMinimize($this->params->get('dMinimize'));

		// export created css file(s).
		$this->DoWrite($css, $table);

		// Exit back to CMS
		return;
	}

	public function DoWrite($css, $table)
	{
		/* Write css file(s).
		 *
		 * add the HTMLHelper to init.php.  Be sure debug helper is added!
		 * then write the params.css file
		 */
		// Used with HTMLHelper::
		/*$HTMLHelperDebug = array('version' => 'auto', 'relative' => true, 'detectDebug' => true);
		if ( $paramsCSS )
		{
			HTMLHelper::_('stylesheet', 'params.css', $HTMLHelperDebug);
		}
		*/

		// What template?
		$filename = $this->params->get('filename');
		$file     = Path::clean(JPATH_SITE . '/templates/' . $table->template . '/css/' . $filename);

		/* @TODO delete before save */

		// write css file
		File::write($file, $css, false);

		/* @TODO check for saved file */

		return;
	}

	public function DoGetParams($table)
	{
		// $table has all the params so lets fetch it.
		$data = json_decode($table->params);

		return $data;
	}

	public function AfterWrite($check)
	{
		// Trap errors.

		return;
	}

	public function DoMinimize($check)
	{

		if ( $this->params->get('DoMinimize') )
		{
			Factory::getApplication()->enqueueMessage($params, 'warning');
		}

		// If minimize compress params.css into params.min.css
		return;
	}

	public function ShowSuccess()
	{
		Factory::getApplication()->enqueueMessage('PLG_BEARSONSAVE_WRITE_OK', 'success');

		return;
	}

	public function ShowFailure()
	{
		Factory::getApplication()->enqueueMessage('PLG_BEARSONSAVE_WRITE_FAIL', 'danger');

		return;
	}

	/* ============= END OF CLASS ================== */
}
