# BearsOnSave
Simple Joomla Plugin to process template parameters into compressed .css file on save.


When the user clicks on save, gather all the parameters in the template & process them into a file called params.css. it is NOT necessary to save the existing file. Simply overwrite it. This file should be loaded JUST before custom.css as it really IS it's own custom.css modifying what is normally in the template. Once this is done we SHOULD be able to remove all parsing from template backend. This should speed up things ENORMOUSLY!
MUST only trigger when active template uses save not during any other time.

to test copy the files in the 'template' folder to your template.
then click "save" from inside any site template.  You should get a message on success or failure.
there should be the following files created in the '/css' folder of your template.
 .	".backup.custom.css"
 . 	"custom.css"
 .	"bos.css"
