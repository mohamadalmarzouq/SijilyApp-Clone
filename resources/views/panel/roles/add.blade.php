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
                <form method="POST" action="{{ route($module.'.add') }}" id="addForm" class="addModal">
                    @csrf
                    <div class="row ">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="" class="mb-1">Name</label>
                                <input type="text" name="name" id="name" required class="form-control" placeholder="Name" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-group pt-2">
                        @include('panel.permissions.show')
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class=" text-right">
                                <button class="btn btn-primary btn-rounded btn-lg float-right" type="submit">Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
