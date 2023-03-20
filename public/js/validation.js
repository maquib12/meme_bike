
var base_url = $('meta[name="base_url"]').attr('content');
jQuery.validator.addMethod("validateEmail", function (value, element, param) {
  return value = value.replace(/\(|\)|\s+|-/g, ""), this.optional(element) || value.length > 5 && value.match(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/);
}, "Please enter a valid email address");

jQuery.validator.addMethod("lettersonly", function (value, element) {
  return this.optional(element) || /^[a-z ]+$/i.test(value);
}, "Please enter alphabets only.");

jQuery.validator.addMethod("lettersDigit", function (value, element) {
  return this.optional(element) || /^[a-z 0-9]+$/i.test(value);
}, "Please do not enter special characters.");

jQuery.validator.addMethod(
  "checkFile",
  function (value, element) {
    $("[name^=document_image]").each(function (i, j) {
      var ext = value.split('.').pop().toLowerCase();
      console.log(ext);
      if ($.inArray(ext, ['jpg', 'jpeg', 'png', 'PDF', 'doc', 'docx']) == -1) {
        return false;
      }
      return true;
    });
  },
  "invalid extension!"
)


$("#forgot-password").validate({
  rules: {
    email: {
      required: true,
      validateEmail: true,
      email: true
    }
  },
  messages: {
    email: {
      required: "*Please enter email.",
      email: "Please enter valid email."

    }
  }
});

$("#resend-mobile-otp").click(function (e) {
    $.ajax({
      url: base_url + '/resend-otp',
      type: "POST",
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      data: {
        'email': $('#email_otp').val()
      },
      success: function (data) {
        console.log(data);
        if (data.status == 200) {
          document.getElementById("error_otp").innerHTML = "";
          $('#email_otp').val(data.email)
          toastr.success(data.message);
        }
      }
    });
  });


  function checkOtpRequired() {

    var otps = $("input[name='otp[]']").serializeArray();
    var count = 1;
    $.each(otps, function (index, otp) {
      if (otp.value != '') {
        count++;
      }
    });
    console.log(count)
    if (count < 6) {
  
      document.getElementById("error_otp").innerHTML = "*Please enter otp.";
      $('#sendOtp').attr('type', 'button');
      $('#sendOtp').attr('onclick', "checkOtpRequired()");
      count = 1;
      return false;
  
    } else {
      document.getElementById("error_otp").innerHTML = "";
      $('#sendOtp').attr('type', 'submit');
      $('#sendOtp').removeAttr('onclick');
      count = 1;
  
      return true;
    }
  }


  function validateOtp(opt, event) {

    var id = parseInt(opt.id);
    var code = event.keyCode || event.which;
  
    if (code != 8) {
      if (opt.value.length >= 1) {
        $("#" + (id + 1)).focus();
      }
    } else {
      $("#" + (id - 1)).focus();
    }
  }

  $("#reset").validate({
    rules: {
      password: {
        required: true
      },
      confirm_password: {
        required: true,
        equalTo: "#password"
      }
    },
    messages: {
      confirm_password: {
        required: "*Please enter confirm password.",
        equalTo: "Please enter confirm password same as password."
  
      },
      password: {
        required: "*Please enter password."
  
      }
    }
  });


  $("#password_change").validate({

    rules: {
  
      c_password: {
        equalTo: "#new_password"
      }
    },
    messages: {
  
      current_password: {
        required: "*Please enter current password.",
  
      },
      new_password: {
        required: "*Please enter new password.",
  
      },
      c_password: {
        required: "*Please enter confirm password.",
        equalTo: "*Password and confirm password must be equal.",
  
      }
    }
  });

  $("#edit_profile").validate({
    rules: {
    first_name: {
      required: true,
      lettersonly: true
    },
    last_name: {
      required: true,
      lettersonly: true
    },
    email: {
      required: true,
      validateEmail: true,
      email: true
    }
  },
  messages: {
    first_name: {
      required: "*Please enter first name."
    },
    last_name: {
      required: "*Please enter last name."
    },
    /*country_code: {
              required: "*Please select country code."
    },*/
    email: {
      required: "*Please enter email.",
      email: "Please enter valid email."

    }
  }
});



var map = null;
var marker = [];
var autocomplete = [];

// var autocompleteOptions = {
//     componentRestrictions: {country: "in"}
// };
// var autocompleteOptions1 = {
//     componentRestrictions: {country:"us"}
// };

$(document).ready(function(){
  var newInput = [];
        var newEl = document.getElementById('pac-input');
        newInput.push(newEl);
        setupAutocomplete(autocomplete, newInput, 0);
    var count = 1;
    $("#addInputField").click(function(){
    var html = '<div class="copy hide">'+
                        '<div class="control-group input-group" style="margin-top:10px">'+
                         '<input type="text" name="location[]" class="form-control mr-2" placeholder="Search location" id="pac-input'+count+'">'+
                         '<div class="input-group-btn"> '+
                          '<button class="btn btn-danger remove pl-2 pr-2 py-0 h-100" type="button">Remove</button>'+
                         '</div>'+
                        '</div>'+
                    '</div>';
        
        console.log('add new input field');
        $("#inputlar").append(html);
        
        var newInput = [];
        var newEl = document.getElementById('pac-input'+ count);
        newInput.push(newEl);
        setupAutocomplete(autocomplete, newInput, 0);
    count++;

    $("body").on("click",".remove",function(){ 
          $(this).parents(".control-group").remove();
      });
    });    
});

function setupAutocomplete(autocomplete, inputs, i) {
    console.log('setupAutocomplete...');
      var type = {types: ['geocode']};
        // autocomplete[i] = new google.maps.places.Autocomplete(inputs[i], autocompleteOptions);
        autocomplete.push(new google.maps.places.Autocomplete(inputs[i], type));
    // autocomplete.push(new google.maps.places.Autocomplete(inputs[i], autocompleteOptions1));
        var idx = autocomplete.length - 1;
        
        

        //google.maps.event.addListener(autocomplete[i], 'place_changed', function() {
        google.maps.event.addListener(autocomplete[idx], 'place_changed', function() {

          var place = autocomplete[idx].getPlace();
          if (!place.geometry) {
            return;
          }

          marker[idx].setPosition(place.geometry.location);
          marker[idx].setVisible(true);

          var address = '';
          if (place.address_components) {
            address = [
              (place.address_components[0] && place.address_components[0].short_name || ''),
              (place.address_components[1] && place.address_components[1].short_name || ''),
              (place.address_components[2] && place.address_components[2].short_name || '')
            ].join(' ');
          }

          infowindow.setContent('<div><strong>' + place.name + '</strong><br>' + address);
          infowindow.open(map, marker[idx]);
        });
    }

function initialize() {
    var mapOptions = {
        center: new google.maps.LatLng(40.4700, 50.0000),
        zoom: 10,
        zoomControl:true,
        zoomControlOptions: {
          style:google.maps.ZoomControlStyle.SMALL
        },
        mapTypeControl:true,
        mapTypeControlOptions: {
          style:google.maps.MapTypeControlStyle.DROPDOWN_MENU 
        }
      };
    map = new google.maps.Map(document.getElementById('map-canvas'),
        mapOptions);
    var types = document.getElementById('type-selector');
    map.controls[google.maps.ControlPosition.TOP_LEFT].push(types);


    var inputs = document.getElementsByClassName("controlsInput");
    for (var i = 0; i < inputs.length; i++) {  
       setupAutocomplete(autocomplete, inputs, i);
    }
    
    // Sets a listener on a radio button to change the filter type on Places
    // Autocomplete.
    function setupClickListener(id, types) {
        var radioButton = document.getElementById(id);
        google.maps.event.addDomListener(radioButton, 'click', function() {
            for (var i=0 ; i<autocomplete.length; i++) {
                autocomplete[i].setTypes(types);
            }
        });
    }

      setupClickListener('changetype-all', []);
      setupClickListener('changetype-establishment', ['establishment']);
      setupClickListener('changetype-geocode', ['geocode']);
    }





$(document).ready(function(){
  $(".months").show();
   $(".weeks").hide();
    $(".years").hide();
  $(".month").click(function(){
    $(".months").show();
    $(".weeks").hide();
    $(".years").hide();

  });
  $(".week").click(function(){
    $(".months").hide();
    $(".weeks").show();
    $(".years").hide();
  });
  $(".year").click(function(){
    $(".months").hide();
    $(".weeks").hide();
    $(".years").show();
  });
});


    $('.delete-location-confirm').on('click', function (event) {
      event.preventDefault();
      const location_url = $(this).attr('href');
   
    
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
}).then((result) => {
        if (result.isConfirmed) {
        Swal.fire(
          'Deleted!',
          'Your file has been deleted.',
          'success'
        )
        window.location.href = location_url;
        } else if (
        /* Read more about handling dismissals below */
        result.dismiss === Swal.DismissReason.cancel
        ) {
        Swal.fire(
          'Cancelled',
        )
        }
      });
    });



function notification(type){

  
  $.ajax({
   
    url: base_url +'/notifications',
    type: 'get',
    data:{"_token": $('meta[name="csrf-token"]').attr('content')},
    dataType: 'json',
    success: function(response){
      var html= "";
      for(var a =0; a<response.push.length; a++){
      var res = response.push[a];
      console.log(res);
      html+='<h6 class="preview-subject font-weight-normal mb-1">New Joining</h6><small class="text-gray ellipsis mb-0" style="color:red">'+res['message']+'</small><br>';

       
     }
      var html1= "";
      for(var b =0; b<response.push1.length; b++){
      var res1 = response.push1[b];
      console.log(res1);
      html1+='<h6 class="preview-subject font-weight-normal mb-1">New Subscription</h6><small class="text-gray ellipsis mb-0" style="color:red">'+res1['message']+'</small>';

     }
     $('.joining_notification').html(html);
     $('.new_subscription').html(html1);
     
    }
  });
  }


$('document').ready(function(){
  $.ajax({
   
    url: base_url +'/notification-count',
    type: 'get',
    data:{"_token": $('meta[name="csrf-token"]').attr('content')},
    dataType: 'json',
    success: function(response){
      console.log(response.count);
      if (response.count == 0) {

        $('.count-symbol').hide();

      }else{
        $('.count-symbol').show();
      }

     }
   });
   

});

function openModal(id){
         $('#deleteValue').val(id);
         
        $('#deleteModal').modal('show');
    }

    // function delete(){//alert(ids);
    //     //$('#deleteModal').modal('hide');
    //     var ids = $('#deleteValue').val();
    //     $.ajax({
    //     url: "{{ url('delete-details') }}",
    //     type: "POST",
    //     headers: {
    //         'X-CSRF-TOKEN': "{{ csrf_token() }}"
    //     },
    //     data: {
    //         'delete_data': ids
    //     },
    //     success: function (data) {
    //         if (data.status == 200) {
    //             toastr.success(data.message);
    //             setTimeout(function(){
    //                 window.location.href = "{{url('advertise-management')}}";
    //             }, 2000);



    //         }else{
    //             toastr.error(data.message);
    //         }
    //     }
    // });

    // };

  







