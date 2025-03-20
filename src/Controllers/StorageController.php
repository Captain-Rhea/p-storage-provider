<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Helpers\ResponseHandle;

class StorageController
{
    public function storageHelpCheck(Request $request, Response $response, array $args): Response
    {
        try {
            $uploadDir = __DIR__ . "/../../public/storage";
            $storageLimitGB = (float) $_ENV['STORAGE_LIMIT_GB'] ?? 10;
            $storageLimitBytes = $storageLimitGB * 1024 * 1024 * 1024;

            $usedBytes = $this->getFolderSize($uploadDir);
            $usedFormatted = $this->formatBytes($usedBytes);
            $limitFormatted = $this->formatBytes($storageLimitBytes);
            $remainingFormatted = $this->formatBytes($storageLimitBytes - $usedBytes);

            return ResponseHandle::success($response, [
                'storage_limit' => $limitFormatted,
                'used' => $usedFormatted,
                'remaining' => $remainingFormatted,
                'used_percentage' => round(($usedBytes / $storageLimitBytes) * 100, 2) . '%'
            ]);
        } catch (\Exception $e) {
            return ResponseHandle::error($response, 'Failed to get storage size', 500, ['error' => $e->getMessage()]);
        }
    }

    private function getFolderSize(string $folder): int
    {
        $size = 0;
        if (!is_dir($folder)) {
            return $size;
        }

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string)$bytes) - 1) / 3);
        return sprintf("%.{$precision}f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }
}
