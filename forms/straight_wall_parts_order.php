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
        <!-- Project Name Field -->
        <label for="PROJECT_NAME">Project Name</label><input type="text" class="w100" name="PROJECT_NAME"><br>
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
        <!-- Accessory 1 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="ACCESSORY_1">Accessory 1</label><select id="ACCESSORY_1" type="text" class="s100" name="ACCESSORY_1">
        </select><br>
        <!-- Accessory 1 Quantity Field (showing only if #ACCESSORY_1.value != '') -->
        <label for="ACCESSORY_QTY_1" style="display: none;">Accessory 1 QTY</label><input id="ACCESSORY_QTY_1" type="text" class="w100" name="ACCESSORY_QTY_1" style="display: none;"><br>
        <!-- Accessory 2 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="ACCESSORY_2">Accessory 2</label><select id="ACCESSORY_2" type="text" class="s100" name="ACCESSORY_2"">
        </select><br>
        <!-- Accessory 2 Quantity Field (showing only if #ACCESSORY_2.value != '') -->
        <label for="ACCESSORY_QTY_2" style="display: none;">Accessory 2 QTY</label><input id="ACCESSORY_QTY_2" type="text" class="w100" name="ACCESSORY_QTY_2" style="display: none;"><br>
        <!-- Accessory 3 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="ACCESSORY_3">Accessory 3</label><select id="ACCESSORY_3" type="text" class="s100" name="ACCESSORY_3">
        </select><br>
        <!-- Accessory 3 Quantity Field (showing only if #ACCESSORY_3.value != '') -->
        <label for="ACCESSORY_QTY_3" style="display: none;">Accessory 3 QTY</label><input id="ACCESSORY_QTY_3" type="text" class="w100" name="ACCESSORY_QTY_3" style="display: none;"><br>
        <!-- Accessory 4 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="ACCESSORY_4">Accessory 4</label><select id="ACCESSORY_4" type="text" class="s100" name="ACCESSORY_4">
        </select><br>
        <!-- Accessory 4 Quantity Field (showing only if #ACCESSORY_4.value != '') -->
        <label for="ACCESSORY_QTY_4" style="display: none;">Accessory 4 QTY</label><input id="ACCESSORY_QTY_4" type="text" class="w100" name="ACCESSORY_QTY_4" style="display: none;"><br>
        <!-- Accessory 5 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="ACCESSORY_5">Accessory 5</label><select id="ACCESSORY_5" type="text" class="s100" name="ACCESSORY_5">
        </select><br>
        <!-- Accessory 5 Quantity Field (showing only if #ACCESSORY_5.value != '') -->
        <label for="ACCESSORY_QTY_5" style="display: none;">Accessory 5 QTY</label><input id="ACCESSORY_QTY_5" type="text" class="w100" name="ACCESSORY_QTY_5" style="display: none;"><br>
        <!-- Accessory 6 Dynamic List, get from HighLoadBlock with ID = ... (constant) (showing only if #.checked == true) -->
        <label for="ACCESSORY_6">Accessory 6</label><select id="ACCESSORY_6" type="text" class="s100" name="ACCESSORY_6">
        </select><br>
        <!-- Accessory 6 Quantity Field (showing only if #ACCESSORY_6.value != '') -->
        <label for="ACCESSORY_QTY_6" style="display: none;">Accessory 6 QTY</label><input id="ACCESSORY_QTY_6" type="text" class="w100" name="ACCESSORY_QTY_6" style="display: none;"><br>
        <!-- Revised Drawings Static List -->
        <label for="REVISED_DRAWINGS">Revised Drawings</label><select id="REVISED_DRAWINGS" type="text" class="s100" name="REVISED_DRAWINGS">
            <option value="" selected></option>
            <option value="Included" >Included</option>
            <option value="Not Included" >Not Included</option>
        </select>
        <!-- Requested Delivery Month Dynamic List, get from HighLoadBlock with ID = REQUESTED_DELIVERY_MONTH_HIGHLOAD (constant) -->
        <label for="REQUESTED_DELIVERY_MONTH">Requested Delivery Month</label><select type="text" class="s100" name="REQUESTED_DELIVERY_MONTH"></select><br>
        <!-- Payment Method Static List -->
        <label for="PAYMENT_METHOD">Payment Method</label><select type="text" class="s100" name="PAYMENT_METHOD">
            <option value="Credit Card">Credit Card&nbsp;&nbsp;</option>
            <option value="Bill Pay / Wire Transfer">Bill Pay / Wire Transfer&nbsp;&nbsp;</option>
            <option value="Pay Pal">Pay Pal&nbsp;&nbsp;</option>
        </select><br>
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

