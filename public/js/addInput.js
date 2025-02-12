var protocol = location.protocol;

$.ajaxSetup({
    headers: {
        //'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        'X-CSRF-TOKEN': $("input[name=_token]").val()
    }
});

function updateLocation(){
    location.reload();
}

function updateMessage(cid, id, value){
    var mensaje = $("textarea[name=message"+id).val();
    var event = window.event || callmymethod.caller.arguments[0];
    event.preventDefault ? event.preventDefault() : (event.returnValue = false);
    var cid = cid;
    var id = id;
    var name = mensaje;
    $.ajax({
        type:'POST',
        url:protocol+"//crmquirky.com.co/campaigns/message/"+id+"/update",
        data:{cid:cid,id:id, name:name},
        success:function(data){
            showMessage(data.mensaje);
            cleanInputs();
        }
    })
}

function deleteMessage(id){
    var event = window.event || callmymethod.caller.arguments[0];
    event.preventDefault ? event.preventDefault() : (event.returnValue = false);
    var id = id;
    $.ajax({
        type:'POST',
        url:protocol+"//crmquirky.com.co/campaigns/message/"+id+"/delete",
        data:{},
        success:function(data){
            showMessage(data.mensaje);
            cleanInputs();
            updateLocation(id);
        }
    })
}


function showMessage(message){
    $("#divmsg").empty();
    $("#divmsg").append("<p>"+message+"</p>");
    $("#divmsg").show(200);
    $("#divmsg").hide(2000);
}

function cleanInputs(){
    //$(#name).val('');
}

$("#submit").click(function(e){
    e.preventDefault();//EVITA CARGAR LA PAGINA

    var name = $("input[name=name]").val();

    $.ajax({
        type:'POST',
        url:protocol+"//crmquirky.com.co/campaigns",
        data:{name:name},
        success:function(data){
            showMessage(data.mensaje);
            cleanInputs();
        }

    })
});


$("#submitAdd").click(function(e){
    e.preventDefault();//EVITA CARGAR LA PAGINA
    var name = $("textarea[name=addMessageName]").val();
    var audience = $("input[name=addCampaignId]").val();
    $.ajax({
        type:'POST',
        url:protocol+"//crmquirky.com.co/campaigns/message/store",
        data:{name:name, cid:audience},
        success:function(data){
            showMessage(data.mensaje);
            cleanInputs();
            updateLocation();
        }

    })
});

$("#submitEdit").click(function(e){
    e.preventDefault();//EVITA CARGAR LA PAGINA
    var name = $("input[name=editCampaignName]").val();
    var campaign = $("input[name=editCampaignId]").val();
    $.ajax({
        type:'GET',
        url:protocol+"//crmquirky.com.co/campaigns/"+campaign+"/update",
        data:{name:name, cid:campaign},
        success:function(data){
            showMessage(data.mensaje);
            cleanInputs();
            updateLocation();
        }

    })
});
