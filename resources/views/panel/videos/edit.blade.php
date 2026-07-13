@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Edit {{ setText($module,true) }}</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>
                         <div id="errorMsg" class="alert alert-danger d-none"></div>
                        <form method="POST" action="{{ route($module.'.update',['id' => $data->id]) }}" id="editForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                 <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Title <span class="color_red">*</span></label>
                                        <input type="text" class="form-control" name="title" value="{{ $data->title }}" required>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Title (Ar) <span class="color_red">*</span></label>
                                        <input type="text" class="form-control" name="title_ar" required value="{{ $data->title_ar }}">
                                    </div>
                                </div>
                                {{-- <div class="col-sm-12" id="videoEmbeddedurl">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Embedded Url <span class="color_red">*</span></label>
                                        <input type="text" class="form-control" name="embedded_url" id="videoEmbeddedurlField" value="{{ $data->embedded_url }}">
                                        <input type="hidden" name="type" value="embedded"/>
                                    </div>
                                </div> --}}
                                <div class="col-sm-12" id="videoEmbeddedurl">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Url <span class="color_red">*</span></label>
                                        <input type="text" class="form-control" name="url" id="videoEmbeddedurlField" value="{{ $data->url }}">
                                        <input type="hidden" name="type" value="embedded"/>
                                    </div>
                                </div>

                                <div class="col-sm-3" id="videoEmbeddedurl">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Thumbnail </label>
                                        <input type="file" class="form-control" name="image" accept=".jpg,.png,.jpeg" onchange="loadFile(event)" id="thumb_nail" >
                                        <img id="output" src="{{ asset($data->thumb_nail) }}" class="img img-thumbnail"/>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary float-right" type="submit">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    var loadFile = function(event) {
        var reader = new FileReader();
        reader.onload = function(){
            var output = document.getElementById('output');
             output.classList.remove("d-none");
            output.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    };
</script>
@endsection
