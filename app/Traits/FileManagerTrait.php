<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Throwable;
use Exception;

trait FileManagerTrait {
    public function uploadFileBase64($file='', $path='', $resizeWidth="200", $resizeHeight="200") {

        try{
            $filename = '';
            if(!$path)
            $path = env('STORAGE_PATH').'images';

            if ($file) {
                $img = $file;
                $pos_ext = strpos($file, '/');
                $pos_ext_end = strpos($file, ';');

                $extension = substr($file, ($pos_ext+1), (($pos_ext_end)-($pos_ext+1)));

                if($extension != 'jpeg' && $extension != 'jpg' && $extension != 'png')
                    throw new Exception('file must be jpg/jpeg/png');

                $img = str_replace('data:image/'.$extension.';base64,', '', $img);
                $img = str_replace(' ', '+', $img);
                $data = base64_decode($img);

                // Image Size 2 MB validation
                $img_size = strlen($data);
                if($img_size > 2048000) throw new Exception('Maximal file 2MB');

                $name = uniqid() . '.' . $extension;

                $filename = microtime() . "_" . $name;
                $filename = preg_replace('/\s/', '-', $filename);
                $path_image = $path . '/' . $filename;

                // Storage::disk('public')->put($path_image, $data);

                $image  = Image::make($data)->resize($resizeWidth, $resizeHeight, function ($constraint) {
                    $constraint->aspectRatio();
                })->encode($extension);

                $file_thumb = $image->getEncoded();

                // save thumbnail
                // $path_thumb_image = $path . '/thumbnail/' . $filename;
                Storage::disk('public')->put($path_image, $file_thumb);
            }

            return $filename;
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }
}
