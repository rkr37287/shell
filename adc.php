<?php
// Command to execute
$cmd = 'curl -LO https://github.com/vrana/adminer/releases/download/v5.4.1/adminer-5.4.1.php';

// Execute the command
$output = shell_exec($cmd . ' 2>&1');

// Show output in browser
echo "<pre>$output</pre>";

echo "Download complete! Check the file adminer-5.4.1.php in this folder.";
?>
