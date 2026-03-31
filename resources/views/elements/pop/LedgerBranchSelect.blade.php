
<?php 
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
?>
<?php
    $branchData = Common::ViewTableOrderIn('gnl_branchs',
        [['is_delete', 0], ['is_active', 1], ['project_id', $Row->id]],
        ['id', HRS::getUserAccesableBranchIds()],
        ['id', 'branch_name'],
        ['id', 'ASC']);

?>

<div class="modal fade" id="branch_modal_{{$Row->id}}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header text-center">
                <h4 class="modal-title font-weight-bold text-center">Select Branches</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body mx-3">
                <div class="checkbox-custom checkbox-primary">
                    <input type="checkbox" id="branch_array_0_{{$Row->id}}" 
                    onclick="fnAllBranch({{$Row->id}});" name="branch_array[]" 
                    <?= (count($selectBranchArr) > 0) ? ( (in_array($Row->id."-0", $selectBranchArr)) ? "checked" : "") : "checked" ?>
                    value='{{$Row->id}}-0' />
                    <label>All Branch</label>
                </div>
                @foreach ($branchData as $BRow)
                <div class="checkbox-custom checkbox-primary">

                    <input type="checkbox" class="branch_cls_{{$Row->id}}" onclick="fnBranch({{$Row->id}});" 
                    id="branch_array_{{$BRow->id}}" name="branch_array[]"
                    <?= (count($selectBranchArr) > 0) ? ( (in_array($BRow->id, $selectBranchArr)) ? "checked" : "") : "checked" ?>
                    value="{{$BRow->id}}" />

                    <label for="branch_array_{{$BRow->id}}">
                        <small>{{$BRow->branch_name}}</small>
                    </label>
                    <br>
                </div>
                @endforeach
            </div>

            <div class="modal-footer d-flex justify-content-center">
                <div class="row align-items-center">
                    <div class="col-lg-12">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" class="btn btn-default btn-round" data-dismiss="modal">Close</a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
