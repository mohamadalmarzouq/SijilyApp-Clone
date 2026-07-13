<?php namespace App\Http\Controllers\Api;

use App\Http\Validation\RulesIdentity as Rules;
use App\Http\Controllers\Controller;
use App\Models\HelpVideo;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    function __construct()
    {
        $this->help_video = new HelpVideo();
    }

    public function getHelpVideos(Request $request)
    {
        $data = $this->help_video::get();
        return makeClientHappy($data,trans('auth.success'));
    }
}
