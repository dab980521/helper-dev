<?php
/**
 * Created by PhpStorm.
 * User: johnd
 * Date: 2018/5/23
 * Time: 下午2:29
 */

namespace App\Handlers;

use Illuminate\Http\UploadedFile;
use Image;

class ImageUploadHandler{
    /**
     * @var array
     */
    protected $allowed_ext = ["png", "jpg", "gif", "jpeg"];

    /**
     * @param UploadedFile $file
     * @param string $folder
     * @param $file_prefix
     * @param bool $max_width
     * @return array|bool
     */
    public function save($file, $folder, $file_prefix, $max_width = false)
    {
        $folder_name = "uploads/images/$folder/" . date("Ym/d", time());
        $upload_path = public_path() . '/' . $folder_name;
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'png';
        $filename = $file_prefix . '_' . time() . '_' . str_random(10) . '.' . $extension;
        if ( ! in_array($extension, $this->allowed_ext)) {
            return false;
        }
        $file->move($upload_path, $filename);
        if ($max_width && $extension != 'gif') {
            $this->reduceSize($upload_path . '/' . $filename, $max_width);
        }
        return [
            'path' => config('app.url') . "/$folder_name/$filename"
        ];
    }

    /**
     * @param string $file_path
     * @param integer $max_width
     */
    public function reduceSize($file_path, $max_width)
    {
        $image = Image::make($file_path);
        $image->resize($max_width, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $image->save();
    }
}