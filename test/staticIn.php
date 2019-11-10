<?php
$font  = $params->googleFontName;
$logo  = $params->googleFontName;
$title = $params->sitetitle;

ob_start();
?>
	<a href = "#bubba" style = "color: blue;" />
<?php
$link = ob_get_contents();
ob_end_clean();


// ===== DO NOT TOUCH BELOW THIS LINE =====
// get the list of vars available at this time
$lines = get_defined_vars();
// get just the template params from the above list.
//$lines = $lines["params"];
var_dump($lines);
