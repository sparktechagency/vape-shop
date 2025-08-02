<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    public function getPageContent($type)
    {
        $page = Page::where('type', $type)->first();

        if (!$page) {
            return response()->error('Page not found', 404);
        }

        return response()->success($page, 'Page content retrieved successfully');
    }


    public function updateOrCreatePage(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'type' => 'required|string',
                'content' => 'required|string'
            ]);

            if($validated->fails()) {
                return response()->error($validated->errors()->first(), 422, $validated->errors());
            }

            $page = Page::updateOrCreate(
                ['type' => $request->type],
                ['content' => $request->content]
            );

            return response()->success($page, 'Page updated or created successfully');
        } catch (\Exception $th) {
            return response()->error('Error occurred while updating or creating page', 500, $th->getMessage());
        }
    }
}
