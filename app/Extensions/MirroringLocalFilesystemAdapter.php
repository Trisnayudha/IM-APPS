<?php

namespace App\Extensions;

use League\Flysystem\Adapter\Local as BaseLocalAdapter;

class MirroringLocalFilesystemAdapter extends BaseLocalAdapter
{
    public function write($path, $contents, $config)
    {
        $result = parent::write($path, $contents, $config);
        $this->mirrorToContabo($path);
        return $result;
    }

    public function writeStream($path, $resource, $config)
    {
        $result = parent::writeStream($path, $resource, $config);
        $this->mirrorToContabo($path);
        return $result;
    }

    protected function mirrorToContabo($path)
    {
        // Buang "public/" dari path tujuan, tapi tetap cek file dari lokasi aslinya
        $local = escapeshellarg(storage_path('app/' . $path));

        // Hilangkan "public/" dari path hanya untuk tujuan remote
        $cleanPath = str_replace('public/', '', $path);

        $remote = escapeshellarg('contabo:indonesiaminer/api/storage/' . $cleanPath);

        $command = "rclone copyto $local $remote 2>&1";
        exec($command, $output, $returnCode);

        dd([
            'command' => $command,
            'output' => $output,
            'return_code' => $returnCode,
            'local_exists' => file_exists(storage_path('app/' . $path)),
        ]);
    }
}
