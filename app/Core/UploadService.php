<?php
declare(strict_types=1);

namespace HRM\Core;

use RuntimeException;

final class UploadService
{
    private const MAX_BYTES = 8388608;
    private const EXTENSIONS = ['pdf', 'png', 'jpg', 'jpeg', 'doc', 'docx', 'xls', 'xlsx', 'txt'];

    public static function save(array $file, string $oldFile = ''): string
    {
        $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return $oldFile;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('بارگذاری فایل انجام نشد؛ دوباره تلاش کنید.');
        }
        $size = (int)($file['size'] ?? 0);
        if ($size < 1 || $size > self::MAX_BYTES) {
            throw new RuntimeException('حجم فایل باید حداکثر ۸ مگابایت باشد.');
        }
        $original = (string)($file['name'] ?? 'file');
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONS, true)) {
            throw new RuntimeException('نوع فایل مجاز نیست. فایل PDF، تصویر یا سند اداری بارگذاری کنید.');
        }
        $tmp = (string)($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('فایل بارگذاری‌شده معتبر نیست.');
        }
        if (class_exists('finfo')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmp) ?: '';
            $allowed = [
                'pdf' => ['application/pdf'],
                'png' => ['image/png'],
                'jpg' => ['image/jpeg'],
                'jpeg' => ['image/jpeg'],
                'doc' => ['application/msword', 'application/octet-stream'],
                'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
                'xls' => ['application/vnd.ms-excel', 'application/octet-stream'],
                'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
                'txt' => ['text/plain', 'application/octet-stream'],
            ];
            if (!in_array($mime, $allowed[$extension] ?? [], true)) {
                throw new RuntimeException('محتوای فایل با پسوند آن مطابقت ندارد.');
            }
        }
        $directory = HRM_STORAGE . '/uploads';
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('پوشه بارگذاری قابل ایجاد نیست.');
        }
        $name = bin2hex(random_bytes(20)) . '.' . $extension;
        if (!move_uploaded_file($tmp, $directory . '/' . $name)) {
            throw new RuntimeException('ذخیره فایل روی سرور انجام نشد.');
        }
        @chmod($directory . '/' . $name, 0640);
        return $name;
    }

    public static function path(string $name): ?string
    {
        $name = basename($name);
        if ($name === '' || !preg_match('/^[a-f0-9]{40}\.[a-z0-9]{2,5}$/', $name)) {
            return null;
        }
        $path = HRM_STORAGE . '/uploads/' . $name;
        return is_file($path) ? $path : null;
    }

    public static function delete(string $name): void
    {
        $path = self::path($name);
        if ($path !== null) {
            @unlink($path);
        }
    }
}
