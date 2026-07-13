@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        <h3 class="tx-18">Add Subscription</h3>
        <div class="row row-xs mt-2">
            <div class="col-sm-12 col-lg-12">
                <div class="card card-body">
                    <div>
                        <div id="errorMsg" class="alert alert-danger d-none"></div>
                        <form method="POST" action="{{ route($module.'.add') }}" id="addForm">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Subscription Name <span class="color_red">*</span></label>
                                        <input name="subscription" type="text" class="form-control" required value=""/>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Subscription Name (Ar) <span class="color_red">*</span></label>
                                        <input name="subscription_ar" type="text" class="form-control" required value=""/>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Package Amount <span class="color_red">*</span></label>
                                        <input name="amount" step="1" onKeyPress="if(this.value.length==4) return false;"  type="number" onkeyup="this.value=this.value.replace(/[^\d]/,'')" min="0" required class="form-control" value="1">
                                    </div>
                                </div>
                                 <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Amount (Per user) <span class="color_red">*</span></label>
                                        <input name="per_user_amount" readonly onKeyPress="if(this.value.length==4) return false;"  step="1" type="number" onkeyup="this.value=this.value.replace(/[^\d]/,'')" min="0" required class="form-control" value="0">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Sub Title <span class="color_red">*</span></label>
                                        <input name="title" type="text" required class="form-control" value="">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Sub Title (Ar)<span class="color_red">*</span></label>
                                        <input name="title_ar" type="text" required class="form-control" value="">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Subscription Type</label>
                                        <select name="type" id="subscription_type" class="form-control">
                                            <option value="1">Single User</option>
                                            <option value="2">Multiple User</option>
                                            <option value="3">Coming Soon</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Images <span class="color_red">*</span></label>
                                        <input type="file" id="subscription_img" name="image" accept=".jpg,.png,.jpeg" required/>
                                        <p class="img_error"></p>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">User Register Limit <span class="color_red">*</span></label>
                                        <input name="register_limit" onKeyPress="if(this.value.length==4) return false;" readonly step="1" type="number" onkeyup="this.value=this.value.replace(/[^\d]/,'')" min="0" required class="form-control" value="0">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Subscription Duration (in days) <span class="color_red">*</span></label>
                                        <input name="duration" onKeyPress="if(this.value.length==4) return false;"  step="1" type="number" onkeyup="this.value=this.value.replace(/[^\d]/,'')" min="3" required class="form-control" value="3">
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Content</label>
                                        <textarea  name="content" type="email" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="" class="mb-1">Content (Ar)</label>
                                        <textarea  name="content_ar" type="email" class="form-control"></textarea>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <input type="hidden" name="status_id" value="1"/>
                                </div>
                            </div>
                            <button class="btn btn-primary float-right" type="submit">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>

        function validate(evt) {
            var theEvent = evt || window.event;

            // Handle paste
            if (theEvent.type === 'paste') {
                key = event.clipboardData.getData('text/plain');
            } else {
            // Handle key press
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
            }
            var regex = /[0-9]|\./;
            if( !regex.test(key) ) {
                theEvent.returnValue = false;
                if(theEvent.preventDefault) theEvent.preventDefault();
            }
        }

        // document.getElementById('subscription_img').addEventListener('change',function(){
        //     console.log("width: "+this.clientWidth);
        //     console.log("height: "+this.clientHeight);

        // })
    </script>
@endsection

