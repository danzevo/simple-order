@extends('adminlte::page')

@section('title', 'Report')

@section('content_header')
@stop

@section('content')
<style>
.modal-body img {
  max-width: 90%;
}
</style>
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive mt-4">
                <table id="article" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Transaction</th>
                            <th>User</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $value)
                            <tr>
                                <td>{{ $value['document_code']. ' - ' . $value['document_number'] }}</td>
                                <td>{{ $value['user'] }}</td>
                                <td>{{ 'Rp. '.number_format($value['total'], 0, ',', '.') }}</td>
                                <td>{{ date('d-m-Y', strtotime($value['date'])) }}</td>
                                <td>@foreach($value['detail'] as $res)
                                        {{ $res->product->product_name . ' X '. $res->quantity }}<br>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- <div class="d-flex justify-content-center"> --}}
            {{-- </div> --}}
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .modal {
        overflow-y:auto;
    }
</style>
@stop

@section('js')
@stop
