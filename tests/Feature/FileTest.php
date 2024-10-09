<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use App\Models\User;
use App\Models\File;

class FileTest extends TestCase
{
    private $accessToken = null;
    private $user_id = null;

    protected function setUp(): Void // ※ Voidが必要
    {
        // 必ずparent::setUp()を呼び出す
        parent::setUp(); 
        // 1.ログインユーザー作成
        // 毎回呼び出されてしまうので別途テストユーザー作る？
        /*
        User::create([
            'name' => 'sample user',
            'email' => 'test@make.test',
            'password' => Hash::make('phpartisanmaketest'),
        ]);
        */
        
        // 2.ログインAPIでアクセストークン取得
        $response = $this->post('/api/login', [
            'email' => 'sample@sample.com',
            'password' => 'sample123'
        ]);
        $response->assertOk();
        // 3.アクセストークンを変数に保存しておく
        $tmptoken = $response->decodeResponseJson();

        $this->accessToken = $tmptoken['authorization']['token'];
        
    }

    protected function tearDown(): void
    {
        $this->summary = null;

        parent::tearDown();
    }

    public function test_create() : void
    {
        Storage::fake('avatars');
   
        $file = UploadedFile::fake()->image('avatar.jpg');

        $data = [
            'file' => $file,
            'message' => 'test'
        ];

        //var_dump($data);

        $response = $this->post('api/file', $data, [
            'Authorization' => 'Bearer ' . $this->accessToken
        ]);

        $result = $response->baseResponse->original;

        //var_dump($result);

        $fileid = $result['file_id'];

        $response->assertJson([
            'is_success' => true
        ]);

        File::Destroy($fileid);


    }

    public function test_show()
    {
        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $data = [
            'file' => $file,
        ];

        $response = $this->post('api/file', $data, [
            'Authorization' => 'Bearer ' . $this->accessToken
        ]);

        $result = $response->baseResponse->original;
        $fileid = $result['file_id'];

        if($fileid != null){
            $response = $this->post('api/file/' . $fileid, [], [
                'Authorization' => 'Bearer ' . $this->accessToken
            ]);

            $response->assertStatus(200);

            File::Destroy($fileid);
        }
    }

    public function test_destroy()
    {
        Storage::fake('avatars');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $data = [
            'file' => $file,
        ];

        //create file
        $response = $this->post('api/file', $data, [
            'Authorization' => 'Bearer ' . $this->accessToken
        ]);

        $result = $response->baseResponse->original;
        $fileid = $result['file_id'];

        if($fileid != null){
            $response = $this->delete('api/file/' . $fileid, [], [
                'Authorization' => 'Bearer ' . $this->accessToken
            ]);

            $response->assertStatus(200);

            $response->assertJson([
                'status' => true
            ]);

        }
    }

    public function test_nologin(){
        //create test file
        Storage::fake('avatars');
   
        $file = UploadedFile::fake()->image('avatar.jpg');

        $data = [
            'file' => $file,
            'message' => 'test'
        ];

        $response = $this->post('api/file', $data, [
            'Authorization' => 'Bearer ' . $this->accessToken
        ]);

        $result = $response->baseResponse->original;

        $fileid = $result['file_id'];

        var_dump($fileid);

        //logout
        $response = $this->post('api/logout', [], [
            'Authorization' => 'Bearer ' . $this->accessToken
        ]);

        $response->assertStatus(200);

        // create file fail?
        $response = $this->post('api/file', $data);

        $response->assertStatus(302);

        // download file fail?
        $response = $this->post('api/file/' . $fileid);

        $response->assertStatus(302);

        // delete file fail?
        $response = $this->delete('api/file/' . $fileid);

        $response->assertStatus(302);

        //delete test file
        File::Destroy($fileid);
    }
}
