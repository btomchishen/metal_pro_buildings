<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/autoload.php';

CJSCore::Init(array("jquery", "ajax"));
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

global $APPLICATION;
$APPLICATION->SetTitle("Quonset Form");
// Js Files
$APPLICATION->AddHeadScript('/forms/script/Form.js');
$APPLICATION->AddHeadScript('/forms/script/QuonsetForm.js');
$APPLICATION->AddHeadScript('/forms/script/StraightWallForm.js');
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
        <!-- Sales Rep Dynamic List, get employees from Sales Department -->
        <label for="SALES_REP">Sales Rep</label><select type="text" class="s100" name="SALES_REP"></select>
        <!-- Customer Field -->
        <label for="CUSTOMER">Customer Name</label><input type="text" class="w100" name="CUSTOMER"><br>
        <!-- Company Field -->
        <label for="COMPANY">Company</label><input type="text" class="w100" name="COMPANY"><br>
        <!-- Account Number Field -->
        <label for="ACCOUNT_NUMBER">Account No.</label><input type="text" class="w100" name="ACCOUNT_NUMBER"><br>
        <!-- Order Status Field -->
        <label for="ORDER_STATUS">Order Status</label><select type="text" class="s100" name="ORDER_STATUS">
            <option value=""></option>
            <option value="New Order">New Order</option>
            <option value="Drawings Only">Drawings Only</option>
        </select><br>
        <!-- Primary Phone Field -->
        <label for="PRIMARY_PHONE">Primary phone</label><input type="tel" id="primary_phone" class="w100" name="PRIMARY_PHONE"><br>
        <!-- Secondary Phone Field -->
        <label for="SECONDARY_PHONE">Secondary phone</label><input type="tel" id="secondary_phone" class="w100" name="SECONDARY_PHONE"><br>
        <!-- Work Field -->
        <label for="WORK">Work</label><input type="text" class="w100" name="WORK"><br>
        <!-- Email Field -->
        <label for="EMAIL">Email</label><input type="email" class="w100" name="EMAIL"><br>
        <!-- Mailing Address Field -->
        <label for="MAILING_ADDRESS">Mailing Address</label><input type="text" class="w100" name="MAILING_ADDRESS"><br>
        <!-- Site Address Field -->
        <label for="SITE_ADDRESS">Site Address</label><input type="text" class="w100" name="SITE_ADDRESS"><br>
        <!-- Shipping Address Field -->
        <label for="SHIPPING_ADDRESS">Shipping Address</label><input type="text" class="w100" name="SHIPPING_ADDRESS"><br>
        <!-- Building Use Static List -->
        <label for="BUILDING_USE">Building Use</label><select type="text" class="s100" name="BUILDING_USE">
            <option value="NO" selected></option>
            <option value="Storage">Category I - Storage</option>
            <option value="Ordinary">Category II - Medium Occupancy</option>
            <option value="High Human Occupancy">Category III - High Occupancy</option>
        </select><br>
        <!-- Is USA Order checkbox -->
        <label for="IS_USA_ORDER">Is this a USA Order? (Please Select)</label>
        <div class="c100">
            <div>Yes</div>
            <input id="IS_USA_ORDER" type="checkbox" class="" name="IS_USA_ORDER">
        </div>
        <!-- USA Building Drawings Static List (showing only if #IS_USA_ORDER.checked == true) -->
        <label for="USA_BUILDING_DRAWINGS" style="display: none">USA Building Drawings</label><select id="USA_BUILDING_DRAWINGS" type="text" class="s100" name="USA_BUILDING_DRAWINGS" style="display: none">
            <option value="" selected></option>
            <option value="Digital Unstamped Building Drawings (Email only)" >Digital Unstamped Building Drawings (Email Only)</option>
            <option value="Digital Stamped Building Drawings (Email Only)" >Digital Stamped Building Drawings (Email Only)</option>
        </select><br>
        <!-- USA Building Use Static List (showing only if #IS_USA_ORDER.checked == true) -->
        <label for="USA_BUILDING_USE" style="display: none;">USA Building Use (Please specify details in notes)</label><select id="USA_BUILDING_USE" type="text" class="s100" name="USA_BUILDING_USE" style="display: none">
            <option value="" selected></option>
            <option value="USA Building Use - Garage" >Garage</option>
            <option value="USA Building Use - Personal Storage" >Personal Storage</option>
            <option value="USA Building Use - Personal Workshop" >Personal Workshop</option>
            <option value="USA Building Use - Commerical Storage" >Commercial Storage</option>
            <option value="Building Use - House" >House</option>
        </select><br>
        <!-- Series Dynamic List, get from Deal Field with ID = MODEL_TYPE (constant) -->
        <label for="SERIES">Series</label><select id="SERIES" type="text" class="s100" name="SERIES"></select><br>
        <!-- Model Dynamic List, get from HighLoadBlock with ID = MODEL_HIGHLOAD (constant) -->
        <label for="MODEL">Model</label><select id="MODEL" type="text" class="s100" name="MODEL"></select><br>
        <!-- Building Width Field -->
        <label for="BUILDING_WIDTH">Building Width</label><input type="text" class="w100" name="BUILDING_WIDTH"><br>
        <!-- Building Length Field -->
        <label for="BUILDING_LENGTH">Building Length</label><input type="text" class="w100" name="BUILDING_LENGTH"><br>
        <!-- Building Height Field -->
        <label for="BUILDING_HEIGHT">Building Height</label><input type="text" class="w100" name="BUILDING_HEIGHT"><br>
        <!-- Gauge Dynamic List, get from Deal Field with ID = GAUGE (constant) -->
        <label for="GAUGE">Gauge</label><select type="text" class="s100" name="GAUGE"></select><br>
        <!-- Foundation System Dynamic List, get from Deal Field with ID = FOUNDATION_SYSTEM (constant) -->
        <label for="FOUNDATION_SYSTEM">Foundation System</label><select type="text" class="s100" name="FOUNDATION_SYSTEM"></select><br>
        <!-- Exposure Conditions Static List -->
        <label for="EXPOSURE_CONDITIONS">Exposure Conditions</label><select id="EXPOSURE_CONDITIONS" type="text" class="s100" name="EXPOSURE_CONDITIONS">
            <option value="Sheltered">Sheltered</option>
            <option value="Shadow/Drift">Shadow/Drift</option>
        </select><br>
        <!-- Shadow Drift Field (showing only if #EXPOSURE_CONDITIONS.value == 'Shadow/Drift') -->
        <label for="SHADOW_DRIFT" style="display: none">Shadow Drift - Please confirm height of existing structure distance from this structure
        </label><textarea id="SHADOW_DRIFT" type="text" class="w100" name="SHADOW_DRIFT" style="display: none"></textarea><br>
        <!-- Front Wall Frame Static List -->
        <label for="FRONT_WALL_FRAME">Front Wall Frame</label><select id="FRONT_WALL_FRAME" type="text" class="s100" name="FRONT_WALL_FRAME">
            <option value="NO">Please Select</option>
            <option value="OPEN - Includes Large C/A">OPEN - Includes Large C/A (Framing Kit)</option>
            <option value="Framed Opening">Framed Opening</option>
            <option value="SOLID">SOLID</option>
        </select><br>
        <!-- Front Wall Frame 1 Field (showing only if #FRONT_WALL_FRAME.value == 'Framed Opening') -->
        <label for="FRONT_WALL_FRAME_1" style="display: none">Front Frame 1 (W x H)</label><input id="FRONT_WALL_FRAME_1" type="text" class="w100" name="FRONT_WALL_FRAME_1" style="display: none"><br>
        <!-- Front Wall Frame Quantity 1 Field (showing only if #FRONT_WALL_FRAME.value == 'Framed Opening') -->
        <label for="FRONT_WALL_FRAME_QTY1" style="display: none">Front Wall Frame QTY 1</label><input id="FRONT_WALL_FRAME_QTY1" type="text" class="w100" name="FRONT_WALL_FRAME_QTY1" style="display: none"><br>
        <!-- Front Wall Frame 2 Field (showing only if #FRONT_WALL_FRAME.value == 'Framed Opening') -->
        <label for="FRONT_WALL_FRAME_2" style="display: none">Front Frame 2 (W x H)</label><input id="FRONT_WALL_FRAME_2" type="text" class="w100" name="FRONT_WALL_FRAME_2" style="display: none"><br>
        <!-- Front Wall Frame Quantity 2 Field (showing only if #FRONT_WALL_FRAME.value == 'Framed Opening') -->
        <label for="FRONT_WALL_FRAME_QTY2" style="display: none">Front Wall Frame QTY 2</label><input id="FRONT_WALL_FRAME_QTY2" type="text" class="w100" name="FRONT_WALL_FRAME_QTY2" style="display: none"><br>
        <!-- Rear Wall Frame Static List -->
        <label for="REAR_WALL_FRAME">Rear Wall Frame</label><select id="REAR_WALL_FRAME" type="text" class="s100" name="REAR_WALL_FRAME">
            <option value="NO">Please Select</option>
            <option value="OPEN - Includes Large">OPEN - Includes Large C/A (Framing Kit)</option>
            <option value="Framed Opening">Framed Opening</option>
            <option value="SOLID">SOLID</option>
        </select><br>
        <!-- Rear Wall Frame 1 Field (showing only if #REAR_WALL_FRAME.value == 'Framed Opening') -->
        <label for="REAR_WALL_FRAME_1" style="display: none">Rear Frame 1 (W x H)</label><input id="REAR_WALL_FRAME_1" type="text" class="w100" name="REAR_WALL_FRAME_1" style="display: none"><br>
        <!-- Rear Wall Frame Quantity 1 Field (showing only if #REAR_WALL_FRAME.value == 'Framed Opening') -->
        <label for="REAR_WALL_FRAME_QTY1" style="display: none">Rear Wall Frame QTY 1</label><input id="REAR_WALL_FRAME_QTY1" type="text" class="w100" name="REAR_WALL_FRAME_QTY1" style="display: none"><br>
        <!-- Rear Wall Frame 2 Field (showing only if #REAR_WALL_FRAME.value == 'Framed Opening') -->
        <label for="REAR_WALL_FRAME_2" style="display: none">Rear Frame 2 (W x H)</label><input id="REAR_WALL_FRAME_2" type="text" class="w100" name="REAR_WALL_FRAME_2" style="display: none"><br>
        <!-- Rear Wall Frame Quantity 2 Field (showing only if #REAR_WALL_FRAME.value == 'Framed Opening') -->
        <label for="REAR_WALL_FRAME_QTY2" style="display: none">Rear Wall Frame QTY 2</label><input id="REAR_WALL_FRAME_QTY2" type="text" class="w100" name="REAR_WALL_FRAME_QTY2" style="display: none"><br>
        <!-- Is Sea Container Building checkbox -->
        <label for="IS_SEA_CONTAINER_BUILDING" style="display: none">Is this a Sea Container Building?</label>
        <div class="c100">
            <div id="IS_SEA_CONTAINER_BUILDING_text" style="display: none">Yes</div>
            <input id="IS_SEA_CONTAINER_BUILDING" type="checkbox" class="" name="IS_SEA_CONTAINER_BUILDING" style="display: none" ><br>
        </div>
        <!-- Sea Container Style Static List (showing only if #IS_SEA_CONTAINER_BUILDING.checked == true) -->
        <label for="SEA_CONTAINER_STYLE" style="display: none;">Sea Container Style</label><select id="SEA_CONTAINER_STYLE" type="text" class="s100" name="SEA_CONTAINER_STYLE" style="display: none">
            <option value="" selected></option>
            <option value="Inner to Inner Sea Container" >Inner to Inner</option>
            <option value="Outer to Outer Sea Container" >Outer to Outer</option>
            <option value="Refer Notes" >Refer Notes</option>
        </select><br>
        <!-- Sea Container Design Static List (showing only if #IS_SEA_CONTAINER_BUILDING.checked == true) -->
        <label for="SEA_CONTAINER_DESIGN" style="display: none;">Sea Container Design</label><select id="SEA_CONTAINER_DESIGN" type="text" class="s100" name="SEA_CONTAINER_DESIGN" style="display: none">
            <option value="" selected></option>
            <option value="Container Design Included" >Container Design Included</option>
            <option value="Foundation Design by Others (Reaction Drawing Included)" >Foundation Design by Others (Reaction Drawing Included)</option>
            <option value="Foundation Design by Others" >Foundation Design by Others</option>
        </select><br>
        <!-- Front Wall Extension Static List (showing only if #IS_SEA_CONTAINER_BUILDING.checked == true) -->
        <label for="FRONT_WALL_EXTENSION" style="display: none;">Front Wall Extension</label><select id="FRONT_WALL_EXTENSION" type="text" class="s100" name="FRONT_WALL_EXTENSION" style="display: none">
            <option value="" selected></option>
            <option value="Yes" >Yes</option>
            <option value="No" >No</option>
        </select><br>
        <!-- Front Wall Sea Container Height Field (showing only if #IS_SEA_CONTAINER_BUILDING.checked == true) -->
        <label for="FRONT_WALL_SEA_CONTAINER_HEIGHT" style="display: none">Front Wall Sea Container Height (in FT & IN)</label><input id="FRONT_WALL_SEA_CONTAINER_HEIGHT" style="display: none" type="text" class="w100" name="FRONT_WALL_SEA_CONTAINER_HEIGHT"><br>
        <!-- Rear Wall Extension Static List (showing only if #IS_SEA_CONTAINER_BUILDING.checked == true) -->
        <label for="REAR_WALL_EXTENSION" style="display: none;">Rear Wall Extension</label><select id="REAR_WALL_EXTENSION" type="text" class="s100" name="REAR_WALL_EXTENSION" style="display: none">
            <option value="" selected></option>
            <option value="Yes" >Yes</option>
            <option value="No" >No</option>
        </select><br>
        <!-- Rear Wall Sea Container Height Field (showing only if #IS_SEA_CONTAINER_BUILDING.checked == true) -->
        <label for="REAR_WALL_SEA_CONTAINER_HEIGHT" style="display: none">Rear Wall Sea Container Height (in FT & IN)</label><input id="REAR_WALL_SEA_CONTAINER_HEIGHT" style="display: none" type="text" class="w100" name="REAR_WALL_SEA_CONTAINER_HEIGHT"><br>
        <!-- Is Accessories checkbox -->
        <label for="IS_ACCESSORIES">Order includes accessories?</label>
        <div class="c100">
            <div>Yes</div>
            <input id="IS_ACCESSORIES1" type="checkbox" class="" name="IS_ACCESSORIES"><br>
        </div>
        <!-- Accessory 1 Dynamic List, get from HighLoadBlock with ID = FORM_ACCESSORY_HIGHLOAD (constant) (showing only if #IS_ACCESSORY.checked == true) -->
        <label for="ACCESSORY_1" style="display: none;">Accessory 1</label><select id="ACCESSORY_1" type="text" class="s100" name="ACCESSORY_1" style="display: none">
        </select><br>
        <!-- Accessory Quantity 1 Field (showing only if #IS_ACCESSORY.checked == true) -->
        <label for="ACCESSORY_QTY_1" style="display: none;">Accessory QTY 1</label><input id="ACCESSORY_QTY_1" type="text" class="w100" name="ACCESSORY_QTY_1" style="display: none;"><br>
        <!-- Accessory 2 Dynamic List, get from HighLoadBlock with ID = FORM_ACCESSORY_HIGHLOAD (constant) (showing only if #IS_ACCESSORY.checked == true) -->
        <label for="ACCESSORY_2" style="display: none;">Accessory 2</label><select id="ACCESSORY_2" type="text" class="s100" name="ACCESSORY_2" style="display: none">
        </select><br>
        <!-- Accessory Quantity 2 Field (showing only if #IS_ACCESSORY.checked == true) -->
        <label for="ACCESSORY_QTY_2" style="display: none;">Accessory QTY 2</label><input id="ACCESSORY_QTY_2" type="text" class="w100" name="ACCESSORY_QTY_2" style="display: none;"><br>
        <!-- Accessory 3 Dynamic List, get from HighLoadBlock with ID = FORM_ACCESSORY_HIGHLOAD (constant) (showing only if #IS_ACCESSORY.checked == true) -->
        <label for="ACCESSORY_3" style="display: none;">Accessory 3</label><select id="ACCESSORY_3" type="text" class="s100" name="ACCESSORY_3" style="display: none">
        </select><br>
        <!-- Accessory Quantity 3 Field (showing only if #IS_ACCESSORY.checked == true) -->
        <label for="ACCESSORY_QTY_3" style="display: none;">Accessory QTY 3</label><input id="ACCESSORY_QTY_3" type="text" class="w100" name="ACCESSORY_QTY_3" style="display: none;"><br>
        <!-- Accessory 4 Dynamic List, get from HighLoadBlock with ID = FORM_ACCESSORY_HIGHLOAD (constant) (showing only if #IS_ACCESSORY.checked == true) -->
        <label for="ACCESSORY_4" style="display: none;">Accessory 4</label><select id="ACCESSORY_4" type="text" class="s100" name="ACCESSORY_4" style="display: none">
        </select><br>
        <!-- Accessory Quantity 4 Field (showing only if #IS_ACCESSORY.checked == true) -->
        <label for="ACCESSORY_QTY_4" style="display: none;">Accessory QTY 4</label><input id="ACCESSORY_QTY_4" type="text" class="w100" name="ACCESSORY_QTY_4" style="display: none;"><br>
        <!-- Accessory 5 Dynamic List, get from HighLoadBlock with ID = FORM_ACCESSORY_HIGHLOAD (constant) (showing only if #IS_ACCESSORY.checked == true) -->
        <label for="ACCESSORY_5" style="display: none;">Accessory 5</label><select id="ACCESSORY_5" type="text" class="s100" name="ACCESSORY_5" style="display: none">
        </select><br>
        <!-- Accessory Quantity 5 Field (showing only if #IS_ACCESSORY.checked == true) -->
        <label for="ACCESSORY_QTY_5" style="display: none;">Accessory QTY 5</label><input id="ACCESSORY_QTY_5" type="text" class="w100" name="ACCESSORY_QTY_5" style="display: none;"><br>
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
            <input type="checkbox" class="" name="IS_PICK_UP"><br>
        </div>
        <!-- Building Price Field, get from Deal Field with ID = BUILDING_PRICE (constant) -->
        <label for="BUILDING_PRICE">Building Price</label><input id="BUILDING_PRICE" type="text" class="w100 money-input" name="BUILDING_PRICE" placeholder="$0.00"><br>
        <!-- Tax Rate Dynamic List, get from HighLoadBlock with ID = TAX_RATE_HIGHLOAD (constant) -->
        <label for="TAX_RATE">Tax Rate</label><select id="TAX_RATE" type="text" class="s100" name="TAX_RATE"></select><br>
        <!-- Tax Dynamic Field. Tax = BuildingPrice * (TaxRate / 100) -->
        <label for="TAX">Tax</label><input type="text" class="w100 money-input" name="TAX" placeholder="$0.00"><br>
        <!-- Sub Total Dynamic Field. SubTotal = BuildingPrice + Tax -->
        <label for="SUB_TOTAL">Sub Total</label><input id="SUB_TOTAL" type="text" class="w100 money-input" name="SUB_TOTAL" placeholder="$0.00"><br>
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
        <!-- Notes Field -->
        <label for="NOTES">Notes</label><textarea type="text" class="w100" name="NOTES"></textarea><br>
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
        <!-- Notes to Office Field -->
        <label for="NOTES_TO_OFFICE">Notes to back office - List any relevant info here!</label><textarea type="text" class="w100" name="NOTES_TO_OFFICE"></textarea><br>
        <!-- Submit Button. Save form to HighLoadBlock with ID = FORMS_HIGHLOAD, create PDF file and then send it to Sales Rep -->
        <input type="button" id="btn" class="submitFF" name="submit" value="Update">
        <!-- Save as New. Save form to HighLoadBlock with ID = FORMS_HIGHLOAD, create PDF file and then send it to Sales Rep -->
        <input type="button" id="btn1" class="submitFF" name="submit1" value="Save New">
        <!-- Delete button -->
        <input type="button" id="delete_btn" class="deleteFF" name="delete" value="Delete">
        <!-- Clear Fields Button -->
        <input type="reset" id="res" class="resetFF" name="reset" value="Reset">
    </form>
</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>

