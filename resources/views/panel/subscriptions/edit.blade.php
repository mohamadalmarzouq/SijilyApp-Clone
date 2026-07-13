@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Edit {{ setText($module,true) }}</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">

                    <div>

                          <div id="errorMsg" class="alert alert-danger d-none"></div>
                        <form method="POST" action="{{ route($module.'.edit',['id' => $data->id]) }}" id="editForm">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Subscription Name <span class="color_red">*</span></label>
                                        <input name="subscription" type="text" class="form-control" required value="{{ $data->subscription }}"/>
                                        <input type="hidden" name="id" value="{{ $data->id }}"/>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Subscription Name (Ar) <span class="color_red">*</span></label>
                                        <input name="subscription_ar" type="text" class="form-control" required value="{{ $data->subscription_ar }}"/>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Package Amount <span class="color_red">*</span></label>
                                        <input name="amount" step="1" onKeyPress="if(this.value.length==4) return false;"   type="number" onkeyup="this.value=this.value.replace(/[^\d]/,'')" min="0" required class="form-control" value="{{ $data->amount }}">
                                    </div>
                                </div>
                                 <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Amount (Per user) <span class="color_red">*</span></label>
                                        <input name="per_user_amount" {{ $data->type !='2' ? 'readonly':'' }} onKeyPress="if(this.value.length==4) return false;" data-amount="{{ $data->per_user_amount }}" type="number" onkeyup="this.value=this.value.replace(/[^\d]/,'')" min="0" required class="form-control" value="{{ $data->type !='2' ? 0 : $data->per_user_amount}}">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Sub Title <span class="color_red">*</span></label>
                                        <input name="title" type="text" required class="form-control" value="{{ $data->title }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Sub Title (Ar)<span class="color_red">*</span></label>
                                        <input name="title_ar" type="text" required class="form-control" value="{{ $data->title_ar }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Subscription Type</label>
                                        <select id="subscription_type" name="type" class="form-control">
                                            <option value="1" {{ $data->type == 1 ? "selected": "" }}>Single User</option>
                                            <option value="2" {{ $data->type == 2 ? "selected": "" }}>Multiple User</option>
                                            <option value="3" {{ $data->type == 3 ? "selected": "" }}>Coming Soon</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Images</label>
                                        <input type="file" id="subscription_img" name="image" accept=".png,.jpg,.bmp,.jpeg,"/>
                                        <p class="img_error"></p>
                                        @if($data->image !="")
                                            <img src="{{ asset($data->image)  }}" style="background:#000" class=" col-md-2 img img-thumbnail"/>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">User Register Limit <span class="color_red">*</span></label>
                                        <input name="register_limit" {{ $data->type !='2' ? 'readonly':'' }} onKeyPress="if(this.value.length==4) return false;" data-amount="{{ $data->register_limit }}"  step="1" type="number" onkeyup="this.value=this.value.replace(/[^\d]/,'')" min="0" required class="form-control" value="{{ $data->type !='2' ? 0 :$data->register_limit }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Subscription Duration (in days)<span class="color_red">*</span></label>
                                        <input name="duration" onKeyPress="if(this.value.length==4) return false;"  step="1" type="number" onkeyup="this.value=this.value.replace(/[^\d]/,'')" min="3" required class="form-control" value="{{ $data->duration }}">
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Content</label>
                                        <textarea  name="content" type="email" class="form-control" value="{{ $data->content }}">{{ $data->content }}</textarea>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Content (Ar)</label>
                                        <textarea  name="content_ar" type="email" class="form-control" value="{{ $data->content_ar }}">{{ $data->content_ar }}</textarea>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                      <label for="" class="mb-1">Status <span class="color_red">*</span></label>
                                    <select class="form-control" name="status">
                                        {{-- <option value="">Select Status</option> --}}
                                        <option value="0" {{ $data->status == "In-Active" ? "selected":""  }}>In Active</option>
                                        <option value="1" {{ $data->status == "Active" ? "selected":""  }}>Active</option>
                                    </select>
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

