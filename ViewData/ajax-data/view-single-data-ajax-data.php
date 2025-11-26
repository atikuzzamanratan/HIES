<?php
error_reporting(1);

require '../../vendor/autoload.php';
include "../../Config/config.php";
include "../../Lib/lib.php";

$app = new Solvers\Dsql\Application();

if ($_REQUEST['recordid'] != '') {
    $recordid = $app->cleanInput($_REQUEST['recordid']);
}
$qry = "SELECT xfr.id, xfr.FormId, xfr.SampleHHNo, xfr.PSU, psl.DivisionName, psl.DistrictName, xfr.UserID, xfr.DataName, xfr.XFormsFilePath, 
					COALESCE(xfr.IsEdited, 0) AS IsRowEdited, xfr.EntryDate, xfr.IsApproved, xfr.DeviceID FROM xformrecord xfr JOIN PSUList psl ON xfr.PSU=psl.PSU WHERE xfr.id = ?";
$resQry = $app->getDBConnection()->fetchAll($qry, $recordid);
//$resQry = $app->getDBConnection()->fetchAll($qry);

$data = array();

foreach ($resQry as $row) {
    $RecordID = $row->id;
    $HhNo = $row->SampleHHNo;
    $PSU = $row->PSU;
    $DivisionName = $row->DivisionName;
    $DistrictName = $row->DistrictName;

    $UserID = $row->UserID;
    $UserName = getValue('userinfo', 'UserName', "id = $UserID");
    $FullName = getValue('userinfo', 'FullName', "id = $UserID");
    $UserInfo = "$FullName ($UserName/$UserID)";

    $UserMobileNo = getValue('userinfo', 'MobileNumber', "id = $UserID");
    $UserMobileNo = whatsAppLink($UserMobileNo);

    $FormId = $row->FormId;
    if ($FormId == 2) {
        $Survey = "Main Survey";
    } elseif ($FormId == 3) {
        $Survey = "Listing Survey";
    }

    $DataName = $row->DataName;
    $XFormsFilePath = $row->XFormsFilePath;
    $DeviceID = $row->DeviceID;
    $EntryDate = date_format($row->EntryDate, 'd-m-Y H:i:s');

    $IsApproved = $row->IsApproved;
    $DataStatus = GetDataStatus($IsApproved);
		
	$IsEdited = $row->IsRowEdited;

    $Duration = 'N/A';

    $SubData = array();

    $actions = "<div style= \"display: flex; align-items: center; justify-content: center;\">
                    <button title=\"$btnTitleView\" type=\"button\" class=\"simple-ajax-modal btn btn-outline-primary\" style=\"display: inline-block;margin: 0 1px;\" data-bs-toggle=\"modal\" data-bs-target=\"#viewDataModal\" onclick=\"ShowDataDetail('$RecordID', '$IsApproved', '$PSU', '$FormId')\"><i class=\"fas fa-eye\"></i></button>
                    
                </div>
                <script type=\"text/javascript\">
                    function ShowDataDetail(recordID, status, psu, formID, data) {
                            $.ajax({
                                url: 'ViewData/ajax-data/data-detail-view-single-data.php',
                                method: 'GET',
                                datatype: 'json',
                                data: {
                                    id: recordID,
                                    status: status,
                                    psu: psu,
                                    formID: formID
                                },
                                success: function (response) {
                                    //alert(response);
                                    $('#dataViewDiv').html(response);
                                }
                            }); 
                        return false;
                    }
                </script>
                
                <!-- View Data Modal-->
                <div class=\"modal fade bd-example-modal-lg\" id=\"viewDataModal\" tabindex=\"-1\" aria-labelledby=\"editDataModalLabel\" aria-hidden=\"true\">
                  <div class=\"modal-dialog modal-lg\">
                    <div id=\"dataViewDiv\" class=\"modal-content\">
                      
                    </div>
                  </div>
                </div>";

    $SubData[] = $actions;

    $SubData[] = $RecordID;
    $SubData[] = $DataStatus;
    $SubData[] = $UserInfo;
    $SubData[] = $UserMobileNo;
    $SubData[] = $Survey;
    $SubData[] = $HhNo;
    $SubData[] = $PSU;
    $SubData[] = $DivisionName;
    $SubData[] = $DistrictName;
    $SubData[] = $EntryDate;
    $SubData[] = $DeviceID;
	//$SubData[] = $IsEdited;

    $data[] = $SubData;
}

$jsonData = json_encode($data);

echo '{"aaData":' . $jsonData . '}';

