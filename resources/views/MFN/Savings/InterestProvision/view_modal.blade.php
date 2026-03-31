<link rel="stylesheet" href="{{ asset('assets/css/cube-grid-spinner.css') }}">
<!-- Modal -->
<div class="modal fade" id="provisionViewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="exampleModalLongTitle">Savings Provision Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped" id="provisionViewTable">
                        <thead>
                            <tr>
                                <td>SL#</td>
                                <td>Account No</td>
                                <td>Account Holder</td>
                                <td>Account Nature</td>
                                <td>Date From</td>
                                <td>Daste To</td>
                                <td>Principal Amount</td>
                                <td>Interest</td>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="sk-cube-grid" id="spinnerId" style="display: none;">
                    <div class="sk-cube sk-cube1"></div>
                    <div class="sk-cube sk-cube2"></div>
                    <div class="sk-cube sk-cube3"></div>
                    <div class="sk-cube sk-cube4"></div>
                    <div class="sk-cube sk-cube5"></div>
                    <div class="sk-cube sk-cube6"></div>
                    <div class="sk-cube sk-cube7"></div>
                    <div class="sk-cube sk-cube8"></div>
                    <div class="sk-cube sk-cube9"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>