@extends('adminlte::page')

@section('title', 'User')

@section('content_header')
    <h1>User</h1>
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
              <button type="button" class="btn btn-primary mt-3 ml-3" onclick="$('#InputModal').modal('show');resetForm()">+ Add</button>
            </div>
            <div class="table-responsive mt-4">
                <table id="article" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>*</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $key => $value)
                            <tr>
                                <td>{{ $paging++ }}</td>
                                <td>{{ $value['name'] }}</td>
                                <td>{{ $value['email'] }}</td>
                                <td>{{ $value['role'] }}</td>
                                <td><a class="btn btn-success btn-sm"
                                    data-id = "{{ $value['id'] }}"
                                    data-name = "{{ $value['name'] }}"
                                    data-email = "{{ $value['email'] }}"
                                    data-role = "{{ $value['role'] }}"
                                onclick="editUser(this)" data-toggle="modal" data-target="#InputModal"><i class="fa fa-edit"></i> Edit</a>
                                <a class="btn btn-warning btn-sm" onclick="destroy({{ $value['id'] }})"><i class="fa fa-trash"></i> Delete</a></td>
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
      <form method="POST" id="formUser">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="modal-body">
          <div class="form-group row">
            <input type="hidden" id="id" name="id">
            <label for="name" class="col-sm-3 col-form-label">Name</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" autocomplete="off" id="name" name="name" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label">Email</label>
            <div class="col-sm-8">
                <input type="email" class="form-control" autocomplete="off" id="email" name="email" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="role" class="col-sm-3 col-form-label">Role</label>
            <div class="col-sm-8">
                <select class="form-control select2" autocomplete="off" id="role" name="role" required>
                    @foreach($role as $row)
                    <option value="{{ $row }}">{{ $row }}</option>
                    @endforeach
                </select>
            </div>
          </div>
          <div class="form-group row">
            <label for="password" class="col-sm-3 col-form-label">Password</label>
            <div class="col-sm-8">
                <input type="password" class="form-control" autocomplete="off" id="password" name="password" required>
            </div>
          </div>
          <div class="form-group row">
            <label for="password_confirmation" class="col-sm-3 col-form-label">Password confirmation</label>
            <div class="col-sm-8">
                <input type="password" class="form-control" autocomplete="off" id="password_confirmation" name="password_confirmation" required>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" onclick="saveUser()" data-dismiss="modal" id="submitUser" class="btn btn-primary">Save</button>
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
  $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
  });
});

function editUser(e) {
        $('#id').val($(e).data('id'));
        $('#name').val($(e).data('name'));
        $('#email').val($(e).data('email'));
        $('#role').val($(e).data('role'));

        $('.alert').hide();
    }

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

  function saveUser()
  {
    let id = document.getElementById('id').value;
    let name = document.getElementById('name').value;
    let email = document.getElementById('email').value;
    let password = document.getElementById('password').value;
    let password_confirmation = document.getElementById('password_confirmation').value;
    let role = document.getElementById('role').value;

    var url = "{{ url('users') }}";
    if(id != '') {
        url = "{{ url('users') }}/"+id;
    }

    if(name == ''){
          Swal.fire("Error!", "Name is required", "error");
       }else{
        // swalLoading();
           document.getElementById("submitUser").disabled = true;
            var form_data = new FormData();
                form_data.append('id', id);
                form_data.append('name', name);
                form_data.append('email', email);
                form_data.append('role', role);
                form_data.append('password', password);
                form_data.append('password_confirmation', password_confirmation);
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
                    document.getElementById("submitUser").disabled = false;

                    $('#InputModal').modal('hide');
                    Swal.fire("Success!", result.message, "success");
                    window.location = "{{ url('users') }}";
                } ,error: function(xhr, status, error) {
                    // Swal.fire("Error!", 'Failed updated article', "error");
                    // console.log(xhr.responseJSON.message);
                    Swal.fire({
                    title: 'Error!',
                    icon: 'error',
                    html: xhr.responseJSON.message,
                    });
                    document.getElementById("submitUser").disabled = false;
                },

            });
       }
  }

    function destroy(id){
        var url = "{{url('users')}}"+"/"+id;
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
                            window.location = "{{ url('users') }}";
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
    $('#name').val('');
    $('#email').val('');
    $('#role').val('administrator');
    $('.alert').hide();
    $('#formUser').trigger("reset");
}
</script>
@stop
