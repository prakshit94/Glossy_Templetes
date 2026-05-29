<?php
$content = file_get_contents('routes/web.php');
// Add middleware to Route::resource
$content = preg_replace('/Route::resource\(\'([a-zA-Z0-9_-]+)\', ([a-zA-Z0-9_:\\\\]+)\);/', "Route::resource('$1', $2)->middleware('permission:$1.view');", $content);
file_put_contents('routes/web.php', $content);
echo "Done.";
