<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GoogleCloudVision\GoogleCloudVision;
use GoogleCloudVision\Request\AnnotateImageRequest;

class OcrController extends Controller
{
    public function annotateImage(Request $request){
        // $data = ['amount'=>20];
        // return makeClientHappy($data);
        // return false;
        if($request->file('image')){
          //convert image to base64
          $image = base64_encode(file_get_contents($request->file('image')));
          //prepare request
          $request = new AnnotateImageRequest();
          $request->setImage($image);
          $request->setFeature("TEXT_DETECTION");
          $gcvRequest = new GoogleCloudVision([$request],  env('GOOGLE_CLOUD_KEY'));
          //send annotation request
          $response = $gcvRequest->annotate();
          if(isset($response->responses[0])){
            if(isset($response->responses[0]->fullTextAnnotation)){
              $text = $response->responses[0]->fullTextAnnotation->text;
              $matches = array();
              $s = preg_match('/(\d[\d.,]*)/', $text, $matches);
              if(!empty($matches)){
              $response = ["amount"=>$matches[1]];
              }else{
                $response = [];
              }
            }else{
              $response = [];
            }
          }

          return makeClientHappy($response,'success');

        }
    }
}
