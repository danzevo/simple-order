@extends('adminlte::page')

@section('title', 'Kategori')

@section('content_header')
    <h1>Kategori</h1>
@stop

@section('content')
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
                <table id="category" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
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
      <form method="POST" action="{{ url('admin/category/save') }}" id="formCategory">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="modal-body">
          <div class="form-group row">
            <input type="hidden" id="id" name="id">
            <label for="nama" class="col-sm-3 col-form-label">Nama</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" autocomplete="off" id="nama" name="nama" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" onclick="saveCategory()" data-dismiss="modal" id="submitCategory" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop

@section('css')
@stop

@section('js')
<script type="text/javascript">
$(document).ready(function(){
  $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
  });
  loadList();

});

function loadList() {
    const page_url = '{{ url('admin/category/get-data') }}';

    $.fn.dataTable.ext.errMode = 'ignore';
    var table = $('#category').DataTable({
        processing: true,
        serverSide: true,
        "bDestroy": true,
        ajax: {
            url: page_url,
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false,searchable: false},
            {data: 'nama', name: 'nama'},
            { "data": null,"sortable": false,
                render: function (data, type, row, meta) {
                    var result = '<a class="btn btn-success btn-sm" \
                                    data-id = '+row.id+' \
                                    data-nama = '+row.nama+' \
                                onclick="editCategory(this)" data-toggle="modal" data-target="#InputModal"><i class="fa fa-pencil"></i> ubah</a>&nbsp;';
                    result += '<a class="btn btn-warning btn-sm" onclick="destroy('+row.id+')"><i class="fa fa-pencil"></i> delete</a>';
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
            console.log(settings.json);
        }
    });

}

function editCategory(e) {
        $('#id').val($(e).data('id'));
        $('#nama').val($(e).data('nama'));
    }

  function saveCategory()
  {
    let id = document.getElementById('id').value;
    let nama = document.getElementById('nama').value;
    var url = "{{ url('admin/category/save') }}";
    if(id != '') {
        url = "{{ url('admin/category/update/') }}/"+id;
    }

    if(nama == ''){
          Swal.fire("Error!", "Name is required", "error");
       }else{
        // swalLoading();
           document.getElementById("submitCategory").disabled = true;
            var form_data = new FormData();
                form_data.append('id', id);
                form_data.append('nama', nama);
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
                    document.getElementById("submitCategory").disabled = false;

                    $('#InputModal').modal('hide');
                    Swal.fire("Success!", result.message, "success");
                    loadList();
                } ,error: function(xhr, status, error) {
                    Swal.fire("Error!", xhr.responseJSON.message.nama[0], "error");
                    document.getElementById("submitCategory").disabled = false;
                },

            });
       }
  }

    function destroy(id){
        var url = "{{url('admin/category/destroy')}}"+"/"+id;
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
    $('#formCategory').trigger("reset");
}
</script>
@stop
