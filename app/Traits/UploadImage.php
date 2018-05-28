<?php
/**
 * Created by PhpStorm.
 * User: johnd
 * Date: 2018/5/28
 * Time: 下午6:35
 */

namespace App\Traits;


use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;

trait UploadImage
{
    /**
     * @param Request $request
     * @param ImageUploadHandler $uploader
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request, ImageUploadHandler $uploader){
        $data = [
            'success' => false,
            'msg' => '上传失败',
            'file_path' => '',
        ];
        $status_code = 500;
        if ($file = $request->upload_file){
            $result = $uploader->save($request->upload_file, 'topics', \Auth::id(), 1024);
            if ($result){
                $data = [
                    'file_path' => $result['path'],
                    'msg' => '上传成功',
                    'success' => true,
                ];
                $status_code = 201;
            }
        }
        return response()->json($data,$status_code);
    }
}