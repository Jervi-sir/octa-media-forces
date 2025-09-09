<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class QualificationStep2Controller extends Controller
{
    public function showStep2(Request $request) {}
    public function listDrafts(Request $request) {}
    public function createPrePost(Request $request) {}
    public function listPrePosts(Request $request) {}
    public function showPrePost($product_id,Request $request) {}
    public function updatePrePost($product_id,Request $request) {}
    public function deletePrePost($product_id,Request $request) {}
    public function createPrePostEntry($product_id,Request $request) {}
}
