
<!-- Page -->
<!-- <div class="page">
    <div class="page-header">
        <h4 class="">Update Union/Zone Entry</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Address</a></li>
            <li class="breadcrumb-item"><a href="index.php?PageId=UnionList">Union/Zone List</a></li>
            <li class="breadcrumb-item active">Entry</li>
        </ol>
    </div>
    <div class="page-content">
        <div class="panel">
            <div class="panel-body"> -->
                <form action="" method=""  data-toggle="validator" novalidate="true">
                  <div class="row">
                      <div class="col-lg-9 offset-lg-3">
                          <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title RequiredStar" for="groupName">Division</label>
                              <div class="col-lg-5 form-group">
                                  <div class="input-group">
                                      <select class="form-control clsSelect2" name="divisionName" id="groupName" required data-error="Please select Division name.">
                                          <option value="">Select One</option>
                                          <option value="1">Assistant Manager</option>
                                          <option value="2">Zonal Manager</option>
                                          <option value="3">Program MAnager</option>
                                          <option value="4">Asst. Director</option>
                                      </select>
                                  </div>
                                  <div class="help-block with-errors is-invalid"></div>
                              </div>
                          </div>
                          <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title RequiredStar" for="groupName">DISTRICT</label>
                              <div class="col-lg-5 form-group">
                                  <div class="input-group">
                                      <select class="form-control clsSelect2" name="districtName" id="groupName" required data-error="Please select District name.">
                                          <option value="">Select One</option>
                                          <option value="1">Assistant Manager</option>
                                          <option value="2">Zonal Manager</option>
                                          <option value="3">Program MAnager</option>
                                          <option value="4">Asst. Director</option>
                                      </select>
                                  </div>
                                  <div class="help-block with-errors is-invalid"></div>
                              </div>
                          </div>
                          <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title RequiredStar" for="groupName">UPAZILA</label>
                              <div class="col-lg-5 form-group">
                                  <div class="input-group">
                                      <select class="form-control clsSelect2" name="upazilaName" id="groupName" required data-error="Please select Upazila name.">
                                          <option value="">Select One</option>
                                          <option value="1">Assistant Manager</option>
                                          <option value="2">Zonal Manager</option>
                                          <option value="3">Program MAnager</option>
                                          <option value="4">Asst. Director</option>
                                      </select>
                                  </div>
                                  <div class="help-block with-errors is-invalid"></div>
                              </div>
                          </div>
                          <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title RequiredStar" for="groupName">UNION/ZONE</label>
                              <div class="col-lg-5 form-group">
                                  <div class="input-group">
                                      <input type="text" class="form-control round" placeholder="Enter village Name" name="brachName" id="unionName" required data-error="Please enter Union name.">
                                  </div>
                                  <div class="help-block with-errors is-invalid"></div>
                              </div>
                          </div>
                  <div class="form-row align-items-center">
                      <div class="col-lg-6">
                          <div class="form-group d-flex justify-content-center">
                              <div class="example example-buttons">
                                  <a href="#" class="btn btn-default btn-round">Close</a>
                                  <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Save</button>
                                  <!--<button type="button" class="btn btn-warning btn-round">Next</button>-->
                              </div>
                          </div>
                      </div>
                  </div>
                </div>
                </form>
            </div>
        </div>

    </div>
</div>
<!-- End Page -->
