<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/autoload.php';

CJSCore::Init(array("jquery", "ajax"));
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

global $APPLICATION;
$APPLICATION->SetTitle("Revision to Purchase Order");
// Js Files
$APPLICATION->AddHeadScript('/forms/script/Form.js');
$APPLICATION->AddHeadScript('/forms/script/QuonsetForm.js');
$APPLICATION->AddHeadScript('/forms/script/StraightWallForm.js');
$APPLICATION->AddHeadScript('/forms/script/QuonsetPartsOrder.js');
$APPLICATION->AddHeadScript('/forms/script/RevisionToPurchaseOrder.js');
$APPLICATION->AddHeadScript('/forms/script/events.js');
$APPLICATION->AddHeadScript('/local/assets/js/jquery.maskMoney.js');
// Style Files
$APPLICATION->SetAdditionalCSS("/forms/style/style.css", true);

$request = Context::getCurrent()->getRequest();
?>
<script src="https://unpkg.com/imask"></script>

<!-- Image loader -->
<div id='loader' style='display: none; width: 100px;height: 100px;position: fixed;top: 50%;left: 50%;margin-left: -50px;margin-top:-50px;'>
    <img src='/forms/loader.gif'>
</div>
<!-- Image loader -->
<div class="formTypeAndDealID" id="<?=$request['FORM_TYPE'].'#'.$request['DEAL_ID'].'#'.$request['ID'];?>">
    <form id="wall_form" class="wall_form" method="post" action="">
        <!-- Sales Rep Name Dynamic List, get employees from Sales Department -->
        <label for="SALES_REP">Sales Rep Name</label><select type="text" class="s100" name="SALES_REP"></select>
        <!-- Sales Rep Email Field -->
        <label for="SALES_REP_EMAIL">Sales Rep Email</label><input type="text" class="w100" name="SALES_REP_EMAIL">
        <!-- Customer Field -->
        <label for="CUSTOMER">Customer Name</label><input type="text" class="w100" name="CUSTOMER"><br>
        <!-- Company Field -->
        <label for="COMPANY">Company</label><input type="text" class="w100" name="COMPANY"><br>
        <!-- Account Number Field -->
        <label for="ACCOUNT_NUMBER">Account No.</label><input type="text" class="w100" name="ACCOUNT_NUMBER"><br>
        <!-- Pioneer ID Field -->
        <label for="VENDOR_ID">Vendor ID</label><input type="text" class="w100" name="VENDOR_ID"><br>
        <!-- Mailing Address Field -->
        <label for="MAILING_ADDRESS">Mailing Address</label><input type="text" class="w100" name="MAILING_ADDRESS"><br>
        <!-- Site Address Field -->
        <label for="SITE_ADDRESS">Site Address</label><input type="text" class="w100" name="SITE_ADDRESS"><br>
        <!-- Shipping Address Field -->
        <label for="SHIPPING_ADDRESS">Shipping Address</label><input type="text" class="w100" name="SHIPPING_ADDRESS"><br>
        <!-- Primary Phone Field -->
        <label for="PRIMARY_PHONE">Primary phone</label><input type="tel" id="primary_phone" class="w100" name="PRIMARY_PHONE"><br>
        <!-- Secondary Phone Field -->
        <label for="SECONDARY_PHONE">Secondary phone</label><input type="tel" id="secondary_phone" class="w100" name="SECONDARY_PHONE"><br>
        <!-- Work Field -->
        <label for="WORK">Work</label><input type="text" class="w100" name="WORK"><br>
        <!-- Email Field -->
        <label for="EMAIL">Email</label><input type="email" class="w100" name="EMAIL"><br>
        <!-- Order Status Static List -->
        <label for="ORDER_STATUS">Order Status</label><select type="text" class="s100" name="ORDER_STATUS">
            <option value=""></option>
            <option value="R1">R1</option>
            <option value="R2">R2</option>
            <option value="R3">R3</option>
            <option value="R4">R4</option>
            <option value="R5">R5</option>
            <option value="R6">R6</option>
            <option value="R7">R7</option>
        </select><br>
        <!-- Model Type Static List -->
        <label for="MODEL_TYPE">Building Use</label><select type="text" class="s100" name="MODEL_TYPE">
            <option value="Please Select" >Please Select</option>
            <option value="Quonset" >Quonset</option>
            <option value="Straight Wall System" >Straight Wall System</option>
        </select><br>
        <!-- Revised Drawings Static List -->
        <label for="REVISED_DRAWINGS">Revised Drawings</label><select id="REVISED_DRAWINGS" type="text" class="s100" name="REVISED_DRAWINGS">
            <option value="Not Included" selected>Not Included</option>
            <option value="Included - Stamped" >Quonset - Included - Stamped</option>
            <option value="Included - Unstamped" >Quonset - Included - Unstamped</option>
            <option value="Included - Stamped" >Straight Wall System - Included – Stamped</option>
        </select>
        <!-- Change 1 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="CHANGE_1">Change 1</label><select id="CHANGE_1" type="text" class="s100" name="CHANGE_1">
            <option value="" ></option>
            <option value="Add" >Add</option>
            <option value="Remove" >Remove</option>
            <option value="Change" >Change</option>
        </select><br>
        <!-- Description 1 Field (showing only if #CHANGE_1.value != '') -->
        <label for="DESCRIPTION_1" style="display: none;">Description 1</label><textarea id="DESCRIPTION_1" type="text" class="w100" name="DESCRIPTION_1" style="display: none"></textarea><br>
        <!-- Change 2 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="CHANGE_2">Change 2</label><select id="CHANGE_2" type="text" class="s100" name="CHANGE_2"">
        <option value="" ></option>
        <option value="Add" >Add</option>
        <option value="Remove" >Remove</option>
        <option value="Change" >Change</option>
        </select><br>
        <!-- Description 2 Field (showing only if #CHANGE_2.value != '') -->
        <label for="DESCRIPTION_2" style="display: none;">Description 2</label><textarea id="DESCRIPTION_2" type="text" class="w100" name="DESCRIPTION_2" style="display: none"></textarea><br>
        <!-- Change 3 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="CHANGE_3">Change 3</label><select id="CHANGE_3" type="text" class="s100" name="CHANGE_3">
            <option value="" ></option>
            <option value="Add" >Add</option>
            <option value="Remove" >Remove</option>
            <option value="Change" >Change</option>
        </select><br>
        <!-- Description 3 Field (showing only if #CHANGE_3.value != '') -->
        <label for="DESCRIPTION_3" style="display: none;">Description 3</label><textarea id="DESCRIPTION_3" type="text" class="w100" name="DESCRIPTION_3" style="display: none"></textarea><br>
        <!-- Change 4 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="CHANGE_4">Change 4</label><select id="CHANGE_4" type="text" class="s100" name="CHANGE_4">
            <option value="" ></option>
            <option value="Add" >Add</option>
            <option value="Remove" >Remove</option>
            <option value="Change" >Change</option>
        </select><br>
        <!-- Description 4 Field (showing only if #CHANGE_4.value != '') -->
        <label for="DESCRIPTION_4" style="display: none;">Description 4</label><textarea id="DESCRIPTION_4" type="text" class="w100" name="DESCRIPTION_4" style="display: none"></textarea><br>
        <!-- Change 5 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="CHANGE_5">Change 5</label><select id="CHANGE_5" type="text" class="s100" name="CHANGE_5">
            <option value="" ></option>
            <option value="Add" >Add</option>
            <option value="Remove" >Remove</option>
            <option value="Change" >Change</option>
        </select><br>
        <!-- Description 5 Field (showing only if #CHANGE_5.value != '') -->
        <label for="DESCRIPTION_5" style="display: none;">Description 5</label><textarea id="DESCRIPTION_5" type="text" class="w100" name="DESCRIPTION_5" style="display: none"></textarea><br>
        <!-- Exposure Conditions Static List -->
        <label for="EXPOSURE_CONDITIONS">Exposure Conditions</label><select id="EXPOSURE_CONDITIONS" type="text" class="s100" name="EXPOSURE_CONDITIONS">
            <option value="" selected></option>
            <option value="Sheltered" >Sheltered</option>
            <option value="Fully Exposed" >Fully Exposed</option>
        </select>
        <!-- Total Amount of Revision Field -->
        <label for="TOTAL_AMOUNT">Total Amount of Revision</label><input type="text" class="w100 money-input" id="TOTAL_AMOUNT"  name="TOTAL_AMOUNT" placeholder="$0.00"><br>
        <!-- Original Contract Amount Field -->
        <label for="ORIGINAL_CONTRACT_AMOUNT">Original Contract Amount</label><input type="text" class="w100 money-input" id="ORIGINAL_CONTRACT_AMOUNT" name="ORIGINAL_CONTRACT_AMOUNT" placeholder="$0.00"><br>
        <!-- Revised Building Price Field -->
        <label for="BUILDING_PRICE">Revised Building Price</label><input id="BUILDING_PRICE" type="text" class="w100 money-input" name="BUILDING_PRICE" placeholder="$0.00"><br>
        <!-- Tax Rate Dynamic List, get from HighLoadBlock with ID = TAX_RATE_HIGHLOAD (constant) -->
        <label for="TAX_RATE">Tax Rate</label><select id="TAX_RATE" type="text" class="s100" name="TAX_RATE"></select><br>
        <!-- Tax Dynamic Field. Tax = BuildingPrice * (TaxRate / 100) -->
        <label for="TAX">Tax</label><input type="text" class="w100 money-input" name="TAX" placeholder="$0.00"><br>
        <!-- Revised Total Contract Price Dynamic Field. SubTotal = BuildingPrice + Tax -->
        <label for="SUB_TOTAL">Revised Total Contract Price</label><input id="SUB_TOTAL" type="text" class="w100 money-input" name="SUB_TOTAL" placeholder="$0.00"><br>
        <!-- First Deposit Dynamic Field. FirstDeposit = SubTotal * 0.25 -->
        <label for="FIRST_DEPOSIT">First Deposit</label><input type="text" class="w100 money-input" name="FIRST_DEPOSIT" placeholder="$0.00"><br>
        <!-- First Deposit Status Static List -->
        <label for="FIRST_DEPOSIT_STATUS">First Deposit Status</label><select type="text" class="s100" name="FIRST_DEPOSIT_STATUS">
            <option value=""></option>
            <option value="Due Today">DUE Today</option>
            <option value="Paid">PAID Thank You!</option>
        </select><br>
        <!-- Second Deposit Dynamic Field. SecondDeposit = SubTotal * 0.25 -->
        <label for="SECOND_DEPOSIT">Second Deposit</label><input type="text" class="w100 money-input" name="SECOND_DEPOSIT" placeholder="$0.00"><br>
        <!-- Second Deposit Status Static List -->
        <label for="SECOND_DEPOSIT_STATUS">Second Deposit Status</label><select type="text" class="s100" name="SECOND_DEPOSIT_STATUS">
            <option value=""></option>
            <option value="Due Today">DUE Today</option>
            <option value="Paid">PAID Thank You!</option>
        </select><br>
        <!-- Balance Remaining Dynamic Field. BalanceRemaining = SubTotal * 0.5 -->
        <label for="BALANCE_REMAINING">Balance Remaining</label><input type="text" class="w100 money-input" name="BALANCE_REMAINING" placeholder="$0.00"><br>
        <!-- Requested Delivery Month Dynamic List, get from HighLoadBlock with ID = REQUESTED_DELIVERY_MONTH_HIGHLOAD (constant) -->
        <label for="REQUESTED_DELIVERY_MONTH">Requested Delivery Month</label><select type="text" class="s100" name="REQUESTED_DELIVERY_MONTH"></select><br>
        <!-- Payment Method Static List -->
        <label for="PAYMENT_METHOD">Payment Method</label><select type="text" class="s100" name="PAYMENT_METHOD">
            <option value="Credit Card">Credit Card&nbsp;&nbsp;</option>
            <option value="Bill Pay / Wire Transfer">Bill Pay / Wire Transfer&nbsp;&nbsp;</option>
            <option value="Pay Pal">Pay Pal&nbsp;&nbsp;</option>
        </select><br>
        <!-- Notes Field -->
        <label for="NOTES">Notes</label><textarea type="text" class="w100" name="NOTES"></textarea><br>
        <!-- Notes to Office Field -->
        <label for="NOTES_TO_OFFICE">Notes to back office - List any relevant info here!</label><textarea type="text" class="w100" name="NOTES_TO_OFFICE"></textarea><br>
        <!-- Submit Button. Save form to HighLoadBlock with ID = FORMS_HIGHLOAD, create PDF file and then send it to Sales Rep -->
        <input type="button" id="btn" class="submitFF" name="submit" value="Update">
        <!-- Save as New. Save form to HighLoadBlock with ID = FORMS_HIGHLOAD, create PDF file and then send it to Sales Rep -->
        <input type="button" id="btn1" class="submitFF" name="submit" value="Save New">
        <!-- Delete button -->
        <input type="button" id="delete_btn" class="deleteFF" name="delete" value="Delete">
        <!-- Clear Fields Button -->
        <input type="reset" id="res" class="resetFF" name="reset" value="Reset">
    </form>
</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>

