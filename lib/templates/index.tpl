<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<title>Federatis Market v<?= VERSION; ?></title>
<link href='css/nao.css' rel='stylesheet' type='text/css'>
<link href='css/jquery.autocomplete.css' rel='stylesheet' type='text/css'>
<link href="css/jquery.jeegoocontext.css" rel="Stylesheet" type="text/css">

<!-- <script src="js/prototype.js" type="text/javascript"></script> -->
<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/jquery.livequery.js" type="text/javascript"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.8.custom.min.js"></script>
<script src="js/jquery.autocomplete.js" type="text/javascript"></script>
<script src="js/jquery.simplemodal.js" type="text/javascript"></script>
<script type="text/javascript" src="js/jquery.form.js"></script>
<script type="text/javascript" src="js/jquery.address-1.3.1.min.js?crawlable=true"></script>  
<script type="text/javascript" src="js/jquery.jeegoocontext.min.js"></script>

<script src="js/sha1.js" type="text/javascript"></script>
<script type="text/javascript">
      // jQuery.noConflict(); 
</script>

<script src="js/main.js" type="text/javascript"></script>
<script type="text/javascript">
  jQuery(document).ready(function() {
    jQuery('#searchMarket').focus(function() {
        if( this.value == this.defaultValue ) {
          this.value = '';
        }
      }).blur(function() {
        if( !this.value.length ) {
          this.value = this.defaultValue;
        }
      }); 

    jQuery('#userSearch').focus(function() {
        if( this.value == this.defaultValue ) {
          this.value = '';
        }
      }).blur(function() {
        if( !this.value.length ) {
          this.value = this.defaultValue;
        }
      });
      
    jQuery('#searchMarket').autocomplete(
      "lib/autocomplete.php",
      {
        delay:10,
        minChars:2,
        matchSubset:1,
        matchContains:1,
        cacheLength:50,
        onItemSelect:selectItem,
        onFindValue:findValue,
        formatItem:formatItem,
        autoFill:false,
        mustMatch:false
      }
    );

    var options = {
      dataType:       'json',
      beforeSubmit:   shout_validate,
      success:        shout_success
    };
    jQuery('#daddy-shoutbox-form').ajaxForm(options);
    

                    jQuery('div#all_ActiveOrders li').jeegoocontext('prodmenu', {
                widthOverflowOffset: 0,
                heightOverflowOffset: 3,
                submenuLeftOffset: -4,
                submenuTopOffset: -5,
                livequery: true,
                onShow: function(e, context){
                  $("ul#prodmenu li.disabled").removeClass("disabled");
                  $("ul#prodmenu #take").addClass("disabled");

                  if($(context).hasClass("assignOther")) {
                    $("ul#prodmenu #step li").addClass("disabled");
                    $("ul#prodmenu #release").addClass("disabled");
                    
                    $("ul#prodmenu #cancel").addClass("disabled");
                    $("ul#prodmenu #edit").addClass("disabled");
                  }
                  if($(context).hasClass("unassigned")) {
                    $("ul#prodmenu #step li").addClass("disabled");
                    $("ul#prodmenu #release").addClass("disabled");
                    $("ul#prodmenu #cancel").addClass("disabled");
                    $("ul#prodmenu #edit").addClass("disabled");
                    $("ul#prodmenu #take").removeClass("disabled");
                  }

                },
                onSelect: function(e, context){
                    if($(this).hasClass('disabled')) return false;
                    id = $(context).attr('id').split('_',2)[1];
                    switch($(this).attr('id'))
                    {
                      case 'cancel':
                        cancelOrder(id);
                        break;
                      case 'edit':
                        editOPrice(id);
                        break;
                      case 'take':
                        TakeAssign(id);
                        break; 
                      case 'release':
                        ReleaseAssign(id);
                        break;  
                      case 'step_1':
                        OrderChangeStep(id, 1);
                        break;
                      case 'step_3':
                        OrderChangeStep(id, 3);
                        break;
                      case 'step_5':
                        OrderChangeStep(id, 5);
                        break;
                      case 'step_7':
                        OrderChangeStep(id, 7);
                        break;
                      case 'step_9':
                        OrderChangeStep(id, 9);
                        break;
                      case 'step_10':
                        OrderChangeStep(id, 10);
                        break;
                      default:
                      return false;
                    }
                    return true;
                },
            }); 

    <?= ($this->loggedin) ? " getCharInfo();" : " autoLog();"; ?>
    inIGB = <?= ($this->IGBMode) ? "1" : "0"; ?>;
    init();
  });
</script>
</head>
<body>
<table class='mainScreen' >
  <tr style="height:15px;">
    <td style="vertical-align: top; text-align: left;">
      <div class='itemInfo'>
        <?= $this->usermenu; ?>
      </div>
    </td>
    <td style="text-align:right;">
      <div class='itemInfo'>
        <form id='logform' style="display:none;" action="javascript:Login();">
          <input type='checkbox' id='remember'>remember me  
          <input type='text' id='username' value='' style='width:110px;'> - 
          <input type='password' id='pass' value='' style='width:110px;'> 
          <input type='submit' name='sendlog' value='login'>
        </form>
        <?= $this->loginForm; ?>
        <div id='mainLoader'></div>
      </div>
    </td>
  </tr>
  <tr>
    <td align='center' valign='middle' colspan='2' id='td_container'>
    
<!-- ### START MARKET SCREEN ### -->
  
     <div id="Scr_Market" style="display:none;">
      <table class='mainframe'>
        <tr>
          <td width='300px' valign='top' class='marketmenu'>
          


            <div class="evemenu">
              <?= $this->market; ?>
            </div>
          </td>
          <td class='details' width='800px' id="item_info" valign='top'>
          </td>
        </tr>
      </table>
     </div>
<!-- ###  END MARKET SCREEN  ### -->

<!--  ### START HOME SCREEN ### -->
    
     <div id="Scr_Home" style="">
      <table class='mainframe' cellspacing='0'>
        <tr>
          <td align='center'>
            Federatis Market &amp; Production Manager<br>
            v<?= VERSION; ?>
          </td>
        </tr>
        <tr>
          <td align='center'>
            [<a href='register.php'>registration</a>] Now open to Federatis Alliance !
          </td>
        </tr>
        <tr style="height:8px;" valign='bottom'>
          <td class='headerOrder' align='center'>
            Minerals Quote (in ISK)
          </td>
        </tr>
        <tr>
          <td height='32px' align='center'>
            <div id="pricebar" class='pricebar'></div>
          </td>
        </tr>
      </table>
     </div>



<!--  ###  END HOME SCREEN  ### -->

<!-- ### START Tools SCREEN ### -->
    <div id="Scr_Tools" style="display:none;">
        <table class='mainframe'>
          <tr>
            <td class="headerOrder" >Contract Calculator</td>
          </tr>
          <tr>
            <td id='oreCCalc' class='details' style="width:410px; margin:0; padding:0;" valign='top'></td>
          </tr>
        </table>
      </div>
    
<!-- ### END Tools SCREEN ### -->

<!-- ### START Stats SCREEN ### -->

    <div id="Scr_Stats" style="display:none;">

    </div>
    
<!-- ### END Stats SCREEN ### -->

<!-- ### START ProdLine SCREEN ### -->
    
     <div id="Scr_PL" style="display:none;">
     <div id="pl_selector"></div>
      <table class='mainframe'>
        <tr>
          <td class="headerOrder">Active orders</td>
          <td class="headerOrder" >Completed orders (last 18)</td>
        </tr>
        <tr>
          <td class='details' valign='top' width='540px'>
            <div id="all_ActiveOrders" class="list_orders"></div>
          </td>
          <td class='details' style="width:410px; margin:0; padding:0;" valign='top'>
            <div id="all_CompletedOrders" class="tinylist_orders"  style="height:434px;"></div>
          </td>
        </tr>
      </table>
     </div>



<!-- ###  END ProdLine SCREEN  ### -->

<!-- ### START Freight SCREEN ### -->

         <div id="Scr_Freight" style="display:none;">
      <table class='mainframe'>
        <tr>
          <td class="headerOrder">Minerals (<a href="#" onclick='javascript:tradeEveMailShow();'>o</a>)</td>
    <td class="headerOrder">Components</td>
        </tr>
        <tr>
          <td class='details' valign='top' width='540px'>
            <div id="list_minerals" class="tinylist_orders" style="height:auto;"></div>
          </td>
    <td class='details' valign='top' width='540px' rowspan='5'>
            <div id="list_components" class="tinylist_orders" style="height:auto;"></div>
          </td>
        </tr>
  <tr>
          <td class="headerOrder">Materials</td>
        </tr>
        <tr>
          <td class='details' valign='top' width='540px'>
            <div id="list_materials" class="tinylist_orders" style="height:auto;"></div>
          </td>
        </tr>
        <tr>
          <td class="headerOrder">Goods</td>
        </tr>
        <tr>
          <td class='details' valign='top' width='540px'>
            <div id="list_goods" class="tinylist_orders" style="height:auto;"></div>
          </td>
        </tr>
        <tr>
          <td class="headerOrder">Datacores</td>
    <td class="headerOrder">R.A.M.</td>
        </tr>
        <tr>
          <td class='details' valign='top' width='540px'>
            <div id="list_datacore" class="tinylist_orders" style="height:auto;"></div>
          </td>
          <td class='details' valign='top' width='540px'>
            <div id="list_RAM" class="tinylist_orders" style="height:auto;"></div>
          </td>
        </tr>



      </table>
     </div>

<!-- ###  END Freight SCREEN  ### -->


<!-- ### START MyOrders SCREEN ### -->
     <div id="Scr_MyOrders" style="display:none;">
      <table class='mainframe'>
        <tr>
          <td class="headerOrder">Active orders</td>
          <td class="headerOrder">Order details</td>
          <td class="headerOrder">Account Information</td>
        </tr>
        <tr>
          <td class='details' id="my_orders" valign='top' style="width:320px; height:434px; margin:0; padding:0;">
            <div id="list_myActiveOrders" class="list_Morders"></div>

    </td>
          <td class='details' valign='top' width='400px'>
            <div id="OrdersDetails" class="list_orders"></div>
          </td>
          <td class='details' align='center' valign='top' width='256px' rowspan='3'>
             <div id="myInfo"></div>
          </td>
        </tr>
        <tr>
          <td class="headerOrder" colspan='2'>Completed orders (last 10)</td>

        </tr>
        <tr>
          <td class='details' colspan='2' style="margin:0; padding:0;" valign='top'>
            <div id="list_myCompletedOrders" class="tinylist_orders"></div>
          </td>

        </tr>
      </table>
     </div>



<!-- ###  END MyOrders SCREEN  ### -->

<!-- ### START CharStats SCREEN ### -->
     <div id="Scr_CharStat" style="display:none;">
      <table class='mainframe'>
        <tr>
          <td class="headerOrder">Character Information</td>
          <td class="headerOrder">Current Stats</td>
        </tr>
        <tr>
          <td class='details' align='center' valign='top' width='512px'>
             <div id="CharInfo"></div>
          </td>
          <td class='details' valign='top' width='600px'>
            <div id="CharStats" class="list_orders"></div>
          </td>
        </tr>
      </table>
     </div>



<!-- ###  END CharStats SCREEN  ### -->

<!-- ### START Admin SCREEN ### -->
     <div id="Scr_Admin" style="display:none;">
      <table class='mainframe'>
        <tr>
          <td class="headerOrder">Admin Panel <div class="searchUser"><input id="userSearch" type=text defaultValue='Search ...' value='Search ...' onchange="searchUser(this);" onpaste="this.onchange();" oninput="this.onchange();"/></td>
        </tr>
        <tr>
          <td class='details' valign='top' width='800px'>
            <div id="listUsers" class="tinylist_orders" style="height:434px;"></div>
          </td>
        </tr>
      </table>
     </div>



<!-- ###  END Admin SCREEN  ### -->

    </td>
  </tr>
  <tr style="height:15px;">
    <td><div id='basket' class='itemInfo'></div>
  <div id="daddy-shoutbox">
    <div id="daddy-shoutbox-list"></div>

    <form id="daddy-shoutbox-form" action="lib/daddy-shoutbox.php?action=add" method="post">
    Say: <input type="text" name="message" >
    <input type="submit" value="send" >
    <span id="daddy-shoutbox-response"></span>
    </form>
  </div>
    </td>
    <td align='right'><span class='copyright'>FedMart <b>v.<?= VERSION; ?></b> - <b>Retribution 1.1</b> DB loaded - <?= $this->IGBMode; ?>Astromechanica Federatis &copy; 2013</span>
    </td>
  </tr>
</table>

    <!--  Context menu -->
    <ul id="prodmenu" class="jeegoocontext cm_default">
        <li class="icon" id="step">
            <span class="ui-icon ui-icon-clock"></span>
            Prod Step
            <ul>
                <li id="step_1">Looking for BP</li>
                <li id="step_3">Research in progress</li>
                <li id="step_5">Looking for materials</li>
                <li id="step_7">Production in progress</li>
                <li id="step_9">Delivering</li>
                <li id="step_10">Completed</li>
            </ul>
        </li>
        <li class="icon" id="take">            
            <span class="ui-icon ui-icon-arrowreturnthick-1-s"></span>
            Take Assignment
        </li>
        <li class="icon" id="release">            
            <span class="ui-icon ui-icon-extlink"></span>
            Release Assignment
        </li>
        <li class="icon" id="edit">            
            <span class="ui-icon ui-icon-tag"></span>
            Edit Price
        </li>
        <li class="separator"></li>
        <li class="icon" id="cancel">
            <span class="ui-icon ui-icon-cancel"></span>
            Cancel Order
        </li>
    </ul>    

</body>
