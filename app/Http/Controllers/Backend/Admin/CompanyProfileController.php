<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Services\CompanyProfileService;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CompanyProfileController extends Controller
{

    use ResponseTrait;
    protected $companyProfileService;

    public function __construct(CompanyProfileService $companyProfileService)
    {
        $this->companyProfileService = $companyProfileService;
    }

    public function CompanyProfileList(Request $request)
    {
        $companyProfileData = CompanyProfile::get();

        // Check if the data is empty
        if ($companyProfileData->isEmpty()) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        // Map over each company profile to add the full logo path
        $companyProfileData->transform(function ($profile) {
            if ($profile->logo) {
                $profile->logo = Storage::url('public/company_logo/' . $profile->logo);
            }
            return $profile;
        });

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $companyProfileData);
    }


    public function saveOrUpdate(Request $request, $companyProfileId = null)
    {
        $result = $this->companyProfileService->saveOrUpdateCompanyProfile($request, $companyProfileId);

        if ($result['success']) {
            return $this->responseSuccess($result['status_code'], true, $result['message'], $result['data']);
        } else {
            return $this->responseError($result['status_code'], false, $result['message']);
        }
    }

    public function destroy($companyProfileId)
    {
        try {
            $companyProfileData = CompanyProfile::find($companyProfileId);
            if (is_null($companyProfileData)) {
                $message = "No data found.";
                return $this->responseError(403, false, $message);
            }
            if (isset($companyProfileData->logo)) {
                Storage::delete('public/company_logo/' . $companyProfileData->logo);
            }

            $companyProfileData->delete();
            $message = "Company Profile Data Deleted Successfully";

            return $this->responseSuccess(200, true, $message, []);

        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

}
