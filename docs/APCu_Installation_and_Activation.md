# Minimal APCu Installation & Activation Guide

## Quick Checklist

* Confirm PHP version (APCu supports PHP 7.2–8.5).
* Install the APCu package for your OS.
* Enable the extension (via `php.ini` or `phpenmod`).
* Reload/restart your web server or PHP-FPM.
* Verify with `php -m`, `phpinfo()`, or `apcu_enabled()`.

---

## Linux

### Debian/Ubuntu

```bash
sudo apt update
sudo apt install php-apcu
sudo phpenmod apcu
# Reload Apache OR restart PHP-FPM:
sudo systemctl reload apache2 || sudo systemctl restart php*-fpm
```

### RHEL/CentOS/Fedora

```bash
# Enable EPEL if needed, then:
sudo dnf install php-pecl-apcu
sudo systemctl reload httpd || sudo systemctl restart php-fpm
```

### Alpine (replace `php82` with your minor version)

```bash
sudo apk add php82-pecl-apcu
sudo rc-service php-fpm restart || sudo rc-service apache2 reload
```

---

## Windows (IIS/Apache)

1. Download the matching `php_apcu.dll` from PECL for your PHP version/architecture and place it in `ext\`.
2. Add to `php.ini`:

   ```ini
   extension=php_apcu.dll
   ```
3. Restart your web server / PHP (IISReset or Apache restart).

---

## Optional Settings

Create or edit `php.ini` (or `conf.d/apcu.ini`):

```ini
apc.enabled=1
apc.shm_size=64M
; Enable for CLI tests only; keep 0 on production CLI:
apc.enable_cli=0
```

---

## Verification

```bash
php -m | grep -i apcu
php -r "var_dump(function_exists('apcu_enabled') ? apcu_enabled() : null);"
```

Or open a `phpinfo()` page in your browser and look for the **APCu** section.

---

## Done

If APCu appears in `php -m`/`phpinfo()` and `apcu_enabled()` returns `true`, APCu is installed and active.
