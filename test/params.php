<?php
defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Factory;

// $data contains template params use $data->get('') to retrieve the params.
// $css is the default variable required to be returned.
// use the following to automatically output varname in the line above the css
// if($debug){ $style .= "\n" . '/* $myvargoeshere */' . "\n";}

if ( defined('JDEBUG') && JDEBUG )
{
	$debug = true;
}
else
{
	$debug = false;
}

$css = "#top{\n"
	. "background-color: purple;\n"
	. "color: yellow !important;\n"
	. "}\n";
