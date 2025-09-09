<?php

namespace App\Http\Controllers\Api\v2_9\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ogm;
use App\Models\OgmQualificationProgress;
use App\Models\Os;
use App\Models\Status;
use Illuminate\Http\Request;

class QualificationController extends Controller
{
    public function submitReviewOgmQualification(Request $request)
    {
        $ogm = Ogm::first();
        $ogm_progress = OgmQualificationProgress::where('ogm_id', $ogm->id)->first();
        $progress = $ogm_progress->progress; // Get the entire progress array

        $progress['ogm_qualification']['type'] = 'completed';       // failed, completed
        $progress['ogm_qualification']['test_score'] = '19/20';     //
        $ogm_progress->progress = $progress;

        $ogm_progress->save();
        dd($ogm_progress);
    }
}
