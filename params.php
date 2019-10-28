<?php
/**
 * @package    bearsonsave
 *
 * @author     Bear <programming@hallhome.us>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://hallhome.us
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

// MANDATORY FUNCTIONS
@include_once 'helper/functions.php';

/*
 * Each template tab should have its own "section" just like it currently is in the framework files.
 * After its all moved over here you can probably delete all framework param files.
 * THIS FILE WILL NOT CREATE THE .CSS, IT'S ONLY TO POPULATE THE PARAMETERS AND CREATE THE $CSS VAR
 * the benefit of doing it this way is the user only changes this file as the template changes so the
 * plugin never has to change!  It's truly template agnostic.
 */

/*
 *  params come in via $params
 *  You'll need to figure out some kind of key=>value pairing in order to
 *  populate the $css variable.
 *  THIS IS THE HARD PART AND THE ENGINE THAT CREATES THE .CSS!!
 *
 *  I think the best way to handle this is going to be to SLOWLY copy the internal behavior in from each file.
 *  break each TAB file down into separate variables and then combine those during the return.
 *  do test write on each TAB so you know that previous section works.
 *  ========== ! ! ! T A K E   Y O U R   T I M E  ! ! ! ==========
 *
 *  This is the only file that should ever be touched for any template and it should match the template_details.xml
 *  field name=>value values.
 *
 *  GOOD LUCK AND HAPPY CODING!! => Bear
 */

// Do we even have any params to work with?
if ( !$data )
{
	try
	{
		Factory::getApplication()->enqueueMessage('WTH ARE THE PARAMS!!!!', 'danger');
	}
	catch ( Exception $e )
	{
		return $e;
	}
	return false;
}

// Cool, lets get to work!

/**
 * ===============
 *  **** LOGO ****
 * ===============
 */
// Logo Params
$json = false;
if ( $data->extendedlogoParams )
{
	$json           = json_decode($data->logoParams, true);
	$filtered_array = group_by_key($json);

	if ( $filtered_array && isset($json['Elements']) )
	{

		// Since we will always only have one row no need to run for each loop
		$logoWidth                = checkPX($filtered_array[0][1]);
		$logoHeight               = checkPX($filtered_array[0][2]);
		$logoMargin               = checkPX($filtered_array[0][3]);
		$logoPadding              = checkPX($filtered_array[0][4]);
		$logohorizontal_alignment = $filtered_array[0][5];
		$logovertical_alignment   = $filtered_array[0][6];

		// SET LOGO image WIDTH AND HEIGHT
		if ( $logoHeight || $logoWidth )
		{
			$css = "header.main-header .logo img{ \n";
			if ( $logoWidth )
			{
				$css .= "	width: " . $logoWidth . ";\n";
				$css .= "	min-width: " . $logoWidth . ";\n";
				$css .= "	max-width: " . $logoWidth . ";\n";
			}
			if ( $logoHeight )
			{
				$css .= "	height: " . $logoHeight . ";\n";
				$css .= "	min-height: " . $logoHeight . ";\n";
				$css .= "	max-height: " . $logoHeight . ";\n";
			}
			if ( $logoMargin )
			{
				$css .= "	margin: " . $logoMargin . ";\n";
			}
			if ( $logoPadding )
			{
				$css .= "	padding: " . $logoPadding . ";\n";
			}
			$css .= "}\n";
		}

		// Allow for positioning of the logo
		$css .= ".logo {\n"
			. "	justify-self: " . $logohorizontal_alignment . ";\n"
			. "	align-self: " . $logovertical_alignment . ";\n"
			. "}\n";

	}
}
/* ----- END LOGO ----- */


/**
 * ==========================
 *  **** TEMPLATE LAYOUT ****
 * ==========================
 */


/**
 * ====================
 *  **** MAIN MENU ****
 * ====================
 */


/**
 * ======================
 *  **** MOBILE MENU ****
 * ======================
 */


/**
 * =================
 *  **** HEADER ****
 * =================
 */


/**
 * =================
 *  **** FOOTER ****
 * =================
 */


/**
 * ==================
 *  ****  FONTS  ****
 * ==================
 */


/**
 * ====================
 *  **** COPYRIGHT ****
 * ====================
 */


/**
 * ========================
 *  **** MISCELLANEOUS ****
 * ========================
 */


/**
 * ======================
 *  **** CUSTOM CODE ****
 * ======================
 */


/**
 * =======================
 *  **** SOCIAL ICONS ****
 * =======================
 */
return $css;
