<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\SavedFilterRequest;
use App\Models\SavedFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SavedFilterController extends Controller
{
    /**
     * Store a newly created saved filter.
     */
    public function store(SavedFilterRequest $request)
    {
        $params = Arr::only($request->validated('params'), SavedFilter::PARAMS);
        $params = array_map(fn ($value) => is_scalar($value) ? (string) $value : '', $params);

        $request->user()->savedFilters()->create([
            'name' => $request->validated('name'),
            'params' => $params,
        ]);

        return $this->response($request, __('Filter has been saved.'));
    }

    /**
     * Rename the specified saved filter.
     */
    public function update(SavedFilterRequest $request, SavedFilter $savedFilter)
    {
        abort_unless($savedFilter->user_id === $request->user()->id, 404);

        $savedFilter->update(['name' => $request->validated('name')]);

        return $this->response($request, __('Filter has been renamed.'));
    }

    /**
     * Remove the specified saved filter.
     */
    public function destroy(Request $request, SavedFilter $savedFilter)
    {
        abort_unless($savedFilter->user_id === $request->user()->id, 404);

        $savedFilter->delete();

        return $this->response($request, __('Filter has been deleted.'));
    }

    /**
     * Build the JSON response with the user's saved filters.
     */
    protected function response(Request $request, string $message)
    {
        return response()->json([
            'message' => $message,
            'filters' => $request->user()->savedFilters()->orderBy('name')->get(['id', 'name', 'params']),
        ]);
    }
}
