<?php

namespace Edgewizz\Blog;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\MediaLink;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Imagick as imgick;




class BlogController extends Controller
{
   
    public function publishedPosts(Request $request)
    {
        if ($request->ajax()) {

            $posts = Post::select([
                "post.id",
                "post.title",
                'post.type',
                DB::raw('DATE_FORMAT(post.publish_utc, "%M %d, %Y") as date,
                CASE
                    WHEN post.type = 1 THEN "Text Article"
                    WHEN post.type = 2 THEN "Image Based Article"
                    WHEN post.type = 3 THEN "Video Based Article"
                END as type'), 
                "users.first_name as author"
            ])
            ->join('users', 'users.id', '=', 'post.created_by')
            ->where("post.status" , 3)
            ->orderby("post.created_at" , "DESC")
            ->get();

            $data = Datatables::of($posts)->addColumn('details', function ($post) {

                $html = '<a href="'. route('edit-blog', [$post->id]) .'" class="btn btn-warning">Edit</a>&nbsp;';

                return $html;
            })
            ->rawColumns(['details'])
            ->make(true);

            return $data;
        }

        return view("blogView::published");

    }

    public function draftPosts(Request $request)
    {
        if ($request->ajax()) {

            $posts = Post::select([
                "post.id",
                "post.title",
                'post.type',
                DB::raw('DATE_FORMAT(post.publish_utc, "%M %d, %Y") as date,
                CASE
                    WHEN post.type = 1 THEN "Text Article"
                    WHEN post.type = 2 THEN "Image Based Article"
                    WHEN post.type = 3 THEN "Video Based Article"
                END as type'), 
                "users.first_name as author"
            ])
            ->join('users', 'users.id', '=', 'post.created_by')
            ->where("post.status" , 0)
            ->orderby("post.created_at" , "DESC")
            ->get();

            $data = Datatables::of($posts)->addColumn('details', function ($post) {

                $html = '<a href="'. route('edit-blog', [$post->id]) .'" class="btn btn-warning">Edit</a>&nbsp;';

                return $html;
            })
            ->rawColumns(['details'])
            ->make(true);

            return $data;

        }

        return view("blogView::draft");
    }


    public function create(Request $request , $id = 0)
    {
        if (!empty($id))
        {
          $stored = Post::select([
                "post.id",
                "post.title",
                "post.description",
                "post.meta_title",
                "post.status",
                "post.slug",
                "media_links.link as pic",
                "post.meta_description",
                "post.meta_keyword",
            ])
            ->leftjoin('media_links', 'media_links.id', '=', 'post.media_id')
            ->where("post.id" , $id)
            ->first();  
            
        }

        return view('blogView::addBlog')->with([

            'stored' => $stored ?? [] ,
            'id' => $id ?? 0
        ]);
    }

    public function uploadFile($file, $path, $extra = [])
    {
        if (!empty($file)) {
            
            $destination = base_path("public/" . $path);
            
            $ext = strtolower($file->getClientOriginalExtension());
            
            $name = Str::random(2) . "_" . time() . "." . $ext;
            
            $file->move($destination, $name);
            
            $links = $destination . "/" . $name;
            
            $imagesize = getimagesize($links);
            
            $urlpath = url("") . "/" . $path;
            
            if (empty($extra["no"]) && !empty($imagesize)) {
                
               $mediaID = MediaLink::insertGetId([

                    "link" => $urlpath . "/" . $name,
                    "type" => 1, // pic
                    "active" => 1,

               ]);

            }
            
            return ["name" => $name, "path" => $links, "id" => $mediaID ?? 0];
        }
    }


    public function getAuthorFromPost($post_id)
    {
        $authorID = Post::where([
            'id' => $post_id,
            'active' => 1
        ])->first(['created_by']);

        return $authorID->created_by ?? 0;
    }

    public function getUserType($user_id)
    {
        $userData = User::where([
            'id' => $user_id,
            'active' => 1
        ])->first(['type']);

        return $userData->type ?? 0;
    }

    public function submit(Request $request)
    {
        //Draft Pending

        if($request->img != "undefined" && !empty($request->img))
        {
            $media = $this->uploadFile($request->img, 'post');
            $val = $this->img_crop($request->width, $request->height, $request->ow, $request->oh, $media['name'], $request->x, $request->y, $request->rotate);
            $imagePath = base_path('public/post/');
            $im = new imgick($imagePath . $val['imgname']);
            $im->scaleImage(1920, 680, true);
            $im->setImageCompressionQuality(85);
            $im->writeImages($imagePath . $val['imgname'], true);

        }

        $status = $request->status;
        $desc = $request->editor;
        $img_check = 'img';
        $video_chk = 'oembed';
        $insiderImage = strpos($desc, $img_check);
        $insiderVideo = strpos($desc, $video_chk);

        if ($insiderVideo != false) {

            $post_type = 3;

        } else if ($insiderImage != false) {

            $post_type = 2;

        } else {

            $post_type = 1;
        }

        $postID = $request->postID;

        if(Auth::user()->type != 2) {

            $userID = $this->getAuthorFromPost($postID);
            $userType = $this->getUserType($userID);
        }
        else
        {
            $userType = Auth::user()->type;
        }


        if($userType == 1) // user_type = 1 (normal user), 2 (admin)
        {

            if(is_numeric($postID) && $postID != "" && $postID != 0 ) {

                if($status == 0 || $status == 3) {

                    Post::where([

                        "id" => $postID

                    ])->update([

                        'title'       => $request->title,
                        'description' => $request->editor,
                        'status'      => $status,
                        'type'        => $post_type,
                    ]);


                    if(isset($media["id"]) && $media["id"] != "" && $media["id"] != 0) {

                        Post::where([

                            "id" => $postID

                        ])->update([

                            "media_id" => $media["id"],
                                
                        ]);
                    }
                }
            
            }
            else
            {
                $postID = Post::insert([

                    'title'       => $request->title,
                    'description' => $request->editor,
                    'status'      => $status,
                    'type'        => $post_type,
                    "media_id"    => $media["id"] ?? 0,
                    "created_by"  => Auth::id(),
                    'created_at'  => date("y-m-d h:i:s")

                ]);
            }

           
        }
        elseif(($status == 3 || $userType == 2 || $status == 0) &&  $userType != 1) // $status = 3
        {
            $insert = [
                "title"            => $request->title,
                "description"      => $request->editor,
                "status"           => $status,
                "type"             => $post_type,
                "meta_title"       => $request->meta_title,
                "meta_description" => $request->meta_description,
                "meta_keyword"     => $request->meta_keyword,
                "slug"             => Str::slug($request->slug)."-".rand(100000,999999),
                "publish_utc"      => date("y-m-d h:i:s"),
                "created_by"       => Auth::id(),
                'created_at'       => date("y-m-d h:i:s"),
            ];

            if(!empty($media["id"]))
            {

                $insert['media_id'] = $media["id"];
            }

            if(is_numeric($postID) && $postID != "" && $postID != 0 ) {

                if($status == 0 || $status == 3) {

                    Post::where([

                        "id" => $postID

                    ])->update($insert);

                }
            
            }
            else
            {
                $data = Post::insert($insert);
            }

        }

        switch ($request->status) {
                case 0:
                    $message = "Draft Post has been ".(isset($request->postID) && $request->postID != "" ? "updated" : "submitted"). " successfully.";
                    break;
                case 1:
                    $message = "Post has been approved successfully.";
                    break;
                case 2:
                    $message = "Post has been declined.";
                    break;
                case 3:
                    $message = "Post has been ".(isset($request->postID) && $request->postID != "" ? "updated" : "submitted"). " successfully.";
                    break;
                default:
                    echo "Your favorite color is neither red, blue, nor green!";
            }

            return $message ; 
            
    }
    public function img_crop($width, $height, $ow, $oh, $imgname, $x, $y, $rot, $imagepost = "")
    {


        if (!empty($imagepost)) {
            $baseurl = base_path("public/images/temp_sportsgram/");
            $urlpath = url('images/temp_sportsgram/');
        } else {
            $baseurl = base_path("public/post/");
            $urlpath = url('post/');
        }

        $imgUrl = $baseurl . $imgname;

        //dd($imgUrl);

        // original sizes
        $imgInitW = $ow;
        $imgInitH = $oh;
        // resized sizes
        $imgW = $ow;
        $imgH = $oh;

        //dd($imgH);
        // offsets
        $imgY1 = $y + 1;
        $imgX1 = $x + 1;
        // crop box
        $cropW = $width - 3;
        $cropH = $height - 3;
        // rotation angle
        $angle = $rot;

        $jpeg_quality = 80;
        // $final_filename = "croppedImg_" . rand();
        $final_filename = $imgname;
        $output_filename = $baseurl . $final_filename;

        // uncomment line below to save the cropped image in the same location as the original image.
        //$output_filename = dirname($imgUrl). "/croppedImg_".rand();

        //print_r($imgUrl);die;

        $what = getimagesize($imgUrl);
        //  print_r($what);die;

        switch (strtolower($what['mime'])) {
            case 'image/png':
                $img_r = \imageCreateFrompng($imgUrl);
        
                $source_image = \imagecreatefrompng($imgUrl);
                $type = '.png';

                break;
            case 'image/jpeg':
                // $img_r = \imagecreatefromjpeg($imgUrl);
                $source_image = \imagecreatefromjpeg($imgUrl);

                $type = '.jpeg';
                break;
            case 'image/gif':
                $img_r = \imagecreatefromgif($imgUrl);
                $source_image = \imagecreatefromgif($imgUrl);
                $type = '.gif';
                break;
            default:
                die('image type not supported');
        }


        // print_r($source_image);die;

        //Check write Access to Directory


        if (!is_writable(dirname($output_filename))) {

            $response = Array(
                "status" => 'error',
                "message" => 'Can`t write cropped File'
            );
        } else {

            // resize the original image to size of editor
            // var_dump($imgW);
            $resizedImage = \imagecreatetruecolor($imgW, $imgH);
            \imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);
            // rotate the rezized image
            $rotated_image = \imagerotate($resizedImage, -$angle, 0);
            // find new width & height of rotated image
            $rotated_width = \imagesx($rotated_image);
            $rotated_height = \imagesy($rotated_image);
            // diff between rotated & original sizes
            $dx = $rotated_width - $imgW;
            $dy = $rotated_height - $imgH;
            // crop rotated image to fit into original rezized rectangle
            $cropped_rotated_image = \imagecreatetruecolor($imgW, $imgH);
            \imagecolortransparent($cropped_rotated_image, \imagecolorallocate($cropped_rotated_image, 0, 0, 0));
            \imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);
            // crop image into selected area

            $final_image = \imagecreatetruecolor($cropW, $cropH);
            \imagecolortransparent($final_image, \imagecolorallocate($final_image, 0, 0, 0));
            \imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);
            // finally output png image
            //imagepng($final_image, $output_filename.$type, $png_quality);
            imagejpeg($final_image, $output_filename, $jpeg_quality);
            $response = Array(
                "status" => 'success',
                "url" => $urlpath . ('/') . $final_filename,
                "imgname" => $final_filename
            );
        }
        return $response;

    }


    public function articleImageUpload(Request $request)
    {       

        if ($request->isMethod("post")) {

        // dd($_FILES["upload"]["name"]);

            $upload = $this->contentImageUpload($request);


            if(!empty($upload['uploaded']))
            {
                $response = $upload;
            }
            else
            {
                return $upload["message"];
            }

        }

        return $response;

    }
    public function contentImageUpload(Request $request)
    {
        $imagePath = base_path('public/post/');

        $allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");

        $temp = explode(".", $_FILES["upload"]["name"]);

        $extension = end($temp);

        //Check write Access to Directory

        if (!is_writable($imagePath)) {

            $response = [
                "status" => 'error',
                "message" => 'Can`t upload File; no write Access'
            ];

            return $response;
        }


        if (in_array($extension, $allowedExts)) {
            if ($_FILES["upload"]["error"] > 0) {
                $response = [
                    "status" => 'error',
                    "message" => 'ERROR Return Code: ' . $_FILES["upload"]["error"],
                ];

                return $response;

            } else {

                $filename = $_FILES["upload"]["tmp_name"];

                list($width, $height) = getimagesize($filename);

                // Create new imagick object

                $media = $this->uploadFile($request->file('upload'), 'post');


                $im = new imgick(base_path("public/post/" . $media['name']));


                $im->scaleImage(650, 367, true);

                $im->writeImages($imagePath . $media['name'], true);

                //move_uploaded_file($filename, $imagePath . $reuse);

                return [
                    "uploaded" => 1,
                    "filenaeme" => $media['path'],
                    "url" => url('post/' . $media['name'])
                ];
            }
        }
    }
}
