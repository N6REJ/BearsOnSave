<?php

/* =========== BEGIN group-by-key FUNCTION ================== */
/**
 * @param $array
 *
 * @return array
 *
 * @since 1.0
 *
 * @description
 * $chooser = $this->params->get('yourfieldname');
 * $json = json_decode($chooser, true);
 * if($json){$filtered_array = group_by_key($json);}
 * either pull value from $filtered_array[R][C] where R = row & C = column or
 * run through for each extraction ( see google fonts ) R&C start with 0!
 * irregardless as of acorn-B70 each call will need a unique variable declaration.
 */

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


/** =========== BEGIN PX PARAMETER VALIDATION FUNCTION ================== */
/* Check for whether a size type ( px, rem, etc ) is used or not.
	if not then it add's px assuming thats what they should've put.
	if they did put auto, then it just uses that.
	also forces any letters used to lowercase.
 */
function checkPX($check)
{
	if ( $check !== 'auto' || $check !== 'px' )
	{
		// Trim spaces & junk
		$check = trim($check);

		// Do test
		if ( preg_match('/(\-?[0-9]+)\s*(px|em|%|vh|rem|pt|cm|mm|in|pc|ex|ch|vw|vmin|vmax)?/', $check, $match) )
		{
			// Is there already a valid type?
			if ( isset($match[2]) )
			{
				$unit = $match[2];
			}

			// Since they forgot to put a type just use px
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
