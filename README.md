# BearsOnSave
Simple Joomla Plugin to process template parameters into compressed .css file on save.


When the user clicks on save, gather all the parameters in the template & process them into a file called params.css. it is NOT necessary to save the existing file. Simply overwrite it. This file should be loaded JUST before custom.css as it really IS it's own custom.css modifying what is normally in the template. Once this is done we SHOULD be able to remove all parsing from template backend. This should speed up things ENORMOUSLY!

REQUIRES:
```php
use Joomla\CMS\HTML\HTMLHelper;

// Used with HTMLHelper::
$HTMLHelperDebug = array('version' => 'auto', 'relative' => true, 'detectDebug' => true);

// Load params.css
HTMLHelper::_('stylesheet', 'params.min.css', $HTMLHelperDebug);
```