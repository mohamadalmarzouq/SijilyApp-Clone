<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel2"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered set_modal_width" role="document">
        <div class="modal-content tx-14">
            <div class="modal-header">
                <h6 class="modal-title mt-1" id="exampleModalLabel2">Add New {{ setText($module,true) }}</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="{{ route($module.'.add') }}" id="addForm">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="" class="mb-1">Name</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Name">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label for="" class="mb-1">User Role</label>
                            <select class="custom-select mr-0 font-weight-500" name="role_id"
                                    id="role_id">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option
                                        value="{{ $role->id }}">
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="" class="mb-1">Email ID</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Email ID">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <label for="" class="mb-1">Status</label>
                            <select class="custom-select mr-0 font-weight-500" name="user_status_id"
                                    id="user_status_id">
                                @foreach($statuses as $status)
                                    <option
                                        value="{{ $status->id }}">
                                        {{ $status->status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button class="btn btn-primary float-right" type="submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
