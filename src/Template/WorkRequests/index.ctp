<div class="quotations">
<div class="ibox-title">
            <h5><?php echo $title; ?></h5>
        <div class="ibox-tools btn-float">
            <?php if($user_type==4) echo $this->Html->link('New Work Request', ['action' => 'add'],['type' => 'button','class' => 'btn btn-primary']);
            $columns = ['id', 'work_request_name','company','contact_name','created', 'status'];
            ?>
            <a href="javascript:void(0);" class="btn btn-secondary open_select"><i class="fa fa-bars"></i> </a>
            <div class="select_columns">
                <select name="choose_columns" name="DataTables_Table_0_length" aria-controls="DataTables_Table_0" class="form-control form-control-sm" multiple style="display:none;width:100%;">
                <option value ="">Select Columns</option>
                    <?php foreach($columns as $key => $col){
                        $selected = "";
                        if(in_array($col,$selected_columns)){
                            $selected = "selected='selected'";
                        } ?>
                        <option value="<?php echo $col; ?>" <?php echo $selected; ?>><?php echo str_replace(' id','',str_replace('_',' ',ucwords($col))); ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
</div>

<div class="ibox-content ibox-contentQuotations">
    <div class="sk-spinner sk-spinner-double-bounce">
        <div class="sk-double-bounce1"></div>
        <div class="sk-double-bounce2"></div>
    </div>
   
            <div id="DataTables_Table_0_wrapper" class="dataTables_wrapper dt-bootstrap4">
            <?php echo $this->element('filter'); ?>
            <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover dataTables-example dataTable resize" role="grid" >       
                <thead>
                        <?php if(in_array('id',$selected_columns)){ ?>
                        <th scope="col" class="sort_data sorting sorting_desc" data-controller="<?php echo $this->request->params['controller']; ?>" data-action="<?php echo $this->request->params['action']; ?>">
                            <?= $this->Paginator->sort('WorkRequests.serial_no','Work Request No.') ?>
                        </th>
                        <?php } ?>
                        <?php if(in_array('work_request_name',$selected_columns)){ ?>
                        <th scope="col" class="sort_data sorting sorting_desc" data-controller="<?php echo $this->request->params['controller']; ?>" data-action="<?php echo $this->request->params['action']; ?>">
                            <?= $this->Paginator->sort('WorkRequests.work_request_name','Work Request Name') ?>
                        </th>
                        <?php } ?>

                        <?php if(in_array('company',$selected_columns)){ ?>
                        <th scope="col" class="sort_data sorting sorting_desc" data-controller="<?php echo $this->request->params['controller']; ?>" data-action="<?php echo $this->request->params['action']; ?>">
                            <?= $this->Paginator->sort('Companies.company_name','Company') ?>
                        </th>
                        <?php } ?>

                        <?php if(in_array('contact_name',$selected_columns)){ ?>
                        <th scope="col" class="sort_data sorting sorting_desc" data-controller="<?php echo $this->request->params['controller']; ?>" data-action="<?php echo $this->request->params['action']; ?>">
                            <?= $this->Paginator->sort('Contacts.first_name','Requestor Name') ?>
                        </th>
                        <?php } ?>

                        

                        <?php if(in_array('created',$selected_columns)){ ?>
                        <th scope="col" class="sort_data sorting" data-controller="<?php echo $this->request->params['controller']; ?>" data-action="<?php echo $this->request->params['action']; ?>">
                            <?= $this->Paginator->sort('WorkRequests.created','Created') ?>
                        </th>
                        <?php } ?>
                        <?php if(in_array('status',$selected_columns)){ ?>
                        <th scope="col" class="sort_data sorting" data-controller="<?php echo $this->request->params['controller']; ?>" data-action="<?php echo $this->request->params['action']; ?>">
                            <?= $this->Paginator->sort('WorkRequests.status','Status') ?>
                        </th>
                        <?php } ?>
                    
                    <th scope="col" class="actions"><?= __('Action') ?></th>
                </thead>
                
                <tbody class="data_listing">
                    <?php  echo $this->element('listing/work_requests_listing'); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="assign_template"></div>
</div>
<?php
$this->Html->script(['developer/workrequests.js'],['block'=>'script']);
?>