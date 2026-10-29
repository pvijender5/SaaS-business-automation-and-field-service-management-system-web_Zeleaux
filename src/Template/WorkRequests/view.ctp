<?php 
$perm = $pre_del =  false;
$per = $this->Common->rolepermissionview('40');  // check permission for project edit
if(!empty($per)){
  $per_arr = explode(',',$per['permission_action']);
  if(in_array('3',$per_arr)){
    $perm = true;
  }if(in_array('4',$per_arr)){
    $pre_del  = true;
  }
}
if(empty($work_request)): echo __("No corresponding data."); else:?>
<div class="contacts-view">

<div class="ibox-title">
    
        <h5><?php echo $title; ?></h5>
        
        <div class="ibox-tools btn-position">
        <?php if(!empty($work_request->quotation->id) && $work_request->quotation->status!=8 && $user_type!=4) echo $this->Html->link('View Quotation',['controller' => 'Quotations','action' => 'view',$work_request->quotation->id],['data-toggle' => 'tooltip','data-placement' => 'bottom', 'title' => 'Add New','type' => 'button','_full' => false,'class' => 'btn btn-primary']); ?>

        <?php if(($work_request->status==1 || $work_request->status==2) && $user_type!=4) echo $this->Html->link('Convert to Project','javascript:void(0)',['data-toggle' => 'tooltip','data-placement' => 'bottom', 'title' => 'Conver to Project','type' => 'button','_full' => false,'class' => 'btn btn-primary','id'=>'work-request-to-project','data-id'=>$id]); ; ?>

        <?php if(empty($work_request->quotation->id) && $user_type!=4) echo $this->Html->link('Convert to Quote','javascript:void(0)',['data-toggle' => 'tooltip','data-placement' => 'bottom', 'title' => 'Conver to Quote','type' => 'button','_full' => false,'class' => 'btn btn-primary','id'=>'work-request-to-quote','data-id'=>$id]); ; ?>

        <?php echo $this->Html->link('Open in PDF',['controller' => $params['controller'],'action' => 'workRequestPdf',$id],['data-toggle' => 'tooltip','data-placement' => 'bottom', 'title' => 'Add New','type' => 'button','_full' => false,'class' => 'btn btn-primary']); ; ?>
            
        <?php if($user_type==4) echo $this->Html->link('Edit',['controller' => $params['controller'],'action' => 'edit',$id],['data-toggle' => 'tooltip','data-placement' => 'bottom', 'title' => 'Add New','type' => 'button','_full' => false,'class' => 'btn btn-primary']); ; ?>

        <?php if(!empty($work_request->quotation->id)  && $user_type!=4 && $work_request->status!=7) echo $this->Html->link('Close Request','javascript:void(0)',['data-toggle' => 'tooltip','data-placement' => 'bottom', 'title' => 'Close Work Request','type' => 'button','_full' => false,'class' => 'btn btn-primary','id'=>'close-work-request','data-id'=>$id]); ?>

        <?php echo $this->Html->link('Back',['controller' => $params['controller'],'action' => 'index'],['data-toggle' => 'tooltip','data-placement' => 'bottom', 'title' => 'Add New','type' => 'button','_full' => false,'class' => 'btn btn-primary']); ; ?>
        <input type="hidden" id="parent-id" value="<?php echo $parent_id ?>">
        </div>
   
</div>
<div class="ibox-content">
    
    <div class="sk-spinner sk-spinner-double-bounce">
        <div class="sk-double-bounce1"></div>
        <div class="sk-double-bounce2"></div>
    </div>
    <div class="row">
        <div class="col-md-10 project-detail-main">
            <div class="row">
                <div class="col-md-4">
                    <label>WO#:</label> <?php echo "WR-".str_pad($work_request['serial_no'], 4, "0", STR_PAD_LEFT);  ?>
                </div>
                <div class="col-md-4">
                    <label>Date submitted:</label> <?php  echo date('M j, Y',strtotime($work_request['created']));  ?>
                </div>
                <div class="col-md-4">
                    <label>Time Submitted:</label> <?php echo date('h:i A',strtotime($work_request['created']));  ?>
                </div>
                <div class="col-md-4">
                    <label>Work request:</label> <?php echo ucwords($this->Common->outputValue($work_request->work_request_name)); ?>
                </div>
                <div class="col-md-4">
                    <label>Requestor Name:</label> <?php echo ucwords($this->Common->outputValue($work_request->contact->first_name." ".$work_request->contact->last_name)); ?>
                </div>
                <div class="col-md-4">
                    <label>Email:</label> <?php echo $this->Common->outputValue($work_request->email); ?>
                </div>

                <div class="col-md-4">
                    <label>Phone Number:</label> <?php echo $this->Common->outputValue($work_request->phone); ?>
                </div>
                <div class="col-md-4">
                    <label>Location Address:</label> <?php echo $this->Common->outputValue($work_request->location_address); ?>
                </div>
                <div class="col-md-4">
                    <label>Bldg/Floor/Room:</label> <?php echo $this->Common->outputValue($work_request->address_1); ?>
                </div>

                <div class="col-md-4">
                    <?php $customer_contact_name = !empty($work_request->customer) ? $work_request->customer->first_name." ".$work_request->customer->last_name : $work_request->customer_contact; ?>
                    <label>Customer Contact Name:</label> <?php echo ucwords($this->Common->outputValue($customer_contact_name)); ?>
                </div>
                <div class="col-md-4">
                    <label>Date needed by:</label>
                     <?php 
                     if(!empty($work_request->date_needed_by)){
                        echo date('F j, Y',strtotime($work_request->date_needed_by)); 
                    }else{
                        echo '--';
                    }
                     ?>
                </div>
                <div class="col-md-4">
                    <?php 
                        $is_it_urgent = "No";
                        if($work_request->is_it_urgent==1) $is_it_urgent = "Yes"
                    ?>
                    <label>Is it urgent? :</label> <?php echo $is_it_urgent; ?>
                </div>
                <div class="col-md-12"  id="uploaddocs">
                <label>Documents:</label>
                <?php if(!empty($work_request->work_request_attachments)){
                              foreach ($work_request->work_request_attachments as $value) { 
                                
                                $link = '/uploads/workrequests/'.$value['attachment'];
                                $icon = "<i class='fa fa-file-text-o' aria-hidden='true'></i>";
                                $fileInfo = pathinfo($link);
                                
                                $image = 'img/default_doc.png';
                                $url = $this->Url->build($link, true);
                                if($fileInfo['extension'] == 'png'){
                                    $icon = "<img src='".$url."' width='50'>";
                                }
                                echo '<div class="original_project">';
                                echo "<a target='_blank' href=".$url.">".$icon."</a>";
                                echo '<p title="'.$value['attachment_name'].'">'.$value['attachment_name'].'</p>';
                                  
                                   
                                  
                                  echo '</div>';
                                }
                              
                            }else{
                              echo '<p> No document found.</p>';
                            } ?>

                </div>  
            </div>

        </div> 

    </div>
    <hr>
    
    <div class="row m-top-10">
        <div class="col-md-12">
            <h4>Request Description:</h4>
            <div class="original"><?php echo $this->Common->outputValue($work_request->description); ?></div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <h4>Comments:</h4>
            <div class="ibox sa_chatbox_design">
                <div class="ibox-content comment-content">
                    <div class="sk-spinner sk-spinner-double-bounce">
                        <div class="sk-double-bounce1"></div>
                        <div class="sk-double-bounce2"></div>
                    </div>
                    <div class="chat-discussion">
                    <?php 
                    if(!empty($workRequestComments)){                        
                        foreach ($workRequestComments as $value) { 
                                if(!empty($value)){
                                echo  '<div class="chat-message left">';

                                if(isset($value['user']['profile_pic']) && ($value['user']['profile_pic'] != '') && (file_exists(WWW_ROOT.'/uploads/Employee/profile_pic/'.$value['user']['profile_pic'])) ){
                                $image = '/uploads/Employee/profile_pic/'.$value['user']['profile_pic'];
                                $url = $this->Url->build($image,true);
                                }else{
                                $image = '/img/profile-default.png';
                                $url = $this->Url->build($image, true);
                                }

                                echo '<div class="chat_avatar"><img class="message-avatar" src="'.$url.'" alt=""></div>';

                                echo '<div class="message">';
                                echo            '<a class="message-author" href="#"> '.$value['user']['first_name'].' </a>';
                                echo            '<span class="message-date">'.date('M j, Y H:i:s', strtotime($value['created'])) .' </span>';
                                echo            '<div class="message_boxing">';
                                if(($value['user_id'] == $logged_in && $perm == true)){
                                echo    '<a href="javascript:void(0)" class="message-edit pull-right action_icon"><i class="fa fa-edit"></i></a>';
                                echo             '<a href="javascript:void(0)"  data-pid="'.$value['project_id'].'" data-cid="'.$value['id'].'" style="display:none" class="message-save pull-right action_icon"><i class="fa fa-check"></i></a>';     
                                }
                            echo            '<span class="message-content">';
                            echo             nl2br($value['comment']);
                            echo            '</span>';
                            echo            '</div>';
                            echo        '</div>';
                            echo    '</div>';
                            } 
                        }
                    }else{  
                        echo '<div class="message no_comment_found text-center"> <p> No comment found </p> </div>'; 
                    } 
                    ?>
                    </div>
                    <?php 
                    if($perm == true || $user_type ==4){
                    echo $this->Form->create(NULL,['url' => [ 'controller' => 'WorkRequests', 'action' => 'savecomments'],'id' => 'work_request_comment']); ?>
                    <div class="chat-message-form sa_chat_form_design" id="comments-box">
                    <div class="form-group">
                        <input type="hidden" name="work_request_id" value="<?php echo $work_request->id; ?>">
                        
                        <div class="input-group">
                        <input class="form-control message-input comment_box custom-control" autocomplete="off" name="comment" placeholder="Enter message text" rows="3" style="resize:none">
                        <button type="submit" class="btn btn-primary addcomment_button" style="z-index: 0; margin: 0 !important;">Send</button>
                        </div>
                    </div>
                    </div>
                    <?php 
                        echo $this->Form->end(); 
                    }
                    ?>
                    <div class="btn-group dropup dropup-top-m">
                        <div class="dropdown-menu" id="users-list">
                            <?php foreach($usersList as $user){ ?>
                                <a href="javascript:void(0);" class="dropdown-item user-list-item" data-userId = <?php echo $user['id']; ?> data-useremail = <?php echo $user['useremail']; ?>><?php echo $user['name']; ?></a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>   
        </div>                       
    </div>
   
</div>

</div>
<?php
$this->Html->script(['developer/workrequests.js'],['block'=>'script']);
?>
<?php endif; ?>