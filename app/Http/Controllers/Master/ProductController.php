<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Master\Product;
use App\Traits\{BugsnagTrait, ResponseBuilder};
use DB;

class ProductController extends Controller
{
    use BugsnagTrait, ResponseBuilder;

    public function index(Request $request) {
        try {
            $data = Product::all();

            return view('pages/product/index', compact('data'));
        }catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #index');
        }
    }

    public function show($id) {
        try {
            $data = Product::find($id);

            return view('pages/product/index', compact('data'));
        }catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #index');
        }
    }

    public function addToCart($id)
    {
        $product = Product::find($id);
        $cart = session('cart');

        if(isset($cart[$product->id])) {
            $cart[$product->id]['qty'] += 1;
            $cart[$product->id]['subtotal'] = $cart[$product->id]['price'] * $cart[$product->id]['qty'];
        } else {
            $cart[$product->id] = array(
                "id" => $product->id,
                "product_code" => $product->product_code,
                "product_name" => $product->product_name,
                "price" => $product->discount ? ($product->price - ($product->price*($product->discount/100))) : $product->price,
                "subtotal" => $product->discount ? ($product->price - ($product->price*($product->discount/100))) : $product->price,
                "qty" => 1,
            );
        }

        session(['cart' => $cart]);

        return response()->json(['success' => true, 'cart_items' => count(session('cart')), 'message' => 'Cart updated.']);
    }
}
