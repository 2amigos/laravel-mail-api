<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;

class FilesCleanup implements ShouldQueue
{
    use Queueable;

    private array $filePaths;

    public function __construct(array $filePaths)
    {
        $this->filePaths = $filePaths;
    }

    public function handle()
    {
        collect($this->filePaths)->each(function ($path) {
            if (Storage::exists($path['path'])) {
                Storage::delete($path['path']);
            }
        });
    }
}
