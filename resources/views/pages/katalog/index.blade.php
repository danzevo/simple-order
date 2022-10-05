@extends('adminlte::page')

@section('title', 'Produk')

@section('content_header')
    <h1>Katalog Produk</h1>
@stop

@section('content')
<div class="row">
    @if(count($product) > 0)
        @foreach($product as $row)
            <div class="col-lg-3 d-flex align-items-stretch">
                <div class="card mb-3">
                    <img style="padding:25px" src="{{ asset('image_product/'.$row->image) }}" class="card-img-top" alt="{{ $row->image }}">
                    <div class="card-body">
                        <p class="text-center"><strong>{{ $row->nama }}</strong></p>
                        <p class="text-center"><strong>{{ 'Rp. '.number_format($row->harga, 0, ',', '.') }}</strong></p>
                        <p class="card-text">{!! $row->deskripsi !!}</p>
                    </div>
                    <div class="card-footer">
                        <p class="text-center"><button class="btn btn-warning">Beli</button></p>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-lg-3 d-flex align-items-stretch">
            <p>Data not Found</p>
        </div>
    @endif
</div>
@stop

@section('css')
<style>
.card {
    border : 1px solid #000 !important;
}
</style>
@stop

@section('js')
@stop
