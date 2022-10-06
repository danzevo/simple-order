@extends('adminlte::page')

@section('title', 'Article')

@section('content_header')
    <h1>Article</h1>
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
            <div class="row">
              <button type="button" class="btn btn-primary mt-3 ml-3" onclick="$('#InputModal').modal('show');resetForm()">+ Tambah</button>
            </div>
            <div class="table-responsive mt-4">
                <table id="article" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Image</th>
                            <th>Creator</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $value)
                            <tr>
                                <td>{{ $paging++ }}</td>
                                <td>{{ $value['title'] }}</td>
                                <td>{{ $value['content'] }}</td>
                                <td>
                                    @if($value['thumbnail_image'])
                                    <img alt="image article" width="60" height="60" src="{{ asset('storage/image/'.$value['thumbnail_image']) }}">
                                    @endif
                                </td>
                                <td>{{ $value['user']['name'] }}</td>
                                <td><a class="btn btn-success btn-sm"
                                    data-id = "{{ $value['id'] }}"
                                    data-title = "{{ $value['title'] }}"
                                    data-content = "{{ $value['content'] }}"
                                    data-image = "{{ $value['thumbnail_image'] }}"
                                onclick="editArticle(this)" data-toggle="modal" data-target="#InputModal"><i class="fa fa-edit"></i> ubah</a>
                                <a class="btn btn-warning btn-sm" onclick="destroy({{ $value['id'] }})"><i class="fa fa-trash"></i> hapus</a></td>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- <div class="d-flex justify-content-center"> --}}
            {{-- </div> --}}
        </div>
    </div>
</div>
{{ $data->links() }}

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
      <form method="POST" id="formArticle">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="modal-body">
          <div class="form-group row">
            <input type="hidden" id="id" name="id">
            <label for="title" class="col-sm-3 col-form-label">Title</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" autocomplete="off" id="title" name="title" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="content" class="col-sm-3 col-form-label">Content</label>
            <div class="col-sm-8">
                <textarea class="form-control" autocomplete="off" id="content" name="content"></textarea>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-6 col-12">
            <label for="image" class="col-sm-6 col-form-label">Image</label>
                <label class="label" data-toggle="tooltip" title="" data-original-title="Change image article"
                    aria-describedby="tooltip733556">
                    <img class="rounded" id="avatar" width="160" height="160"
                        src="{{ asset('image/default-foto.png') }}"
                        alt="avatar">
                    <input type="file" class="sr-only" id="input" name="image" accept="image/*" form="form-article">
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
          <button type="button" onclick="saveArticle()" data-dismiss="modal" id="submitArticle" class="btn btn-primary">Save</button>
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
CKEDITOR.replace( 'content' );

$(document).ready(function(){
  $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
  });
//   location.reload();

});

function loadList() {
    let page_url = '{{ url('get-data-article') }}';

    $.ajax({
            type: "GET",
            url: page_url,
            /* beforeSend: function (xhr) {
                var token = $('meta[name="csrf_token"]').attr('content');

                if (token) {
                    return xhr.setRequestHeader('X-CSRF-TOKEN', token);
                }
            }, */
            dataType: "json",
            contentType: false,
            cache : false,
            processData : false,
            success: function(result){
                $('#content_result').html(result.data);
            } ,error: function(xhr, status, error) {
                console.log(xhr.responseJSON.message);
            },

        });
}

function editArticle(e) {
        $('#id').val($(e).data('id'));
        $('#title').val($(e).data('title'));
        CKEDITOR.instances.content.setData($(e).data('content'));

        if($(e).data('image') != null) {
        $('#avatar').prop('src', '{{ asset('storage/image') }}/'+$(e).data('image'));
        } else {
        $('#avatar').prop('src', '{{ asset('image') }}/default-foto.png');
        }

        $('.alert').hide();
    }

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

  function saveArticle()
  {
    let id = document.getElementById('id').value;
    let title = document.getElementById('title').value;
    let content = CKEDITOR.instances['content'].getData();
    let image = document.getElementById("input").files[0];

    var url = "{{ url('articles') }}";
    if(id != '') {
        url = "{{ url('articles') }}/"+id;
    }

    if(title == ''){
          Swal.fire("Error!", "Title is required", "error");
       }else{
        // swalLoading();
           document.getElementById("submitArticle").disabled = true;
            var form_data = new FormData();
                form_data.append('id', id);
                form_data.append('title', title);
                form_data.append('content', content);
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
                    document.getElementById("submitArticle").disabled = false;

                    $('#InputModal').modal('hide');
                    Swal.fire("Success!", result.message, "success");
                    window.location = "{{ url('articles') }}";
                } ,error: function(xhr, status, error) {
                    // Swal.fire("Error!", 'Failed updated article', "error");
                    // console.log(xhr.responseJSON.message);
                    Swal.fire({
                    title: 'Error!',
                    icon: 'error',
                    html: xhr.responseJSON.message,
                    });
                    document.getElementById("submitArticle").disabled = false;
                },

            });
       }
  }

    function destroy(id){
        var url = "{{url('articles')}}"+"/"+id;
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
                        type: "DELETE",
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
                            window.location = "{{ url('articles') }}";
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
    $('#title').val('');
    CKEDITOR.instances.content.setData('');
    $('#input').val('');
    $('.alert').hide();
    $('#avatar').prop('src', '{{ asset('image') }}/default-foto.png');
    $('#formArticle').trigger("reset");
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
            $.ajax("{{ url('upload-article') }}", {
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
