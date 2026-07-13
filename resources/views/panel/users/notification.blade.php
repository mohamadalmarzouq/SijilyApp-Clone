@extends('panel.master')

@section('main')
    <div class="contents pt-4 pl-4 pr mb-3">
        @include('auth.includes.flash_mesages')
        <div class="row row-xs">
            <div class="col-sm-12 col-lg-12 mt-2">
                <h3 class="tx-18">Notification Settings</h3>
                <div class="card card-body flex-row justify-content-between">
                    <form class="w-100" method="POST"
                          action="{{ route('update_notification',['id' => $data->id]) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-sm-2">
                                <label for="" class="mb-1">Notification On/Off</label>
                                <div class="w-100 mb-4 mt-2 position-relative">
                                    <input type="hidden" name="notification_enable" value="0"/>
                                    <input type="checkbox"
                                           {{ $data->notification_enable ? 'checked' : '' }} name="notification_enable"
                                           value="1"/>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary float-right">Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <!--/// first row end here ///-->
    </div>
@endsection
