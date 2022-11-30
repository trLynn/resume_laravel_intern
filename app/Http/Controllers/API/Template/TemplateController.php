<?php

namespace App\Http\Controllers\API\Template;

use App\Models\Layout;
use App\Models\Template;
use App\Traits\LogTrait;
use Illuminate\Http\Request;
use App\Models\TemplateHeading;
use App\Classes\MessageHelperUtil;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UpdateTemplateRequest;
use App\DBTransactions\Template\SaveTemplate;
use App\Http\Requests\Template\TemplateCreateRequest;
use App\Interfaces\Template\TemplateRepositoryInterface;

class TemplateController extends Controller
{
    use LogTrait;
    private TemplateRepositoryInterface $templateRepository;

    public function __construct( TemplateRepositoryInterface $templateRepository )
    {
        $this->templateRepository = $templateRepository;
        $this->msgHelpUtil = new MessageHelperUtil;
    }

    /**
     * Display all listing of the Types from types table.
     *
     * @author Thu Ta
     * @create  20/06/2022
     * @return \Illuminate\Http\Response
     */
    public function getType()
    {
        $data = $this->templateRepository->getAllTypes();
        if ($data) {          //if get all data is true
            return response()->json(['status'=>'OK','data'=> $data],config('HTTP_CODE_200'));
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.SE006',config('HTTP_CODE_200'));
        }
    }


    /**
     * Display all listing of the Template.
     *
     * @author Thu Ta
     * @create  20/06/2022
     * @return \Illuminate\Http\Response
     */
    public function all()
    {
        $allDataTemp = $this->templateRepository->getAllTemplates();
        if ($allDataTemp) {          //if get all data is true
            return response()->json(['status'=>'OK','data'=> $allDataTemp],config('HTTP_CODE_200'));
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.SE006',config('HTTP_CODE_200'));
        }
    }

    /**
     * Store a newly template created resource in Template.
     *
     * @author Thu Ta
     * @create  20/06/2022
     * @return \Illuminate\Http\Response
     */
    public function store(TemplateCreateRequest $request)
    {
        $tempName = $request->title;
        $layoutId = $request->layout_id;
        $loginId  = $request->login_id;
        $counter = Template::get()->count();
        if ($counter > config('ZERO')) {
            $tempNameExist = Template::where('name',$tempName)->exists();
            $layoutIdExist = Layout::where('id',$layoutId)->exists();
            if (!$tempNameExist) {                  //if the tempalte name is exist
                if ($layoutIdExist) {               //if the layout is exist
                    $process = new SaveTemplate($request);
                    $save = $process->executeProcess();
                    if ($save) {
                        $description = "Save Template";
                        $form = "Create Template Form";
                        $this->writeCRUDLog($loginId, $description, $form, config('SAVE'));               //if save method is true
                        return $this->msgHelpUtil->successMessage('successMessage.SS006',config('HTTP_CODE_200'));
                    } else {
                        return $this->msgHelpUtil->errorMessage('errorMessage.SE010',config('HTTP_CODE_200'));
                    }
                } else {
                    return $this->msgHelpUtil->errorMessage('errorMessage.SE006',config('HTTP_CODE_200'),['attribute'=>'Layout']);
                }
            } else {
                return $this->msgHelpUtil->errorMessage('errorMessage.SE023',config('HTTP_CODE_200'));
            }
        } else {
            $process = new SaveTemplate($request);
            $save = $process->executeProcess();
            if ($save) {
                $description = "Save Template";
                $form = "Create Template Form";
                $this->writeCRUDLog($loginId, $description, $form, config('SAVE'));                       //if save method is true
                return $this->msgHelpUtil->successMessage('successMessage.SS006',config('HTTP_CODE_200'));
            } else {
                return $this->msgHelpUtil->errorMessage('errorMessage.SE010',config('HTTP_CODE_200'));
            }
        }
    }


    /**
     * Display the specified Template.
     *
     * @author Thu Ta
     * @create  20/06/2022
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $result = $this->templateRepository->viewTemplate($id);
        if ($result['status']) {                        //if the data method is true
            return response()->json(['status'=>'OK','data'=> $result['data']],config('HTTP_CODE_200'));
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.'.$result['message'],config('HTTP_CODE_200'));
        }
    }

    /**
     * Change Template active status.
     *
     * @author Thu Ta
     * @create 11/07/2022
     * @param  Request $request
     * @return Response object
     */
    public function changeActiveStatus (Request $request) {
        //template active status data validation
        $validator = Validator::make($request->all(), [
            'template_id' => 'required|integer',
            'active_flag' => 'bail|required|integer|between:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status'=>'NG','message'=>$validator->errors()], config('HTTP_CODE_422'));
        }

        $result = $this->templateRepository->updateTemplateActiveFlag($request->all());
        //check result of DB operation.
        if ($result['status']) {
            return $this->msgHelpUtil->successMessage('successMessage.'.$result['message'], config('HTTP_CODE_200'));
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.'.$result['message'], config('HTTP_CODE_200'));
        }
    }

    /**
     * Display template's data to update.
     *
     * @author Thu Ta
     * @create 20/06/2022
     * @param  int  $templateId
     * @return Response object
     */
    public function edit ($templateId) {
        $result = $this->templateRepository->getTemplateData($templateId);
        //check result of DB operation.
        if ($result['status']) {
            return response()->json(['status'=>'OK', 'data'=> $result['data']], config('HTTP_CODE_200'));
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.'.$result['message'], config('HTTP_CODE_200'));
        }
    }

    /**
     * Update template's data in storage.
     *
     * @author Thu Ta
     * @create 20/06/2022
     * @param  UpdateTemplateRequest $request
     * @return Response object
     */
    public function update (UpdateTemplateRequest $request) {
        $result = $this->templateRepository->updateTemplate($request);
        //check result of DB operation.
        if ($result['status']) {
            return $this->msgHelpUtil->successMessage('successMessage.'.$result['message'], config('HTTP_CODE_200'));
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.'.$result['message'], config('HTTP_CODE_200'));
        }
    }

    /**
     * Show template's id and name for Applicant list search
     *
     * @author Thu Ta
     * @create 21/06/2022
     * @return Response object
     */
    public function templateAllSearch()
    {
        $data = $this->templateRepository->templateAll();
        if($data) {
            return $data;
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.SS004',config('HTTP_CODE_200'));
        }
    }
    /**
     * Search template's data in storage.
     *
     * @author Thu Ta
     * @create 21/06/2022
     * @param  Request $request
     * @return Response object
     */
    public function search(Request $request)
    {
        $data = $this->templateRepository->search($request);
        if ($data) {
            return $data;
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.SS004',config('HTTP_CODE_200'));
        }
    }
    /**
     * Delete template's data in storage.
     *
     * @author Thu Ta
     * @create 20/06/2022
     * @param  Request $request
     * @return Response object
     */
    public function delete(Request $request)
    {
        $data = $this->templateRepository->delete($request);
        if ($data) {
            return $data;
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.SE004',config('HTTP_CODE_200'));
        }
    }
    //Daniel Adams

    /**
     * Show Dashboard.
     *
     * @author Thu Ta
     * @create 13/07/2022
     * @param  Request
     * @return Response object
     */
    public function dashboard() {
        $data = $this->templateRepository->dashboard();
        if ($data) {
            return response()->json(['status'=>'OK','data'=> $data],config('HTTP_CODE_200'));
        } else {
            return $this->msgHelpUtil->errorMessage('errorMessage.SE006',config('HTTP_CODE_200'), ['attribute'=>'Dashboard']);
        }
    }
}
