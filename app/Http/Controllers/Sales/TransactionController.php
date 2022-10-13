<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sales\{
    Transaction,
    TransactionDetail
};
use App\Traits\{BugsnagTrait, ResponseBuilder};
use DB;

class TransactionController extends Controller
{
    use BugsnagTrait, ResponseBuilder;

    public function index(Request $request) {
        try {
            $query = Transaction::with(['detail', 'detail.product']);

            $query->latest('transactions.created_at');

            $data = $query->get();

            return view('pages/report/index', compact('data'));
        }catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #index');
        }
    }

    public function store(Request $request){
        DB::BeginTransaction();
        try{
            $trans = Transaction::orderBy('id', 'desc')->first();

            $doc_number = '001';
            if($trans) {
                $doc_number = (int)$trans->document_number;
                $doc_number += 1;
                $doc_number = substr('000',0,3-strlen($doc_number)).$doc_number;
            }

            $item = array(
                        'document_code' => 'TRX',
                        'document_number' => $doc_number,
                        'user' => auth()->user()->name,
                        'total' => 0,
                        'date' => date('Y-m-d'),
                    );

            $data = Transaction::create($item);

            if($data) {
                $product_code = explode(',',$request->product_code);
                $price = explode(',',$request->price);
                $quantity = explode(',',$request->quantity);

                if(count($product_code) > 0) {
                    foreach($product_code as $key => $value) {
                        $detail = array(
                            'document_code' => $data->document_code,
                            'document_number' => $data->document_number,
                            'product_code' => $value,
                            'discount' => 0,
                            'price' => $price[$key],
                            'quantity' => $quantity[$key],
                            'unit' => $unit ?? 'PCS',
                            'subtotal' => $price[$key] * $quantity[$key],
                        );

                        TransactionDetail::create($detail);
                    }
                }
                session()->forget('cart');

                Transaction::find($data->id)->update(['total' => TransactionDetail::sum('subtotal')]);
            }

            DB::commit();
            return $this->sendResponse(null, 'Transaksi berhasil dilakukan', 201);
		}catch(\Throwable $e) {
            dd($e);
            DB::rollback();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #store');
        }
	}
}
