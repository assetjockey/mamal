<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Methods\FileDetailsDetector;
use App\Models\StorageProvider;
use App\Models\Upload;
use Auth;
use Exception;
use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        $uploadedFileName = $request->file('file')->getClientOriginalName();
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));
        try {
            $unacceptedTypesArr = explode(',', settings('unaccepted_file_types'));
            $fileExt = $request->file('file')->getclientoriginalextension();
            if (in_array($fileExt, $unacceptedTypesArr)) {
                return static::errorResponseHandler(lang('You cannot upload files of this type.', 'upload zone'));
            }
            if (!subscription()->is_subscribed or subscription()->is_expired) {
                return static::errorResponseHandler(lang('You have no subscription or your subscription has been expired', 'alerts'));
            }
            if (subscription()->is_canceled) {
                return static::errorResponseHandler(lang('Your subscription has been canceled, please contact us for more information', 'alerts'));
            }
            if (!is_null(subscription()->storage->remaining_space_number)) {
                if ($request->total_size > subscription()->storage->remaining_space_number) {
                    return static::errorResponseHandler(lang('Insufficient storage space, please check your space or upgrade your plan', 'alerts'));
                }
            }
            if (!is_null(subscription()->plan->transfer_size_number)) {
                if ($request->total_size > subscription()->plan->transfer_size_number) {
                    return static::errorResponseHandler(
                        str_replace('{maxTransferSize}', subscription()->plan->transfer_size, lang('Max size per transfer : {maxTransferSize}.', 'upload zone')));
                }
            }
            $storageProvider = StorageProvider::where([['symbol', env('FILESYSTEM_DRIVER')], ['status', 1]])->first();
            if (is_null($storageProvider)) {
                return static::errorResponseHandler(lang('Unavailable storage provider', 'upload zone'));
            }
            if ($receiver->isUploaded() === false) {
                return static::errorResponseHandler(lang('Failed to upload', 'upload zone') . ' (' . $uploadedFileName . ')');
            }
            $save = $receiver->receive();
            if ($save->isFinished()) {
                $file = $save->getFile();
                $fileName = $file->getClientOriginalName();
                $fileExtension = $file->getClientOriginalExtension();
                $fileMimeType = (!is_null(getFileMimeType($fileExtension))) ? getFileMimeType($fileExtension) : $file->getMimeType();
                if (empty($fileExtension)) {
                    $fileExtension = FileDetailsDetector::lookupExtension($fileMimeType);
                }
                $fileSize = $file->getSize();
                if ($fileSize == 0) {
                    return static::errorResponseHandler(lang('Empty files cannot be uploaded', 'upload zone'));
                }
                if (in_array($fileExtension, $unacceptedTypesArr)) {
                    return static::errorResponseHandler(lang('You cannot upload files of this type.', 'upload zone'));
                }
                if (!is_null(subscription()->storage->remaining_space_number)) {
                    if ($fileSize > subscription()->storage->remaining_space_number) {
                        return static::errorResponseHandler(lang('Insufficient storage space, please check your space or upgrade your plan', 'alerts'));
                    }
                }
                $location = (Auth::user()) ? "users/" . hashid(userAuthInfo()->id) . "/" : "anonymous/";
                $handler = $storageProvider->handler;
                $uploadResponse = $handler::upload($file, $location);
                if ($uploadResponse->type == "error") {
                    return $uploadResponse;
                }
                $userId = (Auth::user()) ? Auth::user()->id : null;
                $createUpload = Upload::create([
                    'ip' => vIpInfo()->ip,
                    'user_id' => $userId,
                    'storage_provider_id' => $storageProvider->id,
                    'name' => $fileName,
                    'filename' => $uploadResponse->filename,
                    'size' => $fileSize,
                    'mime' => $fileMimeType,
                    'extension' => $fileExtension,
                    'path' => $uploadResponse->path,
                ]);
                if ($createUpload) {
                    return response()->json(['type' => 'success', 'id' => hashid($createUpload->id)]);
                }
            }
        } catch (Exception $e) {
            return static::errorResponseHandler(lang('Failed to upload', 'upload zone') . ' (' . $uploadedFileName . ')');
        }
    }

    public function destroy(Request $request)
    {
        if (Auth::user()) {
            $file = Upload::where([['id', unhashid($request->id)], ['user_id', userAuthInfo()->id]])->with('storageProvider')->first();
        } else {
            $file = Upload::where([['id', unhashid($request->id)], ['ip', vIpInfo()->ip], ['user_id', null]])->with('storageProvider')->first();
        }
        if (is_null($file)) {
            return response()->json(['error' => lang('File not exists', 'upload zone')]);
        }
        $handler = $file->storageProvider->handler;
        $deleteFile = $handler::delete($file->path);
        if ($deleteFile) {
            $file->delete();
        }
    }

    private static function errorResponseHandler($response)
    {
        return response()->json(['type' => 'error', 'msg' => $response]);
    }
}
