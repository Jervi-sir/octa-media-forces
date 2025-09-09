<?php

namespace App\Http\Controllers;

use App\Models\Ogm;
use App\Models\Os;
use App\Models\Product;
use App\Models\Status;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $ogm = Ogm::first();
        $os = Os::where('ogm_id', $ogm->id)->orderBy('id')->first();

        if ($ogm->is_approved === false) {
            // check the total of products
            $published_status = Status::where('name', 'published')->first();
            $count = Product::where('os_id', $os->id)
                ->where('status_id', $published_status->id)
                ->count();
            // check the progress status of the qualification
            // $ogm_progress = OgmQualificationProgress::where('ogm_id', $ogm->id)->first();
        }

        dd($count, $ogm_progress->progress['ogm_qualification']['type'] === 'completed');
    }
}
