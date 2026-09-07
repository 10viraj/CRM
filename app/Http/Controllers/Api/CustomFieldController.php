<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of custom fields.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CustomField::query();

        if ($modelType = $request->input('model_type')) {
            $clean = class_basename($modelType);
            $query->where(function ($q) use ($modelType, $clean) {
                $q->where('model_type', $clean)->orWhere('model_type', $modelType);
            });
        }

        $fields = $query->orderBy('order_index')->get();

        return response()->json([
            'success' => true,
            'data' => $fields,
        ]);
    }

    /**
     * Store a newly created custom field.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-custom-fields') && !$user->hasRole(['Admin', 'Manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        if ($mt = $request->input('model_type')) {
            $request->merge(['model_type' => class_basename($mt)]);
        }

        $validated = $request->validate([
            'model_type' => 'required|string|in:Lead,Deal,Contact,Company,Task',
            'label' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'field_type' => 'required|string|in:text,number,date,select,textarea,boolean',
            'options' => 'nullable|array',
            'is_required' => 'nullable|boolean',
            'default_value' => 'nullable|string',
            'order_index' => 'nullable|integer',
        ]);

        if (empty($validated['name'])) {
            $validated['name'] = Str::snake($validated['label']);
        }

        if (!isset($validated['order_index'])) {
            $validated['order_index'] = (CustomField::where('model_type', $validated['model_type'])->max('order_index') ?? 0) + 1;
        }

        $field = CustomField::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Custom field created successfully',
            'data' => $field,
        ], 201);
    }

    /**
     * Update the specified custom field.
     */
    public function update(Request $request, CustomField $customField): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-custom-fields') && !$user->hasRole(['Admin', 'Manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'label' => 'sometimes|required|string|max:255',
            'field_type' => 'sometimes|required|string|in:text,number,date,select,textarea,boolean',
            'options' => 'nullable|array',
            'is_required' => 'nullable|boolean',
            'default_value' => 'nullable|string',
            'order_index' => 'nullable|integer',
        ]);

        $customField->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Custom field updated successfully',
            'data' => $customField,
        ]);
    }

    /**
     * Remove the specified custom field.
     */
    public function destroy(CustomField $customField): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-custom-fields') && !$user->hasRole(['Admin', 'Manager'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $customField->values()->delete();
        $customField->delete();

        return response()->json([
            'success' => true,
            'message' => 'Custom field deleted successfully',
        ]);
    }
}
