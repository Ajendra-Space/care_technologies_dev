<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\CustomField;
use App\Models\ContactCustomField;
use App\Models\ContactAdditionalEmail;
use App\Models\ContactAdditionalPhone;
use App\Models\ContactFile;
use App\Models\ContactMergeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ContactController extends Controller
{
    public function index()
    {
        // Only load essential fields for custom fields (exclude field_options JSON to reduce payload)
        $customFields = CustomField::where('is_active', true)
            ->select('id', 'field_name', 'field_type', 'is_active', 'sort_order')
            ->orderBy('sort_order')
            ->get();
        return view('contacts.index', compact('customFields'));
    }

    public function getContacts(Request $request)
    {
        // Optimize eager loading - load relationships without field_options to reduce payload
        $query = Contact::with([
            'customFieldValues.customField:id,field_name,field_type',
            'additionalEmails',
            'additionalPhones',
            'files'
        ])
            ->where('status', 'active');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('gender') && $request->gender) {
            $query->where('gender', $request->gender);
        }

        if ($request->has('email_filter') && $request->email_filter) {
            $query->where('email', 'like', "%{$request->email_filter}%");
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate(10);

        // Return paginated response with proper structure
        return response()->json([
            'data' => $contacts->items(),
            'current_page' => $contacts->currentPage(),
            'last_page' => $contacts->lastPage(),
            'per_page' => $contacts->perPage(),
            'total' => $contacts->total(),
            'from' => $contacts->firstItem(),
            'to' => $contacts->lastItem(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'nullable|digits_between:10,15',
            'gender' => 'nullable|in:male,female,other',
            'profile_image' => 'nullable|image|max:2048',
            'additional_file' => 'nullable|file|max:5120',
            'custom_fields' => 'nullable|array',
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.digits_between' => 'The phone number must be 10 digits.',
            'profile_image.image' => 'The profile image must be an image file.',
            'profile_image.max' => 'The profile image must not be larger than 2MB.',
            'additional_file.max' => 'The additional file must not be larger than 5MB.',
        ]);

        try {
            DB::beginTransaction();

            $contact = new Contact();
            $contact->name = $validated['name'];
            $contact->email = $validated['email'];
            $contact->phone = $validated['phone'] ?? null;
            $contact->gender = $validated['gender'] ?? null;

            if ($request->hasFile('profile_image')) {
                $contact->profile_image = $request->file('profile_image')->store('contacts/profile_images', 'public');
            }

            $contact->save();

            if ($request->hasFile('additional_file')) {
                $file = $request->file('additional_file');
                ContactFile::create([
                    'contact_id' => $contact->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $file->store('contacts/files', 'public'),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }

            if ($request->has('custom_fields')) {
                foreach ($request->custom_fields as $fieldId => $value) {
                    if ($value !== null && $value !== '') {
                        ContactCustomField::create([
                            'contact_id' => $contact->id,
                            'custom_field_id' => $fieldId,
                            'field_value' => $value,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contact created successfully',
                'contact' => $contact->load(['customFieldValues.customField', 'additionalEmails', 'additionalPhones', 'files']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating contact: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        // Load field_options only when needed (for select fields in edit form)
        $contact = Contact::with([
            'customFieldValues' => function($q) {
                $q->select('id', 'contact_id', 'custom_field_id', 'field_value');
            },
            'customFieldValues.customField' => function($q) {
                $q->select('id', 'field_name', 'field_type', 'field_options');
            },
            'additionalEmails:id,contact_id,email',
            'additionalPhones:id,contact_id,phone',
            'files:id,contact_id,file_name,file_path'
        ])
            ->findOrFail($id);
        
        return response()->json($contact);
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . $id,
            'phone' => 'nullable|digits_between:10,15',
            'gender' => 'nullable|in:male,female,other',
            'profile_image' => 'nullable|image|max:2048',
            'additional_file' => 'nullable|file|max:5120',
            'custom_fields' => 'nullable|array',
        ], [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.digits_between' => 'The phone number must be 10 digits.',
            'profile_image.image' => 'The profile image must be an image file.',
            'profile_image.max' => 'The profile image must not be larger than 2MB.',
            'additional_file.max' => 'The additional file must not be larger than 5MB.',
        ]);

        try {
            DB::beginTransaction();

            $contact->name = $validated['name'];
            $contact->email = $validated['email'];
            $contact->phone = $validated['phone'] ?? null;
            $contact->gender = $validated['gender'] ?? null;

            if ($request->hasFile('profile_image')) {
                if ($contact->profile_image) {
                    Storage::disk('public')->delete($contact->profile_image);
                }
                $contact->profile_image = $request->file('profile_image')->store('contacts/profile_images', 'public');
            }

            $contact->save();

            if ($request->hasFile('additional_file')) {
                $file = $request->file('additional_file');
                ContactFile::create([
                    'contact_id' => $contact->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $file->store('contacts/files', 'public'),
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }

            if ($request->has('custom_fields')) {
                ContactCustomField::where('contact_id', $contact->id)->delete();
                
                foreach ($request->custom_fields as $fieldId => $value) {
                    if ($value !== null && $value !== '') {
                        ContactCustomField::create([
                            'contact_id' => $contact->id,
                            'custom_field_id' => $fieldId,
                            'field_value' => $value,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contact updated successfully',
                'contact' => $contact->load(['customFieldValues.customField', 'additionalEmails', 'additionalPhones', 'files']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating contact: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $contact = Contact::findOrFail($id);
            
            foreach ($contact->files as $file) {
                Storage::disk('public')->delete($file->file_path);
            }
            if ($contact->profile_image) {
                Storage::disk('public')->delete($contact->profile_image);
            }

            $contact->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting contact: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getMergeContacts()
    {
        $contacts = Contact::where('status', 'active')
            ->select('id', 'name', 'email')
            ->get();
        
        return response()->json($contacts);
    }

    public function merge(Request $request)
    {
        $validated = $request->validate([
            'master_contact_id' => 'required|exists:contacts,id',
            'secondary_contact_id' => 'required|exists:contacts,id|different:master_contact_id',
        ]);

        try {
            DB::beginTransaction();

            $master = Contact::with(['customFieldValues', 'additionalEmails', 'additionalPhones'])->findOrFail($validated['master_contact_id']);
            $secondary = Contact::with(['customFieldValues', 'additionalEmails', 'additionalPhones'])->findOrFail($validated['secondary_contact_id']);

            $mergeDetails = [
                'merged_at' => now(),
                'master_data' => $master->toArray(),
                'secondary_data' => $secondary->toArray(),
            ];

            // Merge emails
            $masterEmails = array_unique(array_merge([$master->email], $master->additionalEmails->pluck('email')->toArray()));
            $secondaryEmails = array_unique(array_merge([$secondary->email], $secondary->additionalEmails->pluck('email')->toArray()));
            
            foreach ($secondaryEmails as $email) {
                if (!in_array($email, $masterEmails) && $email !== $master->email) {
                    ContactAdditionalEmail::create([
                        'contact_id' => $master->id,
                        'email' => $email,
                    ]);
                }
            }

            // Merge phones
            $masterPhones = array_filter(array_unique(array_merge(
                $master->phone ? [$master->phone] : [],
                $master->additionalPhones->pluck('phone')->toArray()
            )));
            $secondaryPhones = array_filter(array_unique(array_merge(
                $secondary->phone ? [$secondary->phone] : [],
                $secondary->additionalPhones->pluck('phone')->toArray()
            )));
            
            foreach ($secondaryPhones as $phone) {
                if (!in_array($phone, $masterPhones) && $phone !== $master->phone) {
                    ContactAdditionalPhone::create([
                        'contact_id' => $master->id,
                        'phone' => $phone,
                    ]);
                }
            }

            // Merge custom fields
            foreach ($secondary->customFieldValues as $secondaryField) {
                $existingField = ContactCustomField::where('contact_id', $master->id)
                    ->where('custom_field_id', $secondaryField->custom_field_id)
                    ->first();

                if (!$existingField) {
                    ContactCustomField::create([
                        'contact_id' => $master->id,
                        'custom_field_id' => $secondaryField->custom_field_id,
                        'field_value' => $secondaryField->field_value,
                    ]);
                }
            }

            ContactFile::where('contact_id', $secondary->id)->update(['contact_id' => $master->id]);

            $secondary->status = 'merged';
            $secondary->merged_into_contact_id = $master->id;
            $secondary->merge_history = $mergeDetails;
            $secondary->save();

            ContactMergeHistory::create([
                'master_contact_id' => $master->id,
                'merged_contact_id' => $secondary->id,
                'merge_details' => $mergeDetails,
                'merged_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Contacts merged successfully',
                'master_contact' => $master->fresh(['customFieldValues.customField', 'additionalEmails', 'additionalPhones', 'files']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error merging contacts: ' . $e->getMessage(),
            ], 500);
        }
    }
}