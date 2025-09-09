<?php

namespace App\Http\Controllers\Api\v2_9\OGM;

use App\Helpers\v2_9\OGMHelpers;
use App\Http\Controllers\Controller;
use App\Models\OgmQualificationProgress;
use App\Models\Os;
use App\Models\Product;
use App\Models\QualificationOperation3;
use App\Models\QualificationOperation4;
use App\Models\QualificationRead;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QualificationController extends Controller
{

    protected $qualifications = [
        [
            'id' => 'intro_octaprise',
            'title' => 'What is Octaprise?',
            'description' => 'Octaprise is a system that connects real clothing stores to customers through one smart app.',
            'icon' => 'info-1',
            'order' => 1,
            'unlocked' => true,
        ],
        [
            'id' => 'intro_ogm',
            'title' => 'Oh OGM! What is OGM?',
            'description' => 'A person who manages real clothes store products through the OGM House app.',
            'icon' => 'info-1',
            'order' => 2,
            'unlocked' => false,
        ],
        [
            'id' => 'ogm_qualification',
            'title' => 'OGM Qualification Test',
            'description' => 'Answer a few quick questions to prove your commitment and unlock the next level.',
            'icon' => 'info-2',
            'order' => 3,
            'unlocked' => false,
        ],
        [
            'id' => 'claim_store',
            'title' => 'Claim Your Store',
            'description' => 'Start with 1200 DZD and your first store becomes yours.',
            'icon' => 'money',
            'order' => 4,
            'unlocked' => false,
        ],
        [
            'id' => 'completion_message',
            'title' => 'Congrats genius.',
            'description' => 'You’re OGM now. The game changes from here.',
            'icon' => 'info-1',
            'order' => 5,
            'unlocked' => false,
        ],
    ];

    protected function updateQualificationsAndProgress(array $userProgress): array
    {
        foreach ($this->qualifications as $qualification) {
            $qualId = $qualification['id'];
            if (!isset($userProgress['progress'][$qualId])) {
                $userProgress['progress'][$qualId] = [
                    'type' => 'start',
                    'progress' => 0,
                    'read_time' => '0min',
                ];
            }
        }
        $lastCompletedOrder = -1;
        foreach ($userProgress['progress'] as $qualId => $progress) {
            if ($progress['type'] === 'completed') {
                $qual = array_filter($this->qualifications, fn($q) => $q['id'] === $qualId);
                $qual = reset($qual);
                if ($qual && $qual['order'] > $lastCompletedOrder) {
                    $lastCompletedOrder = $qual['order'];
                }
            }
        }
        $qualifications = array_map(function ($qualification) use ($lastCompletedOrder) {
            if ($qualification['id'] === 'intro_octaprise') {
                return array_merge($qualification, ['unlocked' => true]);
            }
            return array_merge($qualification, ['unlocked' => $qualification['order'] <= $lastCompletedOrder + 1]);
        }, $this->qualifications);

        return [
            'qualifications' => $qualifications,
            'userProgress' => $userProgress,
        ];
    }


    public function status(Request $request)
    {
        $ogm = Auth::user(); // Adjust guard if necessary
        if (!$ogm) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Fetch the user's qualification progress from the database
        $progressRecord = OgmQualificationProgress::where('ogm_id', $ogm->id)->first();

        // Initialize userProgress structure
        $userProgress = [
            'user_id' => $ogm->id,
            'progress' => [],
        ];

        if ($progressRecord && $progressRecord->progress) {
            // Decode the JSON progress field
            $userProgress['progress'] = $progressRecord->progress;
            // Optionally, include the status from the ogm_qualification_progress table
            $userProgress['status'] = $progressRecord->status;
        }

        // Use the reusable function to update qualifications and progress
        $result = $this->updateQualificationsAndProgress($userProgress);

        return response()->json([
            'is_approved' => $ogm->is_approved,
            'max_published_for_approval' => OGMHelpers::$max_published_for_approval,
            'me' => OGMHelpers::OgmAccount($ogm),
            'qualifications' => $result['qualifications'],
            'userProgress' => $result['userProgress'],
        ]);
    }

    public function updateProgress(Request $request)
    {
        $request->validate([
            'qualification_id' => 'required|string|in:intro_octaprise,intro_ogm,ogm_qualification,claim_store,completion_message',
            'progress.read_time' => 'sometimes|string',
        ]);

        $ogm = Auth::user();
        if (!$ogm) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $progressRecord = OgmQualificationProgress::firstOrCreate(
            ['ogm_id' => $ogm->id],
            ['status' => 'pending', 'progress' => []]
        );

        $progress = $progressRecord->progress;

        // Define the order of qualifications
        $qualificationOrder = [
            'intro_octaprise',
            'intro_ogm',
            'ogm_qualification',
            'claim_store',
            'completion_message',
        ];

        // Update progress for the current qualification
        if (in_array($request->qualification_id, ['intro_octaprise', 'intro_ogm', 'completion_message'])) {
            $progress[$request->qualification_id] = [
                'type' => 'completed',
                'progress' => 100,
                'read_time' => $request->input('progress.read_time', '0min'),
            ];
        } else {
            $progress[$request->qualification_id] = $progress[$request->qualification_id] ?? [
                'type' => 'start',
                'progress' => 0,
                'read_time' => $request->input('progress.read_time', '0min'),
            ];
        }

        // Unlock the next qualification if the current one is completed
        $currentIndex = array_search($request->qualification_id, $qualificationOrder);
        if ($currentIndex !== false && $currentIndex < count($qualificationOrder) - 1) {
            $nextQualificationId = $qualificationOrder[$currentIndex + 1];
            // Ensure the next qualification is initialized in progress if not already
            if (!isset($progress[$nextQualificationId])) {
                $progress[$nextQualificationId] = [
                    'type' => 'start',
                    'progress' => 0,
                    'read_time' => '0min',
                ];
            }
        }

        $progressRecord->update(['progress' => $progress]);
        if ($progressRecord->status === 'pending' && $request->qualification_id === 'intro_octaprise') {
            $progressRecord->update(['status' => 'in-review']);
        }

        // Prepare userProgress for the reusable function
        $userProgress = [
            'user_id' => $ogm->id,
            'progress' => $progressRecord->progress,
            'status' => $progressRecord->status,
        ];

        // Use the reusable function to update qualifications and progress
        $result = $this->updateQualificationsAndProgress($userProgress);

        return response()->json([
            'qualifications' => $result['qualifications'],
            'userProgress' => $result['userProgress'],
        ]);
    }



    public function toRead($type, Request $request)
    {
        $qualification_read = QualificationRead::where('type', $type)->firstOrFail();
        $ogm = $request->user();
        $is_liked = $qualification_read->likes()->where('ogm_id', $ogm->id)->exists();

        return response()->json([
            'id' => $qualification_read->id,
            'title' => $qualification_read->title,
            'content' => $qualification_read->content,
            'likes' => $qualification_read->likes, // Integer from the table
            'is_liked' => $is_liked,
            'created_at' => $qualification_read->created_at->toISOString(),
            'updated_at' => $qualification_read->updated_at->toISOString(),
            'type' => $qualification_read->type,
        ]);
    }

    public function createStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'nullable',
            'storeName' => 'required|string|max:255',
            'wilayaId' => 'required|exists:wilayas,id',
            'phoneNumber' => 'required|string|max:20',
            'mapLink' => 'required',
            'storeBio' => 'required|string',
            'imageUrl' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        $os = null;
        $message = null;

        if ($request->has('id')) {
            $os = Os::find($request->id);
            $message = 'Updated Successfully';
        } else {
            $os = new Os();
            $message = 'Created Successfully';
        }

        $os->wilaya_id = $request->wilayaId;
        $os->store_name = $request->storeName;
        $os->image = $request->imageUrl;
        $os->phone_number = $request->phoneNumber;
        $os->map_link = $request->mapLink;
        $os->bio = $request->storeBio;
        $os->save();

        return response()->json([
            'message' => $message,
            'store' => OGMHelpers::OsFormat($os),
        ], 200);
    }

    public function getStore(Request $request)
    {
        $ogm = $request->user();
        $store = Os::where('ogm_id', $ogm->id)->orderBy('id')->first();

        return response()->json(OGMHelpers::OsFormat($store));
    }

    public function getOperation2(Request $request)
    {
        $ogm = $request->user();
        $store = Os::where('ogm_id', $ogm->id)->orderBy('id')->first();
        $status = Status::where('name', 'published')->first();
        $count_published = Product::where('os_id', $store->id)->where('status_id', $status->id)->count();
        $ogm_progress = OgmQualificationProgress::where('ogm_id', $ogm->id)->first();
        return response()->json([
            'count' => $count_published,
            'omg_progress' => $ogm_progress
        ]);
    }


    public function saveOperation3(Request $request)
    {
        Validator::make($request->all(), [
            'images' => 'required|array',
        ]);

        $ogm = $request->user();

        $packaging = QualificationOperation3::updateOrCreate(
            ['ogm_id' => $ogm->id],
            ['images' => OGMHelpers::normalizeImagePath($request->images)]
        );

        return response()->json([
            'packaging' => [
                'id' => $packaging->id,
                'images' => OGMHelpers::GenerateImageUrl($packaging->images),
            ]
        ]);
    }

    public function getOperation3(Request $request)
    {

        $ogm = $request->user();

        $packaging = QualificationOperation3::where('ogm_id', $ogm->id)->first();
        if ($packaging)
            return response()->json([
                'packaging' => [
                    'id' => $packaging->id,
                    'images' => OGMHelpers::GenerateImageUrl($packaging->images),
                ]
            ]);

        return response()->json([
            'packaging' => []
        ]);
    }

    public function getOperation4(Request $request)
    {
        $ogm = $request->user();
        $operation4 = QualificationOperation4::where('ogm_id', $ogm->id)->first();

        return response()->json([
            'operation4' => $operation4
        ]);
    }

    public function saveOperation4(Request $request)
    {
        Validator::make($request->all(), [
            'question_1' => 'required|string',
            'question_2' => 'required|string',
            'question_3' => 'required|string',
            'question_4' => 'required|string',
        ])->validate();

        $ogm = $request->user();

        $operation4 = QualificationOperation4::updateOrCreate(
            ['ogm_id' => $ogm->id],
            [
                'question_1' => $request->question_1,
                'question_2' => $request->question_2,
                'question_3' => $request->question_3,
                'question_4' => $request->question_4,
            ]
        );

        return response()->json([
            'operation4' => $operation4
        ]);
    }


    public function claimYourStore(Request $request)
    {
        $ogm = $request->user();
        // $os = Os::where('ogm_id', $ogm->id)->orderBy('id')->first();
        $ogm_progress = OgmQualificationProgress::where('ogm_id', $ogm->id)->first();
        $progress = $ogm_progress->progress; // Get the entire progress array

        $progress['claim_store']['type'] = 'completed';       // failed, completed
        $ogm_progress->progress = $progress;

        $ogm_progress->save();
        return response()->json([
            'message' => 'unlocked',
            'progress' => $ogm_progress
        ]);
    }

    public function testOgmEligibility(Request $request) 
    {
        $ogm = $request->user();

        return response()->json([
            'is_eligible' => true
        ]);
    }
}
