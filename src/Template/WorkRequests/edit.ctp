<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\workRequest $workRequest
 */
?>
<div class="sa_form_style animated fadeInUp">
    <div class="ibox">
        <?php  if(empty($workRequest)) : echo __("No corresponding data."); else: ?>
        <div class="ibox-content sa_ibox_css project-detail-main">
            <div class="sk-spinner sk-spinner-double-bounce">
                <div class="sk-double-bounce1"></div>
                <div class="sk-double-bounce2"></div>
            </div>
            <?= $this->Form->create($workRequest, ['id' => 'edit-work-request']); ?>
                <div class="sa_form_sec">
                    <div class ="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title <span class="requird_label">*</span></label>
                                <?= $this->Form->input('work_request_name', ['class' => 'form-control', 'required' => false, 'label' => false,'placeholder' => 'Enter title' ]); ?>    
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group contact_id">
                                <label>Requestor Name<span class="requird_label">*</span></label>
                                <div class="input-group">
                                    <?php echo $this->Form->select('contact_id', $contacts,['id' => 'contact_id','class' => 'form-control select2_demo_1', 'required' => false, 'label' => false,'default' => $submit_by_contact_id]); ?> 
                                </div>
                            </div>
                        </div>
                    </div>  
                    <div class ="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone </label>
                                <?php
                            echo $this->Form->input('phone', ['data-mask' => "999.999.9999",'id' => 'phone', 'type' => 'text','class' => 'form-control', 'label' => false,'placeholder' => 'Enter Phone number','default'=>$phone_number ]);
                        ?>  
                            </div>    
                        </div>
                        <div class="col-md-6">
                            <div class="form-group select_customer">
                                <label>Email</label>
                                <?php
                            echo $this->Form->input('email', ['id' => 'email', 'type' => 'text','class' => 'form-control', 'label' => false,'placeholder' => 'Enter email','default'=>$email ]);
                        ?>       
                            </div>
                        </div>
                        
                    </div>

                    <div class ="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Location Address </label>
                                <?php
                            echo $this->Form->input('location_address', ['id' => 'location_address', 'type' => 'text','class' => 'form-control', 'label' => false,'placeholder' => 'Enter local address' ]);
                        ?>  
                            </div>    
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Bldg/Floor/Room</label>
                                <?php
                            echo $this->Form->input('address_1', ['id' => 'address_1', 'type' => 'text','class' => 'form-control', 'label' => false,'placeholder' => 'Enter Bldg/Floor/Room' ]);
                        ?>       
                            </div>
                        </div>
                        
                    </div>

                    <div class ="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Customer Contact Name</label>
                                <div class="input-group">
                                <?php
                                    echo $this->Form->input('customer_contact_id', [ 'type' => 'hidden']);
                                ?> 
                                <input list="customer_contact" class="form-control input-dropdown"   autocomplete="off" name="customer_contact" value='<?php echo $customer_contact_name = !empty($workRequest->customer) ? $workRequest->customer->first_name." ".$workRequest->customer->last_name : $workRequest->customer_contact; ?>'>
                                <datalist id="customer_contact">
                                <?php
                                    if(!empty($contacts)){
                                        foreach ($contacts as $key => $value) {
                                            echo ' <option data-key="'.$key.'" value="'.$value.'">';
                                        }
                                    }
                                 ?>
                                 </datalist>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date Needed by</label>
                                <?php echo $this->element('date_picker',['name' => 'date_needed_by','value'=>$workRequest->date_needed_by]); ?> 
                            </div>
                        </div>
                        
                    </div>

                    <div class ="row">
                        <div class="col-md-6" id="uploaddocs">
                        <div class="fileinput fileinput-new" data-provides="fileinput"> <span class="btn btn-primary btn-file ml-4"><span class="">Upload Doc</span> <span class="fileinput-exists"></span>
                     
                                
                                <input type="file" name="workrequest_files" class="form-control select_project_doc" id="workrequest-files">
                              </span> </div>
                              <div><span class="font-weight-light font-italic"> (Format allowed: .csv, .msword, .document, .pdf, .plain, .txt, .png, .jpg, .gif, .jpeg and size below 5 MB)</span></div>
                        <?php if(!empty($workRequest->work_request_attachments)){
                            echo '<div class="original tooltip-demo mb-2" id="work-requests-files">'; 
                              foreach ($workRequest->work_request_attachments as $value) { 
                                
                                $link = '/uploads/workrequests/'.$value['attachment'];
                                $icon = "<i class='fa fa-file-text-o' aria-hidden='true'></i>";
                                $fileInfo = pathinfo($link);
                                $image = 'img/default_doc.png';
                                $url = $this->Url->build($link, true);
                                if($fileInfo['extension'] == 'png'){
                                $icon = "<img src='".$url."' width='50'>";
                                }
                                echo '<div class="original_project" >';
                                echo '<input type="hidden" name="work_request_attachments[]" value="'.$value['attachment'].'"><input type="hidden" name="work_request_attachment_names[]" value="'.$value['attachment_name'].'">';
                                echo "<a target='_blank' href=".$url.">$icon</a>";
                                echo '<p title="'.$value['attachment_name'].'">'.$value['attachment_name'].'</p>';
                                
                                echo '<div class="project_attch_del"><a href="javascript:void(0);" title="" class="del_work_request_doc" style="-moz-appearance: none;" data-original-title="Remove" aria-describedby="tooltip734508"><i class="fa fa-times fa-lg"></i></a></div>';
                                
                                echo '</div>';
                                 
                                }
                                echo '</div>';
                            }else{
                                echo '<div class="original tooltip-demo mb-2" id="work-requests-files" style="display:none"> 
                                </div>';
                            } ?>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Is this urgent? </label>

                                <?php echo $this->Form->checkbox('is_it_urgent', ['id' => 'is_it_urgent', 'hiddenField' => false,'class' => 'i-checks' ]); ?>
                            </div>
                        </div>
                        
                    </div>

                    <div class ="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Request Description</label>
                                <?php
                                    echo $this->Form->input('description', ['placeholder' => 'Please enter here...','type' => 'textarea' ,'class' => 'form-control summernote','label' => false]);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="hr-line-dashed"></div>
                <div class ="row">
                     <div class="col-md-12">
                        <div class="form-group">
                            <div class="top-btns">
                                <?php
                                if($workRequest->status == 5)
                                    echo $this->Form->input('Save as draft', ['type' => 'submit' ,'class' => 'btn btn-primary pull-right m-l-sm', 'value'=>"draft", 'name'=>"save_as_draft"]);
                                ?>
                                <?= $this->Form->input('status', ['required' => false, 'label' => false,'type'=>'hidden']); ?>
                                <?= $this->Form->button('Submit',['class' => 'btn btn-primary pull-right m-l-sm', 'type' => 'submit']); ?>

                            </div>
                        </div>
                    </div>
                </div>
            <?php echo $this->Form->end(); ?>                         
        </div>
        <?php endif ?>
    </div>
</div>
<?php
$this->Html->script(['developer/workrequests.js'],['block'=>'script']);
$this->Html->css(['plugins/jasny/jasny-bootstrap.min'],['block'=>'css']);
$this->Html->script(['plugins/jasny/jasny-bootstrap.min'],['block'=>'script']);
?>
