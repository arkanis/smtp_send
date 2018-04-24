<?php

ob_start(function($markdown_text){
	file_put_contents("README.md", $markdown_text);
});

?>
# smtp_send

This is a single PHP function to send mails (~90 LOC). The one function in
`smtp_send.php` is all you need. So you can just download that file and
`require` it or copy and paste the code to wherever you need it.

Feel free to open an issue if something doesn't work as expected. Sending a push
request is even better.

<?php
preg_match("/ \/\*\* (.+?) \*\/ /xs", file_get_contents("smtp_send.php"), $matches);
$function_documentation = preg_replace("/^\s*\* /m", "", $matches[1]);
echo(trim($function_documentation));
?>