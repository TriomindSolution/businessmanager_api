<?php

namespace App\Http\Controllers\Backend\Admin;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\CompanyProfileService;
use Illuminate\Database\QueryException;
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

    public function companyProfileStore(Request $request)
    {
        try {
            $result = $this->companyProfileService->storeCompanyProfile($request);

            if ($result['status'] !== Response::HTTP_OK) {
                return $this->responseError($result['status'], false, $result['error']);
            }

            return $this->responseSuccess($result['status'], true, $result['message'], $result['data']);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function CompanyProfileList(Request $request)
    {
        $companyProfileData = CompanyProfile::latest()->get();

        // not empty checking
        if ($companyProfileData->isEmpty()) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $companyProfileData);
    }

    public function update(Request $request, $companyProfileId)
    {

        $result = $this->companyProfileService->updateCompanyProfile($request, $companyProfileId);

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
