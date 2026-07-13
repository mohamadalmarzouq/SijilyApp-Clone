@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Edit {{ setText($module,true) }}</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>
                        <form method="POST" action="{{ route($module.'.update',['id' => $data->id]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Title <span class="color_red">*</span></label>
                                        <input type="text" class="form-control" name="title" value="{{ $data->title }}">
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Title (Ar)<span class="color_red">*</span></label>
                                        <input type="text" class="form-control" name="title_ar" value="{{ $data->title_ar }}">
                                    </div>
                                </div>
                                <div class="col-sm-12" id="pagesDescription">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Description</label>
                                        <textarea name="description" rows="50" cols="40" class="form-control tinymce-editor">{{$data->description}}</textarea>
                                    </div>
                                </div>
                                <div class="col-sm-12" id="pagesDescription">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Description (ar)</label>
                                        <textarea name="description_ar" rows="50" cols="40" class="form-control tinymce-editor">{{$data->description_ar}}</textarea>
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
@endsection
