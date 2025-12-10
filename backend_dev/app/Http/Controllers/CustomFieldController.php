<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    public function index()
    {
        $customFields = CustomField::orderBy('sort_order')->get();
        return response()->json($customFields);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:255|unique:custom_fields,field_name',
            'field_type' => 'required|in:text,number,date,datetime-local,email,tel,url,textarea,select,password',
            'field_options' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        // Convert string boolean to actual boolean
        if (isset($validated['is_active'])) {
            $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $validated['is_active'] = true; // Default to true if not provided
        }

        $customField = CustomField::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Custom field created successfully',
            'custom_field' => $customField,
        ]);
    }

    public function update(Request $request, $id)
    {
        $customField = CustomField::findOrFail($id);

        $validated = $request->validate([
            'field_name' => 'required|string|max:255|unique:custom_fields,field_name,' . $id,
            'field_type' => 'required|in:text,number,date,datetime-local,email,tel,url,textarea,select,password',
            'field_options' => 'nullable|array',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'integer',
        ]);

        // Convert string boolean to actual boolean if provided
        if (isset($validated['is_active'])) {
            $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $customField->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Custom field updated successfully',
            'custom_field' => $customField,
        ]);
    }

    public function destroy($id)
    {
        $customField = CustomField::findOrFail($id);
        $customField->delete();

        return response()->json([
            'success' => true,
            'message' => 'Custom field deleted successfully',
        ]);
    }
}