@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\PosService as POSS;
?>

<form method="post" id="order_form_id">
    @csrf

    <input type="hidden" name="company_id" value="{{ Common::getCompanyId() }}">
    <input type="hidden" name="order_no" value="{{ POSS::generateBillOrder(Common::getBranchId()) }}">
    <input type="hidden" name="order_date" value="{{ Common::systemCurrentDate() }}">

    <div class="row">
        <div class="col-lg-12">
            <div class="form-row align-items-center">
                <label class="col-lg-2 input-title text-center">Supplier</label>
                <?php 
                    $suppliers = App\Model\POS\Supplier::where([['is_delete', 0], ['is_active', 1]])->get();
                ?>
                <div class="col-lg-3">
                    <div class="form-group">
                        <div class="input-group ">
                            <select class="form-control clsSelect2" name="supplier_id" id="supplier_id" onchange="fnGetRequisitionProd();">
                                <option value="">Select Option</option>
                                @foreach ($suppliers as $row)
                                <option value="{{ $row->id }}">{{ $row->sup_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <table class="table table-hover table-striped table-bordered w-full text-center" id="orderTableId">
            	<thead>
            		<tr>
                        <td>
                            <input type="checkBox" id="all_check_box_id">
                        </td>
                        <td>SL#</td>      
                        <td>Product Id</td>      
                        <td>Date</td>      
                        <td>Requisition No</td>      
                        <td>Requisition To</td>      
                        <td>Requisition From</td>      
                        <td>Total Quantity</td>      
                        <td>Supplier</td>      
                        <td>Details</td>      
                        {{-- <td>Status</td>       --}}
                    </tr>
            	</thead>
            	<tbody>
                    
            	</tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="form-group text-right">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                              class="btn btn-default btn-round d-print-none">Back</a>

                        <button type="submit" class="btn btn-primary btn-round"
                        id="order_button_id">Order</button>
                </div>
            </div>
        </div>
    </div>

<!-- Modal -->
    <div class="modal fade" id="delivery_date_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="exampleModalLongTitle">Product Orders</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <br>
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <div class="form-row">
                                <label class="col-lg-4 input-title RequiredStar" for="delivery_date">Delivery Date</label>
                                <div class="col-lg-7 form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend ">
                                            <span class="input-group-text ">
                                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control round datepicker-custom" id="delivery_date" name="delivery_date" autocomplete="off" placeholder="DD-MM-YYYY">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="submit_order" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!--End Page-->
<script type="text/javascript">

    $(document).ready(function(){
        $("#all_check_box_id").click(function(){
            $('input:checkbox').not(this).prop('checked', this.checked);
        });

        $("#orderTableId tr").click(function(event) {
            if (event.target.type !== 'checkbox') {
                $(':checkbox', this).trigger('click');
            }
        });

        fnGetRequisitionProd();
    });

    function fnCheck(row) 
    {
        if($('#order_check_box_'+row).prop("checked") == true)
        {
            $('#order_check_box_'+row).prop("checked", false);
            $('#total_quantity_id_'+ row).prop('readonly', true);
        }
        else if($('#order_check_box_'+row).prop("checked") == false)
        {
            $('#order_check_box_'+row).prop("checked", true);
            $('#total_quantity_id_'+ row).prop('readonly', false);
        }
    }

    $('#order_button_id').on('click', function(event) {
        event.preventDefault();

        if($('.ckeckBoxCls:checked').length == 0){
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please select product for order!!',
            });
        }
        else{
            var supplier = [];
            var flag = true;
            $.each($("input[name='order_check_box_arr[]']:checked"), function(){
                supplier.push($(this).attr('supplier'));
            });

            for (var i = 0; i < supplier.length - 1; i++) {
                if (supplier[i + 1] != supplier[i]) {
                    flag = false;
                    break;
                }
            }

            if(flag == true) {
                $('#delivery_date_modal').modal('toggle')
            }
            else {
                swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'You can order only single supplier!!',
                });
            }
        }
    });


    function fnGetRequisitionProd() {
        
        var supplier_id = $('#supplier_id').val();

        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxLoadSupplierProdByReq') }}",
            dataType: "text",
            data: {
                supplier: supplier_id,
            },
            success: function(data) {
                
                if (data) {
                    $('#orderTableId')
                    .find('tbody')
                    .empty()
                    .end()
                    .append(data);
                }
            }
        });
    }

    $('#submit_order').click(function(){

        var deliverDate = $('#delivery_date').val();

        if(deliverDate != '') {
            $('#order_form_id').submit();
        }
        else{
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please select deliver date.',
            });
        }

    });

    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });

    

</script>
@endsection
