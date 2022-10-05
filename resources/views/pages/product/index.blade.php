@extends('adminlte::page')

@section('title', 'Produk')

@section('content_header')
    <h1>Produk</h1>
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
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">
              <button type="button" class="btn btn-primary mt-3 ml-3" onclick="$('#InputModal').modal('show');resetForm()">+ Tambah</button>
            </div>
            <div class="table-responsive mt-4">
                <table id="product" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Harga</th>
                            <th>Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                </table>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="InputModal" aria-labelledby="InputModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="InputModalLabel">Input Form</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST" action="{{ url('admin/product/save') }}" id="formProduct">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="modal-body">
          <div class="form-group row">
            <input type="hidden" id="id" name="id">
            <label for="nama" class="col-sm-3 col-form-label">Nama</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" autocomplete="off" id="nama" name="nama" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="deskripsi" class="col-sm-3 col-form-label">Deskripsi</label>
            <div class="col-sm-8">
                <textarea class="form-control" autocomplete="off" id="deskripsi" name="deskripsi"></textarea>
            </div>
          </div>
          <div class="form-group row">
            <label for="harga" class="col-sm-3 col-form-label">Harga</label>
            <div class="col-sm-8">
                <div class="input-group mb-3">
                    <span class="input-group-text">Rp</span>
                        <input type="text" autocomplete="off" class="form-control numeral-mask" id="harga" name="harga" required>
                    <span class="input-group-text">.00</span>
                </div>
            </div>
          </div>
          <div class="form-group row">
            <label for="category_id" class="col-sm-3 col-form-label">Kategori</label>
            <div class="col-sm-9">
                <select class="form-control select2" id="category_id" name="category_id" style="width:50%">
                    <option value=''>--Pilih--</option>
                    @foreach($category as $row)
                    <option value="{{ $row->id }}">{{ $row->nama }}</option>
                    @endforeach
                </select>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-6 col-12">
            <label for="deskripsi" class="col-sm-6 col-form-label">Produk</label>
                <label class="label" data-toggle="tooltip" title="" data-original-title="Change image product"
                    aria-describedby="tooltip733556">
                    <img class="rounded" id="avatar" width="160" height="160"
                        src="{{ (isset($product->image_url) && $product->image_url? asset('image_product/'.$product->image_url) : asset('image_product/default-foto.png')) }}"
                        alt="avatar">
                    <input type="file" class="sr-only" id="input" name="image" accept="image/*" form="form-product">
                </label>
                <div class="progress" style="display:none">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0"
                        aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
                <div class="alert" role="alert"></div>
                <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalLabel">Crop the image</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="img-container">
                                    <img id="image" src="https://avatars0.githubusercontent.com/u/3456749">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="crop">Crop</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" onclick="saveProduct()" data-dismiss="modal" id="submitProduct" class="btn btn-primary">Save</button>
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
CKEDITOR.replace( 'deskripsi' );

$(document).ready(function(){
  $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
  });
  loadList();

});

function loadList() {
    const page_url = '{{ url('admin/product/get-data') }}';

    $.fn.dataTable.ext.errMode = 'ignore';
    var table = $('#product').DataTable({
        processing: true,
        serverSide: true,
        "bDestroy": true,
        ajax: {
            url: page_url,
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false,searchable: false},
            {data: 'nama', name: 'nama'},
            {data: 'deskripsi', name: 'deskripsi'},
            {data: 'raw_harga', name: 'raw_harga'},
            {data: 'category_nama', name: 'category_nama'},
            { "data": null,"sortable": false,
                render: function (data, type, row, meta) {
                    var result = '<a class="btn btn-success btn-sm" \
                                    data-id = '+row.id+' \
                                    data-nama = "'+row.nama+'" \
                                    data-deskripsi = \''+row.deskripsi+'\' \
                                    data-harga = '+row.harga+' \
                                    data-category_id = '+row.category_id+' \
                                    data-image = '+row.image+' \
                                onclick="editProduct(this)" data-toggle="modal" data-target="#InputModal"><i class="fa fa-pencil"></i> ubah</a>&nbsp;';
                    result += '<a class="btn btn-warning btn-sm" onclick="destroy('+row.id+')"><i class="fa fa-trash"></i> hapus</a>';
                        return result;
                }
            }
        ],
        responsive: true,
        oLanguage: {
            sLengthMenu: "_MENU_",
            sSearch: ""
        },
        aLengthMenu: [[4, 10, 15, 20], [4, 10, 15, 20]],
        order: [[1, "asc"]],
        pageLength: 10,
        buttons: [
        ],
        initComplete: function (settings, json) {
            $(".dt-buttons .btn").removeClass("btn-secondary")
        },
        drawCallback: function (settings) {
            // console.log(settings.json);
        }
    });

}

function editProduct(e) {
        $('#id').val($(e).data('id'));
        $('#nama').val($(e).data('nama'));
        CKEDITOR.instances.deskripsi.setData($(e).data('deskripsi'));
        $('#harga').val(numberWithCommas(parseInt($(e).data('harga'))));
        $('#category_id').val($(e).data('category_id')).trigger('change');

        if($(e).data('image') != null) {
        $('#avatar').prop('src', '{{ asset('image_product') }}/'+$(e).data('image'));
        } else {
        $('#avatar').prop('src', '{{ asset('image_product') }}/default-foto.png');
        }

        $('.alert').hide();
    }

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

  function saveProduct()
  {
    let id = document.getElementById('id').value;
    let nama = document.getElementById('nama').value;
    let deskripsi = CKEDITOR.instances['deskripsi'].getData();
    let harga = document.getElementById('harga').value;
    let category_id = $('#category_id').val();
    let image = document.getElementById("input").files[0];

    var url = "{{ url('admin/product/save') }}";
    if(id != '') {
        url = "{{ url('admin/product/update') }}/"+id;
    }

    if(nama == ''){
          Swal.fire("Error!", "Name is required", "error");
       }else{
        // swalLoading();
           document.getElementById("submitProduct").disabled = true;
            var form_data = new FormData();
                form_data.append('id', id);
                form_data.append('nama', nama);
                form_data.append('deskripsi', deskripsi);
                form_data.append('harga', harga);
                form_data.append('category_id', category_id);
                form_data.append('image', image);
                if(id != '') {
                    form_data.append('_method', 'PUT');
                }

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
                    document.getElementById("submitProduct").disabled = false;

                    $('#InputModal').modal('hide');
                    Swal.fire("Success!", result.message, "success");
                    loadList();
                } ,error: function(xhr, status, error) {
                    // Swal.fire("Error!", 'Failed updated product', "error");
                    // console.log(xhr.responseJSON.message);
                    Swal.fire({
                    title: 'Error!',
                    icon: 'error',
                    html: xhr.responseJSON.message,
                    });
                    document.getElementById("submitProduct").disabled = false;
                },

            });
       }
  }

    function destroy(id){
        var url = "{{url('admin/product/destroy')}}"+"/"+id;
        Swal.fire({
            title: `Are you sure?`,
            text: ` will be permanantly deleted!`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                if (result.value) {
                        $.ajax({
                    type: "POST",
                    url: url,
                    beforeSend: function (xhr) {
                        var token = $('meta[name="csrf_token"]').attr('content');

                        if (token) {
                            return xhr.setRequestHeader('X-CSRF-TOKEN', token);
                        }
                    },
                    dataType: "json",
                    contentType: false,
                    cache : false,
                    processData : false,
                    success: function(result){
                        Swal.fire("Success!", result.message, "success");
                        loadList();
                    } ,error: function(xhr, status, error) {
                        console.log(xhr.responseJSON.message);
                    },

                });
                }else{

                    }
            })
    }

function resetForm(){
    $('#id').val('');
    $('#nama').val('');
    CKEDITOR.instances.deskripsi.setData('');
    $('#harga').val('');
    $('#input').val('');
    $('#category_id').val('').trigger('change');
    $('.alert').hide();
    $('#avatar').prop('src', '{{ asset('image_product') }}/default-foto.png');
    $('#formProduct').trigger("reset");
}
</script>
<script>
    $('.numeral-mask').each(function (index, ele) {
        var cleaveCustom = new Cleave(ele, {
            numeral:true,
            numeralDecimalMark: ',',
            delimiter: '.'
        });
    });
    window.addEventListener('DOMContentLoaded', function () {
      var avatar = document.getElementById('avatar');
      var image = document.getElementById('image');
      var input = document.getElementById('input');
      var $progress = $('.progress');
      var $progressBar = $('.progress-bar');
      var $alert = $('.alert');
      var $modal = $('#modal');
      var cropper;

      $('[data-toggle="tooltip"]').tooltip();

      input.addEventListener('change', function (e) {
        var files = e.target.files;
        var done = function (url) {
        //   input.value = '';
          image.src = url;
          $alert.hide();
          $modal.modal('show');
        };
        var reader;
        var file;
        var url;

        if (files && files.length > 0) {
          file = files[0];

          if (URL) {
            done(URL.createObjectURL(file));
          } else if (FileReader) {
            reader = new FileReader();
            reader.onload = function (e) {
              done(reader.result);
            };
            reader.readAsDataURL(file);
          }
        }
      });

      $modal.on('shown.bs.modal', function () {
        cropper = new Cropper(image, {
          aspectRatio: 1,
          viewMode: 0
        });
      }).on('hidden.bs.modal', function () {
        cropper.destroy();
        cropper = null;
      });

      document.getElementById('crop').addEventListener('click', function () {
        var initialAvatarURL;
        var canvas;

        $modal.modal('hide');

        if (cropper) {
          canvas = cropper.getCroppedCanvas({
            width: 160,
            height: 160,
          });
          initialAvatarURL = avatar.src;
          avatar.src = canvas.toDataURL();
          $progress.show();
          $alert.removeClass('alert-success alert-warning');
          canvas.toBlob(function (blob) {
            var formData = new FormData();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            formData.append('avatar', blob, 'avatar.jpg');
            $.ajax("{{ url('admin/upload-product') }}", {
              method: 'POST',
              data: formData,
              processData: false,
              contentType: false,

              xhr: function () {
                var xhr = new XMLHttpRequest();

                xhr.upload.onprogress = function (e) {
                  var percent = '0';
                  var percentage = '0%';

                  if (e.lengthComputable) {
                    percent = Math.round((e.loaded / e.total) * 100);
                    percentage = percent + '%';
                    $progressBar.width(percentage).attr('aria-valuenow', percent).text(percentage);
                  }
                };

                return xhr;
              },

              success: function (result) {
                  console.log(result);
                $alert.show().addClass('alert-success').text('Upload success');
              },

              error: function () {
                avatar.src = initialAvatarURL;
                $alert.show().addClass('alert-warning').text('Upload error');
              },

              complete: function () {
                $progress.hide();
              },
            });
          });
        }
      });
    });
</script>
@stop
