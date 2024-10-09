<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\User;
use Exception;

class FileController extends Controller
{
    private static function get_file($id, $user)
    {
        $output = [
            'status' => false,
            'message' => '',
        ];
       
        try {
            $file = File::where('id', $id)->first();
            $output['file'] = $file;

            if($file != null){
                if($file->user_id != $user->id){
                    //user id mismatch
                    throw(new Exception('different user.') );
                } else {
                    // correct user
                    if( !Storage::exists($file->filename) ){
                        throw(new Exception( 'file not found' ) );
                    }
                }
            } else {
                // no item
                throw( new Exception('item not found.') );
            }
        } catch(Exception $e){
            $output['message'] = $e->getMessage();
            return $output;
        }

        $output['status'] = true;
        //$output['file'] = $file;
        return $output;
    }

    public function create(Request $request)
    {
        //upload file
        //post method

        $user = Auth::user();

        $success = false;

        $upfile = $request->file('file');

        if($upfile != null){
            $path = $upfile->store('file');

            $file = File::create([
                'filename' => $path,
                'user_id' => $user->id,
            ]);

            $success = true;
        }

        if($success == true){
            $message = 'ファイルの作成に成功しました!';
        } else {
            $message = 'ファイルの作成に失敗しました!';
        }

        return response()->json([
            'is_success' => $success,
            'message' => $message,
            'file_id' => $file->id 
        ]);
    }

    public function show($id)
    {
        // download file
        // post method

        $user = Auth::user();

        $output = [
            'status' => false,
            'message' => '',
        ];

        $file = $this->get_file($id, $user);

        if($file['status'] == false){
            $output['message'] = $file['message'];
            //$output['file'] = $file['file'];
            //$output['user'] = $user;
            return response()->json($output, 403);
        }

        $filename = $file['file']->filename;

        $mimeType = Storage::mimeType($filename);
        $headers = [['Content-Type' => $mimeType,
                  'Content-Disposition' => 'attachment; filename*=UTF-8\'\''.rawurlencode( basename($filename))]];
        
        return Storage::download($filename, basename($filename), $headers);
    }

    public function destroy($id)
    {
        //delete method

        $user = Auth::user();

        $output = [
            'status' => false,
            'message' => '',
        ];

        $file = $this->get_file($id, $user);

        if($file['status'] == false){
            $output['message'] = 'no file';
            return response()->json($output, 403);
        }

        // delete file
        $filename = $file['file']->filename;
        $file_id = $file['file']->id;

        File::destroy($file_id);
        Storage::delete( $filename );

        $output['status'] = true;
        $output['message'] = 'success delete file.';

        return response()->json($output);
    }
}
