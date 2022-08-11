<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/autoload.php';

CJSCore::Init(array("jquery", "ajax"));
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

global $APPLICATION;
$APPLICATION->SetTitle("Straight Wall Parts Order");
// Js Files
$APPLICATION->AddHeadScript('/forms/script/Form.js');
$APPLICATION->AddHeadScript('/forms/script/StraightWallPartsOrder.js');
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
        <!-- Order Status Field -->
        <label for="ORDER_STATUS">Order Status</label><select type="text" class="s100" name="ORDER_STATUS">
            <option value="Parts New Order" >Parts New Order</option>
            <option value="Parts Order R1" >Parts Order R1</option>
            <option value="Parts Order R2" >Parts Order R2</option>
            <option value="Parts Order R3" >Parts Order R3</option>
            <option value="Parts Order R4" >Parts Order R4</option>
        </select><br>
        <!-- Mailing Address Field -->
        <label for="MAILING_ADDRESS">Mailing Address</label><input type="text" class="w100" name="MAILING_ADDRESS"><br>
        <!-- Site Address Field -->
        <label for="SITE_ADDRESS">Site Address</label><input type="text" class="w100" name="SITE_ADDRESS"><br>
        <!-- Shipping Address Field -->
        <label for="SHIPPING_ADDRESS">Shipping Address</label><input type="text" class="w100" name="SHIPPING_ADDRESS"><br>
        <!-- Account Number Field -->
        <label for="ACCOUNT_NUMBER">Account No.</label><input type="text" class="w100" name="ACCOUNT_NUMBER"><br>
        <!-- Pioneer ID Field -->
        <label for="PIONEER_ID">Pioneer ID</label><input type="text" class="w100" name="PIONEER_ID"><br>
        <!-- Primary Phone Field -->
        <label for="PRIMARY_PHONE">Primary phone</label><input type="tel" id="primary_phone" class="w100" name="PRIMARY_PHONE"><br>
        <!-- Secondary Phone Field -->
        <label for="SECONDARY_PHONE">Secondary phone</label><input type="tel" id="secondary_phone" class="w100" name="SECONDARY_PHONE"><br>
        <!-- Work Field -->
        <label for="WORK">Work</label><input type="text" class="w100" name="WORK"><br>
        <!-- Email Field -->
        <label for="EMAIL">Email</label><input type="email" class="w100" name="EMAIL"><br>
        <!-- Building Use Static List -->
        <label for="BUILDING_USE">Building Use</label><select type="text" class="s100" name="BUILDING_USE">
            <option value="Please Select" >Please Select</option>
            <option value="Category I" >Low Human Occupancy</option>
            <option value="Category II" >Medium Human Occupancy</option>
            <option value="Category III" >High Human Occupancy</option>
        </select><br>
        <!-- Parts 1 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="PARTS_1">Parts 1</label><select id="PARTS_1" type="text" class="s100" name="PARTS_1">
        </select><br>
        <!-- Parts 1 Quantity Field (showing only if #PARTS_1.value != '') -->
        <label for="PARTS_QTY_1" style="display: none;">Parts 1 QTY</label><input id="PARTS_QTY_1" type="text" class="w100" name="PARTS_QTY_1" style="display: none;"><br>
        <!-- Parts 2 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="PARTS_2">Parts 2</label><select id="PARTS_2" type="text" class="s100" name="PARTS_2"">
        </select><br>
        <!-- Parts 2 Quantity Field (showing only if #PARTS_2.value != '') -->
        <label for="PARTS_QTY_2" style="display: none;">Parts 2 QTY</label><input id="PARTS_QTY_2" type="text" class="w100" name="PARTS_QTY_2" style="display: none;"><br>
        <!-- Parts 3 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="PARTS_3">Parts 3</label><select id="PARTS_3" type="text" class="s100" name="PARTS_3">
        </select><br>
        <!-- Parts 3 Quantity Field (showing only if #PARTS_3.value != '') -->
        <label for="PARTS_QTY_3" style="display: none;">Parts 3 QTY</label><input id="PARTS_QTY_3" type="text" class="w100" name="PARTS_QTY_3" style="display: none;"><br>
        <!-- Parts 4 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="PARTS_4">Parts 4</label><select id="PARTS_4" type="text" class="s100" name="PARTS_4">
        </select><br>
        <!-- Parts 4 Quantity Field (showing only if #PARTS_4.value != '') -->
        <label for="PARTS_QTY_4" style="display: none;">Parts 4 QTY</label><input id="PARTS_QTY_4" type="text" class="w100" name="PARTS_QTY_4" style="display: none;"><br>
        <!-- Parts 5 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="PARTS_5">Parts 5</label><select id="PARTS_5" type="text" class="s100" name="PARTS_5">
        </select><br>
        <!-- Parts 5 Quantity Field (showing only if #PARTS_5.value != '') -->
        <label for="PARTS_QTY_5" style="display: none;">Parts 5 QTY</label><input id="PARTS_QTY_5" type="text" class="w100" name="PARTS_QTY_5" style="display: none;"><br>
        <!-- Parts 6 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="PARTS_6">Parts 6</label><select id="PARTS_6" type="text" class="s100" name="PARTS_6">
        </select><br>
        <!-- Parts 6 Quantity Field (showing only if #PARTS_6.value != '') -->
        <label for="PARTS_QTY_6" style="display: none;">Parts 6 QTY</label><input id="PARTS_QTY_6" type="text" class="w100" name="PARTS_QTY_6" style="display: none;"><br>
        <!-- Revised Drawings Static List -->
        <label for="REVISED_DRAWINGS">Revised Drawings</label><select id="REVISED_DRAWINGS" type="text" class="s100" name="REVISED_DRAWINGS">
            <option value="" selected></option>
            <option value="Included" >Included</option>
            <option value="Not Included" >Not Included</option>
        </select>
        <!-- Exposure Conditions Static List -->
        <label for="EXPOSURE_CONDITIONS">Exposure Conditions</label><select id="EXPOSURE_CONDITIONS" type="text" class="s100" name="EXPOSURE_CONDITIONS">
            <option value="" selected></option>
            <option value="Sheltered" >Sheltered</option>
            <option value="Shadow / Drift" >Shadow / Drift</option>
        </select>
        <!-- Is Anchor Wedges or Insulation Included Static List -->
        <label for="IS_ANCHOR_OR_INSULATION">Is Anchor Wedges or Insulation Included</label><select id="IS_ANCHOR_OR_INSULATION" type="text" class="s100" name="IS_ANCHOR_OR_INSULATION">
            <option value="Please Select" >Please Select</option>
            <option value="Anchor Wedges" >Anchor Wedges</option>
            <option value="Pins & Caps" >Pins & Caps</option>
            <option value="Anchor Wedges and Pins & Caps" >Anchor Wedges and Pins & Caps</option>
        </select>
        <!-- Requested Delivery Month Dynamic List, get from HighLoadBlock with ID = REQUESTED_DELIVERY_MONTH_HIGHLOAD (constant) -->
        <label for="REQUESTED_DELIVERY_MONTH">Requested Delivery Month</label><select type="text" class="s100" name="REQUESTED_DELIVERY_MONTH"></select><br>
        <!-- Payment Method Static List -->
        <label for="PAYMENT_METHOD">Payment Method</label><select type="text" class="s100" name="PAYMENT_METHOD">
            <option value="Credit Card">Credit Card&nbsp;&nbsp;</option>
            <option value="Bill Pay / Wire Transfer">Bill Pay / Wire Transfer&nbsp;&nbsp;</option>
            <option value="Pay Pal">Pay Pal&nbsp;&nbsp;</option>
        </select><br>
        <!-- Is Pick Up checkbox -->
        <label for="IS_PICK_UP">Pick up from Storage Yard</label>
        <div class="c100">
            <div>Yes</div>
            <input type="checkbox" id="IS_PICK_UP" class="" name="IS_PICK_UP"><br>
        </div>
        <!-- Pick Up Field -->
        <label for="PICK_UP">Pick Up</label><input type="text" id="PICK_UP" class="w100" name="PICK_UP"><br>
        <!-- Parts Price Field -->
        <label for="BUILDING_PRICE">Parts Price</label><input id="BUILDING_PRICE" type="text" class="w100 money-input" name="BUILDING_PRICE" placeholder="$0.00"><br>
        <!-- Tax Rate Dynamic List, get from HighLoadBlock with ID = TAX_RATE_HIGHLOAD (constant) -->
        <label for="TAX_RATE">Tax Rate</label><select id="TAX_RATE" type="text" class="s100" name="TAX_RATE"></select><br>
        <!-- Tax Dynamic Field. Tax = BuildingPrice * (TaxRate / 100) -->
        <label for="TAX">Tax</label><input type="text" class="w100 money-input" name="TAX" placeholder="$0.00"><br>
        <!-- Sub Total Dynamic Field. SubTotal = BuildingPrice + Tax -->
        <label for="SUB_TOTAL">Sub Total</label><input type="text" id="SUB_TOTAL" class="w100 money-input" name="SUB_TOTAL" placeholder="$0.00"><br>
        <!-- Sub Total Status Static List -->
        <label for="SUB_TOTAL_STATUS">Sub Total Status</label><select type="text" class="s100" name="SUB_TOTAL_STATUS">
            <option value=""></option>
            <option value="Due Today">DUE Today</option>
            <option value="Paid">PAID Thank You!</option>
        </select><br>
        <!-- Addendum 1 Static List -->
        <label for="ADDENDUM_1">Addendum 1</label><select id="ADDENDUM_1" type="text" class="s100" name="ADDENDUM_1">
            <option value="NO">Please Select If Applicable&nbsp;&nbsp;</option>
            <option value="PERMIT_APPROVAL">Conditional Order - Permit Approval&nbsp;&nbsp;</option>
            <option value="FINANCING_APPROVAL">Conditional Order - Financing Approval&nbsp;&nbsp;</option>
            <option value="BUYER_APPROVAL">Conditional Order - Buyer Approval&nbsp;&nbsp;</option>
        </select><br>
        <!--
        Addendum Label Dynamic Label, get from #ADDENDUM_1 and equal to it value
        Addendum Dynamic Field, get from #ADDENDUM_1 and equal to constant with the same name
         -->
        <label id="ADDENDUM_LABEL" for="ADDENDUM">Addendum</label><textarea id="ADDENDUM" type="text" class="w100" name="ADDENDUM"></textarea><br>
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

