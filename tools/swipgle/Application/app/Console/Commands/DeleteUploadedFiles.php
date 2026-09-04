<?php

namespace App\Console\Commands;

use App\Models\Upload;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Storage;

class DeleteUploadedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deleting the files that uploaded by users and not transferred';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $uploadFiles = Upload::where('created_at', '<', Carbon::now()->subHour())->with('storageProvider')->get();
        foreach ($uploadFiles as $uploadFile) {
            $handler = $uploadFile->storageProvider->handler;
            if ($handler::delete($uploadFile->path)) {
                $uploadFile->delete();
            }
        }
        $disk = Storage::disk('local');
        foreach ($disk->allFiles('temp') as $file) {
            if (Carbon::parse($disk->lastModified($file)) < Carbon::now()->subHour()) {
                $disk->delete($file);
            }
        }
    }
}
