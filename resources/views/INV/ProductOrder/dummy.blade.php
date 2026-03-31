    		$requisitionData = DB::table('inv_requisitions_m as rm')
                ->where([['rm.is_delete', 0],['rm.is_approve', 1], ['rm.is_active', 1], ['rm.is_ordered', 0]])
                // ->whereIn('rm.branch_from', HRS::getUserAccesableBranchIds())
                ->select('rm.*', 'sup.sup_name', 'brf.branch_name as sup_branch_from')
                ->leftjoin('inv_suppliers as sup', function ($requisitionData) {
                    $requisitionData->on('rm.supplier_id', '=', 'sup.id');
                })
                ->leftjoin('gnl_branchs as brf', function ($requisitionData) {
                    $requisitionData->on('rm.branch_from', '=', 'brf.id')
                        ->where('brf.is_approve', 1);
                })
                ->orderBy('rm.id', 'DESC')
                ->get();

                <!-- <?php $i = 0; ?>
                    @foreach($requisitionData as $reqData)
                        <?php $i++ ?>
                        <tr>
                            <td>
                                <input type="checkBox" name="order_check_box_arr[]" id="order_check_box_{{ $i }}" value="{{ $reqData->id }}" supplier="{{ $reqData->supplier_id }}">
                            </td>
                            <td>{{ $i }}</td>
                            <td>{{ date('d-m-Y', strtotime($reqData->requisition_date)) }}</td>
                            <td>{{ $reqData->requisition_no }}</td>
                            <td>{{ 'Head Office' }}</td>
                            <td>{{ $reqData->sup_branch_from }}</td>
                            <td>{{ $reqData->total_quantity }}</td>
                            <td>{{ $reqData->sup_name }}</td>
                            <td>
                                <a href="{{ url('inv/requisition/view/'.$reqData->id) }}" title="View" class="btnView">
                                    <i class="icon wb-eye mr-2 blue-grey-600"></i>
                                </a>
                            </td>
                            {{-- <td>{{ 'status' }}</td> --}}
                        </tr>
                        <input type="text" name="requisition_id_arr[]" id="requisition_id_{{ $i }}" value="{{ $reqData->id }}" hidden="true">

                        <input type="text" name="order_to_arr[]" id="supplier_id_{{ $i }}" value="{{ $reqData->supplier_id }}" hidden="true">

                        <input type="text" name="total_quantity_arr[]" id="total_quantity_id_{{ $i }}" value="{{ $reqData->total_quantity }}" hidden="true">

                        <input type="text" name="requisition_no_arr[]" id="requisition_no_id_{{ $i }}" value="{{ $reqData->requisition_no }}" hidden="true">

                        <input type="text" name="requisition_date_arr[]" id="requisition_date_id_{{ $i }}" value="{{ $reqData->requisition_date }}" hidden="true">

                        <input type="text" name="requisition_branch_from_arr[]" id="requisition_branch_from_id_{{ $i }}" value="{{ $reqData->branch_from }}" hidden="true">

                        <input type="text" name="product_quantity_arr[]" id="product_quantity_arr{{ $i }}" value="{{ $reqData->total_quantity }}" hidden="true">
                    @endforeach -->


                    {{-- ordercontroller (view) --}}
                    /*$requistionData = DB::table('inv_orders_m as pom')
                ->where('pom.id', $id)
                ->select('pod.id', 'prd.product_name', 'ppm.model_name', 'pod.product_quantity', 'pp.sale_price', 'br.branch_addr', 'br.contact_person', 'br.branch_phone', 'prd.requisition_no', DB::raw('(pod.product_quantity * pp.sale_price) as total_price'))
                ->leftjoin('inv_orders_d as pod', function($requistionData){
                    $requistionData->on('pod.order_id', '=', 'pom.id')
                        ->where('pod.is_delete', 0);
                })
                ->join('inv_requisitions_d as prd', function($requistionData){
                    $requistionData->on('prd.requisition_no', '=', 'pod.requisition_no')
                        ->where([['prd.is_delete', 0], ['prd.is_active', 1], ['prd.is_ordered', 1]]);
                })
                ->leftjoin('inv_products as pp', function($requistionData){
                    $requistionData->on('pp.id', '=', 'prd.product_id')
                        ->where([['pp.is_delete', 0], ['pp.is_active', 1]]);
                })
                ->leftjoin('inv_p_models as ppm', function($requistionData){
                    $requistionData->on('ppm.id', '=', 'pp.prod_model_id')
                        ->where([['ppm.is_delete', 0], ['ppm.is_active', 1]]);
                })
                ->leftjoin('gnl_branchs as br', function($requistionData){
                    $requistionData->on('br.id', '=', 'prd.branch_from')
                        ->where([['br.is_delete', 0], ['br.is_active', 1]]);
                })
                // ->groupBy('pom.id')
                ->get();*/


                {{-- ajax controller  --}}
                {{-- $queryData = DB::table('inv_requisitions_d as prd')
                ->where([['prd.is_delete', 0], ['prd.is_active', 1], ['prd.is_ordered', 0]])
                ->select('prm.*', 'prd.id as prd_id', 'prd.product_id', 'prd.product_quantity', 'prod.product_name', 'prod.sys_barcode', 'ps.sup_name', 'brf.branch_name as branch_from_name')
                ->leftjoin('inv_requisitions_m as prm', function($queryData){
                    $queryData->on('prd.requisition_id', '=', 'prm.id')
                        ->where([['prm.is_delete', 0], ['prm.is_active', 1]]);
                })
                ->leftjoin('inv_products as prod', function($queryData){
                    $queryData->on('prod.id', '=', 'prd.product_id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->leftjoin('inv_suppliers as ps', function($queryData){
                    $queryData->on('ps.id', '=', 'prm.supplier_id')
                        ->where([['ps.is_delete', 0], ['ps.is_active', 1]]);
                })
                ->leftjoin('gnl_branchs as brf', function ($queryData) {
                    $queryData->on('prm.branch_from', '=', 'brf.id')
                        ->where('brf.is_approve', 1);
                })
                ->where(function($queryData) use ($supplier){
                    if (!empty($supplier)) {
                        $queryData->where('prm.supplier_id', $supplier);
                    }
                })
                ->orderBy('prm.requisition_no')
                ->get(); --}}


                {{-- view --}}
                {{-- $requisitionM = DB::table('inv_requisitions_d as prd')
                        ->where([['prd.is_delete', 0], ['prd.is_active', 1], ['prd.is_ordered', 0]])
                        ->select('prm.*', 'ps.sup_name', 'brf.branch_name as branch_from', 'brt.branch_name as branch_to')
                        ->leftjoin('inv_requisitions_m as prm', function($queryData){
                            $queryData->on('prd.requisition_id', '=', 'prm.id')
                                ->where([['prm.is_delete', 0], ['prm.is_active', 1]]);
                        })
                        ->leftjoin('inv_products as prod', function($queryData){
                            $queryData->on('prod.id', '=', 'prd.product_id')
                                ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                        })
                        ->leftjoin('inv_suppliers as ps', function($queryData){
                            $queryData->on('ps.id', '=', 'prod.supplier_id')
                                ->where([['ps.is_delete', 0], ['ps.is_active', 1]]);
                        })
                        ->leftjoin('gnl_branchs as brf', function ($requisitionData) {
                            $requisitionData->on('prm.branch_from', '=', 'brf.id')
                                ->where('brf.is_approve', 1);
                        })
                        ->leftjoin('gnl_branchs as brt', function ($requisitionData) {
                            $requisitionData->on('prm.branch_to', '=', 'brt.id')
                                ->where('brt.is_approve', 1);
                        })
                        ->first(); --}}