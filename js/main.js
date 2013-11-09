// $Id: main.js 22 2012-09-26 13:27:40Z nao $

oldtitle = "";
loader_img = "<img src=\"img/ajax.gif\">";
IMG_DIR = "img/Odyssey";
var name;
var charID;
var role;

        var count = 0;
        var files = 'lib/';
        var lastTime = 0;
var PLfilter = 'ALL';
var EveMail;
var totalTrade;
var dest;
var lastdest;
var curtitle;
var inIGB;
var debug;

function init() {
  curtitle = document.title;
  refreshBarPrice();
  jQuery.address.init(function() {

  // Initializes the plugin
  jQuery('a[href^=#!]').address();
  }).change(function(event) {
  //alert("event: "+event.value+" param: "+event.pathNames[1]);
      lastdest = dest;
      dest = (event.value == '/') ? 'void' :
      event.pathNames[0].substr(0, 1).toUpperCase()+ event.pathNames[0].substr(1);
    //if(dest != 'void') go(dest);
    switch(dest) {
      case 'void':
        document.title = curtitle;
      break;

      case "Market":
        go(dest);
        document.title = curtitle + ' - ' + dest;
        if(event.pathNames[1]!=null);
      break;

      case "PL":
        go(dest);
        //jQuery("div#pl_selector div.selector").removeClass("selected");
        //loadPLselector();
        
        if(event.pathNames[1]!=null) {
          PLfilter = event.pathNames[1];
          loadOrder(PLfilter);
          loadPLselector(PLfilter);

          //jQuery("div#pl_selector div#"+event.pathNames[1]+" div.selector").addClass("selected");
        }
        else {
          PFfilter = 'ALL';
          loadAllOrder();
          loadPLselector();
          //jQuery("div#pl_selector div#ALL div.selector").addClass("selected");
        }
        document.title = curtitle + ' - ' + dest + ' - ' + PLfilter;
      break;

      case "Charstat":
        if(lastdest!=dest) go(dest);
        if(event.pathNames[1]!=null) {
          charFilter = event.pathNames[1];
          loadCharInfo(charFilter);
          //loadCharStats(charFilter);

        }
        document.title = curtitle + ' - Statistiques : ' + charFilter;
      break;

      default:
        document.title = curtitle + ' - ' + dest;

        go(dest);
      break;

    }
  });
}

function devGroup( id ) {
	if(!jQuery('div#div_'+id).length) {
	//oldtitle = jQuery("#"+id).html();
	jQuery("#market_"+id).append("<div id=\"div_"+id+"\">"+loader_img+"</div>");
	jQuery.post("lib/db.php",{parentID: id, type: 'category'}, function(data){
  	//alert("data : " + data);
  	//data = "<div id=\"div_"+id+"\">"+data+"</div>";
  	jQuery("#div_"+id).html(data);
  	jQuery('li#market_'+id+' img.menuItemMoreImage:first').attr("src",  IMG_DIR+"/Icons/items/38_16_202.png");
	});
	}
	else {
	if(jQuery("#div_"+id).css('display') != 'block'){
    jQuery("#div_"+id).css('display', 'block');
	  jQuery('li#market_'+id+' img.menuItemMoreImage:first').attr("src", IMG_DIR+"/Icons/items/38_16_202.png");
    }
	else {
    jQuery("#div_"+id).css('display', 'none');
	  jQuery('li#market_'+id+' img.menuItemMoreImage:first').attr("src", IMG_DIR+"/Icons/items/38_16_218.png");
    }
	}
}

function showInfo( id , name) {
jQuery('#mainLoader').html(loader_img) ;
  if(name){
    jQuery.post("lib/db.php",{name: id, type: 'item'}, function(data){
      jQuery("#item_info").html(data);
      jQuery('#mainLoader').html("");
    });
  }
  else {
    jQuery.post("lib/db.php",{itemID: id, type: 'item'}, function(data){
      jQuery("#item_info").html(data);
      jQuery('#mainLoader').html("");
    });
  }
   
}

function showLogin() {
    jQuery("div#login").empty();
    jQuery("form#logform").fadeIn();
}

function Login () {
    remember = $("#remember:checkbox").attr("checked");
    username = $('#username').val();
    password = SHA1($('#pass').val());
    //alert(password);
    if(username){
      $('#mainLoader').html(loader_img);
      jQuery.post("lib/db.php",{login: username, pass: password, remember: remember}, function(data){
	  if(data=="KO"){
	  $('#mainLoader').html("<b>login denied!</b>");
	  } else {
          name = jQuery(data).find('name').text();
          charID = jQuery(data).find('id').text();
          role = jQuery(data).find('role').text();
          corp = jQuery(data).find('corpName').text();
          portrait = jQuery(data).find('portrait').text();
          
        //alert(name + " - " + charID);
        jQuery("form#logform").fadeOut('slow',function(){
        $('#login').html("<div id='login'>Logged as "+name+" (<a href=\"#\" onclick=\"javascript:Logout();\">logout</a>)</div>");
        });

        buffer = "<a href=\"#!/home\">Home</a> - <a href=\"#!/market\">Market</a> - <a href=\"#!/tools\">Tools</a>";
        //$('userMenu').innerHTML = "<a href=\"#!/homepoutet\">Home</a> - <a href=\"#!/market\">Market</a> - <a href=\"#!/tools\">Tools</a>";
	if(role>=2)buffer += " - <a href=\"#!/PL\">Prod Line</a>";
	buffer += " - <a href=\"#!/freight\">Freight</a> - <a href=\"#!/myOrders\">My Orders</a> - <a href=\"#!/stats\">Stats</a>";
  if(role>=4)buffer += " - <a href=\"#!/Admin\">Admin</a>";
  buffer += " - <a id=\"chatToggle\" href=\"#\" onclick=\"javascript:toggleChat();\"><span class=\"ui-icon ui-icon-comment\"></span></a>";
	$('#userMenu').html(buffer);
        //$('myInfo').innerHTML = "<img src=\""+portrait+"\" width='256px'/><br />"+name+"<br />"+corp+"<br />";
        getCharInfo();
        $('#mainLoader').html("");
	}
      });

    }
    

}

function autoLog () {
  $('#mainLoader').html(loader_img);
  jQuery.post("lib/db.php",{login: '1', auto: '1'}, function(data){
    if(jQuery(data).length > 0) {
      name = jQuery(data).find('name').text();
      charID = jQuery(data).find('id').text();
      role = jQuery(data).find('role').text();
      corp = jQuery(data).find('corpName').text();
      portrait = jQuery(data).find('portrait').text();
      
      //alert(name + " - " + charID);
      jQuery("form#logform").fadeOut('slow',function(){
      $('#login').html("<div id='login'>Logged as "+name+" (<a href=\"#\" onclick=\"javascript:Logout();\">logout</a>)</div>");
      });
      
      buffer = "<a href=\"#!/home\">Home</a> - <a href=\"#!/market\">Market</a> - <a href=\"#!/tools\">Tools</a>";
      if(role>=2)buffer += " - <a href=\"#!/PL\">Prod Line</a>";
      buffer += " - <a href=\"#!/freight\">Freight</a> - <a href=\"#!/myOrders\">My Orders</a> - <a href=\"#!/stats\">Stats</a> - <a id=\"chatToggle\" href=\"#\" onclick=\"javascript:toggleChat();\"><span class=\"ui-icon ui-icon-comment\"></span></a>";
      
      $('#userMenu').html(buffer);
      //$('myInfo').innerHTML = "<img src=\""+portrait+"\" width='256px'/><br />"+name+"<br />"+corp+"<br />";
      getCharInfo();

    }
    $('#mainLoader').html("");
  });

}

function loadCharInfo(charName) {
  if (jQuery('td#td_container > div:visible').size()) {
    jQuery('td#td_container > div:visible').fadeOut(400, function() {



    jQuery('#CharInfo').empty();
   jQuery.post("lib/db.php",{getCharInfo: charName}, function(data){
     if(jQuery(data).find('name').size() > 0){
     name = jQuery(data).find('name').text();
     charID = jQuery(data).find('id').text();
     role = jQuery(data).find('role').text();
     roleName = jQuery(data).find('roleName').text();
     corp = jQuery(data).find('corpName').text();
     ticker = jQuery(data).find('corpTick').text();
     portrait = jQuery(data).find('portrait').text();


   //  $('login').innerHTML = "<div id='login'>Logged as "+name+" (<a onclick=\"javascript:Logout();\">logout</a>)</div>";
   //  $('userMenu').innerHTML = "<a onclick=\"javascript:go('Home');\">Home</a> - <a onclick=\"javascript:go('Market');\">Market</a> - <a onclick=\"java
     $('#CharInfo').html("<img src=\""+portrait+"\" width='512px'/><br /> \
     ["+ticker+"] "+name+"<br />"+roleName);

     loadCharStat(charID);
     jQuery('#Scr_CharStat').fadeIn();
    } 
    });
  });

}
}

function loadCharStat(charID) {
  jQuery.post("lib/db.php",{getCharStat: charID}, function(data){
    jQuery('#CharStats').html(data);
  });
}

function getCharInfo () {
 //$('#mainLoader').html(loader_img);
 jQuery.post("lib/db.php",{getMyInfo: true}, function(data){
   name = jQuery(data).find('name').text();
   charID = jQuery(data).find('id').text();
   role = jQuery(data).find('role').text();
   roleName = jQuery(data).find('roleName').text();
   corp = jQuery(data).find('corpName').text();
   ticker = jQuery(data).find('corpTick').text();
   portrait = jQuery(data).find('portrait').text();
   totalSpent = jQuery(data).find('totalSpent').text();
   totalOrder = jQuery(data).find('totalOrder').text();
   prdrank = jQuery(data).find('prdrank').text();
   buyrank = jQuery(data).find('buyrank').text();

 //  $('login').innerHTML = "<div id='login'>Logged as "+name+" (<a onclick=\"javascript:Logout();\">logout</a>)</div>";
 //  $('userMenu').innerHTML = "<a onclick=\"javascript:go('Home');\">Home</a> - <a onclick=\"javascript:go('Market');\">Market</a> - <a onclick=\"java
   $('#myInfo').html("<img src=\""+portrait+"\" width='250px'/><br /> \
   ["+ticker+"] "+name+"<br /> \
   "+corp+"<br />"+roleName+"<br /> \
   <br /><br /><br /> \
   Total ISK spent : <b>"+totalSpent+"</b><br /> \
   in <b>"+totalOrder+"</b> orders<br /> \
   Actual Rank level : <b>"+buyrank+"</b><br />");
   
   if(role >= 2){

     totalWin = jQuery(data).find('totalWin').text();
     totalCOrder = jQuery(data).find('totalCOrder').text();
     $('#myInfo').append("<br /> \
     Total ISK earn : <b>"+totalWin+"</b><br /> \
     by completing <b>"+totalCOrder+"</b> orders<br /> \
     Actual Rank level : <b>"+prdrank+"</b><br />");
     
   }
   basketRecap();
   timeoutID = setTimeout(refresh, 1000);
//   jQuery('#daddy-shoutbox').fadeIn();
 });
// $('#mainLoader').html("");

}

function Logout () {
    $('#mainLoader').html(loader_img);
      jQuery.post("lib/db.php",{logout : true}, function(data){
          $('#login').html("<a href=\"#\" onclick=\"javascript:showLogin();\">Login</a>");
	  $('#userMenu').html("<a href=\"#!/home\">Home</a> - <a href=\"#!/market\">Market</a> - <a href=\"#!/tools\">Tools</a> - <a href=\"#!/stats\">Stats</a>");
          $('#myInfo').html("");
          
        //alert(name + " - " + charID);
//        <div id='login'>Logged as $user</div>
    $('#mainLoader').html("");
      });

    

}

function calcPrice ( cost ) {
    qty = $('#quantity').val();
    $('#totalPrice').html(addSeparatorsNF((cost * qty).toFixed(2), ".", ".", ",")+ " ISK" );
}
/*
function MakeOrder( itemID , cost) {
    $('#mainLoader').html(loader_img);
    quantity = $('#quantity').val();
    itemName = $('#itemName').html();
    forCorp = Number($('#corp').attr("checked"));
    validated = true;
    
    if(!inIGB) validated = confirm("Order " + quantity + "x " + itemName +" ?");
    
    if(validated){
    jQuery.post("lib/db.php",{order: itemID, qty: quantity, cost: cost, forCorp: forCorp}, function(data){

      if(!inIGB)alert(data);
      else $('#mainLoader').html("Order Complete! (corp=" + forCorp +")");
    });
    }
    
    $('#mainLoader').html("");
}
*/
function basketAdd( itemID , cost) {
    $('#mainLoader').html(loader_img);
    quantity = $('#quantity').val();
    itemName = $('#itemName').html();
    forCorp = Number($('#corp').attr("checked"));
    corpOnly = Number($('#corpOnly').attr("checked"));
    validated = true;
    
    if(!inIGB) validated = confirm("Order " + quantity + "x " + itemName +" ?");
    
    if(validated){
    jQuery.post("lib/db.php",
     {
       basket: "add",
       itemID: itemID,
       itemName: itemName,
       qty: quantity,
       cost: cost,
       forCorp: forCorp,
       corpOnly: corpOnly
     }, function(data){


//      if(!inIGB)alert(data);
 //     else $('mainLoader').innerHTML = "Order Complete! (corp=" + forCorp +")";
       basketRecap();
    });
    }
    
    $('#mainLoader').html("");
}

function basketRemove( itemID ) {
    $('#mainLoader').html(loader_img);
    //quantity = $('quantity').value;
   // itemName = $('itemName').innerHTML;
    //forCorp = Number($('corp').checked);
    validated = true;
    
   // if(!inIGB) validated = confirm("Order " + quantity + "x " + itemName +" ?");
    
    if(validated){
    jQuery.post("lib/db.php",{basket: "remove", itemID: itemID}, function(data){

//      if(!inIGB)alert(data);
 //     else $('mainLoader').innerHTML = "Order Complete! (corp=" + forCorp +")";
       jQuery("tr#item_"+itemID).fadeOut(500, function (){jQuery(this).remove();if(!jQuery("tr[id*=item]").length)jQuery.modal.close();});
       
       basketRecap();

       
    });
    }
    
    $('#mainLoader').html("");
}

function basketShow () {
    $('#mainLoader').html(loader_img);
    jQuery.post("lib/db.php",{basket: "list"}, function(data){
      jQuery.modal(data,{
        overlayClose:true,
        onOpen: function (dialog) {
          dialog.overlay.fadeIn('slow', function () {
            dialog.data.hide();
		        dialog.container.fadeIn('slow', function () {
              dialog.data.slideDown('slow');
            });
          });
        },
        onClose: function (dialog) {
          dialog.data.fadeOut('slow', function () {
            dialog.container.slideUp('slow', function () {
              dialog.overlay.fadeOut('slow', function () {
                jQuery.modal.close(); // must call this!
              });
            });
          });
        }
      });
    });
    $('#mainLoader').html("");
}

function basketRecap() {
    $('#mainLoader').html(loader_img);
    jQuery.post("lib/db.php",{basket: "mini"}, function(data){
     
      $('#basket').html(data);
      if(jQuery("td#basketTotal").length)
        jQuery("td#basketTotal").html(jQuery("b#miniTotal").html());
    });
    $('#mainLoader').html("");
}

function basketClear() {
    $('#mainLoader').html(loader_img);
    jQuery.post("lib/db.php",{basket: "clear"}, function(data){
       jQuery.modal.close();
       basketRecap();
    });
    $('#mainLoader').html("");
}

function basketCheckout() {
    $('#mainLoader').html(loader_img);
    jQuery.post("lib/db.php",{basket: "checkout"}, function(data){
       jQuery.modal.close();
       basketRecap();
       alert(data);
    });
    $('#mainLoader').html("");
}

function cancelOrder ( orderID ) {
    $('#mainLoader').html(loader_img);
    //alert ("cancel order n°"+orderID);
    validated = true;
    
    if(!inIGB) validated = confirm("cancel item n°"+orderID+" ?");
    
    if(validated){
    jQuery.post("lib/db.php",{delOrder: orderID}, function(data){
      //alert (data);
      if(data=='OK')$("#order_"+orderID).remove();
      else if(!inIGB) alert("Annulation Failed!");
        else $('#mainLoader').html("Annulation Failed!");
    });    

    }
    $('#mainLoader').html("");
}

function cancelMOrder ( MorderID ) {
    $('#mainLoader').html(loader_img);
    //alert ("cancel order n°"+orderID);
    validated = true;
    
    if(!inIGB) validated = confirm("cancel order n°"+MorderID+" ?");
    
    if(validated){
    jQuery.post("lib/db.php",{delMOrder: MorderID}, function(data){
      //alert (data);
      if(data=='OK'){
        $("#order_"+MorderID).remove();
        $('#OrdersDetails').html("");
      }
      else if(!inIGB) alert("Annulation Failed!\r\n"+data);
        else $('#mainLoader').html("Annulation Failed!");
    });    

    }
    $('#mainLoader').html("");
}

function loadMyOrder() {
    $('#mainLoader').html(loader_img);
    $('#OrdersDetails').html("");
    olddata = $('#list_myActiveOrders').html();
      jQuery.post("lib/db.php",{listorder : 'MyActive'}, function(data){
       // alert(data + " -> " + olddata );
        $('#list_myActiveOrders').html(data);
      });
      jQuery.post("lib/db.php",{listorder : 'MyCompleted'}, function(data){
       // alert(data + " -> " + olddata );
        $('#list_myCompletedOrders').html(data);
            $('#mainLoader').html("");
      });
      

}


function OrderDetails( MorderID ) {
    $('#mainLoader').html(loader_img);

      jQuery.post("lib/db.php",{listorder : 'Details', MorderID: MorderID}, function(data){
       // alert(data + " -> " + olddata );
        $('#OrdersDetails').html(data);
            $('#mainLoader').html("");
      });


}


function loadAllOrder() {
    $('#mainLoader').html(loader_img);
      jQuery.post("lib/db.php",{listorder : 'ALLactive'}, function(data){
        //alert(data);
        $('#all_ActiveOrders').html(data);
      });
      jQuery.post("lib/db.php",{listorder : 'ALLcompleted'}, function(data){
        //alert(data);
        $('#all_CompletedOrders').html(data);
   
        $('#mainLoader').html("");
      });
      

}

function loadOrder( filter ) {
    $('#mainLoader').html(loader_img);
      jQuery.post("lib/db.php",{listorder : 'ALLactive', filter : filter}, function(data){
        //alert(data);
        $('#all_ActiveOrders').html(data);
	jQuery("div#pl_selector div#"+filter+" div.selector").addClass("selected");
      });
      jQuery.post("lib/db.php",{listorder : 'ALLcompleted', filter : filter}, function(data){
        //alert(data);
        $('#all_CompletedOrders').html(data);
            $('#mainLoader').html("");
      });


}

function loadPLselector( selected ) {
    if(!selected) selected = 'ALL';
    $('#mainLoader').html(loader_img);
    jQuery.post("lib/db.php",{plSelector : 'ALL'}, function(data){
       //alert(data);
       $('#pl_selector').html(data);
       jQuery("div#pl_selector div#"+selected+" div.selector").addClass("selected");
    });

}

function TakeAssign ( orderID ) {
    $('#mainLoader').html(loader_img);
    jQuery.post("lib/db.php",{assignOrder : orderID}, function(data){
       // alert(data + " -> " + olddata );
//        $('list_myActiveOrders').innerHTML = data;
      if(data=='OK')loadOrder(PLfilter);
      //alert(data);
      });
 
    
    
    $('#mainLoader').html("");
}

function ReleaseAssign ( orderID ) {
    $('#mainLoader').html(loader_img);
    jQuery.post("lib/db.php",{unassignOrder : orderID}, function(data){
       // alert(data + " -> " + olddata );
//        $('list_myActiveOrders').innerHTML = data;
      if(data=='OK')loadOrder(PLfilter);
      //alert(data);
      });
 
    
    
    $('#mainLoader').html("");
}

function OrderChangeStep ( orderID, nextstep ) {
    $('#mainLoader').html(loader_img);
    completed = true;
    if(nextstep == 10 && !inIGB) completed = confirm("Do you want to complete this order ?");
    
    if(completed){
        jQuery.post("lib/db.php",{changeStep : orderID, step : nextstep}, function(data){
          if(data=='OK')loadOrder(PLfilter);
          });
    }
    
    $('#mainLoader').html("");
}

function editOPrice (orderID) {
  $('#mainLoader').html(loader_img);
  exstring = jQuery("#order_"+orderID+" #price").html();
  //alert(exstring);
  //oldprice = exstring.substring(0,(exstring.length - 4)).replace(RegExp("(,)", "g"),"");
  exstring = exstring.match(/^(.*) ISK/gi)[0];
  oldprice = exstring.substring(0,(exstring.length - 4)).replace(RegExp("(,)", "g"),"");
  //oldprice = 10;
  newprice = prompt("Enter new price",oldprice);
  if(newprice && !isNaN(newprice)){
  	//alert("good: "+newprice);
  	jQuery.post("lib/db.php",{editPrice : orderID, price : newprice}, function(data){
  	  if(data=='OK')loadOrder(PLfilter);
  	});
  
  }
  $('#mainLoader').html("");
}


function chRights (input) {
  form = $(input).parent();
  id = parseInt($(input).parent().parent().attr('id'));
  if(id > 0){
    $('#mainLoader').html(loader_img);
    allbox = form.find('input');
    var data = { };
    $(allbox).each(function() {
      data[$(this).attr('name')] = $(this).is(':checked');
    });
    data['admin'] = 'chRights';
    data['charID'] = id;
    jQuery.post("lib/db.php", data, function(data){
        $('#mainLoader').html(data);
    });

  }
}


function refreshBarPrice () {
  $('#mainLoader').html(loader_img);
  jQuery.post("lib/db.php",{barPrice : 'ALL'}, function(data){
       // alert(data + " -> " + olddata );
      $('#pricebar').html(data);
      //alert(data);
        $('#mainLoader').html("");
      });

}

function loadOreCCalc () {
  $('#mainLoader').html(loader_img);
  jQuery.post("lib/db.php",{oreCCalc : 'ALL'}, function(data){
       // alert(data + " -> " + olddata );
      $('#oreCCalc').html(data);
      //alert(data);
        $('#mainLoader').html("");
      });

}

function loadStats () {
  $('#mainLoader').html(loader_img);
  jQuery.post("lib/db.php",{stats : 'ALL'}, function(data){
       // alert(data + " -> " + olddata );
      $('#Scr_Stats').html(data);
      //alert(data);
       $('#mainLoader').html("");
      });
 
}

function loadAdmin () {
  $('#mainLoader').html(loader_img);
  jQuery.post("lib/db.php",{admin : 'listUsers'}, function(data){
      $('#listUsers').html(data);
        $('#mainLoader').html("");
  });

}

function searchUser(name) {
  if(name.value.length>=3 || name.value.length == 0){
    $('#mainLoader').html(loader_img);
    jQuery.post("lib/db.php",{admin : 'listUsers', search: name.value}, function(data){
        $('#listUsers').html(data);
          $('#mainLoader').html("");
    });
  }
}


function loadFreight () {
  totalTrade = 0;
  EveMail = "";
  $('#list_minerals').html(loader_img);
  jQuery.post("lib/db.php",{trade : 'list_minerals'}, function(data){
      // alert(data + " -> " + olddata );
      list = jQuery(data).find('data').text();
      EveMail += jQuery(data).find('mail').text();
      //totalTrade += (parseInt(jQuery(data).find('price').text()));
      $('#list_minerals').html(list);
     //alert(data);
  });
  $('#list_materials').html(loader_img);
  jQuery.post("lib/db.php",{trade : 'list_materials'}, function(data){
      // alert(data + " -> " + olddata );
      list = jQuery(data).find('data').text();
      EveMail += jQuery(data).find('mail').text();
      totalTrade +=  (parseInt(jQuery(data).find('price').text()));
      $('#list_materials').html(list);
     //alert(data);
  });
  $('#list_goods').html(loader_img);
  jQuery.post("lib/db.php",{trade : 'list_goods'}, function(data){
      // alert(data + " -> " + olddata );
      list = jQuery(data).find('data').text();
      EveMail += jQuery(data).find('mail').text();
      totalTrade +=  (parseInt(jQuery(data).find('price').text()));
      $('#list_goods').html(list);
     //alert(data);
  });
  $('#list_datacore').html(loader_img);
  jQuery.post("lib/db.php",{trade : 'list_datacore'}, function(data){
      // alert(data + " -> " + olddata );
      list = jQuery(data).find('data').text();
      EveMail += jQuery(data).find('mail').text();
      totalTrade +=  (parseInt(jQuery(data).find('price').text()));
      $('#list_datacore').html(list);
     //alert(data);
  });
  $('#list_components').html(loader_img);
  jQuery.post("lib/db.php",{trade : 'list_components'}, function(data){
      // alert(data + " -> " + olddata );
      list = jQuery(data).find('data').text();
      EveMail += jQuery(data).find('mail').text();
      $('#list_components').html(list);
     //alert(data);
  });
  $('#list_RAM').html(loader_img);
  jQuery.post("lib/db.php",{trade : 'list_RAM'}, function(data){
      // alert(data + " -> " + olddata );
      list = jQuery(data).find('data').text();
      EveMail += jQuery(data).find('mail').text();
      $('#list_RAM').html(list);
     //alert(data);

     $('#basket').html(addSeparatorsNF(totalTrade.toFixed(2), ".", ".", ",")+ " ISK" );
  });

  

}

function tradeEveMailShow () {
    $('#mainLoader').html(loader_img);
    EveMail += "<br /><br />Estimation : " + addSeparatorsNF(totalTrade.toFixed(2), ".", ".", ",")+ " ISK";
    if(!inIGB){
      jQuery.modal(EveMail,{
        overlayClose:true,
        onOpen: function (dialog) {
          dialog.overlay.fadeIn('slow', function () {
            dialog.data.hide();
            dialog.container.fadeIn('slow', function () {
              dialog.data.slideDown('slow');
            });
          });
        },
        onClose: function (dialog) {
          dialog.data.fadeOut('slow', function () {
            dialog.container.slideUp('slow', function () {
              dialog.overlay.fadeOut('slow', function () {
                jQuery.modal.close(); // must call this!
              });
            });
          });
        }
      });
    }
    else CCPEVE.sendMail(' ','Commande de Matos, TII',EveMail);
    
    $('#mainLoader').html("");
}

function OreCalc (oreName) {
  $("#"+oreName+'\\.tot').html( Number($('#'+oreName+'\\.qty').val() * Number($('#'+oreName+'\\.val').html())).toFixed(2) );
  
  total = 0;
  jQuery("#oreCCalc *[id$=tot]").each(function(){
    if($(this).html()!="")
      total += parseFloat($(this).html());
  });
  
  
  $('#totalContract').html(addSeparatorsNF(total.toFixed(2), ".", ".", ",")+ " ISK" );
  
}

function addSeparatorsNF(nStr, inD, outD, sep)
{
	nStr += '';
	var dpos = nStr.indexOf(inD);
	var nStrEnd = '';
	if (dpos != -1) {
		nStrEnd = outD + nStr.substring(dpos + 1, nStr.length);
		nStr = nStr.substring(0, dpos);
	}
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(nStr)) {
		nStr = nStr.replace(rgx, '$1' + sep + '$2');
	}
	return nStr + nStrEnd;
}


function findValue(li) {
  if( li == null ) return alert("No match!");
  
  // if coming from an AJAX call, let's use the CityId as the value
  if( !!li.extra ) var sValue = li.extra[0];
  
  // otherwise, let's just display the value in the text box
  else var sValue = li.selectValue;
  
  showInfo(sValue);
}

function selectItem(li) {
  findValue(li);
}

function formatItem(row) {
  str = "<img src='" + row[2]+"' width='16' />"+row[0];
  return str;
}

function showDiv(id) {
  //if($(id)) $(id).style.display = "";
  if (jQuery('td#td_container > div:visible').size()) {
    jQuery('td#td_container > div:visible').fadeOut(400, function() {
        jQuery('div#Scr_'+id).fadeIn();
    });
  }
  else jQuery('div#Scr_'+id).fadeIn();
}

function hideDiv(id) {
  //if($(id)) $(id).style.display = "none";
}

function go (where) {
  if (jQuery('div#Scr_'+where).size()) showDiv(where);
  switch (where) {
    case "Home":
      refreshBarPrice();
      basketRecap();
      break;
    
    case "Market":
      basketRecap();
      break;
      
    case "Tools":
      loadOreCCalc();
      basketRecap();
      break;

    case "Stats":
      loadStats();
      basketRecap();
      break;

      
    case "PL":
      basketRecap();
      break;
    
    case "Freight":
      loadFreight();
      break;

    case "MyOrders":
      loadMyOrder();
      basketRecap();
      break;

    case "Admin":
      loadAdmin();
      break;

    default:
      break;
  }
}

function toggleChat() {
  jQuery('#daddy-shoutbox-list').slideToggle();
  jQuery('#daddy-shoutbox').slideToggle();

}

        function shout_prepare(response) {
          var d = new Date();
          count++;
          d.setTime(response.time*1000);
          var mytime = d.getDate()+'/'+(d.getMonth()+1)+' '+d.getHours()+':'+d.getMinutes();
          var string = '<div class="shoutbox-list" id="list-'+count+'">'
              + '<span class="shoutbox-list-time">'+mytime+'</span>'
              + '<span class="shoutbox-list-nick">'+response.nickname+':</span>'
              + '<span class="shoutbox-list-message">'+response.message+'</span>'
              +'</div>';
	if(response.nickname=="")return false;
          return string;
        }

        function shout_success(response, status)  {
          if(status == 'success') {
            lastTime = response.time;
            jQuery('#daddy-shoutbox-response').html('');
            jQuery('#daddy-shoutbox-list').append(shout_prepare(response));
            jQuery('input[name=message]').attr('value', '').focus();
            jQuery('#list-'+count).fadeIn('slow');
            timeoutID = setTimeout(refresh, 5000);
          }
        }

        function shout_validate(formData, jqForm, options) {
          for (var i=0; i < formData.length; i++) {
              if (!formData[i].value) {
                  alert('Please fill in all the fields');
                  jQuery('input[@name='+formData[i].name+']').css('background', 'red');
                  return false;
              }
          }
	  if(!name){return false;}
          jQuery('#daddy-shoutbox-response').html(loader_img);

          clearTimeout(timeoutID);
        }

        function liveShout(json)
        {
            if(json.length) {
              for(i=0; i < json.length; i++) {
                jQuery('#daddy-shoutbox-list').append(shout_prepare(json[i]));
                jQuery('#list-' + count).fadeIn('slow');
              }
              //var j = i-1;
              //lastTime = json[j].time;
            }

    shoutcount = jQuery('div.shoutbox-list').length;
    if(shoutcount>10) jQuery('div.shoutbox-list:lt('+(shoutcount-10)+')').fadeOut(3000,function() {jQuery(this).remove();});

        }

function livePL(json)
{
  if(json.length ) {
    for(order in json) {
      //jQuery('#order_'+json[order].orderID+' #status').html(json[order].status);
      //jQuery('#order_'+json[order].orderID+' #price').html(json[order].price);
      //jQuery('#order_'+json[order].orderID+' #assigned').html(json[order].assignedTo);
      $('#order_'+json[order].orderID).replaceWith(json[order].formated);

      if(json[order].assignedTo == 'Not assigned yet')
        $('#order_'+json[order].orderID).switchClass('assignOther','unassigned');
      else
        $('#order_'+json[order].orderID).switchClass('unassigned','assignOther');
      
      if(!$('#order_'+json[order].orderID).length && json[order].nstat < 10 && json[order].assignedTo == "Not assigned yet") {
        //txt = "<li id=\"order_"+json[order].orderID+"\" class=\"unassigned\">bla</li>";
        $('#all_ActiveOrders').append(json[order].formated);
      }
      
      if(json[order].nstat >= 10) $('#order_'+json[order].orderID).effect('pulsate',{times:2, mode:"hide"},500);
      else $('#order_'+json[order].orderID).effect('pulsate',{times:2},500);
    }
  }
      

}

        function refresh() {
          //jQuery.getJSON(files+"daddy-shoutbox.php?action=view&time="+lastTime, function(json) {
            $.ajax({
                type: 'POST',
                url: "lib/db.php",
                data: { live: "view", time: lastTime },
                
                ifModified: true,
                success: function(json) {
                  debug = json;
                  
                  if(json.shoutbox.length) liveShout(json.shoutbox);
                  if(json.prodline.length) livePL(json.prodline);

                  lastTime = json.lasttime;

                },
                dataType: "json",
            });
            /*jQuery.post("lib/db.php", { live: "view", time: lastTime }, function(json) {
              debug = json;
              
              if(json.shoutbox.length) liveShout(json.shoutbox);
              if(json.prodline.length) livePL(json.prodline);

              lastTime = json.lasttime;

          }, "json");*/
          timeoutID = setTimeout(refresh, 5000);

        }

