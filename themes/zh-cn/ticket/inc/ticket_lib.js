var SingleTicketAvoidAlgorithmEnable=true;  

var scObjRef={};//maintain a reference of seatChartsObj so it can be used;
var areaSelectedSeat=new Array();//maintain a reference of selected seat in each area;
var areaUnselectedSeat=new Array();//maintain a reference of selected seat in each area;

$(function(){
     $('.ticket-mask-layer-btn-show').click(function(){
            <{if !$loginUser}>
                //alert('请先登录！（登陆或用微信扫描二维码自动登陆）');
                window.location.href = '<{$loginReturnUrl}>'+'%23initAjax';
            <{else}>
                $("html, body").animate({ scrollTop: 0 }, "slow");
                initAreaAjax();
            <{/if}>
    });

    if($(location).attr('hash')=='#initAjax'){
        $("html, body").animate({ scrollTop: 0 }, "slow");
        initAreaAjax();
    }

});

function initAreaAjax(){
    $.ajax({
        url: "<{$http_root_www}>index.php?ctl=show&act=seats_data_ajax",
        data: {cid: '<{$coupon.id}>'},
        beforeSend: function(){
         // Handle the beforeSend event
          $('.shadow').html('');
          $('.choices').html('');
          $('.main-image .data-loading').show();
          $('.ticket-mask-layer').show();
       },
       success: function(data){
         // Handle the complete event
         var seatData=JSON.parse(data);

         for (var k in seatData){
            if (typeof seatData[k] !== 'function') {
                 //alert("Key is " + k + ", value is" + seatData[k]);
                var id =k;
                var data=seatData[k];
                
                initChoiceAreaBtns(id,id);
                initAreaForm(id,id+'区选座');
                initseats(data.map,data.unavailable,data.row,data.column,id,data.rowOffset);  
            }
        }

         // for(var i = 1;i<=43;i++){
         //    initChoiceAreaBtns(i,i);
         // }
         // initChoiceAreaBtns('A','A');
         // initChoiceAreaBtns('B','B');
         // initChoiceAreaBtns('C','C');
         // initChoiceAreaBtns('D','D');
         // initChoiceAreaBtns('E','E');
         // initChoiceAreaBtns('F','F');

        initBtnAction();
        adjustCformSize();

        $('.main-image .data-loading').hide();

       },
       error: function(data){
         // Handle the complete event
          $('.main-image .data-loading').hide();
          $('.main-image .data-loading-fail').show();
       },

    })
}

function initBtnAction(){
     //area btn action
        $(".choices a").click(function(){
            var a = $(this).attr("class");
            var b = a.substring(6);

            var c = "form"+b;
            $(".shadow").height(jQuery(document).height());
            $("[id = "+c+"]").show();
            $(".shadow").show();
        });

        $(".closeForm_no").click(function (){
           areaModifyRollback();
           emptyAreaModify();

           $(".CForm").hide();
           $(".shadow").hide();
        });

        $(".closeForm_yes").click(function (){
            if(!validSelectedSeats($(this).data('area'))){
                areaModifyRollback();
            }else{
                $(".CForm").hide();
                $(".shadow").hide();
            }
           emptyAreaModify();
        });

        $('.ticket-mask-layer-btn-cancel').click(function(){
            $('#selected-seats').html('');
            $('#totalPrice').html('总额:');
            $('#totalPrice').data('total','0');

            $('.ticket-mask-layer').hide();
        });

       
}
function validSelectedSeats(area){
        if (!SingleTicketAvoidAlgorithmEnable) return true;

        var selectedSeatIds =scObjRef[area].find('selected').seatIds;
        var availabelSeatIds=scObjRef[area].find('available').seatIds;
        var unavailabelSeatIds=scObjRef[area].find('unavailable').seatIds;

        var arraySelectedSeatIds=convertDataTo2DArray(selectedSeatIds);
        var arrayAvailabelSeatIds=convertDataTo2DArray(availabelSeatIds);
        var arrayUnavailabelSeatIds=convertDataTo2DArray(unavailabelSeatIds);

        var isValid = true;
        //find with row need to be tested
         for (var k in arraySelectedSeatIds){
            if (typeof arraySelectedSeatIds[k] !== 'function') {
                //alert("Key is " + k + ", value is" + seatData[k]);
                var ids_left = arrayAvailabelSeatIds[k];
                var ids_sold = arrayUnavailabelSeatIds[k];
                if(!validSelectedSeatsEachRow(ids_left,ids_sold)){
                    isValid=false;
                    break;
                }
            }
        }
        if(isValid){
            return true;
        }else{
            alert("根据售票方的规定，您的选座能造成一排座位中有单个座位空出，请重新选择您的座位");
            return false;
        }   
}

function validSelectedSeatsEachRowEachSegment(list_left){
    var isValid = true;
    
    if(list_left.length<=1)return isValid;

    for (var i = list_left.length - 1; i >= 0; i--) {
        var check = list_left[i];
        var check_pre,check_next;
        if(i>=1)check_pre = list_left[i-1];
        if(i<=list_left.length-2)check_next=list_left[i+1];
        
        if(parseInt(check)-1 != parseInt(check_pre) && parseInt(check)+1 != parseInt(check_next)){
            //console.log('this is a single seat'+check);
            isValid= false;
            break;
        }
    };
    return isValid;
}

function validSelectedSeatsEachRow(list_left,ids_sold){
    var isValid = true;
    if(!list_left)return isValid;
    
    var list_left_set=breakValidRowIntoSegment(list_left,ids_sold);
    for (var i = list_left_set.length - 1; i >= 0; i--) {
        if(!validSelectedSeatsEachRowEachSegment(list_left_set[i])){
            isValid =false;
            break;
        }
    };

    return isValid;
}

function breakValidRowIntoSegment(list_left,ids_sold){
    // break each row in to valid segment.  
    // 连续座位>= 3 为一个连续座位组.
    //console.log(list_left);
    //console.log(breakIntoSegment(ids_sold));
    var break_points_set=breakIntoSegment(ids_sold);

    var list_left_set=[];
    var segment=[];

    for (var i = 0; i <= list_left.length - 1; i++) {
        var check = parseInt(list_left[i]);
        var check_next,check_against;

        if(i<=list_left.length-2)check_next=parseInt(list_left[i+1]);
        if(break_points_set.length>0){
            check_against = parseInt(break_points_set[0][0]);
        }else{
           //no more break point;
           list_left_set.push(list_left.slice(i));
           break;
        }

        if(segment.length==0)segment.push(check);

        if( check< check_against &&  check_against<check_next ){
            //the next one is break point;
            list_left_set.push(segment);
            segment=[];
            break_points_set =break_points_set.slice(1);
        }else{
            //the next one is continue
            segment.push(check_next);
        }
    }

    if(list_left_set.length==0){
        list_left_set.push(list_left);
    }

    //console.log(list_left_set);
    return list_left_set;
}

function breakIntoSegment(number_list){
    var number_set=[];
    var segment=[];
    
    if(!number_list)return number_set;//return empty if null

    for (var i = 0; i <= number_list.length - 1; i++) {
        var check = parseInt(number_list[i]);
        var check_next;

        if(i<=number_list.length-2)check_next=parseInt(number_list[i+1]);

        if(segment.length==0)segment.push(check);

        if( check+1 != check_next ){
            //the next one is break point;
            number_set.push(segment);
            segment=[];
        }else{
            //the next one is continue
            segment.push(check_next);
        }
    }

    return number_set;
}

function convertDataTo2DArray(data){
    // 1D array of list of ids "HH_1" 'HH_2' 'HH_3' ...
    // to 2D array  data[hh]=[1,2,3 ...]
    var result = {};
    for (var i = data.length - 1; i >= 0; i--) {
        var row=[];
        var id =data[i];
        var res = id.split("_");
        var col = res[0];
        var seat = res[1];
        if(!result[col])result[col]= new Array();
        result[col].push(seat);
    };

    //each row is sorted into its numeric order
    for (var k in result){
        if (typeof result[k] !== 'function') {
            result[k].sort(function(a,b){
                return parseInt(a)-parseInt(b);
            });
        }
    }

    return result;
}
function ticketRemoveFromCarts(id){
    ticketRemove(id);
    saveAreaModifyRemove(id);

    var parts =id.replace(/-/g,'_').split('_');
    var area =parts[2];

    if(!validSelectedSeats(area)){
        areaModifyRollback();

        var c = "form"+area;
        $(".shadow").height(jQuery(document).height());
        $("[id = "+c+"]").show();
        $(".shadow").show();
    }
    emptyAreaModify();
}
function ticketRemoveFromArea(id){
    ticketRemove(id);
    saveAreaModifyRemove(id)
}
function ticketRemove(id){
    var obj = $('#'+id);
    var area =$(obj).data('sa');
    var row = $(obj).data('row');
    var col = $(obj).data('col');
    scObjRef[area].get(row+'_'+col).status('available');
    totalPrice(-parseFloat($(obj).data('price')));
    $(obj).remove();
}

function ticketAdd(id){
    var parts =id.replace(/-/g,'_').split('_');
    var area =parts[2];
    var row = parts[3];
    var col = parts[4];

    var $cart = $('#selected-seats');
    var price = scObjRef[area].get(row+'_'+col).data().price;
    scObjRef[area].get(row+'_'+col).status('selected');

    var obj = $('<li   id="'+ id +'" data-seatId ="'+row+'_'+col+'" data-price="'+price+'" data-row="'+row+'"  data-col="'+col+'" data-sa="'+area+'" >'+area+'区'+row+'排'+col+'座 ($'+price+')<i class="fa fa-close ticket-remove" onClick="ticketRemoveFromCarts(\''+id+'\')"></i></li>');
    obj.appendTo($cart);
    totalPrice(parseFloat(price));

    saveAreaModifyAdd(id)
}

function saveAreaModifyAdd(id){
    var i = areaUnselectedSeat.indexOf(id);
    if(i != -1) {
        areaUnselectedSeat.splice(i, 1);
    }else{
        areaSelectedSeat.push(id);
    }
}
function saveAreaModifyRemove(id){
    var i = areaSelectedSeat.indexOf(id);
    if(i != -1) {
        areaSelectedSeat.splice(i, 1);
    }else{
        areaUnselectedSeat.push(id);
    }
}
function emptyAreaModify(){
    areaSelectedSeat=[];
    areaUnselectedSeat=[];
}
function areaModifyRollback(){
     $(areaSelectedSeat).each(function(index,value){
         ticketRemoveFromArea(value);
    });
    $(areaUnselectedSeat).each(function(index,value){
         ticketAdd(value);
    });
}


function ticketBuyConfirm(){
   

    var seat_des='';
    $('#selected-seats li').each(function(){
        var des =$(this).data('sa')+'_'+$(this).data('row')+'_'+$(this).data('col');
        seat_des+=des+'#';
    });
    if(seat_des==''){alert('请选择座位');return;}

    $.post('<{$http_root_www}>query?cmd=add_carts_ticket', 
    {
        'userId': '<{$loginUser.id}>',
        'main_coupon_id': '<{$coupon.id}>',
        'sub_coupon_id':'<{$coupon.id}>',
        'coupon_name':'<{$coupon.title}>',
        'businessUserId':'<{$coupon.createUserId}>',
        'sub_or_main':'m',
        'seat_des':seat_des
    }, 
    function(data){
        var jdata=JSON.parse(data);
        alert(jdata.msg);
        if(jdata.status==500)window.location.href = '<{$http_root_www}>member/showcart';
    }
    
    );
   
}

function initChoiceAreaBtns(id,label){
    $('.choices').append(' <a class="choice'+id+'">'+label+'</a>');
}
function initAreaForm(id,label){
    var html=new Array();
    html.push('<div class="CForm form'+id+'" id="form'+id+'" style="display:none;">');
    html.push('<div class="theForm">');
    html.push('<div class="area">');
    html.push('<b>',label,'</b>');
    html.push('<div class="show_seat_info"></div>');
    html.push('<div class="stage_direction">场地方向</div>');
    html.push('<div id="seat_map'+id+'" class="seat_map">');
    html.push('</div>');
    html.push('</div>');
    html.push('<div style="clear: both;"></div>');
    html.push('<div class="btns">');
    html.push('<a class="closeForm"><button data-area="'+id+'" class="closeForm_yes" type="submit">确定</button></a>');
    html.push('<a class="closeForm"><button data-area="'+id+'" class="closeForm_no" type="button">取消</button></a>');
    html.push('</div>');
    html.push('</div>');
    html.push('</div>');

    $('.shadow').append(html.join(''));
}

function initseats(mapdate,selleddate,row,column,ar,rowOffset)
{   
    if(mapdate==null)return;

     var seats={
        <{foreach from=$seatsCategory key=k item=c}>
            <{$c.symbol}>:{
                price       : '<{$c.price}>',
                description : '<{$c.dec}>'
            },
        <{/foreach}>
    };

    var activec='#seat_map'+ar;
    var $cart = $('#selected-seats');

    var sc = $(activec).seatCharts({
        map: mapdate,
        naming:{
             columns: column,
             rows: row,
             getId  : function(character, row, column) {
                return row + '_' + (parseInt(column)+parseInt(rowOffset[row]));
            },
            getLabel : function (character, row, column) {
                return (parseInt(column)+parseInt(rowOffset[row]));
            }

        },
        seats: seats,
        focus  : function() {
            if (this.status() == 'available') {

                var parts= this.settings.id.split('_');
                var row=parts[0];
                var col=parts[1];
                var price = this.data().price;
                var des=this.data().description;
                var msg = 'Sec: '+ ar +' Row: ' + row+ ' Seat: ' + col +' ('+des+') ' +'$'+price; 

                $('.show_seat_info').html(msg);
                return 'focused';
            } else  {
                return this.style();
            }
        },
        click: function () { 
            if (this.status() == 'available') {
                var id ='cart-item-'+ar+"_"+this.settings.id;
                ticketAdd(id);
                return 'selected';

            } else if (this.status() == 'selected') {
                var id = 'cart-item-'+ar+'_'+this.settings.id;
                ticketRemoveFromArea(id);
                return 'available';

            } else if (this.status() == 'unavailable') { 
                return 'unavailable';
            } else {
                return this.style();
            }
        }
    });

    sc.get(selleddate).status('unavailable');

    scObjRef[ar]=sc;// add to reference container
}

function totalPrice(amount){
    var price = parseFloat($('#totalPrice').data('total'))+amount;

    $('#totalPrice').html('总额:$'+price.toFixed(2));

    $('#totalPrice').data('total',price);
}
