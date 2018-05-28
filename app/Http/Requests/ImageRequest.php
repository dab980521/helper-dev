<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * Class ImageRequest
 * @package App\Http\Requests
 * @property UploadedFile upload_image
 */
class ImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * "png", "jpg", "gif", "jpeg"
     *
     * @return array
     */
    public function rules()
    {
        return [
            'upload_image' => 'file|mimes:png,gif,jpeg'
        ];
    }
}
