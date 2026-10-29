<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\View\View;
use Cake\Routing\Router;
use Cake\Mailer\Email;

/**
 * WorkRequests Controller
 *
 * @property \App\Model\Table\WorkRequestsTable $WorkRequests
 *
 * @method \App\Model\Entity\WorkRequest[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class WorkRequestsController extends AppController
{

    // $user_type = 4 for contacts/customers other user type is admin and employees
    // work requests status : 1 - Submitted, 2- Received, 3- Order placed,4- Quoted,5 - Draft,7 - Closed, 6 - deleted 
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index($quotation_id=null,$select_company_id=null)
    {
        $title = "Work Request";
        
        $user_type =  $this->Auth->user('user_type');
        $conditions = [];
        
        if($user_type == 4){
            $parent_id = $this->Auth->user('contact_parent_id');
            $company_id = $this->Auth->user('company_id');
            $employee_id = $this->Auth->user('contact_owner_id');
            $submit_by_contact_id = $this->Auth->user('id');
    
            $conditions = [
                'WorkRequests.status in'=>[1,2,3,4,5],
                'WorkRequests.company_id'=>$company_id,
    
            ];
        }else{
            $this->getUserAssignedPermissions(['Work_Requests','1']);
            $parent_id = $this->Auth->user('parent_id');
            if(empty($this->Auth->user('parent_id'))){
                $parent_id = $this->Auth->user('id');
            }
            $employee_id = $this->Auth->user('id');
            $conditions = [
                'WorkRequests.status in'=>[1,2,4,3],
                'WorkRequests.parent_id'=>$parent_id    
            ];
        }    
        
        
        
        $breadcrumb = [
            ['title' => 'Home','url' => ['controller' => 'WorkRequests','action' => 'index']],
            ['title' => 'Work Requests'],
        ];
        if(!empty($quotation_id)){
            $title = "Link Work Request";
            $breadcrumb = [
                ['title' => 'Home','url' => ['controller' => 'WorkRequests','action' => 'index']],
                ['title' => 'Link Work Requests'],
            ];
        }

        if(isset($this->request->query['include_inactive']) && $this->request->query['include_inactive'] == 2 ){
            if($user_type == 4){
                $conditions['WorkRequests.contact_id'] = $submit_by_contact_id;   
            }else{
                $conditions['WorkRequests.employee_id'] = $employee_id; 
            }
        }
        if(!empty($this->request->query['status'])){
            unset($conditions['WorkRequests.status in']);
            $conditions['WorkRequests.status'] =  $this->request->query['status'];
        }
        if(!empty($this->request->query['closed_work_request'])){
            unset($conditions['WorkRequests.status in']);
            $conditions['WorkRequests.status'] =  7;
        }
        if(!empty($this->request->query['contact_id'])){
            $conditions['WorkRequests.contact_id'] =  $this->request->query['contact_id'];
        }
        if(!empty($this->request->query['company_id'])){
            $conditions['WorkRequests.company_id'] =  $this->request->query['company_id'];
        }
        if(!empty($select_company_id)){
            $conditions['WorkRequests.company_id'] =  $select_company_id;
        }
        if(!empty($this->request->query['employee_id'])){
            if(!empty($conditions['WorkRequests.employee_id'])){
                $conditions['WorkRequests.employee_id'] =  0;
            }else{
                $conditions['WorkRequests.employee_id'] =  $this->request->query['employee_id'];
            }
            
        }
        if(isset($this->request->query['search']) && $this->request->query['search'] != '' ){
            $keyword = $this->request->query['search'];
            $conditions['OR'] = ['WorkRequests.work_request_name like' => "%".$keyword."%"];
        }
        if(isset($this->request->query['sort']) && isset($this->request->query['direction']) ){
            $sort = $this->request->query['sort']; 
            $direc = $this->request->query['direction'];
        }else{
            $sort = 'WorkRequests.created'; $direc = 'desc';
        }

        $work_requests_query = $this->WorkRequests->find()->contain(['Contacts','Companies'])->where($conditions)->order([$sort => $direc]);
        $this->paginate['limit'] = isset($this->request->query['limit']) ? $this->request->query['limit'] : 20;
        $this->paginate['page'] = isset($this->request->query['page']) ? $this->request->query['page'] : 1;
        $this->paginate = [
            'fields'=> [
                'WorkRequests.id','WorkRequests.serial_no','WorkRequests.work_request_name','Companies.company_name','contact_name' => 'CONCAT(Contacts.first_name," ",Contacts.last_name)','WorkRequests.created','WorkRequests.status'
            ]
            ];

        $work_requests = $this->paginate($work_requests_query);
        //pr($work_requests);die;
        $this->loadModel('Users');
        
        $selected_columns = $this->Users->getUserEmployeeColumnListing($this->Auth->user('id'), $this->request->params['controller']);
        if (sizeof($selected_columns) == 0) {
            $selected_columns = ['id', 'work_request_name','company','contact_name','created', 'status'];
        } else {
            $selected_columns = explode(',', $selected_columns['columns']);
        }
        
        $contacts =[];
        $companies =[];
        $employees =[];
        if($user_type == 4){
            $contacts = $this->Users->getContactListingByCompany($company_id);
        }else{
            $this->loadModel('Companies');
            $companies = $this->Companies->getCompanies($parent_id,NULL,['id','company_name']);
            // $employees list
            $this->loadModel('Users');
            $employees = $this->Users->getEmployeeListing($parent_id);
            if(!empty($select_company_id)) $contacts = $this->Users->getContactListingByCompany($select_company_id);
        }

        $this->set(compact('quotation_id','select_company_id','title','work_requests','breadcrumb','selected_columns','contacts','companies','user_type','employees'));
        if($this->request->is('Ajax')){
            echo $this->render('/Element/listing/work_requests_listing'); die;
        }
    }


    public function changeStatus(){
        $this->request->allowMethod(['post']);
        $response = ['status' => 'error', 'data' => 'Something went wrong.'];
        $id = $this->request->getData('id');
        $val = $this->request->getData('val');
        $parent_id = $this->Auth->user('parent_id');
        if(empty($this->Auth->user('parent_id'))){
            $parent_id = $this->Auth->user('id');
        }
        $conditions = [
            'WorkRequests.parent_id'=>$parent_id,
            'WorkRequests.id'=>$id
        ];
        
        if ($this->WorkRequests->query()->update()->set(['status' => $val])->where($conditions)->execute()) {
            switch($val){
                case 1:
                    $data = 'Submitted';
                break;
                case 2:
                    $data = 'Received';
                break;

            }
            $response = ['status' => 'success', 'data' => $data];
        }   
        echo json_encode($response);
        die;
    }

    public function deleteQue($id){

        if($id){
            $currnt_date = date('Y-m-d H:i:s');
            if($this->WorkRequests->query()->update()->set(['status' => 6, 'delete_date' => $currnt_date])->where(['id' => $id])->execute()){
                $response = ['status' => 'success', 'data' => 'Work request deleted successfully.'];
            }else{
                $response = ['status' => 'error', 'data' => 'Something went wrong.'];
            }
        }
        echo json_encode($response); die;
    }
    /**
     * View method
     *
     * @param string|null $id Work Request id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->set('title','View Work Request');

        $breadcrumb = [
            ['title' => 'Home','url' => ['controller' => 'WorkRequests','action' => 'index']],
            ['title' => 'View Work Request'],
        ];
        $user_type = $this->Auth->user('user_type');
        $logged_in = $this->Auth->user('id');
        if($user_type == 4){
            $parent_id = $this->Auth->user('contact_parent_id');
            $company_id = $this->Auth->user('company_id');
            $employee_id = $this->Auth->user('contact_owner_id');
            $submit_by_contact_id = $this->Auth->user('id');
            
    
            $conditions = [
                'WorkRequests.company_id'=>$company_id,
                'WorkRequests.id'=>$id,
                'WorkRequests.status in'=>[1,2,3,4,5,7],
            ];
            
        }else{
            $this->getUserAssignedPermissions(['Work_Requests','1']);
            $parent_id = $this->Auth->user('parent_id');
            if(empty($this->Auth->user('parent_id'))){
                $parent_id = $this->Auth->user('id');
            }
            $employee_id = $this->Auth->user('id');
            $conditions = [
                'WorkRequests.parent_id'=>$parent_id,
                'WorkRequests.id'=>$id,
                'WorkRequests.status in'=>[1,2,3,4,7],
    
            ];
            
        } 
        $work_request = $this->WorkRequests->find()->contain(['Contacts',
        'Customers',
        'Quotations',
        'Employees',
        'WorkRequestAttachments'=>function($query){
            return $query->where(['status'=>1]);
        }
        ])->where($conditions)->first();
        if($user_type != 4 && $work_request['status']==1){
            $this->WorkRequests->query()->update()->set(['status' => 2])->where($conditions)->execute();
        }
        $workRequestComments = $this->WorkRequests->WorkRequestsComments->find('all')->contain(['Users'])->where(['work_request_id' => $id])->order(['WorkRequestsComments.id' => 'ASC'])->toArray();
        
        $userId = $work_request['employee']['id'];
        $useremail = $work_request['employee']['email'];
        $username = $work_request['employee']['first_name']." ".$work_request['employee']['last_name'];
        if($userId != $logged_in){
            $usersList[] = ['id'=> $userId, 'name'=>$username ,'useremail'=>$useremail];
        }
        if(!empty($work_request['customer'])){
            $userId = $work_request['customer']['id'];
            $useremail = $work_request['customer']['contact_email'];
            $username = $work_request['customer']['first_name']." ".$work_request['customer']['last_name'];
            $usersList[] = ['id'=> $userId, 'name'=>$username, 'useremail'=>$useremail];
        }

        $userId = $work_request['contact']['id'];
        $useremail = $work_request['contact']['contact_email'];
        $username = $work_request['contact']['first_name']." ".$work_request['contact']['last_name'];
        if($userId != $logged_in){
            $usersList[] = ['id'=> $userId, 'name'=>$username, 'useremail'=>$useremail];
        }
        

        //pr($work_request);
        $this->set(compact('work_request','breadcrumb','id','user_type','workRequestComments','logged_in','usersList','parent_id'));
    }

    public function workRequestPdf($id = null,$pdf=0){
        $this->set('title','Work Requests PDF');

        $breadcrumb = [
            ['title' => 'Home','url' => ['controller' => 'WorkRequests','action' => 'index']],
            ['title' => 'Work request','url' => ['controller' => 'WorkRequests','action' => 'view',$id]],
            ['title' => 'Work Requests PDF'],
        ];
        $user_type = $this->Auth->user('user_type');
        if($user_type == 4){
            $parent_id = $this->Auth->user('contact_parent_id');
            $company_id = $this->Auth->user('company_id');
            $employee_id = $this->Auth->user('contact_owner_id');
            $submit_by_contact_id = $this->Auth->user('id');
    
            $conditions = [
             
                'WorkRequests.company_id'=>$company_id,
                'WorkRequests.id'=>$id,
            ];
        }else{
            $parent_id = $this->Auth->user('parent_id');
            if(empty($this->Auth->user('parent_id'))){
                $parent_id = $this->Auth->user('id');
            }
            $employee_id = $this->Auth->user('id');
            $conditions = [
            
                'WorkRequests.parent_id'=>$parent_id,
                'WorkRequests.id'=>$id,
    
            ];
        } 
        $work_request = $this->WorkRequests->find()->contain(['Contacts','Customers'])->where($conditions)->first();
        //pr($work_request);die;
        $this->set(compact('work_request','breadcrumb','id'));
        if($pdf==1){
            $mpdf = new \Mpdf\Mpdf();
            $this->layout = false;

            $html = $this->render('/WorkRequests/work_request_pdf');   
            $mpdf->showImageErrors = true;    // show if any image errors are present
            $mpdf->debug = true;    // Debug warning or errors if set true(false by default)
            $mpdf->SetProtection(array('print'));
   
            $this->fontdata = array(
                "arial" => array(
                'R' => Router::url('/webroot/fonts/arial.ttf',true)
                )
            );
            $mpdf->SetDisplayMode('fullpage'); 
           
            $stylesheet = file_get_contents(Router::url('/webroot/css/pdf.css',true));
            $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS); 

            $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);
            //$mpdf->Output();
            $mpdf->Output('WR-'.str_pad($work_request['serial_no'], 4, "0", STR_PAD_LEFT).'.pdf', 'D');
        }
        

    }

    public function mobileWorkRequestPdf($id = 0){
        $this->set('title','Work Requests PDF');
        $conditions = [
            'WorkRequests.id'=>$id,
        ];
        
        $work_request = $this->WorkRequests->find()->contain(['Contacts','Customers'])->where($conditions)->first();
        //pr($work_request);die;
        $this->set(compact('work_request'));
        $mpdf = new \Mpdf\Mpdf();
        $this->layout = false;

        $html = $this->render('/WorkRequests/work_request_pdf');   
        $mpdf->showImageErrors = true;    // show if any image errors are present
        $mpdf->debug = true;    // Debug warning or errors if set true(false by default)
        $mpdf->SetProtection(array('print'));

        $this->fontdata = array(
            "arial" => array(
            'R' => Router::url('/webroot/fonts/arial.ttf',true)
            )
        );
        $mpdf->SetDisplayMode('fullpage'); 
        
        $stylesheet = file_get_contents(Router::url('/webroot/css/pdf.css',true));
        $mpdf->WriteHTML($stylesheet,\Mpdf\HTMLParserMode::HEADER_CSS); 

        $mpdf->WriteHTML($html,\Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output();
    }

    public function workRequestToQuote($id,$for=null){
        if ($this->request->is('post')) {
            $parent_id = $this->Auth->user('parent_id');
            if(empty($this->Auth->user('parent_id'))){
                $parent_id = $this->Auth->user('id');
            }
            $employee_id = $this->Auth->user('id');
            $conditions = [
                'WorkRequests.status in'=>[1,2],
                'WorkRequests.parent_id'=>$parent_id,
                'WorkRequests.id'=>$id,

            ];

            $this->loadModel('Quotations');

            $count = $this->Quotations->find()->select(['serial_no'])->where(['user_id' => $parent_id])->hydrate(false)->count();
            $connection = $this->WorkRequests->getConnection();
            try{
                $connection->begin();
                $quotationData = $this->WorkRequests->find()->where($conditions)->hydrate(false)->first();
                unset($quotationData['created']);
                unset($quotationData['modified']);
                $quotationData['user_id'] = $quotationData['parent_id'];
                $quotationData['project_name'] = $quotationData['work_request_name'];
                $quotationData['phone_number'] = $quotationData['phone'];
                $quotationData['working_date'] = date('Y-m-d H:i:s');
                $quotationData['status'] = 1;
                $quotationData['serial_no'] = $count + 1;
                $quotationData['work_request_id'] = $quotationData['id'];
                
                $quotation = $this->Quotations->newEntity();
                $quotation = $this->Quotations->patchEntity($quotation,$quotationData);
                if($quotationResult = $this->Quotations->saveOrFail($quotation)){
                    $setWorkRequestStatus = 4;
                    if($for=='project'){
                        $setWorkRequestStatus = 3;
                    }
                    if($this->WorkRequests->query()->update()->set(['status' => $setWorkRequestStatus])->where(['id' => $id ])->execute()){
                        $connection->commit(); 
                        if($for=='project'){
                            $this->quotetoproject($quotationResult->id);
                        }else{
                            echo json_encode(['status'=>'success','id'=>$quotationResult->id]);
                        }
                        
                        
                        die;
                    }
                    
                }
            }

            catch(\Cake\ORM\Exception\PersistenceFailedException $e){
                $connection->rollback();
                //echo $e->message();
                echo json_encode(['status'=>'error','error_message'=>$e->message()]);
                die;
            } 

            catch(\Cake\Database\Exception $e){
                $connection->rollback();
                //echo $e->message();
                echo json_encode(['status'=>'error','error_message'=>$e->message()]);
                die;
            } 
            
            
            
            
            die;
            //return $this->redirect(['controller' => 'Quotations', 'action' => 'index']);
        }

    }

    public function closeWorkRequest(){
        $parent_id = $this->Auth->user('parent_id');
        if (empty($this->Auth->user('parent_id'))) {
			$parent_id = $this->Auth->user('id');
        }
        $user_id = $this->Auth->user('id');
        $postData= $this->request->getData();
        $conditions = [
            'parent_id'=>$parent_id,
            'id'=>$postData['id'],
        ];
        $workRequest = $this->WorkRequests->find()->where($conditions)->first();
        $response = ['status'=>'error','message'=>'Error while saving data.'];
        if($this->request->is(['patch', 'post', 'put'])) {
            $workRequestData['is_closed'] = 1;
            $workRequestData['status'] = 7;
            $workRequestData['closed_by'] = $user_id;
            $workRequest = $this->WorkRequests->patchEntity($workRequest, $workRequestData);

            if ($workRequestResult = $this->WorkRequests->save($workRequest)) {
                $response = ['status'=>'success','message'=>'Error while saving data.'];
            }
        }
        echo json_encode($response);die;


    }

    public function quotetoproject($id = null)
    {
        //$this->request->allowMethod(['post', 'delete']);
        $this->loadModel('Quotations');
        if($this->Quotations->exists(['id' => $id ])){
            $currnt_date = date('Y-m-d H:i:s');


            $qudata = $this->Quotations->find()->where(['id' => $id ])->hydrate(false)->toArray();
            $this->Quotations->query()->update()->set(['status' => 8])->where(['id' => $id ])->execute();
            $response = ['status' => 'success', 'data' => 'Project created successfully.'];
            foreach ($qudata as $val) {
                $this->createProject($val['id'],$val['employee_id'],$val['contact_id'],$val['project_name']);
            }

        }else{
            $response = ['status' => 'error', 'data' => 'Error while created Project.'];
        }
        
         echo json_encode($response); die;
    }
    
    /**
     * create project
     *
     * @param string|null $id Quotation id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    function createProject($quote_id,$employee_id,$contact_id,$project_name){
        if($quote_id > 0){
            $parent_id = $this->Auth->user('parent_id');
            if(empty($this->Auth->user('parent_id'))){
                $parent_id = $this->Auth->user('id');
            }
            $this->loadmodel('ProjectStages');
            $get_won_status = $this->ProjectStages->find()->select(['id'])->where(['status' => 1,'user_id ' => $parent_id,'first_stage ' => 1])->hydrate(false)->first();

            $this->loadModel('Projects');
            $count = $this->Projects->find()->select(['serial_no'])->where(['user_id' => $parent_id])->hydrate(false)->count();
            
            if($count==0){
                $serial_no = $count + 1;
            }else{
                $count = $this->Projects->find()->select(['serial_no','id'])->where(['user_id' => $parent_id])->hydrate(false)->last();
                if(empty($count['serial_no'])){
                    $serial_no = $count['id'] + 1;
                }else{
                    $serial_no = $count['serial_no'] + 1;
                }
            }
            $data = ['user_id' => $parent_id,'quotation_id' => $quote_id, 'status' => $get_won_status['id'], 'project_name' => $project_name,'serial_no'=>$serial_no];
            $this->loadModel('Users');
            if($this->Users->exists(['id' => $parent_id, 'project_stages' => 1])){
                $data['is_custom_stage'] = 1;
            }else{
                $data['is_custom_stage'] = 0;
            }
            $data['is_quote'] = 1;
            $project = $this->Projects->newEntity();
            $project = $this->Projects->patchEntity($project,$data);
            
            if($save = $this->Projects->save($project)){

                // Checklists
                $this->loadModel('ProjectChecklists');
                $this->loadModel('Checklists');
                $this->loadModel('ProjectChecklistData');
                $checklist = $this->Checklists->find()
                ->select(['Checklists.id','Checklists.name','Checklists.type'])
                ->contain(['ChecklistData'=> function($query){
                return $query->select(['ChecklistData.description','ChecklistData.checklist_id']);
                }])
                ->where(['Checklists.status' => 1,'Checklists.parent_id' => $parent_id, 'Checklists.type' => 0])
                ->hydrate(false)
                ->toArray();
                    
                if(!empty($checklist)){
                    foreach ($checklist as $value) {                  
                        $value_check= [ 'project_id' => $save->id, 'checklist_id' => $value['id'],'checklist_name' => $value['name'], 'type' => $value['type']];
                        $projectCheck = $this->Projects->ProjectChecklists->newEntity();
                        $ProjectChecklists_patch = $this->Projects->ProjectChecklists->patchEntity($projectCheck,$value_check);

                        $checklist_save = $this->Projects->ProjectChecklists->save($ProjectChecklists_patch);
                        foreach ($value['checklist_data'] as $val) {
                
                            $value_data= [ 'project_checklists_id' => $checklist_save['id'], 'description' => $val['description'], 'status' => 0];

                            $projectcdata = $this->ProjectChecklistData->newEntity();
                            $ProjectChecklists_patch_data = $this->ProjectChecklistData->patchEntity($projectcdata,$value_data);
                            $this->ProjectChecklistData->save($ProjectChecklists_patch_data);
                       }
                    }
                }
                
                // employees add
                if($employee_id > 0){
                    $emp_data = ['project_id' => $save->id, 'employee_id' => $employee_id,'status' => 1 ];
                    $emp_data = $this->Projects->ProjectEmployees->newEntity($emp_data);
                    $this->Projects->ProjectEmployees->save($emp_data);
                }
                
                // add project contact
                if($contact_id > 0){
                    $con_data = ['project_id' => $save->id, 'contact_id' => $contact_id,'status' => 1 ];
                    $con_data = $this->Projects->ProjectContacts->newEntity($con_data);
                    $this->Projects->ProjectContacts->save($con_data);
                }
                $this->Flash->success(__('Congratulations work request is converted to project.'));

                return $this->redirect(['controller' => 'Projects', 'action' => 'index']);
            }else{
                $this->Flash->error(__('Error while creating Project.'));
            }
        }
    }

    function sendTestMail(){
        $email = new Email();
        $toEmail = "zeleauxcontact@yopmail.com";
        $subject =  "Test Mail";
        $content =  "Mail content";
        $email->from(['donotreply@zeleaux.com' => SITE_TITLE ])
        ->emailFormat('html')
        ->to($toEmail)
        ->subject($subject)
        ->send($content);
        die;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function uploadWorkrequestFiles()
    {
       
        $response = ['status' => 'error', 'message' => 'error while updating Image.'];
		//$workRequestAttachments = $this->WorkRequests->WorkRequestAttachments->newEntity();
		
		if ($this->request->is(['patch', 'post', 'put'])) {

			if (isset($this->request->data['workrequest_files']) && ($this->request->data['workrequest_files']['size'] > 0 && $this->request->data['workrequest_files']['size'] < 5242880)) {
                $this->loadComponent('Common');
                $actual_name = $this->request->data['workrequest_files']['name'];
                $this->request->data['workrequest_files']['name'] = preg_replace('/\s/', '', $this->request->data['workrequest_files']['name']);
				$image = $this->Common->uploadFile($this->request->data['workrequest_files'], 'workrequests/', ['text/csv', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/html', 'application/pdf', 'text/plain', 'application/vnd.ms-excel','image/png', 'image/jpg', 'image/gif', 'image/jpeg']);

                if(!empty($image)){
                    $image_url = Router::url('/',true).'uploads/workrequests/'.$image;
                    $data = ['name'=>$image,'url'=>$image_url,'actual_name'=>$actual_name,'type'=>$this->request->data['workrequest_files']['type']];
                    $response = ['status' => 'success', 'message' => 'Photo updated successfully.','data'=>$data ];
                }
				// if ($image != '') {
                //    echo  $data['attachment'] = $image;
                //    echo  $data['work_request_id'] = 15;
                  
                //     $workRequests = $this->WorkRequests->WorkRequestAttachments->patchEntity($workRequestAttachments, $data);
                //     pr($workRequests);
				// 	if ($result = $this->WorkRequests->WorkRequestAttachments->save($workRequests)) {
                //         pr($result);
				// 		$response = ['status' => 'success', 'message' => 'Photo updated successfully.'];
				// 	}
				// }
			}
		}
		echo json_encode($response);die;
        die;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $workRequest = $this->WorkRequests->newEntity();
        $this->set('title', 'Add Work Request');
        $parent_id = $this->Auth->user('contact_parent_id');
        $company_id = $this->Auth->user('company_id');
        $employee_id = $this->Auth->user('contact_owner_id');
        $submit_by_contact_id = $this->Auth->user('id');

        
        if ($this->request->is('post')) {
            
            $date_needed_by = $this->request->getData('date_needed_by');
            $workRequestData =  $this->request->getData();
            
            
            if(isset($workRequestData['save_as_draft'])){
                $workRequestData['status'] = 5;
            } 
            //pr($workRequestData);die;
            $workRequestData['date_needed_by'] = (empty($date_needed_by)) ? NULL : date('Y-m-d',strtotime($date_needed_by));
            $workRequestData['parent_id']=$parent_id;
            $workRequestData['company_id']=$company_id;
            $workRequestData['employee_id']=$employee_id;
            $workRequestData['submit_by_contact_id']=$submit_by_contact_id;
            $count = $this->WorkRequests->find()->select(['serial_no'])->where(['parent_id' => $parent_id])->hydrate(false)->count();
            $workRequestData['serial_no']=$count+1;
            $workRequest = $this->WorkRequests->patchEntity($workRequest, $workRequestData);
            
            if ($workRequestResult = $this->WorkRequests->save($workRequest)) {
                $this->Flash->success(__('The work request has been saved.'));
                if(isset($workRequestData['work_request_attachments'])){
                    foreach($workRequestData['work_request_attachments'] as $key => $work_request_attachment){
                        $data[$key]['attachment'] =  $work_request_attachment;
                        $data[$key]['attachment_name'] = $workRequestData['work_request_attachment_names'][$key];
                        $data[$key]['work_request_id'] =  $workRequestResult->id;
                    }
                    
                    $workRequestAttachments = $this->WorkRequests->WorkRequestAttachments->newEntities($data);
                    $this->WorkRequests->WorkRequestAttachments->saveMany($workRequestAttachments);
                }
                $this->loadModel('Notifications');
                // send notification on submit, not on save as draft
                if(!isset($workRequestData['save_as_draft'])){
                    $connection = $this->Notifications->getConnection();
                    try{
                        $connection->begin();

                        $work_request_name = $this->request->getData('work_request_name');
                        
                        $notification = $this->Notifications->newEntity();
                        $link = Router::url('/',true).'work-requests/view/'.$workRequestResult->id;
                        $notificationData = [
                            'user_id'=>$submit_by_contact_id,
                            'title'=>'New work request.','message'=>'You have received work request '.$work_request_name. ' on '.date('Y-m-d').' <br> To view work request <a class="wr-link" href="'.$link.'">click here</a>'
                        ];
                        $notification = $this->Notifications->patchEntity($notification, $notificationData);
                        if($notificationsResult = $this->Notifications->saveOrFail($notification,['atomic' => false])){

                            $notificationEmployees = $this->Notifications->NotificationEmployees->newEntity();
                            $notificationEmployeesData = ['notification_id'=>$notificationsResult->id,'employee_id'=>$employee_id,'status'=>2,'is_schedule'=>3,'schedule_id'=>$workRequestResult->id];
                            $notificationEmployees = $this->Notifications->NotificationEmployees->patchEntity($notificationEmployees, $notificationEmployeesData);
                            if($this->Notifications->NotificationEmployees->saveOrFail($notificationEmployees,['atomic' => false])){
                                $connection->commit();
                                $email = new Email();
                                if(!empty($this->Auth->user('contact_email'))){
                                    $toEmail = $this->Auth->user('contact_email');
                                    $username = $this->Auth->user('first_name')." ".$this->Auth->user('last_name');
                                    
                                    $subject = "Work Request submitted";
                                    $content = '<p>Hi '.$username.'</p>';
                                    $content .= '<p>Work request '.$work_request_name. ' submitted successfully on '.date('Y-m-d').'</p>';
                                    $content .= '<p class="aligncenter content-block">Powered by <a href="'.Router::url('/',true).'">'.SITE_TITLE.'</a></p>';
                                    $email->from(['donotreply@zeleaux.com' => SITE_TITLE ])
                                        ->emailFormat('html')
                                        ->to($toEmail)
                                        ->subject($subject)
                                        ->send($content);
                                }
                                $this->loadModel('Users');
                                if(!empty($employee_id)){
                                    if ($this->Users->exists(['id' => $employee_id])) {
                                        $contact_owner = $this->Users->find()->select(['first_name','last_name','email'])->where(['id' => $employee_id])->hydrate(false)->first();
                                        $contact_owner_email = $contact_owner['email']; 
                                        $username = $contact_owner['first_name'].' '.$contact_owner['last_name'];
                                        $subject = $notificationData['title'];
                                        $content = '<p>Hi '.$username.'</p>';
                                        $content .= $notificationData['message'];
                                        $content .= '<p class="aligncenter content-block">Powered by <a href="'.Router::url('/',true).'">'.SITE_TITLE.'</a></p>';
                                        $email->from(['donotreply@zeleaux.com' => SITE_TITLE ])
                                            ->emailFormat('html')
                                            ->to($contact_owner_email)
                                            ->subject($subject)
                                            ->send($content);
                                    }
                                }
                                 
                            }
                            $this->loadComponent('PushNotifications');
                            $messageBody = 'You have received work request '.$work_request_name. ' on '.date('Y-m-d');
                            $this->PushNotifications->sendMobileNotification($notificationData['title'], $messageBody, $employee_id);
                        }

                        
                    
                    }
                    catch(\Cake\ORM\Exception\PersistenceFailedException $e){
                        $connection->rollback();
                        //echo $e->message();
                        $this->Flash->error(__('The work request notification not sent'));
                    } 

                    catch(\Cake\Database\Exception $e){
                        $connection->rollback();
                        //echo $e->message();
                        $this->Flash->error(__('The work request notification not sent'));
                    } 
                }
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The work request could not be saved. Please, try again.'));
        }
        $this->loadModel('Users');
        $phone_number = $email = null;
        $contacts = $this->Users->getContactListingByCompany($company_id);
        if(!empty($contacts)){
            $contactsIndex = $this->find_array_with_position($contacts,$submit_by_contact_id);
            $phone_number = $contacts[$contactsIndex]['phone_number'];
            $email = $contacts[$contactsIndex]['contact_email']; 
        }
        $contacts = array_column($contacts,'name', 'id');
        
        $this->set(compact('workRequest','contacts','submit_by_contact_id','phone_number','email'));
    }

    function find_array_with_position($array, $id) {
        foreach($array as $index => $value) {
            if($value['id'] == $id) return $index;
        }
        return FALSE;
    }

    function getContactDetails(){
        if ($this->request->is('post')) {
            $response = ['status'=>'empty'];
            $contact_id =  $this->request->data['contact_id'];
            $this->loadModel('Users');
            if(!empty($contact_id)){
                if ($this->Users->exists(['id' => $contact_id])) {
                    $contact = $this->Users->find()->select(['phone_number','email'])->where(['id' => $contact_id])->hydrate(false)->first();
                    $response['status'] = 'success';
                    $response['phone_number'] = $contact['phone_number'];
                    $response['email'] = $contact['email'];
                }
            }
            echo json_encode($response);
            die;
        }
    }

    /**
     * Edit method
     *
     * @param string|null $id Work Request id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->set('title', 'Edit Work Request');
        $parent_id = $this->Auth->user('contact_parent_id');
        $company_id = $this->Auth->user('company_id');
        $employee_id = $this->Auth->user('contact_owner_id');
        $submit_by_contact_id = $this->Auth->user('id');
        $breadcrumb = [
            ['title' => 'Home','url' => ['controller' => 'WorkRequests','action' => 'index']],
            ['title' => 'Edit Work Requests'],
        ];
        $conditions = [
            'WorkRequests.company_id'=>$company_id,
            'WorkRequests.id'=>$id,
        ];
        $workRequest = $this->WorkRequests->find()->contain([
            'Contacts',
            'Customers',
            'WorkRequestAttachments'=>function($query){
                return $query->where(['status'=>1]);
            }
            ])->where($conditions)->first();
        
        if($this->request->is(['patch', 'post', 'put'])) {
            $date_needed_by = $this->request->getData('date_needed_by');
            $workRequestData =  $this->request->getData();

            $perviousStatus = 0;
            if(isset($workRequestData['save_as_draft'])){
                $workRequestData['status'] = 5;
            }else{
                if($workRequestData['status']==5) {
                    $perviousStatus = $workRequestData['status'];
                    $workRequestData['status'] = 1;
                }
            }
            $workRequestData['is_it_urgent'] = isset($this->request->data['is_it_urgent']) ? $this->request->data['is_it_urgent'] : 0;
            $workRequestData['date_needed_by'] = (empty($date_needed_by)) ? NULL : date('Y-m-d',strtotime($date_needed_by));
            $workRequest = $this->WorkRequests->patchEntity($workRequest, $workRequestData);

            if ($workRequestResult = $this->WorkRequests->save($workRequest)) {
                if(isset($workRequestData['save_as_draft'])){
                    $this->Flash->success(__('The work request has been saved.'));
                }else{
                    $this->Flash->success(__('The work request has been submitted.'));
                }

                $this->WorkRequests->WorkRequestAttachments->query()->update()->set(['status' => 2])->where(['work_request_id'=>$workRequestResult->id])->execute();
                if(isset($workRequestData['work_request_attachments'])){
                    foreach($workRequestData['work_request_attachments'] as $key => $work_request_attachment){
                        $data[$key]['attachment'] =  $work_request_attachment;
                        $data[$key]['attachment_name'] = $workRequestData['work_request_attachment_names'][$key];
                        $data[$key]['work_request_id'] =  $workRequestResult->id;
                    }
    
                    $workRequestAttachments = $this->WorkRequests->WorkRequestAttachments->newEntities($data);
                    $this->WorkRequests->WorkRequestAttachments->saveMany($workRequestAttachments); 
                }
               
                $this->loadModel('Notifications');
                if(!isset($workRequestData['save_as_draft']) && $perviousStatus == 5 ){
                    $connection = $this->Notifications->getConnection();
                    try{
                        $connection->begin();

                        $work_request_name = $this->request->getData('work_request_name');
                        
                        $notification = $this->Notifications->newEntity();
                        $link = Router::url('/',true).'work-requests/view/'.$workRequestResult->id;
                        $notificationData = [
                            'user_id'=>$submit_by_contact_id,
                            'title'=>'New work request.','message'=>'You have received work request '.$work_request_name. ' on '.date('Y-m-d').' <br> To view work request <a class="wr-link" href="'.$link.'">click here</a>'
                        ];
                        $notification = $this->Notifications->patchEntity($notification, $notificationData);
                        if($notificationsResult = $this->Notifications->saveOrFail($notification,['atomic' => false])){

                            $notificationEmployees = $this->Notifications->NotificationEmployees->newEntity();
                            $notificationEmployeesData = ['notification_id'=>$notificationsResult->id,'employee_id'=>$employee_id,'status'=>2,'is_schedule'=>3,'schedule_id'=>$workRequestResult->id];
                            $notificationEmployees = $this->Notifications->NotificationEmployees->patchEntity($notificationEmployees, $notificationEmployeesData);
                            if($this->Notifications->NotificationEmployees->saveOrFail($notificationEmployees,['atomic' => false])){
                                $connection->commit(); 
                            }
                            $this->loadComponent('PushNotifications');
                            $messageBody = 'You have received work request '.$work_request_name. ' on '.date('Y-m-d');
                            $this->PushNotifications->sendMobileNotification($notificationData['title'], $messageBody, $employee_id);
                        }

                        
                    
                    }
                    catch(\Cake\ORM\Exception\PersistenceFailedException $e){
                        $connection->rollback();
                        //echo $e->message();
                        $this->Flash->error(__('The work request notification not sent'));
                    } 

                    catch(\Cake\Database\Exception $e){
                        $connection->rollback();
                        //echo $e->message();
                        $this->Flash->error(__('The work request notification not sent'));
                    } 
                }
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The work request could not be saved. Please, try again.'));
        }
        $this->loadModel('Users');
        $phone_number = $email = null;
        $contacts = $this->Users->getContactListingByCompany($company_id);
        if(!empty($contacts)){
            $contactsIndex = $this->find_array_with_position($contacts,$submit_by_contact_id);
            $phone_number = $contacts[$contactsIndex]['phone_number'];
            $email = $contacts[$contactsIndex]['email']; 
        }
        $contacts = array_column($contacts,'name', 'id');
        
        $this->set(compact('workRequest','contacts','submit_by_contact_id','phone_number','email','breadcrumb'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Work Request id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $company_id = $this->Auth->user('company_id');
        $conditions = [
            'WorkRequests.status in'=>[1,2],
            'WorkRequests.company_id'=>$company_id,
            'WorkRequests.id'=>$id,
        ];
        $workRequest = $this->WorkRequests->find()->where($conditions)->first();
        if ($this->WorkRequests->delete($workRequest)) {
            $this->Flash->success(__('The work request has been deleted.'));
        } else {
            $this->Flash->error(__('The work request could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function savecomments() {
		$response = ['status' => 'error', 'message' => 'comment not added.'];
		$this->request->data['user_id'] = $this->Auth->user('id');
        $user_type = $this->Auth->user('user_type');
        if($user_type == 4){
            $this->request->data['parent_id'] = $this->Auth->user('contact_parent_id');
        }else{
            $this->request->data['parent_id']  = $this->Auth->user('parent_id');
        } 
		$work_request_id = $this->request->data['work_request_id'];
		$work_request_comments = $this->WorkRequests->WorkRequestsComments->newEntity();

		if ($this->request->is(['patch', 'post', 'put'])) {
			$work_request_comments = $this->WorkRequests->WorkRequestsComments->patchEntity($work_request_comments, $this->request->data);
			if ($save = $this->WorkRequests->WorkRequestsComments->save($work_request_comments)) {
				$user = $this->Auth->user();

                $SITEURL = Router::url('/',true);

		        

				if (isset($user['profile_pic']) && ($user['profile_pic'] != '')) {

					$url = $SITEURL . '/webroot/uploads/Employee/profile_pic/' . $user['profile_pic'];

				} else {
					$url = $SITEURL . '/webroot/img/profile-default.png';
				}
				$time = time();
				$chatapend = '';
				$chatapend = '   <div class="chat-message left">
                                <div class="chat_avatar"><img class="message-avatar" src="' . $url . '" alt=""></div>
                                <div class="message">
                                    <a class="message-author" href="#"> ' . $user['first_name'] . " " . $user['last_name'] . ' </a>
                                        <span class="message-date">' . date('F j, Y, g:i a') . ' </span>
                                            <div class="message_boxing">
                                                <a href="javascript:void(0)" class="message-edit pull-right action_icon"><i class="fa fa-edit"></i></a>
                                                <a href="javascript:void(0)"  data-pid="' . $work_request_id . '" data-cid="' . $save->id . '" style="display:none" class="message-save pull-right action_icon"><i class="fa fa-check"></i></a>
                                            <span class="message-content">' . $save['comment'] . '</span>
                                        </div>
                                   </div>
                                   </div>
                            </div>';
				$response = ['status' => 'success', 'message' => 'Comment added successfully.', 'appendchat' => $chatapend];
			}
		}

		echo json_encode($response);die;
    }

    public function commentMail() {
		$this->request->data['user_id'] = $this->Auth->user('id');
        $user_type = $this->Auth->user('user_type');
		$work_request_id = $this->request->data['work_request_id'];
		$comment = $this->request->data['comment'];
		$response = ['status' => 'not_available', 'message' => 'No message'];

		if ($this->request->is(['patch', 'post', 'put'])) {
			
            $user = $this->Auth->user();

            $SITEURL = Router::url('/',true);
            if(isset($this->request->data['useremails'])){
                $useremails = $this->request->data['useremails'];
                foreach($useremails as $useremail){
                    $email = new Email();
                    if(!empty($useremail)){
                        $toEmail = $useremail;
                        $loggedinusername = $this->Auth->user('first_name')." ".$this->Auth->user('last_name');
                        
                        $subject = "ZELEAUX COMMENT NOTIFICATION";
                        $content = '<p>'.$loggedinusername.' has mentioned you in a comment: </p>';
                        $content .= '<p>'.$comment.' </p><br>';
                        $content .= '<a style="border: 1px solid transparent; background-color:#f29e1a; text-decoration:none; color:#fff;padding-top:10px; padding-bottom:10px;padding-right:5px;padding-left:5px" href="'.$SITEURL.'/work-requests/view/'.$work_request_id.'">VIEW OR REPLY IN ZELEAUX </a>';
                        $emailsent = $email->from(['donotreply@zeleaux.com' => SITE_TITLE ])
                            ->emailFormat('html')
                            ->to($toEmail)
                            ->subject($subject)
                            ->send($content);
                        if($emailsent) $response = ['status' => 'success', 'message' => 'Mail sent'];
                    }
                }
            }
		        
			
		}

		echo json_encode($response);die;
    }
    
    public function editWorkRequestComments() {
		$response = ['status' => 'error', 'message' => 'comment not added.'];

		$commentid = $this->request->data['commentid'];
        $content = $this->request->data['content'];
        $user_type  = $this->Auth->user('user_type');

        if($user_type == 4){
            $parent_id = $this->Auth->user('contact_parent_id');
        }else{
            $parent_id  = $this->Auth->user('parent_id');
        } 
		if ($this->WorkRequests->WorkRequestsComments->exists(['id' => $commentid,'parent_id'=>$parent_id])) {
			$this->WorkRequests->WorkRequestsComments->query()->update()->set(['comment' => $content])->where(['id' => $commentid,'parent_id'=>$parent_id])->execute();

			$response = ['status' => 'success', 'message' => 'Comment added successfully.'];
		} else {
			$response = ['status' => 'error', 'message' => 'data no found.'];

		}

		echo json_encode($response);die;
	}
}
