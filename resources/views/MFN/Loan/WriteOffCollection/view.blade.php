<!-- Modal -->
<div class="modal fade" id="writeOffCollnViewModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="exampleModalLongTitle">Collection Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <td width="25%">Member Name: </td>
                                <td width="25%" id="memberName"></td>
                                <td width="25%">Principal</td>
                                <td width="25%" id="principalAmount"></td>
                            </tr>
                            <tr>
                                <td width="25%">Meber Code: </td>
                                <td width="25%" id="memberCode"></td>
                                <td width="25%">Interest: </td>
                                <td width="25%" id="interestAmount"></td>
                            </tr>
                            <tr>
                                <td width="25%">Loan Code: </td>
                                <td width="25%" id="loanCode"></td>
                                <td width="25%">P + I: </td>
                                <td width="25%" id="principalWithInterest"></td>
                            </tr>
                            <tr>
                                <td width="25%">Date: </td>
                                <td width="25%" id="date"></td>
                                <td width="25%">Collection Amount: </td>
                                <td width="25%" id="amount"></td>
                            </tr>
                            <tr>
                                <td width="25%">Entry By: </td>
                                <td width="25%" id="entryBy"></td>
                                <td width="25%">Mode Of Payment: </td>
                                <td width="25%" id="paymentType"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>