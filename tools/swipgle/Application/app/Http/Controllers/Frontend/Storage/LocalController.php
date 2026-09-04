<?php

namespace App\Http\Controllers\Frontend\Storage;

use App\Http\Controllers\Controller;
use Exception;
use Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LocalController extends Controller
{
    public static function upload($file, $location)
    {
        try {
            $filename = generateFileName($file);
            $path = "app/public/" . $location;
            $upload = $file->move(storage_path($path), $filename);
            if ($upload) {
                $data = [
                    "type" => "success",
                    "filename" => $filename,
                    "path" => $location . $filename,
                ];
                return responseHandler($data);
            }
        } catch (Exception $e) {
            return responseHandler(["type" => "error", 'msg' => lang('Storage provider error', 'upload zone')]);
        }
    }

    public static function download($transferFile)
    {
        try {
            $disk = Storage::disk('public');
            $fileName = $transferFile->filename;
            $filePath = $disk->path($transferFile->path);
            if ($disk->has($transferFile->path)) {
                $headers = [
                    'Content-Type' => $transferFile->mime,
                    'Content-Disposition' => 'attachment; filename="' . $transferFile->name . '"',
                    'Content-Length' => $transferFile->size,
                ];
                $response = new StreamedResponse(
                    function () use ($filePath, $fileName) {
                        if ($file = fopen($filePath, 'rb')) {
                            while (!feof($file) and (connection_status() == 0)) {
                                print(fread($file, 1024 * 8));
                                flush();
                            }
                            fclose($file);
                        }
                    },
                    200, $headers);
                return $response;
            } else {
                throw new Exception(lang('There was a problem while trying to download the file', 'download page'));
            }
        } catch (Exception $e) {
            throw new Exception(lang('There was a problem while trying to download the file', 'download page'));
        }
    }

    public static function delete($filePath)
    {
        $disk = Storage::disk('public');
        if ($disk->has($filePath)) {
            $disk->delete($filePath);
        }
        return true;
    }

}
