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
use Joomla\CMS\Filesystem\Path;

/*
 *  ==== How to use @import! ====
 *  to use @import assign the string to the $import var.
 *
        $import = '@import "foo";' . "\n";
 *
 *   at the very end of the file you want to have
 *
		$css .= $import . "\n" . $css;

 * This will automatically add it to the custom.css file
 */
$import = "";

/*
 *  ======== LOAD FUNCTIONS =========
 */
if ( !function_exists('group_by_key') )
{
	function group_by_key($array)
	{
		$result = array();
		/* Safety Trap - Alan */
		if ( !is_array($array) )
		{
			return $result;
		}

		foreach ( $array as $sub )
		{
			foreach ( $sub as $k => $v )
			{
				$result[$k][] = $v;
			}
		}

		return $result;
	}
}

// =========== BEGIN PX PARAMETER VALIDATION FUNCTION ==================
/* Check for whether a size type ( px, rem, etc ) is used or not.
	if not then it add's px assuming thats what they should've put.
	if they did put auto, then it just uses that.
	also forces any letters used to lowercase.
 */
function checkPX($check)
{
	if ( $check !== 'auto' )
	{
		if ( preg_match('/(\-?[0-9]+)\s*(px|em|%|vh|rem|pt|cm|mm|in|pc|ex|ch|vw|vmin|vmax)?/', $check, $match) )
		{
			if ( isset($match[2]) )
			{
				$unit = $match[2];
			}
			else
			{
				$unit = 'px';
			}
			$check = $match[1] . $unit;
		}
	}

	return strtolower($check);
}

// =========== END PX PARAMETER VALIDATION FUNCTION ==================

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

// Set var's
$css .= '';

// Params are in $data now.
// Logo Tab
$logo      = '';
$logoImage = $data->logoImage;
$brandText = $data->brandText;


// Do we even have any params to work with?
if ( !$data )
{

	Factory::getApplication()->enqueueMessage('WTH ARE THE PARAMS!!!!', 'danger');

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
			$css .= "header.main-header .logo img{ \n";
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
 * ====================
 *  **** MAIN MENU ****
 * ====================
 */

// Variables
//$css                  = '';
$banner_columns           = '';
$nav_bg_color             = '';
$nav_Top                  = '';
$nav_Bottom               = '';
$nav_Left                 = '';
$nav_Right                = '';
$nav_Border               = '';
$nav_location             = $data->nav_location ? $data->nav_location : 'navbar-standard';
$nav_wide                 = $data->nav_wide;
$nav_style                = $data->nav_style;
$nav_vertical_alignment   = $data->nav_vertical_alignment;
$nav_horizontal_alignment = $data->nav_horizontal_alignment;
$nav_lineheight           = $data->nav_lineheight;
$icon_caret               = $data->icon_caret ? $data->icon_caret : 'icon-arrow-down-3';

// If navbar-top, then we need to move it like inline does.
if ( $nav_location === 'navbar-fixed-top' || $nav_location === 'navbar-top' )
{
	/*
	* ========== BROKEN!!! =========
	 * WHATS SUPPOSED TO GO HERE?
		 $css .= "nav > {\n"
			. "justify-content: " . $nav_horizontal_alignment . ";\n}\n";
	*/
}
else
{
	if ( $nav_location === 'navbar-standard' )
	{
		$css .= ".navbar-standard nav.navbar{\n"
			. " justify-content: " . $nav_horizontal_alignment . ";\n}\n";

		$css .= ".navbar-standard #nav-toggle{\n"
			. " padding-left: 0;\n"
			. "}";
		$css .= ".navbar-standard #nav-toggle{\n"
			. " padding-right: 0;\n"
			. "}\n";
	}
	else
	{
		/*
* ========== BROKEN!!! =========
 * WHATS SUPPOSED TO GO HERE?
		// Align main menu
		$css .= "nav > {\n"
			. "	justify-content: " . $nav_horizontal_alignment . ";\n"
			. "	align-items: " . $nav_vertical_alignment . ";\n}\n";
	*/
	}
}

if ( $nav_lineheight )
{
	$css .= "ul.nav.navbar > .dropdown > li > a  {\n"
		. " line-height: " . $nav_lineheight . ";\n"
		. "}\n";
}

// Check for extended parameters to be active
if ( $data->extendedmainmenuParams )
{

	/**
	 * ==================================================
	 * Call Main Menu color chooser
	 * ==================================================
	 */
	$chooser        = $data->nav_bg_colorParams;
	$json           = json_decode($chooser, true);
	$filtered_array = group_by_key($json);

	if ( $filtered_array )
	{
// Since we will always only have one row no need to run for each loop
		$nav_bg_color                = $filtered_array[0][0];
		$nav_barColor                = $filtered_array[0][1];
		$nav_activebackgroundColor   = $filtered_array[0][2];
		$nav_backgroundhoverColor    = $filtered_array[0][3];
		$nav_linkbackgroundColor     = $filtered_array[0][4];
		$nav_linkColor               = $filtered_array[0][5];
		$nav_dropdownbackgroundColor = $filtered_array[0][6];
		$nav_dropdownhoverColor      = $filtered_array[0][7];
		$nav_dropdownlinkColor       = $filtered_array[0][8];

		if ( $nav_bg_color )
		{
			$css .= "nav > .navbar {\n"
				. " background: " . $nav_bg_color . ";\n"
				. "}\n";
		}
		if ( $nav_barColor )
		{
			$css .= "nav > .navbar-nav {\n"
				. " background: " . $nav_barColor . ";\n"
				. "}\n";
		}

		if ( $nav_activebackgroundColor )
		{
			$css .= "nav > .navbar-nav > li.active > a,nav > .navbar-nav > li.active > a"
				. "nav > .navbar-nav > li.active.parent > a,"
				. "nav > .navbar-nav > li.active > span.separator,"
				. "nav > .navbar-nav > li.active.parent > span.separator,"
				. "nav > .navbar-nav > li.active.parent > span.nav-header{\n"
				. " background: " . $nav_activebackgroundColor . ";\n"
				. "}\n";

		}

		if ( $nav_backgroundhoverColor )
		{
			$css .= "nav > .navbar-nav > li > a:hover,"
				. "nav > .navbar-nav > li.parent:hover > a,"
				. "nav > .navbar-nav > li > span.separator:hover,"
				. "nav > .navbar-nav > li.parent:hover > span.separator,"
				. "nav > .navbar-nav > li > span.nav-header:hover,"
				. "nav > .nav > li > a:hover,"
				. "nav > .nav > li > a:focus,"
				. "nav > .navbar-nav > li.parent:hover > span.nav-header{\n "
				. " background-color: " . $nav_backgroundhoverColor . ";\n"
				. "}\n";

		}
		if ( $nav_linkbackgroundColor || $nav_linkColor )
		{
			$css .= "nav > ul.navbar-nav li a, nav#mnu ul.navbar-nav li > span.nav-header, nav > ul.navbar-nav li > span.separator {\n ";
			if ( $nav_linkColor )
			{
				$css .= "	color: " . $nav_linkColor . ";\n";
			}
			if ( $nav_linkbackgroundColor )
			{
				$css .= "	background-color: " . $nav_linkbackgroundColor . ";\n";
			}
			$css .= "}\n";

		}
		if ( $nav_dropdownhoverColor )
		{
			$css .= "nav .dropdown-menu > li > a.selected,"
				. "nav .dropdown-menu > li > a:hover,"
				. "nav .dropdown-menu > li > a:focus,"
				. "nav .dropdown-submenu:hover > a,"
				. "nav .dropdown-submenu:focus > a,"
				. "nav li.selected,"
				. "nav.navbar-nav ul.dropdown-menu li a:hover"
				. "{\n"
				. " background-image: none;\n"
				. " background-color: " . $nav_dropdownhoverColor . ";\n"
				. "}\n";
		}

		if ( $nav_dropdownbackgroundColor )
		{
			$css .= "nav > .dropdown {\n"
				. " background-color: " . $nav_dropdownbackgroundColor . ";\n"
				. "}\n";
		}
		if ( $nav_dropdownlinkColor )
		{
			$css .= "nav > ul.navbar-nav .dropdown-menu li a{\n"
				. " color: " . $nav_dropdownlinkColor . ";\n"
				. "}\n";
		}
	}
	/**
	 * ==================================================
	 * Call Main Menu border & Decoration chooser
	 * ==================================================
	 */
	$chooser        = $data->nav_borderParams;
	$json           = json_decode($chooser, true);
	$filtered_array = group_by_key($json);

	if ( $filtered_array )
	{
		$nav_borderPlacement     = $filtered_array[0][0];
		$nav_borderColor         = $filtered_array[0][1];
		$nav_borderStyle         = $filtered_array[0][2];
		$nav_borderSize          = checkPX($filtered_array[0][3]);
		$nav_itemborderPlacement = $filtered_array[0][4];
		$nav_itemborderColor     = $filtered_array[0][5];
		$nav_itemborderStyle     = $filtered_array[0][6];
		$nav_itemborderSize      = checkPX($filtered_array[0][7]);

// Set menu placement values
		if ( $nav_borderPlacement == 'topandbottom' )
		{
			$nav_Top    = 'top';
			$nav_Bottom = 'bottom';
		}
		elseif ( $nav_borderPlacement == 'leftandright' )
		{
			$nav_Left  = 'left';
			$nav_Right = 'right';
		}
		elseif ( $nav_borderPlacement == 'all' )
		{
			$nav_Border = 'all';
		}
		elseif ( $nav_borderPlacement == 'none' )
		{
			$nav_Border = 'none';
		}
		elseif ( $nav_borderPlacement == 'left' )
		{
			$nav_Left = 'left';
		}
		elseif ( $nav_borderPlacement == 'right' )
		{
			$nav_Right = 'right';
		}
		elseif ( $nav_borderPlacement == 'bottom' )
		{
			$nav_Bottom = 'bottom';
		}
		elseif ( $nav_borderPlacement == 'top' )
		{
			$nav_Top = 'top';
		}

		// Set border styles
		if ( $nav_borderSize || $nav_borderPlacement || $nav_borderColor || $nav_borderStyle )
		{
			$css .= "nav > .navbar-nav {\n ";
			// top & bottom
			if ( $nav_Bottom && $nav_Top )
			{
				if ( $nav_borderSize )
				{
					$css .= "	border-" . $nav_Bottom . "-width: " . $nav_borderSize . ";\n";
					$css .= "	border-" . $nav_Top . "-width: " . $nav_borderSize . ";\n";
				}
				if ( $nav_borderStyle )
				{
					$css .= "	border-" . $nav_Bottom . "-style: " . $nav_borderStyle . ";\n";
					$css .= "	border-" . $nav_Top . "-style: " . $nav_borderStyle . ";\n";
				}
				if ( $nav_borderColor )
				{
					$css .= "	border-" . $nav_Bottom . "-color: " . $nav_borderColor . ";\n";
					$css .= "	border-" . $nav_Top . "-color: " . $nav_borderColor . ";\n";
				}
			}
			elseif ( $nav_Left && $nav_Right )
			{
				if ( $nav_borderSize )
				{
					$css .= "	border-" . $nav_Right . "-width: " . $nav_borderSize . ";\n";
					$css .= "	border-" . $nav_Left . "-width: " . $nav_borderSize . ";\n";
				}
				if ( $nav_borderStyle )
				{
					$css .= "	border-" . $nav_Right . "-style: " . $nav_borderStyle . ";\n";
					$css .= "	border-" . $nav_Left . "-style: " . $nav_borderStyle . ";\n";
				}
				if ( $nav_borderColor )
				{
					$css .= "	border-" . $nav_Right . "-color: " . $nav_borderColor . ";\n";
					$css .= "	border-" . $nav_Left . "-color: " . $nav_borderColor . ";\n";
				}
			}
			elseif ( $nav_Left && !$nav_Right )
			{
				if ( $nav_borderSize )
				{
					$css .= "	border-" . $nav_Left . "-width: " . $nav_borderSize . ";\n";
				}
				if ( $nav_borderStyle )
				{
					$css .= "	border-" . $nav_Left . "-style: " . $nav_borderStyle . ";\n";
				}
				if ( $nav_borderColor )
				{
					$css .= "	border-" . $nav_Left . "-color: " . $nav_borderColor . ";\n";
				}
			}
			elseif ( $nav_Right && !$nav_Left )
			{
				if ( $nav_borderSize )
				{
					$css .= "	border-" . $nav_Right . "-width: " . $nav_borderSize . ";\n";
				}
				if ( $nav_borderStyle )
				{
					$css .= "	border-" . $nav_Right . "-style: " . $nav_borderStyle . ";\n";
				}
				if ( $nav_borderColor )
				{
					$css .= "	border-" . $nav_Right . "-color: " . $nav_borderColor . ";\n";
				}
			}
			elseif ( $nav_Top && !$nav_Bottom )
			{
				if ( $nav_borderSize )
				{
					$css .= "	border-" . $nav_Top . "-width: " . $nav_borderSize . ";\n";
				}
				if ( $nav_borderStyle )
				{
					$css .= "	border-" . $nav_Top . "-style: " . $nav_borderStyle . ";\n";
				}
				if ( $nav_borderColor )
				{
					$css .= "	border-" . $nav_Top . "-color: " . $nav_borderColor . ";\n";
				}
			}
			elseif ( $nav_Bottom && !$nav_Top )
			{
				if ( $nav_borderSize )
				{
					$css .= "	border-" . $nav_Bottom . "-width: " . $nav_borderSize . ";\n";
				}
				if ( $nav_borderStyle )
				{
					$css .= "	border-" . $nav_Bottom . "-style: " . $nav_borderStyle . ";\n";
				}
				if ( $nav_borderColor )
				{
					$css .= "	border-" . $nav_Bottom . "-color: " . $nav_borderColor . ";\n";
				}
			}
			elseif ( $nav_Border = 'all' )
			{
				$css .= "nav > .navbar-nav {\n ";

				if ( $nav_borderSize )
				{
					$css .= "	border-width: " . $nav_borderSize . ";\n";
				}
				if ( $nav_borderStyle )
				{
					$css .= "	border-style: " . $nav_borderStyle . ";\n";
				}
				if ( $nav_borderColor )
				{
					$css .= "	border-color: " . $nav_borderColor . ";\n";
				}
			}
			elseif ( $nav_Border = 'none' )
			{
				$css .= "	border:none;\n";
			}
			$css .= "}\n";

		}

// Item border
		$nav_itemTop    = '';
		$nav_itemBottom = '';
		$nav_itemLeft   = '';
		$nav_itemRight  = '';
		$nav_itemBorder = '';

		if ( $nav_itemborderPlacement == 'topandbottom' )
		{
			$nav_itemTop    = 'top';
			$nav_itemBottom = 'bottom';
		}
		elseif ( $nav_itemborderPlacement == 'leftandright' )
		{
			$nav_itemLeft  = 'left';
			$nav_itemRight = 'right';
		}
		elseif ( $nav_itemborderPlacement == 'all' )
		{
			$nav_itemBorder = 'all';
		}
		elseif ( $nav_itemborderPlacement == 'none' )
		{
			$nav_itemBorder = 'none';
		}
		elseif ( $nav_itemborderPlacement == 'left' )
		{
			$nav_itemLeft = 'left';
		}
		elseif ( $nav_itemborderPlacement == 'right' )
		{
			$nav_itemRight = 'right';
		}
		elseif ( $nav_itemborderPlacement == 'bottom' )
		{
			$nav_itemBottom = 'bottom';
		}
		elseif ( $nav_itemborderPlacement == 'top' )
		{
			$nav_itemTop = 'top';
		}

		if ( $nav_itemborderSize || $nav_itemborderPlacement || $nav_itemborderColor || $nav_itemborderStyle )
		{
			$css .= "nav > .navbar-nav li {\n ";
			// top & bottom
			if ( $nav_itemBottom && $nav_itemTop )
			{
				if ( $nav_itemborderSize )
				{
					$css .= "	border-" . $nav_itemBottom . "-width: " . $nav_itemborderSize . ";\n";
					$css .= "	border-" . $nav_itemTop . "-width: " . $nav_itemborderSize . ";\n";
				}
				if ( $nav_itemborderStyle )
				{
					$css .= "	border-" . $nav_itemBottom . "-style: " . $nav_itemborderStyle . ";\n";
					$css .= "	border-" . $nav_itemTop . "-style: " . $nav_itemborderStyle . ";\n";
				}
				if ( $nav_itemborderColor )
				{
					$css .= "	border-" . $nav_itemBottom . "-color: " . $nav_itemborderColor . ";\n";
					$css .= "	border-" . $nav_itemTop . "-color: " . $nav_itemborderColor . ";\n";
				}
			}
			elseif ( $nav_itemLeft && $nav_itemRight )
			{
				if ( $nav_itemborderSize )
				{
					$css .= "	border-" . $nav_itemRight . "-width: " . $nav_itemborderSize . ";\n";
					$css .= "	border-" . $nav_itemLeft . "-width: " . $nav_itemborderSize . ";\n";
				}
				if ( $nav_itemborderStyle )
				{
					$css .= "	border-" . $nav_itemRight . "-style: " . $nav_itemborderStyle . ";\n";
					$css .= "	border-" . $nav_itemLeft . "-style: " . $nav_itemborderStyle . ";\n";
				}
				if ( $nav_itemborderColor )
				{
					$css .= "	border-" . $nav_itemRight . "-color: " . $nav_itemborderColor . ";\n";
					$css .= "	border-" . $nav_itemLeft . "-color: " . $nav_itemborderColor . ";\n";
				}
			}
			elseif ( $nav_itemLeft && !$nav_itemRight )
			{
				if ( $nav_itemborderSize )
				{
					$css .= "	border-" . $nav_itemLeft . "-width: " . $nav_itemborderSize . ";\n";
				}
				if ( $nav_itemborderStyle )
				{
					$css .= "	border-" . $nav_itemLeft . "-style: " . $nav_itemborderStyle . ";\n";
				}
				if ( $nav_itemborderColor )
				{
					$css .= "	border-" . $nav_itemLeft . "-color: " . $nav_itemborderColor . ";\n";
				}
			}
			elseif ( $nav_itemRight && !$nav_itemLeft )
			{
				if ( $nav_itemborderSize )
				{
					$css .= "	border-" . $nav_itemRight . "-width: " . $nav_itemborderSize . ";\n";
				}
				if ( $nav_itemborderStyle )
				{
					$css .= "	border-" . $nav_itemRight . "-style: " . $nav_itemborderStyle . ";\n";
				}
				if ( $nav_itemborderColor )
				{
					$css .= "	border-" . $nav_itemRight . "-color: " . $nav_itemborderColor . ";\n";
				}
			}
			elseif ( $nav_itemTop && !$nav_itemBottom )
			{
				if ( $nav_itemborderSize )
				{
					$css .= "	border-" . $nav_itemTop . "-width: " . $nav_itemborderSize . ";\n";
				}
				if ( $nav_itemborderStyle )
				{
					$css .= "	border-" . $nav_itemTop . "-style: " . $nav_itemborderStyle . ";\n";
				}
				if ( $nav_itemborderColor )
				{
					$css .= "	border-" . $nav_itemTop . "-color: " . $nav_itemborderColor . ";\n";
				}
			}
			elseif ( $nav_itemBottom && !$nav_itemTop )
			{
				if ( $nav_itemborderSize )
				{
					$css .= "	border-" . $nav_itemBottom . "-width: " . $nav_itemborderSize . ";\n";
				}
				if ( $nav_itemborderStyle )
				{
					$css .= "	border-" . $nav_itemBottom . "-style: " . $nav_itemborderStyle . ";\n";
				}
				if ( $nav_itemborderColor )
				{
					$css .= "	border-" . $nav_itemBottom . "-color: " . $nav_itemborderColor . ";\n";
				}
			}
			elseif ( $nav_itemBorder = 'all' )
			{
				if ( $nav_itemborderSize )
				{
					$css .= "	border-width: " . $nav_itemborderSize . ";\n";
				}
				if ( $nav_itemborderStyle )
				{
					$css .= "	border-style: " . $nav_itemborderStyle . ";\n";
				}
				if ( $nav_itemborderColor )
				{
					$css .= "	border-color: " . $nav_itemborderColor . ";\n";
				}
			}
			elseif ( $nav_itemBorder = 'none' )
			{
				$css .= "	border: none;\n";
			}
			$css .= "}\n";

		}
	}
}


/**
 * ======================
 *  **** MOBILE MENU ****
 * ======================
 */
$mmenuhamburgerBackground = $data->mmenuhamburgerBackground;

// Set caret rotation for mobile menu
$css .= "ul.mm-list > .dropdown i." . $icon_caret . ",\n"
	. "ul.mm-list > .dropdown span." . $icon_caret . ",\n"
	. "ul.mm-list > .dropdown span.caret,\n"
	. "ul.mm-list > .dropdown i.caret{\n"
	. "	transform: rotate(-90deg);\n
	}\n";

/* Set hamburger background color if set in params */
if ( $mmenuhamburgerBackground )
{
	$css .= ".navbar-toggle .icon-bar {\n"
		. " background-color: " . $mmenuhamburgerBackground . ";\n"
		. "}\n";
}

/**
 * =================
 *  **** HEADER ****
 * =================
 */

/*
 * ==================================================
 * Call Header chooser
 * ==================================================
 */
$chooser        = $data->headerParams;
$json           = json_decode($chooser, true);
$filtered_array = group_by_key($json);

$headerbackgroundColor = $filtered_array[0][0];
$headerColor           = $filtered_array[0][1];
$headerlinkColor       = $filtered_array[0][2];
$headerhoverColor      = $filtered_array[0][3];
$headerMargin          = checkPX($filtered_array[0][4]);
$headerPadding         = checkPX($filtered_array[0][5]);
$headerborderPlacement = $filtered_array[0][6];
$headerborderColor     = $filtered_array[0][7];
$headerborderStyle     = $filtered_array[0][8];
$headerborderSize      = checkPX($filtered_array[0][9]);
/*  ----- END HEADER CHOOSER ----- */

$headerTop    = '';
$headerBottom = '';
$headerLeft   = '';
$headerRight  = '';
$headerBorder = '';

if ( !$headerborderPlacement == '' )
{

	if ( $headerborderPlacement == 'none' )
	{
		$headerBorder = 'none';
	}
	elseif ( $headerborderPlacement == 'topandbottom' )
	{
		$headerTop    = 'top';
		$headerBottom = 'bottom';
	}
	elseif ( $headerborderPlacement == 'leftandright' )
	{
		$headerLeft  = 'left';
		$headerRight = 'right';
	}
	elseif ( $headerborderPlacement == 'all' )
	{
		$headerBorder = 'all';
	}
	elseif ( $headerborderPlacement == 'left' )
	{
		$headerLeft = 'left';
	}
	elseif ( $headerborderPlacement == 'right' )
	{
		$headerRight = 'right';
	}
	elseif ( $headerborderPlacement == 'bottom' )
	{
		$headerBottom = 'bottom';
	}
	elseif ( $headerborderPlacement == 'top' )
	{
		$headerTop = 'top';
	}
}

if ( $headerbackgroundColor || $headerColor || $headerMargin || $headerPadding || $headerborderPlacement || $headerborderColor || $headerborderStyle || $headerborderSize )
{

	$css .= "header.main-header {\n";

	// None
	if ( $headerBorder == 'none' )
	{
		$css .= "	border: none;\n";

// top & bottom
	}
	elseif ( $headerBottom && $headerTop )
	{
		if ( $headerborderSize )
		{
			$css .= "	border-" . $headerBottom . "-width: " . $headerborderSize . ";\n";
			$css .= "	border-" . $headerTop . "-width: " . $headerborderSize . ";\n";
		}
		if ( $headerborderStyle )
		{
			$css .= "	border-" . $headerBottom . "-style: " . $headerborderStyle . ";\n";
			$css .= "	border-" . $headerTop . "-style: " . $headerborderStyle . ";\n";
		}
		if ( $headerborderColor )
		{
			$css .= "	border-" . $headerBottom . "-color: " . $headerborderColor . ";\n";
			$css .= "	border-" . $headerTop . "-color: " . $headerborderColor . ";\n";
		}
	}
	elseif ( $headerLeft && $headerRight )
	{

		if ( $headerborderSize )
		{
			$css .= "	border-" . $headerRight . "-width: " . $headerborderSize . ";\n";
			$css .= "	border-" . $headerLeft . "-width: " . $headerborderSize . ";\n";
		}
		if ( $headerborderStyle )
		{
			$css .= "	border-" . $headerRight . "-style: " . $headerborderStyle . ";\n";
			$css .= "	border-" . $headerLeft . "-style: " . $headerborderStyle . ";\n";
		}
		if ( $headerborderColor )
		{
			$css .= "	border-" . $headerRight . "-color: " . $headerborderColor . ";\n";
			$css .= "	border-" . $headerLeft . "-color: " . $headerborderColor . ";\n";
		}
	}
	elseif ( $headerLeft && !$headerRight )
	{
		if ( $headerborderSize )
		{
			$css .= "	border-" . $headerLeft . "-width: " . $headerborderSize . ";\n";
		}
		if ( $headerborderStyle )
		{
			$css .= "	border-" . $headerLeft . "-style: " . $headerborderStyle . ";\n";
		}
		if ( $headerborderColor )
		{
			$css .= "	border-" . $headerLeft . "-color: " . $headerborderColor . ";\n";
		}
	}
	elseif ( $headerRight && !$headerLeft )
	{
		if ( $headerborderSize )
		{
			$css .= "	border-" . $headerRight . "-width: " . $headerborderSize . ";\n";
		}
		if ( $headerborderStyle )
		{
			$css .= "	border-" . $headerRight . "-style: " . $headerborderStyle . ";\n";
		}
		if ( $headerborderColor )
		{
			$css .= "	border-" . $headerRight . "-color: " . $headerborderColor . ";\n";
		}
	}
	elseif ( $headerTop && !$headerBottom )
	{
		if ( $headerborderSize )
		{
			$css .= "	border-" . $headerTop . "-width: " . $headerborderSize . ";\n";
		}
		if ( $headerborderStyle )
		{
			$css .= "	border-" . $headerTop . "-style: " . $headerborderStyle . ";\n";
		}
		if ( $headerborderColor )
		{
			$css .= "	border-" . $headerTop . "-color: " . $headerborderColor . ";\n";
		}
	}
	elseif ( $headerBottom && !$headerTop )
	{
		if ( $headerborderSize )
		{
			$css .= "	border-" . $headerBottom . "-width: " . $headerborderSize . ";\n";
		}
		if ( $headerborderStyle )
		{
			$css .= "	border-" . $headerBottom . "-style: " . $headerborderStyle . ";\n";
		}
		if ( $headerborderColor )
		{
			$css .= "	border-" . $headerBottom . "-color: " . $headerborderColor . ";\n";
		}
	}
	elseif ( $headerBorder = 'all' )
	{
		if ( $headerborderSize )
		{
			$css .= "	border-width: " . $headerborderSize . ";\n";
		}
		if ( $headerborderStyle )
		{
			$css .= "	border-style: " . $headerborderStyle . ";\n";
		}
		if ( $headerborderColor )
		{
			$css .= "	border-color: " . $headerborderColor . ";\n";
		}
	}

	/* ---- BEGIN HEADER COLORING----  */
	if ( $headerColor )
	{
		$css .= "	color: " . $headerColor . ";\n";
	}

// Header MARGIN AND PADDING
	if ( $headerMargin || $headerPadding )
	{

		if ( $headerMargin )
		{
			$css .= "	margin: " . $headerMargin . ";\n";
		}
		if ( $headerPadding )
		{
			$css .= "	padding: " . $headerPadding . ";\n";
		}
	}
	$css .= "}\n";
}

if ( $headerlinkColor )
{
	$css .= "header.main-header a { color: " . $headerlinkColor . ";}\n";
}
if ( $headerhoverColor )
{
	$css .= "header.main-header a:hover { color: " . $headerhoverColor . ";}\n";
}
if ( $headerbackgroundColor )
{
	$css .= "header.main-header {	background-color: " . $headerbackgroundColor . ";}\n";
}
/* ---- END HEADER  ---- */


/**
 * =================
 *  **** FOOTER ****
 * =================
 */

// Set variables

/*
 * ==================================================
 * Call Footer chooser
 * ==================================================
 */
if ( $data->extendedfooterParams )
{
	$chooser        = $data->footerParams;
	$json           = json_decode($chooser, true);
	$filtered_array = group_by_key($json);

	$footerbackgroundColor = $filtered_array[0][0];
	$footerColor           = $filtered_array[0][1];
	$footerlinkColor       = $filtered_array[0][2];
	$footerhoverColor      = $filtered_array[0][3];
	$footerMargin          = checkPX($filtered_array[0][4]);
	$footerPadding         = checkPX($filtered_array[0][5]);
	$footerborderPlacement = $filtered_array[0][6];
	$footerborderColor     = $filtered_array[0][7];
	$footerborderStyle     = $filtered_array[0][8];
	$footerborderSize      = checkPX($filtered_array[0][9]);
	/*  ----- END FOOTER CHOOSER ----- */

	$footerTop    = '';
	$footerBottom = '';
	$footerLeft   = '';
	$footerRight  = '';
	$footerBorder = '';

	if ( !$footerborderPlacement == '' )
	{

		if ( $footerborderPlacement == 'none' )
		{
			$footerBorder = 'none';
		}
		elseif ( $footerborderPlacement == 'topandbottom' )
		{
			$footerTop    = 'top';
			$footerBottom = 'bottom';
		}
		elseif ( $footerborderPlacement == 'leftandright' )
		{
			$footerLeft  = 'left';
			$footerRight = 'right';
		}
		elseif ( $footerborderPlacement == 'all' )
		{
			$footerBorder = 'all';
		}
		elseif ( $footerborderPlacement == 'left' )
		{
			$footerLeft = 'left';
		}
		elseif ( $footerborderPlacement == 'right' )
		{
			$footerRight = 'right';
		}
		elseif ( $footerborderPlacement == 'bottom' )
		{
			$footerBottom = 'bottom';
		}
		elseif ( $footerborderPlacement == 'top' )
		{
			$footerTop = 'top';
		}
	}

	if ( $footerborderSize == 'none' || $footerborderSize == '0px' )
	{
		$css .= "footer.footer #footer .container {border:none;}";
	}

	if ( $footerColor || $footerMargin || $footerPadding || $footerborderPlacement || $footerborderColor || $footerborderStyle || $footerborderSize )
	{

		$css .= "footer.footer #footer .container {\n";

// top & bottom
		if ( $footerBottom && $footerTop )
		{
			if ( $footerborderSize )
			{
				$css .= "	border-" . $footerBottom . "-width: " . $footerborderSize . ";\n";
				$css .= "	border-" . $footerTop . "-width: " . $footerborderSize . ";\n";
			}
			if ( $footerborderStyle )
			{
				$css .= "	border-" . $footerBottom . "-style: " . $footerborderStyle . ";\n";
				$css .= "	border-" . $footerTop . "-style: " . $footerborderStyle . ";\n";
			}
			if ( $footerborderColor )
			{
				$css .= "	border-" . $footerBottom . "-color: " . $footerborderColor . ";\n";
				$css .= "	border-" . $footerTop . "-color: " . $footerborderColor . ";\n";
			}
		}
		elseif ( $footerLeft && $footerRight )
		{

			if ( $footerborderSize )
			{
				$css .= "	border-" . $footerRight . "-width: " . $footerborderSize . ";\n";
				$css .= "	border-" . $footerLeft . "-width: " . $footerborderSize . ";\n";
			}
			if ( $footerborderStyle )
			{
				$css .= "	border-" . $footerRight . "-style: " . $footerborderStyle . ";\n";
				$css .= "	border-" . $footerLeft . "-style: " . $footerborderStyle . ";\n";
			}
			if ( $footerborderColor )
			{
				$css .= "	border-" . $footerRight . "-color: " . $footerborderColor . ";\n";
				$css .= "	border-" . $footerLeft . "-color: " . $footerborderColor . ";\n";
			}
		}
		elseif ( $footerLeft && !$footerRight )
		{
			if ( $footerborderSize )
			{
				$css .= "	border-" . $footerLeft . "-width: " . $footerborderSize . ";\n";
			}
			if ( $footerborderStyle )
			{
				$css .= "	border-" . $footerLeft . "-style: " . $footerborderStyle . ";\n";
			}
			if ( $footerborderColor )
			{
				$css .= "	border-" . $footerLeft . "-color: " . $footerborderColor . ";\n";
			}
		}
		elseif ( $footerRight && !$footerLeft )
		{
			if ( $footerborderSize )
			{
				$css .= "	border-" . $footerRight . "-width: " . $footerborderSize . ";\n";
			}
			if ( $footerborderStyle )
			{
				$css .= "	border-" . $footerRight . "-style: " . $footerborderStyle . ";\n";
			}
			if ( $footerborderColor )
			{
				$css .= "	border-" . $footerRight . "-color: " . $footerborderColor . ";\n";
			}
		}
		elseif ( $footerTop && !$footerBottom )
		{
			if ( $footerborderSize )
			{
				$css .= "	border-" . $footerTop . "-width: " . $footerborderSize . ";\n";
			}
			if ( $footerborderStyle )
			{
				$css .= "	border-" . $footerTop . "-style: " . $footerborderStyle . ";\n";
			}
			if ( $footerborderColor )
			{
				$css .= "	border-" . $footerTop . "-color: " . $footerborderColor . ";\n";
			}
		}
		elseif ( $footerBottom && !$footerTop )
		{
			if ( $footerborderSize )
			{
				$css .= "	border-" . $footerBottom . "-width: " . $footerborderSize . ";\n";
			}
			if ( $footerborderStyle )
			{
				$css .= "	border-" . $footerBottom . "-style: " . $footerborderStyle . ";\n";
			}
			if ( $footerborderColor )
			{
				$css .= "	border-" . $footerBottom . "-color: " . $footerborderColor . ";\n";
			}
		}
		elseif ( $footerBorder = 'all' )
		{
			if ( $footerborderSize )
			{
				$css .= "	border-width: " . $footerborderSize . ";\n";
			}
			if ( $footerborderStyle )
			{
				$css .= "	border-style: " . $footerborderStyle . ";\n";
			}
			if ( $footerborderColor )
			{
				$css .= "	border-color: " . $footerborderColor . ";\n";
			}
		}

		/* ---- BEGIN FOOTER COLORING----  */
		if ( $footerColor )
		{
			$css .= "	color: " . $footerColor . ";\n";
		}

		$css .= "}";
	}

	if ( $footerbackgroundColor )
	{
		$css .= "footer.footer #footer {\n"
			. " background-color: " . $footerbackgroundColor . ";\n"
			. "}\n";
	}
	if ( $footerlinkColor )
	{
		$css .= "footer.footer #footer a {\n"
			. " color: " . $footerlinkColor . ";\n"
			. "}\n";
	}
	if ( $footerhoverColor )
	{
		$css .= "footer.footer #footer a:hover {\n"
			. " color: " . $footerhoverColor . ";\n"
			. "}\n";
	}
// Footer MARGIN AND PADDING
	if ( $footerMargin || $footerPadding )
	{
		$css .= "footer.footer #footer .module{\n";
		if ( $footerMargin )
		{
			$css .= "	margin: " . $footerMargin . ";\n";
		}
		if ( $footerPadding )
		{
			$css .= "	padding: " . $footerPadding . ";\n";
		}
		$css .= "}\n";
	}
	/* ---- END FOOTER  ---- */


	/*
	* ===== BEGIN FOOTER WIDE =====
	*/

	// Call Footer chooser
	$chooser        = $data->footerwideParams;
	$json           = json_decode($chooser, true);
	$filtered_array = group_by_key($json);

	$footerwidebackgroundColor = $filtered_array[0][0];
	$footerwideColor           = $filtered_array[0][1];
	$footerwidelinkColor       = $filtered_array[0][2];
	$footerwidehoverColor      = $filtered_array[0][3];
	$footerwidefontSize        = $filtered_array[0][4];
	$footerwideMargin          = checkPX($filtered_array[0][5]);
	$footerwidePadding         = checkPX($filtered_array[0][6]);
	$footerwideborderPlacement = $filtered_array[0][7];
	$footerwideborderColor     = $filtered_array[0][8];
	$footerwideborderStyle     = $filtered_array[0][9];
	$footerwideborderSize      = checkPX($filtered_array[0][10]);
	/*  ----- END FOOTER CHOOSER ----- */


	$footerwideTop    = '';
	$footerwideBottom = '';
	$footerwideLeft   = '';
	$footerwideRight  = '';
	$footerwideBorder = '';

	if ( !$footerwideborderPlacement == '' )
	{

		if ( $footerwideborderPlacement == 'none' )
		{
			$footerwideBorder = 'none';
		}
		elseif ( $footerwideborderPlacement == 'topandbottom' )
		{
			$footerwideTop    = 'top';
			$footerwideBottom = 'bottom';
		}
		elseif ( $footerwideborderPlacement == 'leftandright' )
		{
			$footerwideLeft  = 'left';
			$footerwideRight = 'right';
		}
		elseif ( $footerwideborderPlacement == 'all' )
		{
			$footerwideBorder = 'all';
		}
		elseif ( $footerwideborderPlacement == 'left' )
		{
			$footerwideLeft = 'left';
		}
		elseif ( $footerwideborderPlacement == 'right' )
		{
			$footerwideRight = 'right';
		}
		elseif ( $footerwideborderPlacement == 'bottom' )
		{
			$footerwideBottom = 'bottom';
		}
		elseif ( $footerwideborderPlacement == 'top' )
		{
			$footerwideTop = 'top';
		}
	}

	if ( $footerwideborderSize == 'none' || $footerwideborderSize == '0px' )
	{
		$css .= "footer.footer > #footer-wide > .footer-wide{\n"
			. " border: none;\n"
			. "}\n";
	}
	if ( $footerwideColor || $footerwideMargin || $footerwidePadding || $footerwideborderPlacement || $footerwideborderColor || $footerwideborderStyle || $footerwideborderSize )
	{

		$css .= "footer.footer > #footer-wide > .footer-wide{\n";

// top & bottom
		if ( $footerwideBottom && $footerwideTop )
		{
			if ( $footerwideborderSize )
			{
				$css .= "	border-" . $footerwideBottom . "-width: " . $footerwideborderSize . ";\n";
				$css .= "	border-" . $footerwideTop . "-width: " . $footerwideborderSize . ";\n";
			}
			if ( $footerwideborderStyle )
			{
				$css .= "	border-" . $footerwideBottom . "-style: " . $footerwideborderStyle . ";\n";
				$css .= "	border-" . $footerwideTop . "-style: " . $footerwideborderStyle . ";\n";
			}
			if ( $footerwideborderColor )
			{
				$css .= "	border-" . $footerwideBottom . "-color: " . $footerwideborderColor . ";\n";
				$css .= "	border-" . $footerwideTop . "-color: " . $footerwideborderColor . ";\n";
			}
		}
		elseif ( $footerwideLeft && $footerwideRight )
		{

			if ( $footerwideborderSize )
			{
				$css .= "	border-" . $footerwideRight . "-width: " . $footerwideborderSize . ";\n";
				$css .= "	border-" . $footerwideLeft . "-width: " . $footerwideborderSize . ";\n";
			}
			if ( $footerwideborderStyle )
			{
				$css .= "	border-" . $footerwideRight . "-style: " . $footerwideborderStyle . ";\n";
				$css .= "	border-" . $footerwideLeft . "-style: " . $footerwideborderStyle . ";\n";
			}
			if ( $footerwideborderColor )
			{
				$css .= "	border-" . $footerwideRight . "-color: " . $footerwideborderColor . ";\n";
				$css .= "	border-" . $footerwideLeft . "-color: " . $footerwideborderColor . ";\n";
			}
		}
		elseif ( $footerwideLeft && !$footerwideRight )
		{
			if ( $footerwideborderSize )
			{
				$css .= "	border-" . $footerwideLeft . "-width: " . $footerwideborderSize . ";\n";
			}
			if ( $footerwideborderStyle )
			{
				$css .= "	border-" . $footerwideLeft . "-style: " . $footerwideborderStyle . ";\n";
			}
			if ( $footerwideborderColor )
			{
				$css .= "	border-" . $footerwideLeft . "-color: " . $footerwideborderColor . ";\n";
			}
		}
		elseif ( $footerwideRight && !$footerwideLeft )
		{
			if ( $footerwideborderSize )
			{
				$css .= "	border-" . $footerwideRight . "-width: " . $footerwideborderSize . ";\n";
			}
			if ( $footerwideborderStyle )
			{
				$css .= "	border-" . $footerwideRight . "-style: " . $footerwideborderStyle . ";\n";
			}
			if ( $footerwideborderColor )
			{
				$css .= "	border-" . $footerwideRight . "-color: " . $footerwideborderColor . ";\n";
			}
		}
		elseif ( $footerwideTop && !$footerwideBottom )
		{
			if ( $footerwideborderSize )
			{
				$css .= "	border-" . $footerwideTop . "-width: " . $footerwideborderSize . ";\n";
			}
			if ( $footerwideborderStyle )
			{
				$css .= "	border-" . $footerwideTop . "-style: " . $footerwideborderStyle . ";\n";
			}
			if ( $footerwideborderColor )
			{
				$css .= "	border-" . $footerwideTop . "-color: " . $footerwideborderColor . ";\n";
			}
		}
		elseif ( $footerwideBottom && !$footerwideTop )
		{
			if ( $footerwideborderSize )
			{
				$css .= "	border-" . $footerwideBottom . "-width: " . $footerwideborderSize . ";\n";
			}
			if ( $footerwideborderStyle )
			{
				$css .= "	border-" . $footerwideBottom . "-style: " . $footerwideborderStyle . ";\n";
			}
			if ( $footerwideborderColor )
			{
				$css .= "	border-" . $footerwideBottom . "-color: " . $footerwideborderColor . ";\n";
			}
		}
		elseif ( $footerwideBorder = 'all' )
		{
			if ( $footerwideborderSize )
			{
				$css .= "	border-width: " . $footerwideborderSize . ";\n";
			}
			if ( $footerwideborderStyle )
			{
				$css .= "	border-style: " . $footerwideborderStyle . ";\n";
			}
			if ( $footerwideborderColor )
			{
				$css .= "	border-color: " . $footerwideborderColor . ";\n";
			}
		}

		if ( $footerwideColor )
		{
			$css .= "	color: " . $footerwideColor . ";\n";
		}
		$css .= "}\n";

	}
	if ( $footerwidebackgroundColor )
	{
		$css .= "footer.footer > #footer-wide {\n"
			. " background-color: " . $footerwidebackgroundColor . ";\n"
			. "}\n";
	}
	if ( $footerwidelinkColor )
	{
		$css .= "footer.footer > #footer-wide  a {\n"
			. " color: " . $footerwidelinkColor . ";\n"
			. "}\n";
	}
	if ( $footerwidehoverColor )
	{
		$css .= "footer.footer > #footer-wide  a:hover {\n"
			. " color: " . $footerwidehoverColor . ";\n"
			. "}\n";
	}
// Footer MARGIN AND PADDING
	if ( $footerwideMargin || $footerwidePadding )
	{
		$css .= "footer.footer #footer-wide .footer-wide .module{\n";
		if ( $footerwideMargin )
		{
			$css .= "	margin: " . $footerwideMargin . ";\n";
		}
		if ( $footerwidePadding )
		{
			$css .= "	padding: " . $footerwidePadding . ";\n";
		}
		$css .= "}\n";
	}

}
/* ---- END FOOTER  ---- */

/**
 * ==================
 *  ****  FONTS  ****
 * ==================
 */

/*
 * ==================================================
 * Call Typography elements & Google fonts
 * ==================================================
 */

// Is Google Fonts active?
if ( $data->googleFonts )
{

// Trim @import url( from beginning and ); from end leaving only url
	$googlefont = trim($data->fontURL);
	$fontfamily = trim($data->fontFamily);

// Throw error if fonts url or fonts css are empty
	if ( !$googlefont || !$fontfamily )
	{
		//output error
		Factory::getApplication()->enqueueMessage('<h2>If you want to use Google Fonts you have to add the GF code.</h2>', 'danger');

		return false;
	}

	// Throw error if fonts url is empty or wrong format
	if ( substr($googlefont, 0, 12) <> "@import url(" )
	{
		//output error
		Factory::getApplication()->enqueueMessage('<h2>GF URL Code MUST begin with <i>@import</i>.</h2>', 'danger');

		return false;
	}
	// Throw error if fonts css is empty or wrong format
	if ( substr($fontfamily, 0, 12) <> "font-family:" )
	{
		//output error
		Factory::getApplication()->enqueueMessage('<h2>GF CSS Code MUST begin with <i>font-family</i>.</h2>', 'danger');

		return false;
	}

	// Create stylesheet link
	$import = $googlefont . "\n";

// Get the Google font choices
// Make sure we dont' have empty row
	$chooser        = $data->googlefontsChooser;
	$json           = json_decode($chooser, true);
	$filtered_array = group_by_key($json);

	foreach ( $filtered_array as $index => $value )
	{
		$html                 = $value[0];
		$function             = $value[1];
		$font                 = $value[2];
		$size                 = checkPX($value[3]);
		$weight               = $value[4];
		$style                = $value[5];
		$color                = $value[6];
		$backgroundColor      = $value[7];
		$backgroundhoverColor = $value[8];
		$hoverColor           = $value[9];
		$linkColor            = $value[10];
		$hoverlinkColor       = $value[11];
		$fontstring           = '';


		/* check for duplicate links within elements */
		if ( $hoverColor && $hoverlinkColor && $html == "body" )
		{
			Factory::getApplication()->enqueueMessage(JText::_('TPL_ACORN_DUPLICATE_LINK_ERROR'), 'warning');
		}
		/* throw error if " a" included in element */
		if ( strpos($html, ' a') !== false )
		{
			Factory::getApplication()->enqueueMessage(JText::_('TPL_ACORN_EMBEDDED_LINK_ERROR'), 'warning');
			$html = str_replace(' a', '', $html);
		}

// Is there anything to process?
		if ( $html != '' && ($font || $size || $weight || $style || $color || $backgroundColor) )
		{
			// process fonts
			// create CSS declaration.
			$css .= $html . " {\n";

			// Continue on with chosen font per element.
			if ( $font )
			{
				// create string containing all fonts desired
				// so they can be put into google font link string.

				$css .= '   font-family: "' . $font . "\";\n";
			}
			if ( $size )
			{
				// add font size to css declaration
				$css .= "   font-size: " . $size . ";\n";
			}
			if ( $weight )
			{
				//add font weight to css declaration
				$css .= "   font-weight: " . $weight . ";\n";
			}
			if ( $style )
			{
				//add font style to css declaration
				$css .= "   font-style: " . $style . ";\n";
			}
			if ( $color && $html !== "a" )
			{
				// add font color to css declaration
				$css .= "   color: " . $color . ";\n";
			}
			if ( $backgroundColor )
			{
				// add background color to css declaration
				$css .= "   background-color: " . $backgroundColor . ";\n";
			}

			$css .= "	}\n";

		}

		/* we need to make sure we're not putting active colors for body */
		if ( $html !== "body" )
		{

			if ( $backgroundhoverColor )
			{
				//add font hover color to css declaration
				$css .= $html . ":hover, " . $html . ":focus {\n";
				$css .= "	background-color: " . $backgroundhoverColor . ";\n}\n";

			}
			if ( $hoverColor )
			{
				//add font link color to css declaration
				$css .= $html . ":hover, " . $html . ":focus {\n";
				$css .= "	color: " . $hoverColor . ";\n}\n";

			}

			if ( $linkColor )
			{
				//add font link color to css declaration
				$css .= $html . " a {\n";
				$css .= "	color: " . $linkColor . ";\n}\n";

			}
			if ( $hoverlinkColor )
			{
				//add font link color to css declaration
				$css .= $html . " a:hover {\n";
				$css .= "	color: " . $hoverlinkColor . ";\n}\n";

			}
		}
		else
		{
			if ( $linkColor )
			{
				//add font link color to css declaration
				$css .= "a{\n";
				$css .= "   color: " . $linkColor . ";\n}\n";

			}
			if ( $hoverlinkColor && !$hoverColor )
			{
				//add font link color to css declaration
				$css .= "a:hover {\n";
				$css .= "   color: " . $hoverlinkColor . ";\n}\n";

			}
			if ( !$hoverlinkColor && $hoverColor )
			{
				//add font link color to css declaration
				$css .= "a:hover, a:focus {\n";
				$css .= "   color: " . $hoverColor . ";\n}\n";

			}
			if ( $backgroundhoverColor )
			{
				//add font hover color to css declaration
				$css .= "a:hover, a:focus {\n";
				$css .= "   background-color: " . $backgroundhoverColor . ";\n}\n";
			}
		}
		// next row
	}
	/* ------ END GOOGLE FONTS ------ */
}
else
{


	/*
	 * ==================================================
	 * Call Typography elements but not fonts
	 * ==================================================
	 */

// Make sure we dont' have empty row

	$chooser        = $data->fontsChooser;
	$json           = json_decode($chooser, true);
	$filtered_array = group_by_key($json);

	foreach ( $filtered_array as $index => $value )
	{
		$html                 = $value[0];
		$function             = $value[1];
		$size                 = checkPX($value[2]);
		$weight               = $value[3];
		$style                = $value[4];
		$color                = $value[5];
		$backgroundColor      = $value[6];
		$backgroundhoverColor = $value[7];
		$hoverColor           = $value[8];
		$linkColor            = $value[9];
		$hoverlinkColor       = $value[10];


		/* check for duplicate links within elements */
		if ( $hoverColor && $hoverlinkColor && $html == "body" )
		{
			Factory::getApplication()->enqueueMessage(JText::_('TPL_ACORN_DUPLICATE_LINK_ERROR'), 'warning');
		}
		/* throw error if " a" included in element */
		if ( strpos($html, ' a') !== false )
		{
			Factory::getApplication()->enqueueMessage(JText::_('TPL_ACORN_EMBEDDED_LINK_ERROR'), 'warning');
			$html = str_replace(' a', '', $html);
		}

		// Is there anything to process?
		if ( $html != '' && ($size || $weight || $style || $color || $backgroundColor) )
		{
			// process fonts
			// create CSS declaration.
			$css .= $html . " {\n";

			if ( $size )
			{
				// add font size to css declaration
				$css .= "   font-size: " . $size . ";\n";
			}
			if ( $weight )
			{
				//add font weight to css declaration
				$css .= "   font-weight: " . $weight . ";\n";
			}
			if ( $style )
			{
				//add font style to css declaration
				$css .= "   font-style: " . $style . ";\n";
			}
			if ( $color && $html !== "a" )
			{
				// add font color to css declaration
				$css .= "   color: " . $color . ";\n";
			}
			if ( $backgroundColor )
			{
				// add background color to css declaration
				$css .= "   background-color: " . $backgroundColor . ";\n";
			}
			$css .= "	}\n";

		}

		/* we need to make sure we're not putting active colors for body */
		if ( $html !== "body" )
		{
			if ( $backgroundhoverColor )
			{
				//add font hover color to css declaration
				$css .= $html . ":hover, " . $html . ":focus {\n";
				$css .= "	background-color: " . $backgroundhoverColor . ";\n}\n";
			}
			if ( $hoverColor )
			{
				//add font link color to css declaration
				$css .= $html . ":hover, " . $html . ":focus {\n";
				$css .= "	color: " . $hoverColor . ";\n}\n";
			}

			if ( $linkColor )
			{
				//add font link color to css declaration
				$css .= $html . " a {\n";
				$css .= "	color: " . $linkColor . ";\n}\n";
			}
			if ( $hoverlinkColor )
			{
				//add font link color to css declaration
				$css .= $html . " a:hover {\n";
				$css .= "	color: " . $hoverlinkColor . ";\n}\n";
			}
		}
		else
		{
			if ( $linkColor )
			{
				//add font link color to css declaration
				$css .= "a{\n";
				$css .= "   color: " . $linkColor . ";\n}\n";
			}
			if ( $hoverlinkColor && !$hoverColor )
			{
				//add font link color to css declaration
				$css .= "a:hover {\n";
				$css .= "   color: " . $hoverlinkColor . ";\n}\n";
			}
			if ( !$hoverlinkColor && $hoverColor )
			{
				//add font link color to css declaration
				$css .= "a:hover, a:focus {\n";
				$css .= "   color: " . $hoverColor . ";\n}\n";
			}
			if ( $backgroundhoverColor )
			{
				//add font hover color to css declaration
				$css .= "a:hover, a:focus {\n";
				$css .= "   background-color: " . $backgroundhoverColor . ";\n}\n";
			}
		}
	}
}


/**
 * ====================
 *  **** COPYRIGHT ****
 * ====================
 */
$copyrightPosition = $data->copyrightPosition;

if ( $copyrightPosition )
{
	$css .= "#copyright .copyright{\n	text-align: " . $copyrightPosition . ";\n}";
}

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


/**
 * ===============================================
 *  **** DO NOT CHANGE ANYTHING BELOW THIS!!! ****
 * ===============================================
 */

$css = $import ? $import . "\n" . $css : $css;
