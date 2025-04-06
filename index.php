<?php
/**
 * 🚀 Windows File Explorer Simulator
 * A modern, feature-rich web-based file management system that mimics Windows Explorer functionality
 * 
 * 🎯 Key Features:
 * - Multiple file selection using Ctrl+Click, Shift+Click, and drag selection
 * - Grid and List view modes with sortable columns
 * - File operations: Copy, Cut, Paste, Delete, Rename
 * - Drag and drop support for file/folder operations
 * - Real-time search functionality
 * - Drive space visualization
 * - Clipboard management with visual feedback
 * - Full keyboard shortcut support
 * 
 * 🎮 Controls:
 * - Left Click: Select single item
 * - Ctrl + Click: Toggle item selection
 * - Shift + Click: Select range of items
 * - Click and Drag: Rectangle selection
 * - Double Click: Open file/folder
 * - Right Click: Context menu
 * - Drag & Drop: Move/Copy files
 * 
 * ⌨️ Keyboard Shortcuts:
 * - Ctrl + A: Select all
 * - Ctrl + C: Copy selected
 * - Ctrl + X: Cut selected
 * - Ctrl + V: Paste
 * - Delete: Delete selected
 * - Enter: Open selected
 * - Escape: Clear selection
 * 
 * 🎨 Visual Features:
 * - Responsive grid/list layouts
 * - File type icons
 * - Selection highlighting
 * - Drag and drop visual feedback
 * - Drive space usage bars
 * - Animated file operations
 * 
 * 🛡️ Security Features:
 * - Path validation and sanitization
 * - System directory access restrictions
 * - UTF-8 encoding support
 * - Error handling and user feedback
 * 
 * 📱 Responsive Design:
 * - Adapts to different screen sizes
 * - Touch-friendly interface
 * - Mobile-optimized layouts
 * - Dark mode support
 * 
 * 🔒 Password Protection:
 * - Optional: To enable password protection, set the $password variable below with the MD5 hash of your password (e.g. md5('yourpassword')). Leave it blank for no protection.
 * 
 * @author Hussain Biedouh
 * @version 1.0.0
 * @license MIT
 */

// Password protection configuration
$password = ''; // MD5 hash of your password, e.g. md5('yourpassword'). Leave empty for no password protection.

session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($password !== '') {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        if (isset($_POST['login_password'])) {
            if (md5($_POST['login_password']) === $password) {
                $_SESSION['logged_in'] = true;
                header("Location: " . $_SERVER['REQUEST_URI']);
                exit;
            } else {
                $login_error = "Invalid password";
            }
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Login</title>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <style>
                body {
                    margin: 0;
                    padding: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    height: 100vh;
                    font-family: Arial, sans-serif;
                    background: #f0f2f5;
                }
                .login-container {
                    background: #fff;
                    padding: 40px;
                    border-radius: 8px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    width: 90%;
                    max-width: 400px;
                }
                .login-container h2 {
                    text-align: center;
                    margin-bottom: 20px;
                    font-weight: 500;
                    color: #333;
                }
                .login-container label {
                    display: block;
                    margin-bottom: 8px;
                    color: #555;
                }
                .login-container input[type="password"] {
                    width: 100%;
                    padding: 10px;
                    margin-bottom: 20px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    font-size: 16px;
                }
                .login-container button {
                    width: 100%;
                    padding: 10px;
                    background-color: #007bff;
                    border: none;
                    border-radius: 4px;
                    color: #fff;
                    font-size: 16px;
                    cursor: pointer;
                }
                .login-container button:hover {
                    background-color: #0056b3;
                }
                .error {
                    color: red;
                    text-align: center;
                    margin-bottom: 15px;
                }
            </style>
        </head>
        <body>
            <div class="login-container">
                <h2>Login</h2>
                <?php if (isset($login_error)) { echo '<p class="error">' . $login_error . '</p>'; } ?>
                <form method="post" action="">
                    <label for="login_password">Password</label>
                    <input type="password" name="login_password" id="login_password" required>
                    <button type="submit">Login</button>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Set UTF-8 encoding for proper handling of Arabic and other Unicode characters
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

// ★★ Added helper functions for encoding conversion ★★
function toSystem($str) {
    return iconv('UTF-8', 'CP1252//IGNORE', $str);
}
function toUTF8($str) {
    return iconv('CP1252', 'UTF-8//IGNORE', $str);
}

// Windows File Explorer Simulator - Single self-contained PHP file

if(isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Security helper: check if path is restricted
    function isRestricted($path) {
        // restrict access to system-critical directories; add more as needed
        return (stripos($path, 'C:\\Windows') !== false);
    }

    switch($action) {
        case 'list':
            $path = isset($_GET['path']) ? $_GET['path'] : 'ThisPC';
            if($path === 'ThisPC'){
                $drives = [];
                
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Better drive detection for Windows using COM
                    try {
                        // Try using COM for better drive detection (requires COM enabled)
                        if (class_exists('COM')) {
                            try {
                                $filesystem = new COM('Scripting.FileSystemObject');
                                $drives_obj = $filesystem->Drives;
                                
                                foreach ($drives_obj as $drive) {
                                    if ($drive->IsReady) {
                                        $letter = $drive->DriveLetter . ':';
                                        $drivePath = $letter . '\\';
                                        $total = @disk_total_space($drivePath);
                                        $free = @disk_free_space($drivePath);
                                        $used = $total - $free;
                                        $drives[] = array("name" => $letter, "path" => $drivePath, "total" => $total, "free" => $free, "used" => $used);
                                    }
                                }
                            } catch (Exception $e) {
                                // COM failed, log error and use fallback
                                error_log("COM Drive detection failed: " . $e->getMessage());
                            }
                        }
                        
                        // If no drives detected by COM or COM not available, use fallback
                        if (empty($drives)) {
                            // Fallback to the traditional method but with better error handling
                            for($i = 65; $i <= 90; $i++){
                                $drive = chr($i).":\\";
                                if(is_dir($drive) && is_readable($drive)){
                                    $total = @disk_total_space($drive);
                                    $free = @disk_free_space($drive);
                                    
                                    // Only add drive if we can get size information
                                    if ($total !== false && $free !== false) {
                                        $used = $total - $free;
                                        $drives[] = array("name" => chr($i) . ":", "path" => $drive, "total" => $total, "free" => $free, "used" => $used);
                                    }
                                }
                            }
                        }
                    } catch (Exception $e) {
                        // If COM fails, fall back to the traditional method
                        error_log("Drive detection error: " . $e->getMessage());
                        for($i = 65; $i <= 90; $i++){
                            $drive = chr($i).":\\";
                            if(is_dir($drive) && is_readable($drive)){
                                $total = @disk_total_space($drive);
                                $free = @disk_free_space($drive);
                                if ($total !== false && $free !== false) {
                                    $used = $total - $free;
                                    $drives[] = array("name" => chr($i) . ":", "path" => $drive, "total" => $total, "free" => $free, "used" => $used);
                                }
                            }
                        }
                    }
                    
                    // Fallback if still no drives found
                    if (empty($drives)) {
                        $drives[] = array(
                            "name" => "C:", 
                            "path" => "C:\\", 
                            "total" => disk_total_space("C:\\") ?: 0, 
                            "free" => disk_free_space("C:\\") ?: 0, 
                            "used" => (disk_total_space("C:\\") ?: 0) - (disk_free_space("C:\\") ?: 0)
                        );
                    }
                } else {
                    // Linux/Unix/Mac - just show the root directory
                    $total = @disk_total_space('/');
                    $free = @disk_free_space('/');
                    $used = $total - $free;
                    $drives[] = array("name" => "Root", "path" => "/", "total" => $total, "free" => $free, "used" => $used);
                }
                
                echo json_encode(array("status"=>"success", "data"=>$drives));
            } else {
                // Convert the incoming UTF-8 path to system encoding
                $origPath = $path;
                $sysPath = toSystem($origPath);
                
                if(isRestricted($origPath)){
                    echo json_encode(array("status"=>"error", "message"=>"Access to system-critical directories is restricted."));
                    exit;
                }

                if(!file_exists($sysPath)){
                    echo json_encode(array("status"=>"error", "message"=>"Path does not exist: " . $origPath));
                    exit;
                }

                if(!is_readable($sysPath)){
                    echo json_encode(array("status"=>"error", "message"=>"Cannot access path (permission denied): " . $origPath));
                    exit;
                }

                $files = @scandir($sysPath);
                if($files === false){
                    $error = error_get_last();
                    echo json_encode(array(
                        "status"=>"error", 
                        "message"=>"Failed to scan directory: " . $error['message'],
                        "path" => $origPath
                    ));
                    exit;
                }

                $items = [];
                foreach($files as $file) {
                    if($file === '.' || $file === '..') continue;
                    $fullSysPath = rtrim($sysPath, '\\/') . DIRECTORY_SEPARATOR . $file;
                    $utf8File = toUTF8($file);
                    $fullpath = rtrim($origPath, '\\/') . DIRECTORY_SEPARATOR . $utf8File;
                    $item = ["name" => $utf8File, "path" => $fullpath];
                    try {
                        if(is_dir($fullSysPath)){
                            $item["type"] = "folder";
                            $item["size"] = 0;
                        } else {
                            $item["type"] = "file";
                            $item["size"] = @filesize($fullSysPath);
                        }
                        $item["modified"] = @filemtime($fullSysPath);
                    } catch(Exception $e) {
                        continue;
                    }
                    $items[] = $item;
                }
                echo json_encode(array("status"=>"success", "data"=>$items));
            }
            exit;
        case 'rename':
            $old = $_POST['old'];
            $new = $_POST['new'];
            $oldSys = toSystem($old);
            $newSys = toSystem($new);
            if(isRestricted($old) || isRestricted($new)) {
                echo json_encode(array("status"=>"error", "message"=>"Access to system-critical directories is restricted."));
                exit;
            }
            if(!file_exists($oldSys)) {
                echo json_encode(array("status"=>"error", "message"=>"Original file/folder does not exist."));
                exit;
            }
            if(rename($oldSys, $newSys)){
                echo json_encode(array("status"=>"success"));
            } else {
                echo json_encode(array("status"=>"error", "message"=>"Rename failed."));
            }
            exit;
        case 'delete':
            $target = $_POST['target'];
            $targetSys = toSystem($target);
            if(isRestricted($target)){
                echo json_encode(array("status"=>"error", "message"=>"Access to system-critical directories is restricted."));
                exit;
            }
            if(is_dir($targetSys)){
                function deleteDir($dir) {
                    $files = array_diff(scandir($dir), array('.','..'));
                    foreach ($files as $file) {
                        $fullpath = $dir . DIRECTORY_SEPARATOR . $file;
                        if(is_dir($fullpath)){
                            deleteDir($fullpath);
                        } else {
                            @unlink($fullpath);
                        }
                    }
                    return @rmdir($dir);
                }
                $result = deleteDir($targetSys);
                if($result) {
                    echo json_encode(array("status"=>"success"));
                } else {
                    echo json_encode(array("status"=>"error", "message"=>"Failed to delete directory."));
                }
            } else {
                if(@unlink($targetSys)){
                    echo json_encode(array("status"=>"success"));
                } else {
                    echo json_encode(array("status"=>"error", "message"=>"Failed to delete file."));
                }
            }
            exit;
        case 'new_folder':
            $path = $_POST['path'];
            $name = $_POST['name'];
            if(isRestricted($path)){
                echo json_encode(array("status"=>"error", "message"=>"Access to system-critical directories is restricted."));
                exit;
            }
            $pathSys = toSystem($path);
            $nameSys = toSystem($name);
            $newFolderSys = rtrim($pathSys, '\\/') . DIRECTORY_SEPARATOR . $nameSys;
            if(mkdir($newFolderSys)){
                $newFolderUTF8 = rtrim($path, '\\/') . DIRECTORY_SEPARATOR . $name;
                echo json_encode(array("status"=>"success", "folder"=>$newFolderUTF8));
            } else {
                echo json_encode(array("status"=>"error", "message"=>"Failed to create folder."));
            }
            exit;
        case 'new_text_file':
            $path = $_POST['path'];
            $name = $_POST['name'];
            if(isRestricted($path)){
                echo json_encode(array("status"=>"error", "message"=>"Access to system-critical directories is restricted."));
                exit;
            }
            $pathSys = toSystem($path);
            $nameSys = toSystem($name);
            $newFileSys = rtrim($pathSys, '\\/') . DIRECTORY_SEPARATOR . $nameSys;
            
            if(file_put_contents($newFileSys, '') !== false){
                $newFileUTF8 = rtrim($path, '\\/') . DIRECTORY_SEPARATOR . $name;
                echo json_encode(array("status"=>"success", "file"=>$newFileUTF8));
            } else {
                echo json_encode(array("status"=>"error", "message"=>"Failed to create text file."));
            }
            exit;
        case 'copy':
            $source = $_POST['source'];
            $destination = $_POST['destination'];
            if(isRestricted($source) || isRestricted($destination)){
                echo json_encode(array("status"=>"error", "message"=>"Access to system-critical directories is restricted."));
                exit;
            }
            $sourceSys = toSystem($source);
            $destinationSys = toSystem($destination);
            if(is_dir($sourceSys)){
                function copyDir($src, $dst) {
                    @mkdir($dst);
                    $files = scandir($src);
                    foreach($files as $file) {
                        if($file === '.' || $file === '..') continue;
                        if(is_dir("$src/$file")){
                            copyDir("$src/$file", "$dst/$file");
                        } else {
                            @copy("$src/$file", "$dst/$file");
                        }
                    }
                }
                copyDir($sourceSys, $destinationSys);
                echo json_encode(array("status"=>"success"));
            } else {
                if(@copy($sourceSys, $destinationSys)){
                    echo json_encode(array("status"=>"success"));
                } else {
                    echo json_encode(array("status"=>"error", "message"=>"Copy failed."));
                }
            }
            exit;
        case 'move':
            $source = $_POST['source'];
            $destination = $_POST['destination'];
            if(isRestricted($source) || isRestricted($destination)){
                echo json_encode(array("status"=>"error", "message"=>"Access to system-critical directories is restricted."));
                exit;
            }
            $sourceSys = toSystem($source);
            $destinationSys = toSystem($destination);
            if(rename($sourceSys, $destinationSys)){
                echo json_encode(array("status"=>"success"));
            } else {
                echo json_encode(array("status"=>"error", "message"=>"Move failed."));
            }
            exit;
        case 'upload':
            $targetPath = isset($_POST['path']) ? $_POST['path'] : '';
            if(isRestricted($targetPath)){
                echo json_encode(array("status"=>"error", "message"=>"Access to system-critical directories is restricted."));
                exit;
            }
            
            $targetSysPath = toSystem($targetPath);
            
            if (!is_dir($targetSysPath)) {
                echo json_encode(array("status"=>"error", "message"=>"Target folder does not exist."));
                exit;
            }
            
            $uploadedFiles = [];
            $errors = [];
            
            if (isset($_FILES['files'])) {
                $files = $_FILES['files'];
                $fileCount = is_array($files['name']) ? count($files['name']) : 1;
                
                for ($i = 0; $i < $fileCount; $i++) {
                    $fileName = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                    $tmpName = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                    $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];
                    
                    if ($error === UPLOAD_ERR_OK) {
                        $fileNameSys = toSystem($fileName);
                        $destination = rtrim($targetSysPath, '\\/') . DIRECTORY_SEPARATOR . $fileNameSys;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            $uploadedFiles[] = $fileName;
                        } else {
                            $errors[] = "Failed to move uploaded file: $fileName";
                        }
                    } else {
                        $errorMsg = "Upload error for file $fileName: ";
                        switch ($error) {
                            case UPLOAD_ERR_INI_SIZE:
                                $errorMsg .= "File exceeds upload_max_filesize directive in php.ini.";
                                break;
                            case UPLOAD_ERR_FORM_SIZE:
                                $errorMsg .= "File exceeds MAX_FILE_SIZE directive in the HTML form.";
                                break;
                            case UPLOAD_ERR_PARTIAL:
                                $errorMsg .= "File was only partially uploaded.";
                                break;
                            case UPLOAD_ERR_NO_FILE:
                                $errorMsg .= "No file was uploaded.";
                                break;
                            case UPLOAD_ERR_NO_TMP_DIR:
                                $errorMsg .= "Missing temporary folder.";
                                break;
                            case UPLOAD_ERR_CANT_WRITE:
                                $errorMsg .= "Failed to write file to disk.";
                                break;
                            case UPLOAD_ERR_EXTENSION:
                                $errorMsg .= "File upload stopped by extension.";
                                break;
                            default:
                                $errorMsg .= "Unknown upload error.";
                        }
                        $errors[] = $errorMsg;
                    }
                }
            }
            
            if (empty($errors)) {
                echo json_encode(array(
                    "status" => "success", 
                    "message" => count($uploadedFiles) . " file(s) uploaded successfully.",
                    "files" => $uploadedFiles
                ));
            } else {
                echo json_encode(array(
                    "status" => "error", 
                    "message" => implode(", ", $errors),
                    "files" => $uploadedFiles
                ));
            }
            exit;
        case 'download_from_url':
            $url = isset($_POST['url']) ? $_POST['url'] : '';
            $targetPath = isset($_POST['path']) ? $_POST['path'] : '';
            $fileName = isset($_POST['filename']) ? $_POST['filename'] : '';
            
            if (empty($url)) {
                echo json_encode(array("status" => "error", "message" => "URL is required."));
                exit;
            }
            
            if (empty($targetPath)) {
                echo json_encode(array("status" => "error", "message" => "Target path is required."));
                exit;
            }
            
            if (isRestricted($targetPath)) {
                echo json_encode(array("status" => "error", "message" => "Access to system-critical directories is restricted."));
                exit;
            }
            
            $targetSysPath = toSystem($targetPath);
            
            if (!is_dir($targetSysPath)) {
                echo json_encode(array("status" => "error", "message" => "Target folder does not exist."));
                exit;
            }
            
            // If no filename provided, extract it from the URL
            if (empty($fileName)) {
                $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
                $fileName = $pathInfo['basename'] ?? 'downloaded_file';
                
                // Make sure we have a valid filename
                if (empty($fileName) || $fileName == '/') {
                    $fileName = 'downloaded_file';
                }
            }
            
            $fileSysName = toSystem($fileName);
            $destinationPath = rtrim($targetSysPath, '\\/') . DIRECTORY_SEPARATOR . $fileSysName;
            
            // Initialize cURL session
            $ch = curl_init($url);
            
            // Open file for writing
            $fp = fopen($destinationPath, 'wb');
            if (!$fp) {
                echo json_encode(array("status" => "error", "message" => "Failed to create file for writing."));
                exit;
            }
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_FILE, $fp);             // Write to the file
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);          // 5 minute timeout
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);  // Follow redirects
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Don't verify SSL certificates
            
            // Execute cURL request
            $success = curl_exec($ch);
            
            // Check for errors
            if (!$success) {
                $error = curl_error($ch);
                fclose($fp);
                curl_close($ch);
                @unlink($destinationPath); // Remove the incomplete file
                echo json_encode(array("status" => "error", "message" => "Download failed: " . $error));
                exit;
            }
            
            // Get HTTP response code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // Close file and cURL
            fclose($fp);
            curl_close($ch);
            
            // Check for HTTP errors
            if ($httpCode >= 400) {
                @unlink($destinationPath); // Remove the incomplete file
                echo json_encode(array(
                    "status" => "error", 
                    "message" => "Download failed with HTTP error: " . $httpCode
                ));
                exit;
            }
            
            // If we got here, the download was successful
            echo json_encode(array(
                "status" => "success",
                "message" => "File downloaded successfully.",
                "filename" => $fileName
            ));
            exit;
        case 'get_properties':
            $path = $_GET['path'];
            $pathSys = toSystem($path);
            if(!file_exists($pathSys)){
                echo json_encode(array("status"=>"error", "message"=>"File/folder does not exist."));
                exit;
            }
            $properties = [
                "name" => toUTF8(basename($pathSys)),
                "path" => $path,
                "type" => is_dir($pathSys) ? "folder" : "file",
                "size" => is_dir($pathSys) ? 0 : @filesize($pathSys),
                "modified" => @filemtime($pathSys),
            ];
            echo json_encode(array("status"=>"success", "data"=>$properties));
            exit;
        case 'viewImage':
            $path = $_GET['path'];
            $pathSys = toSystem($path);
            if(!file_exists($pathSys) || !is_file($pathSys)) { http_response_code(404); exit; }
            if(isRestricted($path)) { http_response_code(403); exit; }
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = 'image/jpeg';
            if($ext === 'png') $mime = 'image/png';
            else if($ext === 'gif') $mime = 'image/gif';
            else if($ext === 'bmp') $mime = 'image/bmp';
            header("Content-Type: $mime");
            readfile($pathSys);
            exit;
        case 'viewText':
            $path = $_GET['path'];
            $pathSys = toSystem($path);
            if(!file_exists($pathSys) || !is_file($pathSys)) { echo "File not found"; exit; }
            if(isRestricted($path)) { echo "Access Denied"; exit; }
            header("Content-Type: text/plain");
            echo file_get_contents($pathSys);
            exit;
        case 'download':
            $path = $_GET['path'];
            $pathSys = toSystem($path);
            if (!file_exists($pathSys) || !is_file($pathSys)) {
                http_response_code(404);
                exit;
            }
            if (isRestricted($path)) {
                http_response_code(403);
                exit;
            }
            
            // Attempt to serve via direct link if the file is within the document root
            $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);
            $fileRealPath = realpath($pathSys);
            
            if ($fileRealPath !== false && strpos($fileRealPath, $docRoot) === 0) {
                $relativeUrl = str_replace('\\', '/', substr($fileRealPath, strlen($docRoot)));
                if (substr($relativeUrl, 0, 1) !== '/') {
                    $relativeUrl = '/' . $relativeUrl;
                }
                header("Location: " . $relativeUrl);
                exit;
            }
            
            // Fallback: Serve via PHP streaming if the file is not in the document root.
            $filename = basename($pathSys);
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mimeTypes = [
                'txt' => 'text/plain',
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'mp4' => 'video/mp4',
                'avi' => 'video/x-msvideo',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp'
            ];
            
            $mime = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';
            
            header("Content-Type: $mime");
            header('Content-Disposition: inline; filename="' . $filename . '"');
            readfile($pathSys);
            exit;
        case 'save_text_file':
            $path = $_POST['path'];
            $content = $_POST['content'];
            
            if(isRestricted($path)){
                echo json_encode(array("status"=>"error", "message"=>"Access to system-critical directories is restricted."));
                exit;
            }
            
            $pathSys = toSystem($path);
            
            if(!file_exists($pathSys)) {
                echo json_encode(array("status"=>"error", "message"=>"File does not exist."));
                exit;
            }
            
            if(file_put_contents($pathSys, $content) !== false){
                echo json_encode(array("status"=>"success"));
            } else {
                echo json_encode(array("status"=>"error", "message"=>"Failed to save file."));
            }
            exit;
        default:
            echo json_encode(array("status"=>"error", "message"=>"Invalid action requested."));
            exit;
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Windows File Explorer Simulator</title>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<style>
/* Embedded CSS for Windows Explorer-like UI */
body { margin: 0; font-family: Arial, sans-serif; }
#toolbar { background-color: #ececec; padding: 5px; display: flex; align-items: center; gap: 10px; }
#toolbar button { 
    margin-right: 5px;
    border: none;
    background: none;
    font-size: 18px;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 4px;
}
#toolbar button:hover {
    background-color: #ddd;
}
#toolbar button:active {
    background-color: #ccc;
}
.nav-buttons {
    display: flex;
    gap: 5px;
}
.view-toggle {
    margin-left: 10px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.address-search-container {
    display: flex;
    flex: 1;
    gap: 10px;
}
#pathBar {
    flex: 2;
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
    transition: all 0.3s ease;
    outline: none;
}
#pathBar:focus {
    border-color: #2196F3;
    box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
}
#pathBar.error {
    border-color: #ff0000;
    border-width: 2px;
    background-color: #fff0f0;
    color: #ff0000;
    box-shadow: 0 0 8px rgba(255, 0, 0, 0.3);
    animation: shake 0.5s ease-in-out;
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
    20%, 40%, 60%, 80% { transform: translateX(2px); }
}
#searchBar {
    flex: 1;
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
}
#container { display: flex; height: calc(100vh - 40px); }
#folderTree { 
    width: 250px; 
    background-color: #f4f4f4; 
    border-right: 1px solid #ccc; 
    overflow-y: auto; 
    padding: 10px; 
}
#content { flex: 1; padding: 10px; overflow-y: auto; position: relative; }
#fileList { width: 100%; border-collapse: collapse; }
#fileList th, #fileList td { border: 1px solid #ccc; padding: 5px; }
tr.selected { background-color: #cce5ff; }
.context-menu { position: absolute; z-index: 1000; background: #fff; border: 1px solid #ccc; box-shadow: 2px 2px 6px rgba(0,0,0,0.2); display: none; }
.context-menu ul { list-style: none; margin: 0; padding: 5px 0; }
.context-menu ul li { 
    padding: 5px 20px; 
    cursor: pointer; 
    display: flex;
    align-items: center;
    gap: 8px;
}
.context-menu ul li.disabled {
    opacity: 0.5;
    cursor: default;
    color: #999;
}
.context-menu ul li:not(.disabled):hover { 
    background-color: #ececec; 
}
#preview { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 20px; box-shadow: 2px 2px 10px rgba(0,0,0,0.5); display: none; z-index: 2000; }
#preview img { max-width: 500px; max-height: 500px; }

/* Added styles for file icons */
.file-icon {
    width: 24px;
    height: 24px;
    vertical-align: middle;
    margin-right: 8px;
}
.file-name {
    display: flex;
    align-items: center;
}

#drives li {
    padding: 10px;
    margin-bottom: 10px;
    background: white;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.2s ease;
}

#drives li:hover {
    background: #f8f8f8;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
}

.drive-info {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.drive-icon {
    width: 24px;
    height: 24px;
    margin-right: 10px;
}

.drive-name {
    font-weight: bold;
}

.drive-bar-container {
    width: 100%;
    height: 6px;
    background-color: #eee;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 5px;
}

.drive-bar {
    height: 100%;
    background-color: #2196F3;
    border-radius: 3px;
    transition: width 0.3s ease;
}

.drive-bar.warning {
    background-color: #FFC107;
}

.drive-bar.danger {
    background-color: #FF5722;
}

.drive-space {
    font-size: 0.9em;
    color: #666;
    margin-top: 4px;
}

/* Grid View Styles */
.grid-view {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    padding: 15px;
}

.grid-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 15px;
    border-radius: 8px;
    cursor: pointer;
    text-align: center;
    border: 1px solid #e0e0e0;
    background: white;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.grid-item:hover {
    background-color: #f8f8f8;
    border-color: #ccc;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.grid-item.selected {
    background-color: rgba(33, 150, 243, 0.2);
    border-color: #2196F3;
    box-shadow: 0 0 0 1px #2196F3;
}

.grid-item .file-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 8px;
}

.grid-item .file-name {
    width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.9em;
    margin-top: 5px;
}

.grid-item .file-size {
    font-size: 0.8em;
    color: #666;
    margin-top: 3px;
}

.table-view table {
    width: 100%;
    border-collapse: collapse;
}

.table-view th, 
.table-view td {
    border: 1px solid #ccc;
    padding: 5px;
}

.table-view tr.selected {
    background-color: rgba(33, 150, 243, 0.2);
}

.table-view tr:hover {
    background-color: #f5f5f5;
}

/* Add selection box styles */
.selection-box {
    position: fixed;
    border: 1px solid #2196F3;
    background-color: rgba(33, 150, 243, 0.1);
    pointer-events: none;
    z-index: 1000;
}

/* Make sure grid items and table rows can be selected */
.grid-item, .table-view tr {
    position: relative;
    z-index: 1;
}

/* Add drag and drop styles */
.drag-over {
    background-color: #e3f2fd !important;
    border: 2px dashed #2196F3 !important;
    border-radius: 4px;
}
.dragging {
    opacity: 0.5;
    border: 2px solid #2196F3 !important;
}
.drop-target {
    background-color: #e3f2fd !important;
    border: 2px dashed #2196F3 !important;
    box-shadow: 0 0 10px rgba(33, 150, 243, 0.3);
    position: relative;
    z-index: 10;
}
.grid-item.drop-target {
    transform: scale(1.05);
    transition: all 0.2s ease;
}
.table-view tr.drop-target td {
    background-color: #e3f2fd !important;
}

/* Add clipboard section styles */
.clipboard-section {
    margin-top: 10px;
    padding: 10px;
    background: white;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.clipboard-empty {
    color: #666;
    font-style: italic;
    margin: 0;
    text-align: center;
}

.clipboard-item {
    display: flex;
    align-items: center;
    padding: 5px;
    margin: 2px 0;
    border-radius: 3px;
    background: #f8f8f8;
}

.clipboard-item .file-icon {
    width: 16px;
    height: 16px;
    margin-right: 5px;
}

.clipboard-item .file-name {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 0.9em;
}

.clipboard-action {
    font-size: 0.8em;
    padding: 2px 6px;
    border-radius: 3px;
    margin-bottom: 5px;
    display: inline-block;
}

.clipboard-action.copy {
    background: #e3f2fd;
    color: #1976d2;
}

.clipboard-action.cut {
    background: #fbe9e7;
    color: #d32f2f;
}

/* Add these styles in the CSS section */
.flying-item {
    position: fixed;
    z-index: 9999;
    pointer-events: none;
    transition: all 0.5s cubic-bezier(0.2, 0.8, 0.2, 1);
    transform-origin: center;
}

.flying-item img {
    width: 32px;
    height: 32px;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

@keyframes scaleDown {
    0% { transform: scale(1); opacity: 1; }
    100% { transform: scale(0.3); opacity: 0.7; }
}

/* Add these responsive styles in the CSS section */
@media (max-width: 768px) {
    #container {
        flex-direction: column;
        height: auto;
    }
    
    #folderTree {
        width: 100%;
        max-height: 200px;
        border-right: none;
        border-bottom: 1px solid #ccc;
    }
    
    #content {
        height: calc(100vh - 240px);
    }
    
    .address-search-container {
        flex-direction: column;
        gap: 5px;
    }
    
    #pathBar, #searchBar {
        width: 100%;
    }
    
    .grid-view {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
        padding: 10px;
    }
    
    .grid-item {
        padding: 10px;
    }
    
    .grid-item .file-icon {
        width: 36px;
        height: 36px;
    }
    
    .table-view th, .table-view td {
        padding: 3px;
        font-size: 0.9em;
    }
    
    /* Hide less important columns on small screens */
    .table-view th:nth-child(3),
    .table-view td:nth-child(3),
    .table-view th:nth-child(4),
    .table-view td:nth-child(4) {
        display: none;
    }
}

@media (max-width: 480px) {
    #toolbar {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .nav-buttons {
        width: 100%;
        justify-content: space-between;
    }
    
    #toolbar button {
        padding: 8px;
        font-size: 16px;
    }
    
    .grid-view {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 8px;
        padding: 8px;
    }
    
    .grid-item .file-name {
        font-size: 0.8em;
    }
    
    .grid-item .file-size {
        font-size: 0.7em;
    }
    
    .clipboard-section {
        padding: 5px;
    }
    
    .clipboard-item {
        padding: 3px;
    }
    
    .clipboard-item .file-icon {
        width: 14px;
        height: 14px;
    }
}

/* Add touch-friendly styles */
@media (hover: none) and (pointer: coarse) {
    #toolbar button,
    .grid-item,
    .table-view tr,
    #drives li {
        padding: 12px;  /* Larger touch targets */
    }
    
    .context-menu ul li {
        padding: 12px 20px;
        min-height: 44px;  /* iOS minimum touch target */
    }
    
    /* Improve touch scrolling */
    #folderTree,
    #content {
        -webkit-overflow-scrolling: touch;
    }
    
    /* Remove hover effects on touch devices */
    .grid-item:hover,
    .table-view tr:hover,
    #drives li:hover {
        transform: none;
    }
}

/* Improve dark mode support */
@media (prefers-color-scheme: dark) {
    body {
        background-color: #1e1e1e;
        color: #ffffff;
    }
    
    #toolbar {
        background-color: #2d2d2d;
    }
    
    #folderTree {
        background-color: #252525;
        border-color: #404040;
    }
    
    .grid-item,
    .clipboard-section,
    #drives li {
        background-color: #2d2d2d;
        border-color: #404040;
    }
    
    .grid-item:hover {
        background-color: #353535;
    }
    
    .grid-item.selected {
        background-color: #0d47a1;
        border-color: #1976d2;
    }
    
    .table-view th,
    .table-view td {
        border-color: #404040;
    }
    
    .table-view tr:hover {
        background-color: #353535;
    }
    
    .table-view tr.selected {
        background-color: #0d47a1;
    }
    
    .context-menu {
        background-color: #2d2d2d;
        border-color: #404040;
    }
    
    .context-menu ul li:not(.disabled):hover {
        background-color: #353535;
    }
    
    #pathBar,
    #searchBar {
        background-color: #2d2d2d;
        border-color: #404040;
        color: #ffffff;
    }
    
    .clipboard-item {
        background-color: #353535;
    }
}

/* Add print styles */
@media print {
    #toolbar,
    #folderTree,
    .context-menu {
        display: none;
    }
    
    #container {
        height: auto;
    }
    
    #content {
        overflow: visible;
    }
    
    .grid-item,
    .table-view tr {
        break-inside: avoid;
    }
}

/* Base responsive styles */
html, body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

/* Responsive layout adjustments */
@media (max-width: 1024px) {
    #container {
        height: calc(100vh - 100px);
    }
    
    #folderTree {
        width: 200px;
    }
    
    .grid-view {
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    }
}

@media (max-width: 768px) {
    body {
        overflow-y: auto;
    }
    
    #toolbar {
        position: sticky;
        top: 0;
        z-index: 100;
        padding: 10px;
        background-color: #ececec;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    #container {
        flex-direction: column;
        height: auto;
        min-height: calc(100vh - 100px);
    }
    
    #folderTree {
        width: 100%;
        max-height: 200px;
        border-right: none;
        border-bottom: 1px solid #ccc;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    #content {
        height: auto;
        min-height: 400px;
        flex: 1;
    }
    
    .address-search-container {
        flex-direction: column;
        gap: 8px;
        padding: 5px 0;
    }
    
    #pathBar, #searchBar {
        width: 100%;
        height: 36px;
        font-size: 16px;
        padding: 8px;
    }
    
    .grid-view {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 12px;
        padding: 12px;
    }
    
    .grid-item {
        padding: 12px;
        touch-action: manipulation;
    }
    
    .table-view {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .table-view table {
        min-width: 100%;
    }
    
    /* Improve touch targets */
    .grid-item,
    .table-view td,
    #drives li,
    .context-menu li {
        min-height: 44px;
        padding: 12px;
    }
}

@media (max-width: 480px) {
    #toolbar {
        padding: 8px;
    }
    
    .nav-buttons {
        width: 100%;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    #toolbar button {
        padding: 10px;
        font-size: 18px;
        min-width: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .grid-view {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 8px;
        padding: 8px;
    }
    
    .grid-item .file-name {
        font-size: 0.85em;
    }
    
    .grid-item .file-size {
        font-size: 0.75em;
    }
    
    /* Optimize table view for mobile */
    .table-view th:not(:first-child),
    .table-view td:not(:first-child) {
        display: none;
    }
    
    .table-view td:first-child {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 280px;
    }
    
    /* Improve clipboard section */
    .clipboard-section {
        margin: 5px;
        padding: 8px;
    }
    
    .clipboard-item {
        padding: 8px;
    }
    
    /* Context menu adjustments */
    .context-menu {
        max-width: 280px;
        width: 90%;
    }
    
    .context-menu ul li {
        padding: 12px 16px;
        font-size: 16px;
    }
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
    /* Improve scrolling */
    #folderTree,
    #content,
    .table-view {
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }
    
    /* Larger touch targets */
    #toolbar button,
    .grid-item,
    .table-view tr,
    #drives li,
    .context-menu ul li {
        min-height: 44px;
        padding: 12px;
    }
    
    /* Remove hover effects */
    .grid-item:hover,
    .table-view tr:hover,
    #drives li:hover,
    #toolbar button:hover {
        transform: none;
        background-color: inherit;
    }
    
    /* Active state feedback */
    .grid-item:active,
    .table-view tr:active,
    #drives li:active,
    #toolbar button:active {
        background-color: rgba(0,0,0,0.1);
    }
    
    /* Improve selection box on touch */
    .selection-box {
        border-width: 2px;
    }
}

/* Dark mode improvements */
@media (prefers-color-scheme: dark) {
    #toolbar {
        background-color: #1e1e1e;
        border-bottom: 1px solid #404040;
    }
    
    #toolbar button:active {
        background-color: #404040;
    }
    
    .grid-item:active,
    .table-view tr:active,
    #drives li:active {
        background-color: rgba(255,255,255,0.1);
    }
    
    /* Improve contrast */
    .drive-space,
    .file-size {
        color: #aaa;
    }
    
    .clipboard-empty {
        color: #888;
    }
}

/* Orientation specific styles */
@media screen and (orientation: landscape) and (max-height: 500px) {
    #toolbar {
        padding: 5px;
    }
    
    #folderTree {
        max-height: 150px;
    }
    
    .grid-view {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
}

/* Print optimization */
@media print {
    body {
        background: white;
    }
    
    #toolbar,
    #folderTree,
    .context-menu,
    .selection-box {
        display: none !important;
    }
    
    #container {
        height: auto;
        display: block;
    }
    
    #content {
        overflow: visible;
        height: auto;
    }
    
    .grid-view,
    .table-view {
        page-break-inside: avoid;
    }
}

/* Spinner style for path progress indicator */
#pathProgress .spinner {
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    animation: spin 1s linear infinite;
    display: inline-block;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Upload notification styling */
.notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    padding: 15px;
    border-radius: 4px;
    z-index: 1000;
    min-width: 300px;
    max-width: 400px;
}

.notification-title {
    font-weight: bold;
    margin-bottom: 10px;
}

.progress-bar {
    background-color: #ddd;
    height: 10px;
    border-radius: 5px;
    overflow: hidden;
}

.progress {
    background-color: #4CAF50;
    height: 100%;
    width: 0;
    transition: width 0.3s ease;
}

/* Indeterminate progress animation for downloads with unknown size */
.progress-indeterminate {
    height: 100%;
    width: 100%;
    background: linear-gradient(to right, #ddd 0%, #4CAF50 50%, #ddd 100%);
    background-size: 200% 100%;
    animation: progress-bar-indeterminate 1.5s infinite linear;
}

@keyframes progress-bar-indeterminate {
    0% { background-position: 200% 0; }
    100% { background-position: 0 0; }
}

/* Text Editor Modal Styles */
.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content.text-editor {
    background-color: #f9f9f9;
    margin: 5% auto;
    padding: 0;
    border: 1px solid #888;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    width: 70%;
    height: 70%;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.editor-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    background-color: #f0f0f0;
    border-bottom: 1px solid #ddd;
}

.editor-title {
    font-weight: bold;
    font-size: 1.2em;
}

.close-btn {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    background: none;
    border: none;
    cursor: pointer;
}

.close-btn:hover {
    color: black;
}

.editor-textarea {
    flex: 1;
    padding: 12px;
    font-family: Consolas, monospace;
    font-size: 14px;
    line-height: 1.5;
    border: none;
    resize: none;
}

.editor-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 10px 16px;
    background-color: #f0f0f0;
    border-top: 1px solid #ddd;
}

.save-btn, .cancel-btn {
    padding: 8px 16px;
    font-size: 14px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.save-btn {
    background-color: #4CAF50;
    color: white;
}

.save-btn:hover {
    background-color: #45a049;
}

.cancel-btn {
    background-color: #f44336;
    color: white;
}

.cancel-btn:hover {
    background-color: #d32f2f;
}

@media print {
    body {
        background: white;
    }
    
    #toolbar,
    #folderTree,
    .context-menu,
    .selection-box {
        display: none !important;
    }
    
    #container {
        height: auto;
        display: block;
    }
    
    #content {
        overflow: visible;
        height: auto;
    }
    
    .grid-view,
    .table-view {
        page-break-inside: avoid;
    }
}

// Convert SVG to data URL
function svgToDataURL(svg) {
    try {
        return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
    } catch (e) {
        console.error('SVG conversion error:', e);
        return '';
    }
}

.error-message {
    color: #ff3333;
    padding: 10px;
    background-color: #ffeeee;
    border-radius: 4px;
    text-align: center;
    margin: 10px 0;
}

/* Add print styles */
@media print {
</style>
</head>
<body>
<div id="toolbar">
    <div class="nav-buttons">
        <button onclick="goBack()" title="Back">⬅️</button>
        <button onclick="goForward()" title="Forward">➡️</button>
        <button onclick="refresh()" title="Refresh">🔄</button>
        <button onclick="newFolder()" title="New Folder">📁</button>
        <button onclick="toggleView()" title="Switch View">
            <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSIjMjE5NkYzIj48cmVjdCB4PSIzIiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiByeD0iMSIvPjxyZWN0IHg9IjE0IiB5PSIzIiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiByeD0iMSIvPjxyZWN0IHg9IjMiIHk9IjE0IiB3aWR0aD0iNyIgaGVpZ2h0PSI3IiByeD0iMSIvPjxyZWN0IHg9IjE0IiB5PSIxNCIgd2lkdGg9IjciIGhlaWdodD0iNyIgcng9IjEiLz48L2c+PC9zdmc+" alt="View Mode" style="width: 20px; height: 20px;">
        </button>
        <button onclick="location.href='?logout=1'" title="Logout">🔓</button>
    </div>
    <div class="address-search-container">
        <input type="text" id="pathBar" placeholder="Address">
        <span id="pathProgress" style="display:none;"></span>
        <input type="text" id="searchBar" placeholder="🔍 Search...">
    </div>
</div>

<div id="container">
    <div id="folderTree">
        <h3>This PC</h3>
        <ul id="drives"></ul>
        <h3>Clipboard</h3>
        <div id="clipboardContent" class="clipboard-section">
            <p class="clipboard-empty">Clipboard is empty</p>
        </div>
    </div>
    <div id="content">
        <div id="fileList" class="table-view">
            <table>
            <thead>
                <tr>
                    <th onclick="sortFiles('name')">Name</th>
                    <th onclick="sortFiles('size')">Size</th>
                    <th onclick="sortFiles('type')">Type</th>
                    <th onclick="sortFiles('modified')">Modified</th>
                </tr>
            </thead>
            <tbody id="fileListBody">
            </tbody>
        </table>
        </div>
    </div>
</div>

<div class="context-menu" id="contextMenu">
    <ul id="contextMenuList"></ul>
</div>

<script>
// Add SVG icons as constants at the top of script
const ICONS = {
    folder: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#FFC107" d="M20,6h-8l-2-2H4C2.9,4,2,4.9,2,6v12c0,1.1,0.9,2,2,2h16c1.1,0,2-0.9,2-2V8C22,6.9,21.1,6,20,6z"/></svg>`,
    
    text: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#607D8B" d="M14,2H6C4.9,2,4,2.9,4,4v16c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2V8L14,2z M16,18H8v-2h8V18z M16,14H8v-2h8V14z M13,9V3.5L18.5,9H13z"/></svg>`,
    
    video: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#F44336" d="M18,4v1h-2V4c0-0.55-0.45-1-1-1H9C8.45,3,8,3.45,8,4v1H6V4c0-1.1,0.9-2,2-2h7C16.1,2,17,2.9,17,4z M20,6H4C2.9,6,2,6.9,2,8v11c0,1.1,0.9,2,2,2h16c1.1,0,2-0.9,2-2V8C22,6.9,21.1,6,20,6z M15,16l-5-3l5-3V16z"/></svg>`,
    
    music: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#9C27B0" d="M12,3v10.55c-0.59-0.34-1.27-0.55-2-0.55c-2.21,0-4,1.79-4,4s1.79,4,4,4s4-1.79,4-4V7h4V3H12z"/></svg>`,
    
    image: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#4CAF50" d="M21,19V5c0-1.1-0.9-2-2-2H5c-1.1,0-2,0.9-2,2v14c0,1.1,0.9,2,2,2h14C20.1,21,21,20.1,21,19z M8.5,13.5l2.5,3.01L14.5,12l4.5,6H5l3.5-4.5z"/></svg>`,
    
    pdf: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#FF5722" d="M20,2H8C6.9,2,6,2.9,6,4v12c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2V4C22,2.9,21.1,2,20,2z M11.5,9.5c0,0.83-0.67,1.5-1.5,1.5H9v2H7.5V7H10C10.83,7,11.5,7.67,11.5,8.5V9.5z M16.5,11.5c0,0.83-0.67,1.5-1.5,1.5h-2.5V7H15c0.83,0,1.5,0.67,1.5,1.5V11.5z M20.5,8.5H19v1h1.5V11H19v2h-1.5V7h3V8.5z M9,9h1v1H9V9z M4,6H2v14c0,1.1,0.9,2,2,2h14v-2H4V6z M14,11h1V8h-1V11z"/></svg>`,
    
    word: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#2196F3" d="M19,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5C21,3.9,20.1,3,19,3z M15.5,17h-2v-2h2V17z M15.5,13h-2v-2h2V13z M15.5,9h-2V7h2V9z M9.5,17h-2v-2h2V17z M9.5,13h-2v-2h2V13z M9.5,9h-2V7h2V9z"/></svg>`,
    
    powerpoint: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#FF5722" d="M19,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5C21,3.9,20.1,3,19,3z M9.8,13.4V17H8V7h4.3c1.1,0,2,0.9,2,2v2.4c0,1.1-0.9,2-2,2H9.8z M9.8,8.6v3.2h2.5V8.6H9.8z"/></svg>`,
    
    excel: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#4CAF50" d="M19,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5C21,3.9,20.1,3,19,3z M9,17H7v-2h2V17z M9,13H7v-2h2V13z M9,9H7V7h2V9z M13,17h-2v-2h2V17z M13,13h-2v-2h2V13z M13,9h-2V7h2V9z M17,17h-2v-2h2V17z M17,13h-2v-2h2V13z M17,9h-2V7h2V9z"/></svg>`,
    
    drive: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill="#2196F3" d="M20,8h-3V4H3C1.9,4,1,4.9,1,6v12c0,1.1,0.9,2,2,2h18c1.1,0,2-0.9,2-2V10C23,8.9,22.1,8,20,8z M18,16H6v-2h12V16z M17,9H4V6h13V9z"/><circle fill="#ffffff" cx="15" cy="15" r="1"/></svg>`,
    
    gridView: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g fill="#2196F3"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></g></svg>`,
    
    listView: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><g fill="#2196F3"><rect x="3" y="4" width="18" height="3" rx="1"/><rect x="3" y="10.5" width="18" height="3" rx="1"/><rect x="3" y="17" width="18" height="3" rx="1"/></g></svg>`,
    
    windowsFolder: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path fill="#FFC107" d="M20,6h-8l-2-2H4C2.9,4,2,4.9,2,6v12c0,1.1,0.9,2,2,2h16c1.1,0,2-0.9,2-2V8C22,6.9,21.1,6,20,6z"/>
        <g transform="translate(4,8) scale(0.7)">
            <path fill="#00A4EF" d="M3,3h8v8H3V3z"/>
            <path fill="#F25022" d="M13,3h8v8h-8V3z"/>
            <path fill="#7FBA00" d="M3,13h8v8H3V13z"/>
            <path fill="#FFB900" d="M13,13h8v8h-8V13z"/>
        </g>
    </svg>`,

    programFilesFolder: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path fill="#FFC107" d="M20,6h-8l-2-2H4C2.9,4,2,4.9,2,6v12c0,1.1,0.9,2,2,2h16c1.1,0,2-0.9,2-2V8C22,6.9,21.1,6,20,6z"/>
        <g transform="translate(6,8) scale(0.6)">
            <rect fill="#2196F3" x="2" y="2" width="20" height="20" rx="2"/>
            <path fill="white" d="M15,10v8h-2v-6h-2v6H9v-8H7v10h10V10H15z"/>
        </g>
    </svg>`,

    usersFolder: `<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path fill="#FFC107" d="M20,6h-8l-2-2H4C2.9,4,2,4.9,2,6v12c0,1.1,0.9,2,2,2h16c1.1,0,2-0.9,2-2V8C22,6.9,21.1,6,20,6z"/>
        <g transform="translate(6,8) scale(0.6)">
            <circle fill="#2196F3" cx="12" cy="8" r="4"/>
            <path fill="#2196F3" d="M12,14c-4,0-8,2-8,6v2h16v-2C20,16,16,14,12,14z"/>
        </g>
    </svg>`
};

// Embedded JavaScript for dynamic functionality 🚀

// Global variables for copy/cut and selection
let clipboard = { action: '', sources: [] };
let currentPath = "";
let filesData = [];
let selectedFiles = new Set();
let lastSelectedIndex = -1;
let clipboardMonitoringActive = false;

// Add navigation history support
let navigationHistory = [];
let currentHistoryIndex = -1;

function addToHistory(path) {
    // Remove forward history when new path is added
    if (currentHistoryIndex < navigationHistory.length - 1) {
        navigationHistory = navigationHistory.slice(0, currentHistoryIndex + 1);
    }
    navigationHistory.push(path);
    currentHistoryIndex = navigationHistory.length - 1;
}

function goBack() {
    if (currentHistoryIndex > 0) {
        currentHistoryIndex--;
        loadFolder(navigationHistory[currentHistoryIndex], false);
    }
}

function goForward() {
    if (currentHistoryIndex < navigationHistory.length - 1) {
        currentHistoryIndex++;
        loadFolder(navigationHistory[currentHistoryIndex], false);
    }
}

// Load "This PC" view on start
window.onload = function() {
    // Always load drives in the left panel first
    loadDrives();
    
    // Check URL parameter for initial path
    const urlParams = new URLSearchParams(window.location.search);
    const pathParam = urlParams.get('path');
    
    if (pathParam) {
        // Try to navigate to the path from parameter
        validateAndNavigateInitial(pathParam);
    }

    // Start clipboard monitoring
    startClipboardMonitoring();

    const contentArea = document.getElementById('content');
    const fileList = document.getElementById('fileList');
    
    // Add empty space context menu
    contentArea.addEventListener('contextmenu', function(e) {
        if (!e.target.closest('.grid-item') && !e.target.closest('tr')) {
        e.preventDefault(); 
            showEmptyContextMenu(e.pageX, e.pageY);
        }
    });

    fileList.addEventListener('contextmenu', function(e) {
        if (!e.target.closest('.grid-item') && !e.target.closest('tr')) {
            e.preventDefault();
            showEmptyContextMenu(e.pageX, e.pageY);
        }
    });

    // Add drop handling for empty space
    [contentArea, fileList].forEach(element => {
        element.addEventListener('dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!e.target.closest('.grid-item') && !e.target.closest('tr')) {
                this.classList.add('drag-over');
            }
        });

        element.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!e.target.closest('.grid-item') && !e.target.closest('tr')) {
                e.dataTransfer.dropEffect = 'copy';
            }
        });

        element.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!e.target.closest('.grid-item') && !e.target.closest('tr')) {
                this.classList.remove('drag-over');
            }
        });

        element.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove('drag-over');
            
            // Check if this is an external file drop (from the user's system)
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                handleFileUpload(e.dataTransfer.files, currentPath);
                return;
            }
            
            // Handle internal drag and drop (files within the file manager)
            if (!e.target.closest('.grid-item') && !e.target.closest('tr')) {
                const source = e.dataTransfer.getData('text/plain');
                if(source) {
                    handleDrop(source, currentPath);
                }
            }
        });
    });

    // Search functionality
    document.getElementById('searchBar').addEventListener('keyup', function(){
        let q = this.value.toLowerCase();
        let filtered = filesData.filter(f => f.name.toLowerCase().includes(q));
        renderFileList(filtered);
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Prevent handling if in an input field or textarea (especially the text editor)
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        if (e.ctrlKey && e.key.toLowerCase() === 'a') {
            // Ctrl+A: Select all
            e.preventDefault();
            selectedFiles.clear();
            filesData.forEach(file => selectedFiles.add(file.path));
            renderFileList(filesData);
        } else if (e.key === 'Delete') {
            e.preventDefault();
            if (selectedFiles.size > 0) {
                deleteItems([...selectedFiles]);
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedFiles.size > 0) {
                // Only one item is selected, so open that.
                let [selected] = Array.from(selectedFiles);
                const fileData = filesData.find(f => f.path === selected);
                if (fileData) {
                    if (fileData.type === 'folder') {
                        loadFolder(selected);
                    } else {
                        openFile(selected);
                    }
                }
            }
        } else if (e.key === 'Escape') {
            e.preventDefault();
            selectedFiles.clear();
            updateSelectionStyles();
        } else if (e.ctrlKey && e.key.toLowerCase() === 'c') {
            e.preventDefault();
            if (selectedFiles.size > 0) {
                handleCopyOrCut('copy');
            }
        } else if (e.ctrlKey && e.key.toLowerCase() === 'x') {
            e.preventDefault();
            if (selectedFiles.size > 0) {
                handleCopyOrCut('cut');
            }
        } else if (e.ctrlKey && e.key.toLowerCase() === 'v') {
            e.preventDefault();
            pasteItems();
        }
    });

    // Add pathBar validation
    const pathBar = document.getElementById('pathBar');
    pathBar.addEventListener('keydown', function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            validateAndNavigate(this.value);
        }
    });

    pathBar.addEventListener('blur', function() {
        // Reset the path to current path if user leaves without pressing enter
        this.value = currentPath;
        this.classList.remove('error');
    });

    // Add selection box functionality
    let isSelecting = false;
    let selectionBox = null;
    let startX = 0;
    let startY = 0;
    let initialSelection = new Set();

    function createSelectionBox(e) {
        // Store initial selection state
        initialSelection = new Set(selectedFiles);
        
        // Create selection box
        selectionBox = document.createElement('div');
        selectionBox.className = 'selection-box';
        document.body.appendChild(selectionBox);
        
        // Set initial position
        startX = e.pageX;
        startY = e.pageY;
        selectionBox.style.left = startX + 'px';
        selectionBox.style.top = startY + 'px';
        selectionBox.style.width = '0px';
        selectionBox.style.height = '0px';
    }
    
    function updateSelectionBox(e) {
        if (!selectionBox) return;
        
        // Calculate dimensions
        const currentX = e.pageX;
        const currentY = e.pageY;
        const width = Math.abs(currentX - startX);
        const height = Math.abs(currentY - startY);
        
        // Update box position and size
        selectionBox.style.left = Math.min(startX, currentX) + 'px';
        selectionBox.style.top = Math.min(startY, currentY) + 'px';
        selectionBox.style.width = width + 'px';
        selectionBox.style.height = height + 'px';
        
        // Get selection box boundaries
        const boxRect = selectionBox.getBoundingClientRect();
        
        // Check which items are within the selection box
        const items = isGridView ? 
            document.querySelectorAll('.grid-item') : 
            document.querySelectorAll('.table-view tr:not(:first-child)');
        
        // Start with initial selection if Ctrl is pressed
        if (e.ctrlKey) {
            selectedFiles = new Set(initialSelection);
        } else {
            selectedFiles.clear();
        }
        
        items.forEach(item => {
            const itemRect = item.getBoundingClientRect();
            
            // Check if item intersects with selection box
            if (!(itemRect.right < boxRect.left || 
                itemRect.left > boxRect.right || 
                itemRect.bottom < boxRect.top || 
                itemRect.top > boxRect.bottom)) {
                selectedFiles.add(item.dataset.path);
            }
        });
        
        updateSelectionStyles();
    }
    
    function removeSelectionBox() {
        if (selectionBox) {
            selectionBox.remove();
            selectionBox = null;
        }
        isSelecting = false;
    }

    // Mouse down event
    content.addEventListener('mousedown', function(e) {
        const isClickingItem = e.target.closest('.grid-item') || e.target.closest('tr') || e.target.closest('th');
        
        if (!isClickingItem) {
            isSelecting = true;
            if (!e.ctrlKey) {
                selectedFiles.clear();
                updateSelectionStyles();
            }
            createSelectionBox(e);
            e.preventDefault();
        }
    });
    
    // Mouse move event
    content.addEventListener('mousemove', function(e) {
        if (isSelecting) {
            updateSelectionBox(e);
            e.preventDefault();
        }
    });
    
    // Mouse up event
    document.addEventListener('mouseup', function(e) {
        if (isSelecting) {
            removeSelectionBox();
            e.preventDefault();
        }
    });

    // Update the content click handler
    content.addEventListener('click', function(e) {
        const isClickingItem = e.target.closest('.grid-item') || e.target.closest('tr');
        
        // Only clear selection if clicking directly on empty space
        if (!isClickingItem && !e.ctrlKey && !e.shiftKey && !isSelecting) {
            selectedFiles.clear();
            updateSelectionStyles();
        }
    });

    // Add scroll handling during selection
    content.addEventListener('scroll', function(e) {
        if (isSelecting) {
            updateSelectionBox(lastMouseEvent);
        }
    });

    // Store last mouse event for scroll updates
    let lastMouseEvent;
    document.addEventListener('mousemove', function(e) {
        lastMouseEvent = e;
        if (isSelecting) {
            updateSelectionBox(e);
        }
    });

    // Add mousedown handler to fileList as well
    fileList.addEventListener('mousedown', function(e) {
        // Same conditions as content mousedown
        const isClickingItem = e.target.closest('.grid-item') || e.target.closest('tr') || e.target.closest('th');
        
        if (!isClickingItem || e.ctrlKey) {
            isSelecting = true;
            if (!e.ctrlKey && !e.shiftKey) {
                selectedFiles.clear();
            }
            createSelectionBox(e);
            
            // Prevent text selection
            e.preventDefault();
        }
    });

    initializeSelectionHandlers();
};

// Convert SVG to data URL
function svgToDataURL(svg) {
    try {
        return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
    } catch (e) {
        console.error('SVG conversion error:', e);
        return '';
    }
}

// Update getFileIcon function to use embedded SVGs
function getFileIcon(file) {
    if (file.type === 'folder') {
        // Check for special system folders
        const lowerPath = file.path.toLowerCase();
        if (lowerPath.includes('c:\\windows')) {
            return svgToDataURL(ICONS.windowsFolder);
        }
        if (lowerPath.includes('c:\\program files') || lowerPath.includes('c:\\program files (x86)')) {
            return svgToDataURL(ICONS.programFilesFolder);
        }
        if (lowerPath.includes('c:\\users')) {
            return svgToDataURL(ICONS.usersFolder);
        }
        return svgToDataURL(ICONS.folder);
    }
    
    const ext = file.name.split('.').pop().toLowerCase();
    
    // Map file extensions to icons
    const iconMap = {
        // Text files
        'txt': ICONS.text,
        'log': ICONS.text,
        'md': ICONS.text,
        
        // Video files
        'mp4': ICONS.video,
        'avi': ICONS.video,
        'mkv': ICONS.video,
        'mov': ICONS.video,
        'wmv': ICONS.video,
        
        // Audio files
        'mp3': ICONS.music,
        'wav': ICONS.music,
        'ogg': ICONS.music,
        'm4a': ICONS.music,
        
        // Image files
        'jpg': ICONS.image,
        'jpeg': ICONS.image,
        'png': ICONS.image,
        'gif': ICONS.image,
        'bmp': ICONS.image,
        'webp': ICONS.image,
        
        // Document files
        'pdf': ICONS.pdf,
        'doc': ICONS.word,
        'docx': ICONS.word,
        'ppt': ICONS.powerpoint,
        'pptx': ICONS.powerpoint,
        'xls': ICONS.excel,
        'xlsx': ICONS.excel
    };
    
    return svgToDataURL(iconMap[ext] || ICONS.text);
}

// Add view state variable
let isGridView = false;

function toggleView() {
    isGridView = !isGridView;
    const fileList = document.getElementById('fileList');
    const viewButton = document.querySelector('[onclick="toggleView()"] img');
    viewButton.src = svgToDataURL(isGridView ? ICONS.listView : ICONS.gridView);
    
    if (isGridView) {
        fileList.className = 'grid-view';
        fileList.innerHTML = '';
    } else {
        fileList.className = 'table-view';
        fileList.innerHTML = `
            <table>
                <thead>
                    <tr>
                        <th onclick="sortFiles('name')">Name</th>
                        <th onclick="sortFiles('size')">Size</th>
                        <th onclick="sortFiles('type')">Type</th>
                        <th onclick="sortFiles('modified')">Modified</th>
                    </tr>
                </thead>
                <tbody id="fileListBody"></tbody>
            </table>
        `;
    }
    renderFileList(filesData);
}

function renderFileList(files) {
    if (isGridView) {
        const fileList = document.getElementById('fileList');
        fileList.innerHTML = '';
        files.forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'grid-item' + (selectedFiles.has(file.path) ? ' selected' : '');
            div.draggable = true;
            div.dataset.path = file.path;
            div.dataset.type = file.type;

            // Drag event
            div.addEventListener('dragstart', function(e) {
                e.stopPropagation();
                if(selectedFiles.has(file.path)) {
                    e.dataTransfer.setData('text/plain', JSON.stringify([...selectedFiles]));
                } else {
                    e.dataTransfer.setData('text/plain', JSON.stringify([file.path]));
                    selectedFiles.clear();
                    selectedFiles.add(file.path);
                    renderFileList(filesData);
                }
                e.dataTransfer.effectAllowed = 'copyMove';
                this.classList.add('dragging');
            });

            div.addEventListener('dragend', function() {
                this.classList.remove('dragging');
                document.querySelectorAll('.drop-target').forEach(el => el.classList.remove('drop-target'));
            });

            // Add drop handling for folders
            if (file.type === 'folder') {
                div.addEventListener('dragenter', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Get the actual folder item, not its children
                    const folderItem = e.target.closest('.grid-item, tr');
                    if (folderItem && !folderItem.classList.contains('dragging')) {
                        // Remove drop-target from all other items
                        document.querySelectorAll('.drop-target').forEach(el => el.classList.remove('drop-target'));
                        folderItem.classList.add('drop-target');
                    }
                });

                div.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const folderItem = e.target.closest('.grid-item, tr');
                    if (folderItem && !folderItem.classList.contains('dragging')) {
                        e.dataTransfer.dropEffect = 'copy';
                    }
                });

                div.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Only remove drop-target if we're leaving the folder item itself
                    const folderItem = e.target.closest('.grid-item, tr');
                    const relatedTarget = e.relatedTarget?.closest('.grid-item, tr');
                    
                    if (folderItem && !relatedTarget) {
                        folderItem.classList.remove('drop-target');
                    }
                });

                div.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const folderItem = e.target.closest('.grid-item, tr');
                    if (folderItem) {
                        folderItem.classList.remove('drop-target');
                        
                        // Check if this is an external file drop (from user's system)
                        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                            handleFileUpload(e.dataTransfer.files, folderItem.dataset.path);
                            return;
                        }
                        
                        // Handle internal drag and drop
                        const source = e.dataTransfer.getData('text/plain');
                        if(source) {
                            const targetPath = folderItem.dataset.path;
                            handleDrop(source, targetPath);
                        }
                    }
                });
            }

            // Click event
            div.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent event from bubbling to container
                handleFileSelection(file, index, e);
            });

            // Double click
            div.ondblclick = () => {
                if(file.type === 'folder') {
                    loadFolder(file.path);
                } else {
                    openFile(file.path);
                }
            };

            // Context menu
            div.oncontextmenu = (e) => {
                e.preventDefault();
                if(!selectedFiles.has(file.path)) {
                    selectedFiles.clear();
                    selectedFiles.add(file.path);
                    renderFileList(filesData);
                }
                showContextMenu(e.pageX, e.pageY);
            };

            const iconSrc = getFileIcon(file);
            div.innerHTML = `
                <img src="${iconSrc}" class="file-icon" alt="${file.type} icon">
                <div class="file-name">${file.name}</div>
                ${file.type !== 'folder' ? `<div class="file-size">${formatBytes(file.size)}</div>` : ''}
            `;
            
            fileList.appendChild(div);
        });
    } else {
    const tbody = document.getElementById('fileListBody');
        if (!tbody) return;
        
    tbody.innerHTML = '';
        files.forEach((file, index) => {
        let tr = document.createElement('tr');
        tr.draggable = true;
            tr.dataset.path = file.path;
            tr.dataset.type = file.type;
            
            if(selectedFiles.has(file.path)) {
                tr.classList.add('selected');
            }
            
            // Drag event handler
            tr.addEventListener('dragstart', function(e) {
                e.stopPropagation();
                if(selectedFiles.has(file.path)) {
                    e.dataTransfer.setData('text/plain', JSON.stringify([...selectedFiles]));
                } else {
                    e.dataTransfer.setData('text/plain', JSON.stringify([file.path]));
                    selectedFiles.clear();
                    selectedFiles.add(file.path);
                    renderFileList(filesData);
                }
                e.dataTransfer.effectAllowed = 'copyMove';
                this.classList.add('dragging');
            });

            tr.addEventListener('dragend', function() {
                this.classList.remove('dragging');
                document.querySelectorAll('.drop-target').forEach(el => el.classList.remove('drop-target'));
            });

            // Add drop handling for folders
            if (file.type === 'folder') {
                tr.addEventListener('dragenter', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const folderRow = e.target.closest('tr');
                    if (folderRow && !folderRow.classList.contains('dragging')) {
                        document.querySelectorAll('.drop-target').forEach(el => el.classList.remove('drop-target'));
                        folderRow.classList.add('drop-target');
                    }
                });

                tr.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const folderRow = e.target.closest('tr');
                    if (folderRow && !folderRow.classList.contains('dragging')) {
                        e.dataTransfer.dropEffect = 'copy';
                    }
                });

                tr.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const folderRow = e.target.closest('tr');
                    const relatedTarget = e.relatedTarget?.closest('tr');
                    
                    if (folderRow && !relatedTarget) {
                        folderRow.classList.remove('drop-target');
                    }
                });

                tr.addEventListener('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const folderRow = e.target.closest('tr');
                    if (folderRow) {
                        folderRow.classList.remove('drop-target');
                        
                        // Check if this is an external file drop (from user's system)
                        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                            handleFileUpload(e.dataTransfer.files, folderRow.dataset.path);
                            return;
                        }
                        
                        // Handle internal drag and drop
                        const source = e.dataTransfer.getData('text/plain');
                        if(source) {
                            const targetPath = folderRow.dataset.path;
                            handleDrop(source, targetPath);
                        }
                    }
                });
            }

            // Click event handler
        tr.addEventListener('click', function(e) {
                e.stopPropagation(); // Prevent event from bubbling to container
                handleFileSelection(file, index, e);
        });
        
            // Double click handler
        tr.ondblclick = () => {
            if(file.type === 'folder'){
                loadFolder(file.path);
            } else {
                openFile(file.path);
            }
        };
        
            // Context menu handler
        tr.oncontextmenu = (e) => {
            e.preventDefault();
                if(!selectedFiles.has(file.path)) {
                    selectedFiles.clear();
                    selectedFiles.add(file.path);
                    renderFileList(filesData);
                }
                showContextMenu(e.pageX, e.pageY);
            };
            
            const iconSrc = getFileIcon(file);
            tr.innerHTML = `
                <td>
                    <div class="file-name">
                        <img src="${iconSrc}" class="file-icon" alt="${file.type} icon">
                        ${file.name}
                    </div>
                </td>
                <td>${file.type==='folder'?'':formatBytes(file.size)}</td>
                <td>${file.type}</td>
                <td>${new Date(file.modified * 1000).toLocaleString()}</td>
            `;
        tbody.appendChild(tr);
    });
    }
}

function handleFileSelection(file, index, event) {
    event.stopPropagation();
    
    if (event.ctrlKey) {
        // Toggle selection with Ctrl key
        if (selectedFiles.has(file.path)) {
            selectedFiles.delete(file.path);
        } else {
            selectedFiles.add(file.path);
        }
    } else if (event.shiftKey && lastSelectedIndex !== -1) {
        // Shift key for range selection
        const start = Math.min(lastSelectedIndex, index);
        const end = Math.max(lastSelectedIndex, index);
        
        if (!event.ctrlKey) {
            selectedFiles.clear();
        }
        
        for (let i = start; i <= end; i++) {
            if (filesData[i]) {
                selectedFiles.add(filesData[i].path);
            }
        }
    } else {
        // Normal click - clear selection and select only clicked item
        selectedFiles.clear();
        selectedFiles.add(file.path);
    }
    
    lastSelectedIndex = index;
    updateSelectionStyles();
}

function openFile(path) {
    // Check if it's a text file
    const fileName = path.split('\\').pop();
    const isTextFile = fileName.toLowerCase().endsWith('.txt');
    
    if (isTextFile) {
        openTextEditor(path);
    } else {
        // All other files open in a new tab using the download action
        window.open('?action=download&path=' + encodeURIComponent(path), '_blank');
    }
}

function openTextEditor(path) {
    // Fetch the text file content
    fetchWithEncoding('?action=viewText&path=' + encodeURIComponent(path))
    .then(response => response.text())
    .then(content => {
        // Create editor modal
        const modal = document.createElement('div');
        modal.className = 'modal';
        
        const fileName = path.split('\\').pop();
        
        modal.innerHTML = `
            <div class="modal-content text-editor">
                <div class="editor-header">
                    <span class="editor-title">${fileName}</span>
                    <button class="close-btn">×</button>
                </div>
                <textarea class="editor-textarea">${content}</textarea>
                <div class="editor-footer">
                    <button class="save-btn">💾 Save</button>
                    <button class="cancel-btn">❌ Cancel</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Focus the textarea
        const textarea = modal.querySelector('.editor-textarea');
        textarea.focus();
        
        // Close button event
        modal.querySelector('.close-btn').onclick = function() {
            document.body.removeChild(modal);
        };
        
        // Cancel button event
        modal.querySelector('.cancel-btn').onclick = function() {
            document.body.removeChild(modal);
        };
        
        // Save button event
        modal.querySelector('.save-btn').onclick = function() {
            const newContent = textarea.value;
            
            fetchWithEncoding('?action=save_text_file', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: 'path=' + encodeURIComponent(path) + '&content=' + encodeURIComponent(newContent)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.body.removeChild(modal);
                    refresh(); // Refresh to update file size, etc.
                } else {
                    alert('Error saving file: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error saving file: ' + error);
            });
        };
        
        // Close when clicking outside the modal content
        modal.onclick = function(e) {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        };
    })
    .catch(error => {
        alert('Error opening file: ' + error);
    });
}

function showContextMenu(x, y) {
    const menu = document.getElementById('contextMenu');
    const menuList = document.getElementById('contextMenuList');
    menuList.innerHTML = '';

    const selectedFilesArray = [...selectedFiles];
    const allFolders = selectedFilesArray.every(path => 
        filesData.find(f => f.path === path)?.type === 'folder'
    );
    const allFiles = selectedFilesArray.every(path => 
        filesData.find(f => f.path === path)?.type === 'file'
    );

    if(selectedFilesArray.length === 1) {
        const file = filesData.find(f => f.path === selectedFilesArray[0]);
        if(file.type === 'folder') {
            addMenuItem(menuList, '📂 Open', () => { loadFolder(file.path); });
            addMenuItem(menuList, '📥 Upload from URL', () => { downloadFromURL(file.path); });
        } else {
            addMenuItem(menuList, '📄 Open', () => { openFile(file.path); });
        }
    }

    addMenuItem(menuList, `📋 Copy ${selectedFilesArray.length} item(s)`, () => {
        handleCopyOrCut('copy');
    });
    addMenuItem(menuList, `✂️ Cut ${selectedFilesArray.length} item(s)`, () => {
        handleCopyOrCut('cut');
    });
    addMenuItem(menuList, `🗑️ Delete ${selectedFilesArray.length} item(s)`, () => {
        deleteItems(selectedFilesArray);
    });
    if(selectedFilesArray.length === 1) {
        addMenuItem(menuList, '✏️ Rename', () => { renameItem(selectedFilesArray[0]); });
        addMenuItem(menuList, 'ℹ️ Properties', () => { showProperties(selectedFilesArray[0]); });
    }

    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
    menu.style.display = 'block';
}

window.onclick = function() {
    document.getElementById('contextMenu').style.display = 'none';
};

function addMenuItem(menuList, text, callback, disabled = false) {
    let li = document.createElement('li');
    li.textContent = text;
    if(!disabled && callback) {
        li.onclick = (e) => {
            e.stopPropagation();
            callback();
            document.getElementById('contextMenu').style.display = 'none';
        };
    }
    if(disabled) {
        li.classList.add('disabled');
    }
    menuList.appendChild(li);
}

function deleteItems(paths) {
    if(confirm('Are you sure you want to delete ' + paths.length + ' item(s)?')) {
        // Second confirmation for extra safety
        if(confirm('⚠️ WARNING: This action cannot be undone! Confirm deletion of ' + paths.length + ' item(s)?')) {
            let completed = 0;
            let failed = 0;
            
            paths.forEach(path => {
                fetchWithEncoding('?action=delete', {
                method: 'POST',
                    headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
                body: 'target=' + encodeURIComponent(path)
            })
            .then(response => response.json())
            .then(data => {
                    if(data.status === 'success') {
                        completed++;
                } else {
                        failed++;
                    }
                    if(completed + failed === paths.length) {
                        if(failed > 0) {
                            alert(failed + ' item(s) could not be deleted');
                        }
                        refresh();
                    }
                });
            });
        }
    }
}

function renameItem(path) {
    const oldName = path.split('\\').pop(); // Get just the name part
    let newName = prompt('Enter new name for ' + oldName + ':');
    if(newName) {
        // Construct new path by combining current directory with new name
        const newPath = path.substring(0, path.lastIndexOf('\\') + 1) + newName;
        
        fetchWithEncoding('?action=rename', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
            body: 'old=' + encodeURIComponent(path) + '&new=' + encodeURIComponent(newPath)
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                refresh();
            } else {
                alert(data.message);
            }
        });
    }
}

function newFolder() {
    let folderName = prompt('Enter new folder name:');
    if(folderName){
        fetchWithEncoding('?action=new_folder', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: 'path=' + encodeURIComponent(currentPath) + '&name=' + encodeURIComponent(folderName)
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success'){
                refresh();
            } else {
                alert(data.message);
            }
        });
    }
}

function newTextFile() {
    let fileName = prompt('Enter new text file name:');
    if (fileName) {
        // Add .txt extension if not present
        if (!fileName.toLowerCase().endsWith('.txt')) {
            fileName += '.txt';
        }
        
        fetchWithEncoding('?action=new_text_file', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: 'path=' + encodeURIComponent(currentPath) + '&name=' + encodeURIComponent(fileName)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                refresh();
            } else {
                alert(data.message);
            }
        });
    }
}

function refresh() {
    if(currentPath){
        loadFolder(currentPath);
    } else {
        loadDrives();
    }
}

function sortFiles(criteria) {
    filesData.sort((a, b) => {
        if(a[criteria] < b[criteria]) return -1;
        if(a[criteria] > b[criteria]) return 1;
        return 0;
    });
    renderFileList(filesData);
}

function formatBytes(bytes) {
    if(bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Paste operation used by clipboard as well as context menu paste
function pasteItems() {
    // First check internal clipboard
    if(clipboard.sources && clipboard.sources.length > 0) {
        performPasteOperation(clipboard.sources, clipboard.action);
    } 
    // Then try to read from system clipboard if our clipboard is empty
    else if (navigator.clipboard && navigator.clipboard.readText) {
        navigator.clipboard.readText()
            .then(clipText => {
                if (clipText) {
                    // Try to parse clipboard content as file paths
                    const paths = clipText.split('\n').filter(path => path.trim() && /^[A-Za-z]:\\/.test(path.trim()));
                    if (paths.length > 0) {
                        performPasteOperation(paths, 'copy');
                    }
                }
            })
            .catch(err => {
                console.warn('Unable to read from clipboard:', err);
            });
    }
}

function performPasteOperation(sources, action) {
    let completed = 0;
    let failed = 0;
    const totalOperations = sources.length;
    
    const finishOperation = () => {
        if(completed + failed === totalOperations) {
            if(failed > 0) {
                alert(failed + ' item(s) could not be ' + action + 'ed');
            }
            if(action === 'cut') {
                clipboard = { action: '', sources: [] };
                updateClipboardDisplay();
            }
            refresh(); // Refresh after all operations are complete
        }
    };
    
    sources.forEach(source => {
        const destination = currentPath + '\\' + source.split('\\').pop();
        const operationAction = action === 'copy' ? 'copy' : 'move';
        
        fetchWithEncoding('?action=' + operationAction, {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
            body: 'source=' + encodeURIComponent(source) + '&destination=' + encodeURIComponent(destination)
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                completed++;
            } else {
                failed++;
            }
            finishOperation();
        })
        .catch(() => {
            failed++;
            finishOperation();
        });
    });
}

// Function to attempt monitoring system clipboard
function startClipboardMonitoring() {
    if (clipboardMonitoringActive || !navigator.clipboard) {
        return; // Already monitoring or not supported
    }

    try {
        // Set up periodic check for clipboard changes
        let lastClipboardContent = "";
        
        const checkClipboard = async () => {
            try {
                const newContent = await navigator.clipboard.readText();
                if (newContent !== lastClipboardContent) {
                    lastClipboardContent = newContent;
                    // Check if the clipboard contains file paths
                    const paths = newContent.split('\n').filter(path => path.trim() && /^[A-Za-z]:\\/.test(path.trim()));
                    if (paths.length > 0) {
                        // Update our internal clipboard to match system clipboard
                        clipboard = { action: 'copy', sources: paths };
                        updateClipboardDisplay();
                    }
                }
            } catch (e) {
                console.warn('Clipboard check failed:', e);
            }
        };

        // Check clipboard every 2 seconds
        const clipboardInterval = setInterval(checkClipboard, 2000);
        clipboardMonitoringActive = true;
        
        // Initial check
        checkClipboard();
        
        console.log('Clipboard monitoring started');
        
        // Stop monitoring if page is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearInterval(clipboardInterval);
                clipboardMonitoringActive = false;
            } else {
                startClipboardMonitoring();
            }
        });
    } catch (e) {
        console.warn('Unable to monitor clipboard:', e);
    }
}

// Update handleDrop function to handle both folder and empty space drops
function handleDrop(sourcesJson, targetPath) {
    let sources;
    try {
        sources = JSON.parse(sourcesJson);
        if (!Array.isArray(sources)) {
            sources = [sources];
        }
    } catch (e) {
        console.error('Error parsing drag sources:', e);
        return;
    }
    
    let completed = 0;
    let failed = 0;
    
    sources.forEach(source => {
        // Skip if trying to drop into itself
        if (source === targetPath) {
            failed++;
            return;
        }

        // Skip if trying to drop into a subfolder of itself
        if (targetPath.startsWith(source + '\\')) {
            failed++;
            return;
        }

        const destination = targetPath + '\\' + (typeof source === 'string' ? source.split('\\').pop() : '');
        
        // Get drive letters to compare
        const sourceDrive = source.substring(0, 1).toUpperCase();
        const destDrive = targetPath.substring(0, 1).toUpperCase();
        
        // If same drive, move; if different drives, copy
        const action = sourceDrive === destDrive ? 'move' : 'copy';
        
        fetchWithEncoding('?action=' + action, {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
        body: 'source=' + encodeURIComponent(source) + '&destination=' + encodeURIComponent(destination)
    })
    .then(response => response.json())
    .then(data => { 
            if(data.status === 'success') {
                completed++;
                if(action === 'copy' && completed + failed === sources.length) {
                    if(confirm('Files were copied. Do you want to delete the originals?')) {
                        // Add second confirmation for deletion of originals
                        if(confirm('⚠️ WARNING: This action cannot be undone! Confirm deletion of original files?')) {
                            deleteItems(sources);
                        } else {
                            refresh();
                        }
                    } else {
                        refresh();
                    }
                }
            } else {
                failed++;
                console.error('Failed to', action, 'item:', data.message);
            }
            if(completed + failed === sources.length) {
                if(failed > 0) {
                    alert(failed + ' item(s) could not be ' + action + 'd');
                }
                refresh();
            }
        })
        .catch(error => {
            failed++;
            console.error('Error during', action, ':', error);
            if(completed + failed === sources.length) {
                alert('Some items could not be processed');
                refresh();
            }
        });
    });
}

function showProperties(path){
    fetchWithEncoding('?action=get_properties&path=' + encodeURIComponent(path))
    .then(response => response.json())
    .then(data => { 
        if(data.status==='success'){
            let props = data.data;
            alert('Name: ' + props.name + '\nPath: ' + props.path + '\nType: ' + props.type + '\nSize: ' + formatBytes(props.size) + '\nModified: ' + new Date(props.modified*1000).toLocaleString());
        } else { 
            alert(data.message);
        }
    });
}

// Add new function for empty space context menu
function showEmptyContextMenu(x, y) {
    const menu = document.getElementById('contextMenu');
    const menuList = document.getElementById('contextMenuList');
    menuList.innerHTML = '';
    addMenuItem(menuList, '📁 New Folder', newFolder);
    addMenuItem(menuList, '📄 New Text File', newTextFile);
    addMenuItem(menuList, '📥 Download from URL', () => { downloadFromURL(currentPath); });
    
    // Add paste item with disabled state if clipboard is empty
    if(clipboard.sources && clipboard.sources.length > 0) {
        addMenuItem(menuList, `📋 Paste ${clipboard.sources.length} item(s)`, pasteItems);
    } else {
        addMenuItem(menuList, '📋 Paste', null, true); // disabled state
    }
    
    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
    menu.style.display = 'block';
}

function validateAndNavigate(path) {
    const pathBar = document.getElementById('pathBar');
    
    // Check if it's a drive letter
    if(/^[A-Za-z]:$/.test(path)) {
        path += '\\';
    }

    // First validate the path format
    if(!/^[A-Za-z]:\\/.test(path)) {
        pathBar.classList.add('error');
        return;
    }

    // Try to navigate to the path
    fetchWithEncoding('?action=list&path=' + encodeURIComponent(path))
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            pathBar.classList.remove('error');
            loadFolder(path);
        } else {
            pathBar.classList.add('error');
        }
    })
    .catch(() => {
        pathBar.classList.add('error');
    });
}

function validateAndNavigateInitial(path) {
    // Check if it's a drive letter
    if(/^[A-Za-z]:$/.test(path)) {
        path += '\\';
    }

    // First validate the path format
    if(!/^[A-Za-z]:\\/.test(path)) {
        loadDrives(); // Invalid path, load default
        return;
    }

    // Try to navigate to the path from URL parameter
    fetchWithEncoding('?action=list&path=' + encodeURIComponent(path))
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            loadFolder(path);
        } else {
            loadDrives(); // Path not found, load default
        }
    })
    .catch(() => {
        loadDrives(); // Error loading path, load default
    });
}

// Update clipboard display whenever it changes
function updateClipboardDisplay() {
    const clipboardContent = document.getElementById('clipboardContent');
    if (!clipboard.sources || clipboard.sources.length === 0) {
        clipboardContent.innerHTML = '<p class="clipboard-empty">Clipboard is empty</p>';
        return;
    }

    let html = `<div class="clipboard-action ${clipboard.action}">${clipboard.action.toUpperCase()}</div>`;
    clipboard.sources.forEach(path => {
        const file = filesData.find(f => f.path === path) || {
            name: path.split('\\').pop(),
            type: path.includes('.') ? 'file' : 'folder'
        };
        const iconSrc = getFileIcon(file);
        html += `
            <div class="clipboard-item">
                <img src="${iconSrc}" class="file-icon" alt="${file.type} icon">
                <span class="file-name" title="${file.name}">${file.name}</span>
            </div>
        `;
    });
    clipboardContent.innerHTML = html;
}

// Add UTF-8 encoding to all fetch requests
function fetchWithEncoding(url, options = {}) {
    if (!options.headers) {
        options.headers = {};
    }
    options.headers['Accept-Charset'] = 'UTF-8';
    return fetch(url, options);
}

function loadDrives() {
    console.log("Loading drives...");
    fetchWithEncoding('?action=list&path=ThisPC')
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            console.log("Drive data received:", data.data);
            const drivesList = document.getElementById('drives');
            drivesList.innerHTML = '';
            if(data.data && data.data.length > 0) {
                data.data.forEach(drive => {
                    try {
                        const usedPercentage = (drive.used / drive.total) * 100;
                        let barClass = 'drive-bar';
                        if (usedPercentage > 90) {
                            barClass += ' danger';
                        } else if (usedPercentage > 70) {
                            barClass += ' warning';
                        }

                        let li = document.createElement('li');
                        li.innerHTML = `
                            <div class="drive-info">
                                <img src="${svgToDataURL(ICONS.drive)}" class="drive-icon" alt="Drive icon">
                                <span class="drive-name">${drive.name}</span>
                            </div>
                            <div class="drive-bar-container">
                                <div class="${barClass}" style="width: ${usedPercentage}%"></div>
                            </div>
                            <div class="drive-space">
                                ${formatBytes(drive.used)} used of ${formatBytes(drive.total)}
                            </div>
                        `;
                        li.onclick = () => {
                            currentPath = drive.path;
                            loadFolder(drive.path);
                        };
                        drivesList.appendChild(li);
                    } catch(e) {
                        console.error("Error rendering drive:", drive, e);
                    }
                });
            } else {
                console.error("No drives found in the data");
                drivesList.innerHTML = '<li class="error-message">No drives available</li>';
            }
        } else {
            console.error("Error loading drives:", data);
        }
    })
    .catch(error => {
        console.error("Failed to load drives:", error);
        document.getElementById('drives').innerHTML = '<li class="error-message">Failed to load drives</li>';
    });
}

function loadFolder(path, addHistory = true) {
    if (addHistory) {
        addToHistory(path);
    }
    
    // Update URL parameter without reloading the page
    const url = new URL(window.location.href);
    
    if (path === 'ThisPC') {
        // Clear path parameter if navigating to "This PC"
        url.searchParams.delete('path');
    } else {
        // Set path parameter
        url.searchParams.set('path', path);
    }
    
    // Update the URL without reloading the page
    window.history.pushState({}, '', url);
    
    currentPath = path;
    const pathBar = document.getElementById('pathBar');
    pathBar.value = path;
    pathBar.classList.remove('error');
    const progress = document.getElementById('pathProgress');
    progress.style.display = 'inline-block';
    progress.innerHTML = '<span class="spinner"></span>';
    fetchWithEncoding('?action=list&path=' + encodeURIComponent(path))
    .then(response => {
         if (!response.ok) {
             throw new Error('Network response was not ok');
         }
         return response.text().then(text => {
             try {
                 return JSON.parse(text);
             } catch (e) {
                 console.error('Failed to parse JSON:', text);
                 throw new Error('Invalid JSON response from server');
             }
         });
    })
    .then(data => {
        progress.style.display = 'none';
        if(data.status === 'success'){
            filesData = data.data;
            renderFileList(data.data);
            selectedFiles.clear();
        } else {
            alert('Error: ' + data.message);
            console.error('Server error:', data);
            // Reset the file list to empty on error
            filesData = [];
            renderFileList([]);
        }
    })
    .catch(error => {
        progress.style.display = 'none';
        alert('Failed to load folder contents: ' + error.message);
        console.error('Error loading folder:', error);
        // Reset the file list to empty on error
        filesData = [];
        renderFileList([]);
    });
}

// Add the following new function below updateSelectionBox (or at an appropriate location in the script):
function updateSelectionStyles() {
    if (isGridView) {
        const items = document.querySelectorAll('.grid-item');
        items.forEach(item => {
            const path = item.dataset.path;
            if (selectedFiles.has(path)) {
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
    } else {
        const rows = document.querySelectorAll('.table-view tr:not(:first-child)');
        rows.forEach(row => {
            const path = row.dataset.path;
            if (selectedFiles.has(path)) {
                row.classList.add('selected');
            } else {
                row.classList.remove('selected');
            }
        });
    }
}

// Add this new function after the clipboard functions
function animateToClipboard(items, action) {
    const clipboardSection = document.getElementById('clipboardContent');
    const clipboardRect = clipboardSection.getBoundingClientRect();
    const targetX = clipboardRect.left + clipboardRect.width / 2;
    const targetY = clipboardRect.top + clipboardRect.height / 2;
    
    // Create flying items with slight delay between each
    items.forEach((path, index) => {
        const file = filesData.find(f => f.path === path) || {
            name: path.split('\\').pop(),
            type: path.includes('.') ? 'file' : 'folder'
        };
        
        // Find the source element
        const sourceElement = document.querySelector(`[data-path="${CSS.escape(path)}"]`);
        if (!sourceElement) return;
        
        const sourceRect = sourceElement.getBoundingClientRect();
        const iconSrc = getFileIcon(file);
        
        // Create flying item
        const flyingItem = document.createElement('div');
        flyingItem.className = 'flying-item';
        flyingItem.innerHTML = `<img src="${iconSrc}" alt="${file.type}">`;
        
        // Set initial position
        flyingItem.style.left = sourceRect.left + 'px';
        flyingItem.style.top = sourceRect.top + 'px';
        document.body.appendChild(flyingItem);
        
        // Trigger animation with delay
        setTimeout(() => {
            // Add scale down animation
            flyingItem.style.animation = 'scaleDown 0.5s forwards';
            
            // Move to clipboard
            flyingItem.style.left = targetX + 'px';
            flyingItem.style.top = targetY + 'px';
            
            // Remove the element after animation
            setTimeout(() => {
                flyingItem.remove();
                // Update clipboard after last animation
                if (index === items.length - 1) {
                    clipboard = { action, sources: items };
                    updateClipboardDisplay();
                }
            }, 500);
        }, index * 100); // 100ms delay between each item
    });
}

// Update the copy/cut handlers to use animation
function handleCopyOrCut(action) {
    const selectedFilesArray = [...selectedFiles];
    if (selectedFilesArray.length > 0) {
        // Update internal clipboard
        animateToClipboard(selectedFilesArray, action);
        
        // If possible, also update system clipboard with file paths
        try {
            const textData = selectedFilesArray.join('\n');
            navigator.clipboard.writeText(textData).then(() => {
                console.log('File paths copied to system clipboard');
            }).catch(err => {
                console.warn('Could not copy to system clipboard:', err);
            });
        } catch (e) {
            console.warn('System clipboard API not available:', e);
        }
    }
}

// Simplified selection handling
function initializeSelectionHandlers() {
    const content = document.getElementById('content');
    let isSelecting = false;
    let selectionBox = null;
    let startX = 0;
    let startY = 0;
    let initialSelection = new Set();

    function createSelectionBox(e) {
        // Store initial selection state
        initialSelection = new Set(selectedFiles);
        
        // Create selection box
        selectionBox = document.createElement('div');
        selectionBox.className = 'selection-box';
        document.body.appendChild(selectionBox);
        
        // Set initial position
        startX = e.pageX;
        startY = e.pageY;
        selectionBox.style.left = startX + 'px';
        selectionBox.style.top = startY + 'px';
        selectionBox.style.width = '0px';
        selectionBox.style.height = '0px';
    }
    
    function updateSelectionBox(e) {
        if (!selectionBox) return;
        
        // Calculate dimensions
        const currentX = e.pageX;
        const currentY = e.pageY;
        const width = Math.abs(currentX - startX);
        const height = Math.abs(currentY - startY);
        
        // Update box position and size
        selectionBox.style.left = Math.min(startX, currentX) + 'px';
        selectionBox.style.top = Math.min(startY, currentY) + 'px';
        selectionBox.style.width = width + 'px';
        selectionBox.style.height = height + 'px';
        
        // Get selection box boundaries
        const boxRect = selectionBox.getBoundingClientRect();
        
        // Check which items are within the selection box
        const items = isGridView ? 
            document.querySelectorAll('.grid-item') : 
            document.querySelectorAll('.table-view tr:not(:first-child)');
        
        // Start with initial selection if Ctrl is pressed
        if (e.ctrlKey) {
            selectedFiles = new Set(initialSelection);
        } else {
            selectedFiles.clear();
        }
        
        items.forEach(item => {
            const itemRect = item.getBoundingClientRect();
            
            // Check if item intersects with selection box
            if (!(itemRect.right < boxRect.left || 
                itemRect.left > boxRect.right || 
                itemRect.bottom < boxRect.top || 
                itemRect.top > boxRect.bottom)) {
                selectedFiles.add(item.dataset.path);
            }
        });
        
        updateSelectionStyles();
    }
    
    function removeSelectionBox() {
        if (selectionBox) {
            selectionBox.remove();
            selectionBox = null;
        }
        isSelecting = false;
    }

    // Mouse down event
    content.addEventListener('mousedown', function(e) {
        const isClickingItem = e.target.closest('.grid-item') || e.target.closest('tr') || e.target.closest('th');
        
        if (!isClickingItem) {
            isSelecting = true;
            if (!e.ctrlKey) {
                selectedFiles.clear();
                updateSelectionStyles();
            }
            createSelectionBox(e);
            e.preventDefault();
        }
    });
    
    // Mouse move event
    content.addEventListener('mousemove', function(e) {
        if (isSelecting) {
            updateSelectionBox(e);
            e.preventDefault();
        }
    });
    
    // Mouse up event
    document.addEventListener('mouseup', function(e) {
        if (isSelecting) {
            removeSelectionBox();
            e.preventDefault();
        }
    });
}

// Handle file uploads via drag and drop
function handleFileUpload(files, targetPath) {
    if (!files || files.length === 0) return;
    
    const formData = new FormData();
    formData.append('path', targetPath);
    
    // Add all files to the form data
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    
    // Show upload progress indicator
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `
        <div class="notification-title">Uploading ${files.length} file(s)...</div>
        <div class="progress-bar">
            <div class="progress" style="width: 0%"></div>
        </div>
    `;
    document.body.appendChild(notification);
    
    // Create and configure XMLHttpRequest to track upload progress
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '?action=upload', true);
    
    xhr.upload.onprogress = (e) => {
        if (e.lengthComputable) {
            const percentComplete = (e.loaded / e.total) * 100;
            notification.querySelector('.progress').style.width = percentComplete + '%';
        }
    };
    
    xhr.onload = function() {
        document.body.removeChild(notification);
        
        if (xhr.status === 200) {
            let response;
            try {
                response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    refresh(); // Refresh the file list to show the new files
                } else {
                    alert('Upload Error: ' + response.message);
                }
            } catch (e) {
                alert('Error processing server response');
            }
        } else {
            alert('Upload failed. Server returned status: ' + xhr.status);
        }
    };
    
    xhr.onerror = function() {
        document.body.removeChild(notification);
        alert('Upload failed. Please check your connection and try again.');
    };
    
    xhr.send(formData);
}

// Function to download a file from a URL to a specific path
function downloadFromURL(path) {
    const url = prompt('Enter the URL of the file to download:');
    if (!url) return;
    
    // Basic URL validation
    if (!url.match(/^https?:\/\/.+/i)) {
        alert('Please enter a valid URL starting with http:// or https://');
        return;
    }
    
    // Optionally ask for a custom filename
    let customFilename = '';
    if (confirm('Would you like to specify a custom filename? (Click Cancel to use the original filename)')) {
        customFilename = prompt('Enter the filename:');
        if (!customFilename) {
            // User canceled or entered an empty filename
            return;
        }
    }
    
    // Show download notification
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `
        <div class="notification-title">Downloading file from URL...</div>
        <div class="progress-bar">
            <div class="progress-indeterminate"></div>
        </div>
    `;
    document.body.appendChild(notification);
    
    // Prepare data for the request
    const formData = new FormData();
    formData.append('url', url);
    formData.append('path', path);
    if (customFilename) {
        formData.append('filename', customFilename);
    }
    
    // Send the download request
    fetchWithEncoding('?action=download_from_url', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.body.removeChild(notification);
        
        if (data.status === 'success') {
            alert(`Successfully downloaded file: ${data.filename}`);
            refresh(); // Refresh to show the new file
        } else {
            alert('Download failed: ' + data.message);
        }
    })
    .catch(error => {
        document.body.removeChild(notification);
        alert('Download failed: ' + error.message);
    });
}

// Update the files display with the current selection state
</script>
</body>
</html> 