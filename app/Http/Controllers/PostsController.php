<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Posts;
use App\CategoryProduct;
use App\Http\Controllers\AdminController;
use App\BrandProduct;
use App\MultiImage;
use App\Post;
use DB;
use App\Http\Requests;
use Session;
use Illuminate\Support\Facades\Redirect;
class PostsController extends Controller
{
 
     public function listServiceMassage(Request $req){
        $meta_desc = "Lea Beauty - Spa Điều Trị Da Uy Tín Phú Nhuận";
        $meta_keywords = "Trị mụn tắm trắng triệt lông";
        $meta_title = "Chăm sóc sắc đẹp";
        $url_cannonical = $req->url();
        return view("pages.listServiceMassage")->with(compact('meta_desc','meta_keywords','meta_title','url_cannonical'));
    }
    
    public function postsManagement(){
        $checkAuth = (new AdminController)->checkAuthAdmin();
        if(!$checkAuth) return redirect('/admin-login');
        
        $posts = Posts::orderby('post_id','DESC')->paginate(5);

        return view('admin.post.list')->with(compact('posts'));
    }

    public function addPost(){
        $checkAuth = (new AdminController)->checkAuthAdmin();
        if(!$checkAuth) return redirect('/admin-login');
        $categoryList = CategoryProduct::all(); 
        return view('admin.post.add')->with(compact('categoryList'));
    }

    public function savePost(Request $req){
        $data = $req->all();
        $path = 'public/uploads/post';

        if(empty($req->id)){
            $post = new Posts();
            $post->post_title = $data['title'];
            $post->post_desc = $data['sortDesc'];
            $post->catID = $data['category'];
            $post->post_content = empty($data['content'])?'':$data['category'];
            $post->post_slug = $data['slug'];
            if(isset($data['show'])) $post->display = json_encode($data['show']);
       
            $getImage = $req->file('imgProduct');
            if($getImage){
                 $newImage = strtotime("now") .'-'.$getImage->getClientOriginalName();
                 $getImage->move($path,$newImage);
                 $post->post_image = $newImage; 
            }
             $result = $post->save();
 
            if($result){
                Session::put('message','success_add');
             } else {
                Session::put('message','fail_add');
             }
        } else{
                $post = Posts::find($req->id);
                if($req->imgProductupdate){
                    //delete file if image old isset
                    $images = scandir($path, SCANDIR_SORT_DESCENDING);
                    foreach( $images as $image){
                        if($image == $post->post_image){
                            unlink($path.DIRECTORY_SEPARATOR.$image );
                        }
                    }
                    //insert new file
                    $getImage = $req->file('imgProductupdate');
                    $newImage = strtotime("now") .'-'.$getImage->getClientOriginalName();
                    $getImage->move($path,$newImage);
                    $post->post_image = $newImage; 
                }  

                $post->post_title = $data['title'];
                $post->post_desc = $data['sortDesc'];
                $post->catID = $data['category'];
                date_default_timezone_set('Asia/Ho_Chi_Minh');
                $post->update_at = date("Y-m-d");
                $post->post_content = empty($data['content'])?'':$data['content'];
                $post->post_slug = $data['slug'];
                if(isset($data['show'])) $post->display = json_encode($data['show']);
                else{
                    $post->display = NULL;
                }
                // json_decode($post->display,true); dd($display);
  
            $result = $post->save();
            if($result){
                Session::put('message','success_edit');
             } else {
                Session::put('message','fail_edit');
             }
        }
        return Redirect::to('/quan-ly-bai-viet');
    }

    public function updatePost($id){
        $checkAuth = (new AdminController)->checkAuthAdmin();
        if(!$checkAuth) return redirect('/admin-login');
        
        $postById = Posts::find($id);
        $categoryList = CategoryProduct::all();
      
        if( empty($postById)){
         Session::put('message','fail_edit');
         return Redirect::to('/quan-ly-bai-viet');
         }
        return view('admin.post.add')->with('postById',$postById)->with('categoryList',$categoryList);    
    }
    public function deletePost($id){
        $result = Posts::find($id);
        if($result){
            $path = 'public/uploads/post';
            //delete file if image old isset
           $images = scandir($path, SCANDIR_SORT_DESCENDING);
           foreach( $images as $image){
               if($image == $result->post_image){
                   unlink($path.DIRECTORY_SEPARATOR.$image );
               }
           }
            $result->delete();
            Session::put('message','success_delete');
        } else {
            Session::put('message','fail_delete');
        }
       
        return Redirect::to('/quan-ly-bai-viet');
    }

    public function homePost(Request $req){
        $meta_desc = "Lea Beauty - Spa Điều Trị Da Uy Tín Phú Nhuận";
        $meta_keywords = "Trị mụn tắm trắng triệt lông";
        $meta_title = "Cùng Lea cập nhật tin mới mỗi ngày";
        $url_cannonical = $req->url();
        $listPost = Posts::where('display','<>',NULL)->get();
       // echo '<pre>';

       $homePost = [];
        foreach($listPost as $post){
            $show = json_decode($post['display']);
           
            if (in_array(3, $show)) {
                $homePost[] = $post;
            }
           

        }

        return view("pages.listServiceMassage")->with(compact('meta_desc','meta_keywords','meta_title','url_cannonical','homePost'));
    }

    public function detailPost(Request $req,$hi,$ha){
        $meta_desc = "Lea Beauty - Spa Điều Trị Da Uy Tín Phú Nhuận";
        $meta_keywords = "Trị mụn tắm trắng triệt lông";
        $meta_title = "Cùng Lea cập nhật tin mới mỗi ngày";
        $url_cannonical = $req->url();
        $post = Posts::find($hi);
        $recentPost = Posts::orderby('post_id','DESC')->limit(2)->get();

        // $categoryPost = CategoryProduct::rightJoin('tbl_posts','tbl_posts.catID','=','tbl_category_product.category_id')
        // ->groupBy('tbl_category_product.category_id') ->get();
        $categoryPost= DB::select("SELECT tbl_posts.catID FROM tbl_category_product  RIGHT JOIN tbl_posts  
        ON tbl_category_product.category_id=tbl_posts.catID GROUP BY tbl_posts.catID");
        //dd( $categoryPost);
        $listCate = [];
        foreach(  $categoryPost as $cate){
             $listCate[] = CategoryProduct::find($cate->catID);
        }

        if($post){
            return view("pages.detailServiceMassage")->with(compact('listCate','meta_desc','meta_keywords','meta_title','url_cannonical','recentPost','post'));
        } else{
            return Redirect('tin-tuc');
        }
    }
    public function listCategoryById(Request $req, $hi, $ha){
        $categoryPost = Posts::join('tbl_category_product','tbl_category_product.category_id','=','tbl_posts.catID')
        ->where('catID',$hi)->get();
      
        $homePost = [];
        foreach($categoryPost as $post){
            $show = json_decode($post['display']);
           
            if (in_array(1, $show) || in_array(3, $show) ) {
                $homePost[] = $post;
            }
        }
        $meta_desc = "Lea Beauty - Spa Điều Trị Da Uy Tín Phú Nhuận";
        $meta_keywords = "Trị mụn tắm trắng triệt lông";
        $meta_title = "Cùng Lea cập nhật tin mới mỗi ngày";
        $url_cannonical = $req->url();
        return view("pages.listServiceMassage")->with(compact('meta_desc','meta_keywords','meta_title','url_cannonical','homePost'));
    }

    public function searchPost(Request $req){
        $meta_desc = "Lea Beauty - Spa Điều Trị Da Uy Tín Phú Nhuận";
        $meta_keywords = "Trị mụn tắm trắng triệt lông";
        $meta_title = "Cùng Lea cập nhật tin mới mỗi ngày";
        $url_cannonical = $req->url();

        $posts = Posts::where('post_content','LIKE','%'.$req->search.'%')
        ->orWhere('post_desc','LIKE','%'.$req->search.'%')->get();
       
        $homePost = [];
        foreach($posts as $post){
            $show = json_decode($post['display']);
           
            if (in_array(1, $show) || in_array(3, $show) ) {
                $homePost[] = $post;
            }
        }
        return view("pages.listServiceMassage")->with(compact('meta_desc','meta_keywords','meta_title','url_cannonical','homePost'));
    }
    
}
