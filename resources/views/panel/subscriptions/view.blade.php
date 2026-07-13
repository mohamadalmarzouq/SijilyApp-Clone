@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">View {{ setText($module,true) }}</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>
                        {{-- {{ dd($data) }} --}}
                        {{-- {{ route($module.'.edit',['id' => $data->id]) }}  id="editForm"--}}
                        <form method="POST" action="">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Subscription Name</label>
                                        <input name="subscription" type="text" class="form-control" disabled value="{{ $data->subscription }}"/>
                                        <input type="hidden" name="id" value="{{ $data->id }}"/>
                                    </div>
                                </div>
                                 <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Amount (Per user)</label>
                                        <input name="per_user_amount" type="number" min="0" disabled class="form-control" value="{{ $data->per_user_amount }}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Sub Title</label>
                                        <input name="title" type="text" disabled class="form-control" value="{{ $data->title }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        {{-- <label for="" class="mb-1">Images</label> --}}
                                        {{-- <input type="file" name="image" accept=".png,.jpg,.bmp" disabled/> --}}
                                        @if($data->image !="")
                                            <img src="{{ asset($data->image)  }}" style="background:#000;" class=" col-md-2 img img-thumbnail"/>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Content</label>
                                        <textarea  name="content" type="email" disabled class="form-control" value="{{ $data->content }}">{{ $data->content }}</textarea>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <select class="form-control" name="status" disabled>
                                        <option value="">Select Status</option>
                                        <option value="0" {{ !$data->status ? "selected":""  }}>In Active</option>
                                        <option value="1" {{ $data->status ? "selected":""  }}>Active</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

