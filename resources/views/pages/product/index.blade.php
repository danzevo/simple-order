@extends('adminlte::page')

@section('title', 'Order')

@section('content_header')
@stop

@section('content')
<style>
.modal-body img {
  max-width: 90%;
}
</style>
<div class="container flex-grow-1 flex-shrink-0 py-5">
    <div class="mb-5 p-4 bg-white shadow-sm">
      <h3>Product List</h3>
      <div id="stepper1" class="bs-stepper">
        <div class="bs-stepper-header" role="tablist">
          <div class="step" data-target="#test-l-1">
            <button type="button" class="step-trigger" role="tab" id="stepper1trigger1" aria-controls="test-l-1">
              <span class="bs-stepper-circle">1</span>
              <span class="bs-stepper-label">Product</span>
            </button>
          </div>
          <div class="bs-stepper-line"></div>
          <div class="step" data-target="#test-l-2">
            <button type="button" class="step-trigger" role="tab" id="stepper1trigger2" aria-controls="test-l-2">
              <span class="bs-stepper-circle">2</span>
              <span class="bs-stepper-label">Checkout</span>
            </button>
          </div>
          {{-- <div class="bs-stepper-line"></div>
          <div class="step" data-target="#test-l-3">
            <button type="button" class="step-trigger" role="tab" id="stepper1trigger3" aria-controls="test-l-3">
              <span class="bs-stepper-circle">3</span>
              <span class="bs-stepper-label">Validate</span>
            </button>
          </div> --}}
        </div>
        <div class="bs-stepper-content">
          <form onSubmit="return false">
            <div id="test-l-1" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger1">
                <ul class="list-unstyled">
                    @foreach($data as $row)
                    <li class="media">
                      <img class="mr-3" src="{{ asset('image/default-foto.png') }}" width="60" height="60" alt="Generic placeholder image">
                      <div class="media-body">
                        <h5 class="mt-0 mb-1"><a href="javascript:void(0)"
                            data-id = "{{ $row->id }}"
                            data-product_name = "{{ $row->product_name }}"
                            data-price = "{{ (int)$row->price }}"
                            data-discount = "{{ $row->discount }}"
                            data-dimension = "{{ $row->dimension }}"
                            data-unit = "{{ $row->unit }}"
                        onclick="show(this)" data-toggle="modal" data-target="#InputModal"> {{ $row->product_name}}</a>
                        </h5>
                        @if($row->discount)
                            <del class='text-red'>{{ 'Rp. '.number_format($row->price, 0, ',', '.') }}</del>
                            {{ 'Rp. '.number_format(($row->price - ($row->price*($row->discount/100))), 0, ',', '.') }}
                        @else
                            {{ 'Rp. '.number_format($row->price, 0, ',', '.') }}
                        @endif
                      </div>
                      <button class="btn btn-primary" onclick="addToCart({{ $row->id }})">Buy</button>
                    </li>
                    @endforeach
                </ul>
              <button class="btn btn-primary" onclick="stepper1.next()">Checkout</button>
            </div>
            <div id="test-l-2" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger2">
                <ul class="list-unstyled">
                    @if(session('cart'))
                        @php
                            $id = [];
                            $total = 0;
                        @endphp
                        @foreach(session('cart') as $row)
                        @php
                            $id[] = $row['id'];
                            $total += $row['subtotal'];
                        @endphp
                        <li class="media">
                            <img class="mr-3" src="{{ asset('image/default-foto.png') }}" width="60" height="60" alt="Generic placeholder image">
                            <div class="media-body">
                                <h5 class="mt-0 mb-1">{{ $row['product_name']}}</h5>
                                <div class="form-group-sm row col-4 p-0">
                                    <div class="col-10">
                                    <input type="text" class="form-control" id="qty{{ $row['id'] }}" name="qty[]" value="{{ $row['qty'] }}" oninput="calculate({{ $row['id'] }}, {{ $row['price'] }})">
                                    </div>
                                    <label class="col-2"> PCS </label>
                                </div>
                                <div class="form-group-sm">
                                    Subtotal : <span id="subtotal{{ $row['id'] }}">{{ 'Rp. '.number_format($row['subtotal'], 0, ',', '.') }}</span>
                                    <input type="hidden" id="subtotal_pure{{ $row['id'] }}" name="subtotal_pure[]" value="{{ $row['subtotal'] }}">
                                    <input type="hidden" id="price{{ $row['id'] }}" name="price[]" value="{{ $row['price'] }}">
                                    <input type="hidden" id="product_code{{ $row['id'] }}" name="product_code[]" value="{{ $row['product_code'] }}">
                                </div>
                            </div>
                        </li>
                        @endforeach
                        <div class="form-group-sm">
                            <h3 class="text-center">
                                Total : <span id="grandtotal">{{ 'Rp. '.number_format($total, 0, ',', '.') }}</span>
                                <input type="hidden" id="grandtotal_pure" name="grandtotal_pure" value="{{ $total }}">
                                <input type="hidden" id="array_id" name="array_id" value="<?php print_r($id) ?>">
                            </h3>
                        </div>
                    @else
                        <p>Your cart is empty</p>
                    @endif
                </ul>
              <button class="btn btn-primary" onclick="stepper1.previous()">Previous</button>
              <button class="btn btn-primary" onclick="save();">Confirm</button>
              {{-- <button class="btn btn-primary" onclick="save();stepper1.next()">Confirm</button> --}}
            </div>
            {{-- <div id="test-l-3" role="tabpanel" class="bs-stepper-pane text-center" aria-labelledby="stepper1trigger3">
              <button class="btn btn-primary mt-5" onclick="stepper1.previous()">Previous</button>
              <button type="submit" class="btn btn-primary mt-5">Submit</button>
            </div> --}}
          </form>
        </div>
      </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="InputModal" aria-labelledby="InputModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="InputModalLabel">Detail Product</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" id="formUser">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="modal-body">
            <li class="media">
                <img class="mr-3" src="{{ asset('image/default-foto.png') }}" width="60" height="60" alt="Generic placeholder image">
                <div class="media-body">
                  <input type="hidden" id="show_id" name="show_id">
                  <h5><span id="product_name"></span></h5>
                  <del><span class='text-red' id="price_discount"></span></del><br>
                  <span id="price"></span><br>
                  Dimension : <span id="dimension"></span><br>
                  Price Unit : <span id="unit"></span><br>
                </div>
              </li>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" data-dismiss="modal" onclick="addToCart($('#show_id').val())">Buy</button>
        </div>
      </form>
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
<script type="text/javascript">
$(".select2").select2();

    $(document).ready(function(){
        window.stepper1 = new Stepper(document.querySelector('#stepper1'))

        $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
        });
    });

    function addToCart(id) {
        $.ajax({
            type: "POST",
            url: '{{ url('product')}}/'+id,
            beforeSend: function (xhr) {
                var token = $('meta[name="csrf_token"]').attr('content');

                if (token) {
                    return xhr.setRequestHeader('X-CSRF-TOKEN', token);
                }
            },
            // data: form_data,
            dataType: "json",
            contentType: false,
            cache : false,
            processData : false,
            success: function(result){
                // document.getElementById("submitUser").disabled = false;

                $('#InputModal').modal('hide');
                Swal.fire("Success!", result.message, "success");
                location.reload();
            } ,error: function(xhr, status, error) {
                // Swal.fire("Error!", 'Failed updated article', "error");
                // console.log(xhr.responseJSON.message);
                Swal.fire({
                title: 'Error!',
                icon: 'error',
                html: xhr.responseJSON.message,
                });
                // document.getElementById("submitUser").disabled = false;
            },

        });
    }

    function show(e) {
        $('#show_id').val($(e).data('id'));
        $('#product_name').html($(e).data('product_name'));
        if($(e).data('discount')) {
            $('#price_discount').html(numberWithCommas(($(e).data('price') - ($(e).data('price') * ($(e).data('discount')/100)))));
        } else {
            $('#price_discount').html(0);
        }
        $('#price').html(numberWithCommas($(e).data('price')));
        $('#dimension').html($(e).data('dimension'));
        $('#unit').html($(e).data('unit'));

        $('.alert').hide();
    }

    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function calculate(id, price, qty) {
        var qty = $('#qty'+id).val();
        var subtotal = price * qty;
        $('#subtotal'+id).html(subtotal);
        $('#subtotal_pure'+id).val(subtotal);
        var values = $("input[name='subtotal_pure[]']")
              .map(function(){return parseInt($(this).val());}).get();

        var total = 0;
        for(i=0;i<values.length;i++) {
            total += values[i];
        }

        var grandtotal = 'Rp. ' + numberWithCommas(total);
        $('#grandtotal').html(grandtotal);
        $('#grandtotal_pure').val(total);
    }

  function save()
  {
    let product_code = $("input[name='product_code[]']")
              .map(function(){return $(this).val();}).get();
    let price = $("input[name='price[]']")
              .map(function(){return parseInt($(this).val());}).get();
    let qty = $("input[name='qty[]']")
              .map(function(){return parseInt($(this).val());}).get();

    var url = "{{ url('transaction') }}";

        //    document.getElementById("submitUser").disabled = true;
            var form_data = new FormData();
                form_data.append('product_code', product_code);
                form_data.append('price', price);
                form_data.append('quantity', qty);

            $.ajax({
                type: "POST",
                url: url,
                beforeSend: function (xhr) {
                    var token = $('meta[name="csrf_token"]').attr('content');

                    if (token) {
                        return xhr.setRequestHeader('X-CSRF-TOKEN', token);
                    }
                },
                data: form_data,
                dataType: "json",
                contentType: false,
                cache : false,
                processData : false,
                success: function(result){
                    // document.getElementById("submitUser").disabled = false;

                    // $('#InputModal').modal('hide');
                    Swal.fire("Success!", result.message, "success");
                    location.reload();
                } ,error: function(xhr, status, error) {
                    // Swal.fire("Error!", 'Failed updated article', "error");
                    // console.log(xhr.responseJSON.message);
                    Swal.fire({
                    title: 'Error!',
                    icon: 'error',
                    html: xhr.responseJSON.message,
                    });
                    // document.getElementById("submitUser").disabled = false;
                },

            });
  }
</script>
@stop
