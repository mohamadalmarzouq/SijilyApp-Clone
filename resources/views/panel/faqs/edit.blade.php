@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Edit {{ setText($module,true) }}</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>
                        <form method="POST" action="{{ route($module.'.update',['id' => $data->id]) }}" id="editForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Question <span class="color_red">*</span></label>
                                        <input type="text" class="form-control" name="question" required value="{{$data->question}}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Question (Ar) <span class="color_red">*</span></label>
                                        <input type="text" class="form-control" name="question_ar" required value="{{$data->question_ar}}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Answer <span class="color_red">*</span></label>
                                        <input type="text" class="form-control" name="answer" required value="{{$data->answer}}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Answer (Ar) <span class="color_red">*</span></label>
                                        <input type="text" class="form-control" name="answer_ar" required value="{{$data->answer_ar}}">
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
