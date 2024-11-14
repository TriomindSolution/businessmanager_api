<?php

namespace App\Services;

use App\Models\CompanyProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CompanyProfileService
{
    public function storeCompanyProfile(Request $request)
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100|unique:company_profiles',
            'email' => 'nullable|string|max:100|unique:company_profiles',
            'website' => 'nullable|string|max:255',
            'company_details' => 'nullable|string|max:255',
            'registration_no' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|string|max:255',
            'twitter_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ];

        // Validate input data
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return [
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'error' => $validator->errors()->first(),
            ];
        }

        // Handle Company Logo
        $logo = null;
        if ($request->hasFile('logo')) {
            $image = $request->file('logo');
            $uniqueCode = Str::uuid();
            $companyLogoName = $uniqueCode . '.' . $image->getClientOriginalExtension();
            Storage::putFileAs('public/company_logo', $image, $companyLogoName);
            $logo = $companyLogoName;
        }

        // Prepare data for creation
        $data = [
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'website' => $request->website,
            'company_details' => $request->company_details,
            'registration_no' => $request->registration_no,
            'facebook_url' => $request->facebook_url,
            'twitter_url' => $request->twitter_url,
            'instagram_url' => $request->instagram_url,
            'linkedin_url' => $request->linkedin_url,
            'status' => $request->status,
            'logo' => $logo,
        ];

        $companyProfile = CompanyProfile::create($data);

        return [
            'status' => Response::HTTP_OK,
            'message' => "Company Profile created successfully",
            'data' => $companyProfile,
        ];
    }
    public function saveOrUpdateCompanyProfile(Request $request, $companyProfileId = null)
    {
        // Validation rules
        $rules = [
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:255',
            'company_details' => 'nullable|string|max:255',
            'registration_no' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|string|max:255',
            'twitter_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'linkedin_url' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->first(),
                'status_code' => 422,
            ];
        }

        $user = auth()->user();
        $companyId = $user->default_company_id;

        $companyProfile = CompanyProfile::where('default_company_id', $companyId)->first();

        // if (!$companyProfile) {
        //     $companyProfile = new CompanyProfile();
        //     $companyProfile->default_company_id = $companyId;
        // }

        $isCreatingNewProfile = !$companyProfile;

        if ($isCreatingNewProfile) {
            $companyProfile = new CompanyProfile();
            $companyProfile->default_company_id = $companyId;
        }

        if ($request->hasFile('logo')) {
            if ($companyProfile->logo) {
                Storage::delete('public/company_logo/' . $companyProfile->logo);
            }
            $image = $request->file('logo');
            $uniqueCode = Str::uuid();
            $companyLogoName = $uniqueCode . '.' . $image->getClientOriginalExtension();
            Storage::putFileAs('public/company_logo', $image, $companyLogoName);
            $companyProfile->logo = $companyLogoName;
        }

        // Update or set profile attributes
        $companyProfile->fill([
            'name' => $request->name,
            'address' => $request->address,
            'phone' => $request->phone,
            'email' => $request->email,
            'website' => $request->website,
            'company_details' => $request->company_details,
            'registration_no' => $request->registration_no,
            'facebook_url' => $request->facebook_url,
            'twitter_url' => $request->twitter_url,
            'instagram_url' => $request->instagram_url,
            'linkedin_url' => $request->linkedin_url,
            'status' => $request->status,
        ]);
        $companyProfile->save();

        return [
            'success' => true,
            'message' => $isCreatingNewProfile ? 'Company profile created successfully' : 'Company profile updated successfully',
            'status_code' => 200,
            'data' => $companyProfile,
        ];
    }

}
