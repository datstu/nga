<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use App\MultiImage;
use App\CategoryProduct;
use App\Visitor;
use Illuminate\Support\Facades\Redirect;
use Session;
use Carbon\Carbon;
use App\VisitorsOnline;

class ProductController extends Controller
{
    public function  countVisitorOnline( $req){
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        } 
        $sessionId =  session_id();
        $user_ip_address = $req->ip();
       
        /** current online */
        $visitorCurrent = Visitor::where('ip_address',$user_ip_address)
        ->where('session_id',$sessionId)->get();
        $visitorCount =  $visitorCurrent->count();
        if( $visitorCount <1 ){
            $visitor = new Visitor();
            $visitor->ip_address = $user_ip_address;
            $visitor->session_id = $sessionId;
            $toDate = Carbon::now('Asia/Ho_Chi_Minh')->toDateString();
            $visitor->date_visit = $toDate;
            $visitor->save();
        }      
            $visitorOnline =  VisitorsOnline::where('session_id', $sessionId)->first();
            $time = time();
           
            if(empty($visitorOnline)){
                $newVisitorOnline = new VisitorsOnline();
                $newVisitorOnline->session_id = $sessionId;
                $newVisitorOnline->time = $time;
                $newVisitorOnline->save();
            } else {
                $visitorOnline->time =  $time;
                $visitorOnline->save();
            }
    }


    public function searchFrontend(Request $req){
        $search =  $req->searchFE;
       // $featureProducts = Product::orderby('productID','DESC')->paginate(30);
        $productCategory = Product::join('tbl_category_product','tbl_category_product.category_id','=','tbl_product.catID')
        ->where('tbl_category_product.category_name','LIKE', "%{$search}%") 
        ->orwhere('tbl_product.productName','LIKE', "%{$search}%") 
        ->orwhere('tbl_product.productName','LIKE', "%{$search}%") 
        ->paginate(12);
        $meta_desc = "Chuyển các dòng sản phẩm trị mụn, dịch vụ làm đẹp cao cấp hàng đầu Việt Nam";
        $meta_keywords = "trị mụn, spa, sản phẩm y tế";
        $meta_title = "Khỏe cùng Lea chăm sóc sắc đẹp";
        $url_canonical = $req->url();
       $this->countVisitorOnline($req);
       return view('pages.product.searchProduct')->with(compact('search','productCategory','meta_desc','meta_keywords','meta_title','url_canonical'));
  
    }
    
    public function allProduct(Request $req)
    {
        
        $featureProducts = Product::where('status',1)->orderby('productID','DESC')->paginate(30);
        //dd($featureProducts);
       // $meidcanProducts = Product::join('tbl_category_product','tbl_category_product.category_id','=','tbl_product.catID')
       // ->where('tbl_product.catID',27)->orderby('productID','DESC')
       
       // var_dump( $meidcanProducts);
       // exit;
         /*seo*/
        $meta_desc = "Chuyển các dòng sản phẩm trị mụn, dịch vụ làm đẹp cao cấp hàng đầu Việt Nam";
        $meta_keywords = "trị mụn, spa, sản phẩm y tế";
        $meta_title = "Khỏe cùng Lea chăm sóc sắc đẹp";
        $url_canonical = $req->url();
        /*end seo*/
        
        $this->countVisitorOnline($req);
        return view('pages.product.allProduct')->with(compact('featureProducts','meta_desc','meta_keywords','meta_title','url_canonical'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listProduct(Request $req)
    {
        
        $featureProducts = Product::where('status',1)->orderby('productID','DESC')->where('type',1)
        ->get();
        //dd($featureProducts);
       // $meidcanProducts = Product::join('tbl_category_product','tbl_category_product.category_id','=','tbl_product.catID')
       // ->where('tbl_product.catID',27)->orderby('productID','DESC')
       $productSales = Product::take(8)->where('status',1)->orderby('productID','DESC')->where('type',2)->get();

       $productBelow3tr = Product::take(8)->where('status',1)->where('type','!=' ,2)->orderby('productID','DESC')->where('catID',16)->get();
       $product3trTo5tr = Product::take(8)->where('status',1)->where('type','!=' ,2)->orderby('productID','DESC')->where('catID',15)->get();
       $product5trTo7tr = Product::take(8)->where('status',1)->where('type','!=' ,2)->orderby('productID','DESC')->where('catID',17)->get();
       $product7trTo10tr = Product::take(8)->where('status',1)->where('type','!=' ,2)->orderby('productID','DESC')->where('catID',18)->get();
       $productOver10tr = Product::take(8)->where('status',1)->where('type','!=' ,2)->orderby('productID','DESC')->where('catID',19)->get();
       // var_dump( $meidcanProducts);
       // exit;
         /*seo*/
        $meta_desc = "Chuyển các dòng sản phẩm trị mụn, dịch vụ làm đẹp cao cấp hàng đầu Việt Nam";
        $meta_keywords = "trị mụn, spa, sản phẩm y tế";
        $meta_title = "Khỏe cùng Lea chăm sóc sắc đẹp";
        $url_canonical = $req->url();
        /*end seo*/
        
        $this->countVisitorOnline($req);
        return view('pages.product')->with(compact('productOver10tr','product7trTo10tr','productOver10tr','product5trTo7tr','product3trTo5tr','productBelow3tr','productSales','meta_desc','meta_keywords','meta_title','url_canonical'));
    }

    
    public function detailProduct( $productId){
        $req = new Request();
        $this->countVisitorOnline($req);
        
        $productDetail = Product::find($productId);
        $listImage = MultiImage::where('productID',$productId)->get();
       
        if($productDetail){
            $productsByCateId = $this->getProductByCateId($productDetail->catID);
            $productFeature = Product::where('type',1)->take(4)->get();
              /*seo*/
        $meta_desc = $productDetail->moTaNgan;
        $meta_keywords =  $productDetail->meta_keyswords;
        $meta_title = $productDetail->productName;
        if(empty($meta_keywords)){
            $meta_keywords =  $meta_title;
        }
        $url_canonical = $req->url();
        /*end seo*/
           
            return view('pages.detailProduct')->with(compact('productDetail','listImage','productsByCateId','productFeature',
        'meta_desc','meta_keywords','meta_title','url_canonical'));
        } else {
            return Redirect('/danh-sach-san-pham');
        }
       
        
    }
    public function getProductByCateId($id){
     $req = new Request();
        $this->countVisitorOnline($req);
        if($cate = CategoryProduct::find($id)){
            $listProduct = Product::where('catID',$id)->take(4)->get();
            return $listProduct?$listProduct:[];
        } 
    }
    
}
