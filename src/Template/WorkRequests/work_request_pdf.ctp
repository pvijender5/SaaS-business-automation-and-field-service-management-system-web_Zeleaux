<?php  if(empty($work_request)) : echo __("No corresponding data."); else: ?>
<div class="row">
    <div class="col-lg-12">
        <div class="wrapper wrapper-content animated fadeInRight view_pdf_outer">
        <div class="qt-pdf-container" style="background-color: #ffffff; border:none !important; padding: 0px 0px 0px !important; width:100% !important; margin-left: auto !important; margin-right: auto !important; display: flex; justify-content:center; align-item:center; border: 1px solid white;">
            <div style="width:100%; clear: both; box-shadow: none; background-color: #ffffff; padding-top: 0px; padding-left: 0px; padding-right: 0px; padding-bottom: 0px; border:none;">
                
                <div style="clear:both;">
                <div class="qt-pdf-row" class="display: flex; width: 100%;">
                <div class="qt-pdf-col-4" style="float:left;width:33.33%;max-width: 50%;flex: 0 0 50%;">
                <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">WO# </span>
                <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;"> <?php echo "WR-".str_pad($work_request['serial_no'], 4, "0", STR_PAD_LEFT); ?></span>
                </div>
                <div class="qt-pdf-col-4" style="float:left;width:33.33%;max-width: 50%;flex: 0 0 50%;">
                <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Date submitted:</span>
                <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;"> <?php echo date('M j, Y',strtotime($work_request['created'])); ?></span>
                </div>
                <div class="qt-pdf-col-4" style="float:left;width:33.33%;max-width: 50%;flex: 0 0 50%;">
                <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Time Submitted:</span>
                <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;"><?php echo date('h:i A',strtotime($work_request['created'])); ?></span>
                </div>
                </div>
            </div>
            <div class="sa-sale-rep-area" style="border-top: 1px solid; clear: both;">
                <div class="qt-pdf-row" style="clear:both; display: flex; width: 100%;">
                <div class="qt-pdf-col-6" style="float:left;width:50%;max-width: 50%;flex: 0 0 50%;">
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Requestor name:</span>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;"> <?php echo ucwords($this->Common->outputValue($work_request->contact->first_name." ".$work_request->contact->last_name)); ?></span>
                    
                </div>
                <div class="qt-pdf-col-6" style="float:left;width:50%;max-width: 50%;flex: 0 0 50%;">
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Location address: </span>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;"> <?php echo $this->Common->outputValue($work_request->location_address); ?></span>
                </div>
                </div>
                <div class="qt-pdf-row" style="clear:both; display: flex; width: 100%;">
                <div class="qt-pdf-col-6" style="float:left;width:50%;max-width: 50%;flex: 0 0 50%;">
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Phone: </span>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;"> <?php echo $this->Common->outputValue($work_request->phone); ?></span>
                    
                </div>
                <div class="qt-pdf-col-6" style="float:left;width:50%;max-width: 50%;flex: 0 0 50%;">
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Bldg/Floor/Room: </span>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;"> <?php echo $this->Common->outputValue($work_request->address_1); ?></span>
                </div>
                </div>
                <div class="qt-pdf-row" style="clear:both; display: flex; width: 100%;">
                <div class="qt-pdf-col-6" style="float:left;width:50%;max-width: 50%;flex: 0 0 50%;">
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Email: </span>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;"> <?php echo $this->Common->outputValue($work_request->email); ?></span>
                    
                </div>
                <div class="qt-pdf-col-6" style="float:left;width:50%;max-width: 50%;flex: 0 0 50%;">
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;"></span>
                    
                </div>
                </div>
                <div class="qt-pdf-row" style="clear:both; display: flex; width: 100%;">
                <div class="qt-pdf-col-6" style="float:left;width:50%;max-width: 50%;flex: 0 0 50%;">
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Customer contact: </span>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;"> <?php $customer_contact_name = !empty($work_request->customer) ? $work_request->customer->first_name." ".$work_request->customer->last_name : $work_request->customer_contact; ?>
                     <?php echo ucwords($this->Common->outputValue($customer_contact_name)); ?>
                     </span>
                    
                </div>
                <div class="qt-pdf-col-6" style="float:left;width:50%;max-width: 50%;flex: 0 0 50%;">
                    <div>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Date needed by:</span>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;">
                    <?php 
                     if(!empty($work_request->date_needed_by)){
                        echo date('F j, Y',strtotime($work_request->date_needed_by)); 
                    }else{
                        echo '--';
                    }
                     ?>
                    </span>
                    </div>
                    <div>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Is it urgent?:</span>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;">
                    <?php 
                        $is_it_urgent = "No";
                        if($work_request->is_it_urgent==1) $is_it_urgent = "Yes"
                    ?>
                    <?php echo $is_it_urgent; ?>
                    </span>
                    </div>
                </div>
                </div>
                <div class="qt-pdf-row" style="clear:both; display: flex; width: 100%;">
                <div class="qt-pdf-col-12" style="float:left;width:100%;max-width: 100%;flex: 0 0 50%;">
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; font-weight:bold; margin-top:0;">Request Description: </span>
                    <span style="font-family:arial; font-size:11px; line-height: 1.5; margin-top:0;"><?php echo $this->Common->outputValue($work_request->description); ?></span>
                </div>
                </div>
            </div>
            

            
                <div class="pdf_footer" style="text-align: center; line-height: 1.5; padding-top: 10px; font-family:arial; font-size:11px; left:0; bottom:15px; position:absolute; right:0;">
                    Copyright © 2020 Powered by <a href='http://zeleaux.com/' style="color: #007bff;     text-decoration: underline; " >Zeleaux</a>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
<?php endif ?>