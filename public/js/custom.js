(function($) {
  'use strict';
  $(function() {
    var body = $('body');
    var sidebar = $('.sidebar');

    function addActiveClass(element) {
      if (current === "") {
        //for root url
        if (element.attr('href').indexOf("index.html") !== -1) {
          element.parents('.nav-item').last().addClass('active');
          if (element.parents('.sub-menu').length) {
            element.closest('.collapse').addClass('show');
            element.addClass('active');
          }
        }
      } else {
        //for other url
        if (element.attr('href').indexOf(current) !== -1) {
          element.parents('.nav-item').last().addClass('active');
          if (element.parents('.sub-menu').length) {
            element.closest('.collapse').addClass('show');
            element.addClass('active');
          }
          if (element.parents('.submenu-item').length) {
            element.addClass('active');
          }
        }
      }
    }

    var current = location.pathname.split("/").slice(-1)[0].replace(/^\/|\/$/g, '');
    $('.nav li a', sidebar).each(function() {
      var $this = $(this);
      addActiveClass($this);
    })

    $('.horizontal-menu .nav li a').each(function() {
      var $this = $(this);
      addActiveClass($this);
    })

    //Close other submenu in sidebar on opening any

    sidebar.on('show.bs.collapse', '.collapse', function() {
      sidebar.find('.collapse.show').collapse('hide');
    });

    $('[data-toggle="minimize"]').on("click", function() {
      if ((body.hasClass('sidebar-toggle-display')) || (body.hasClass('sidebar-absolute'))) {
        body.toggleClass('sidebar-hidden');
      } else {
        body.toggleClass('sidebar-icon-only');
      }
    });

    //checkbox and radios
    $(".form-check label,.form-radio label").append('<i class="input-helper"></i>');

  });
	
	
	//Open submenu on hover in compact sidebar mode and horizontal menu mode
  $(document).on('mouseenter mouseleave', '.sidebar .nav-item', function(ev) {
    var body = $('body');
    var sidebarIconOnly = body.hasClass("sidebar-icon-only");
    var sidebarFixed = body.hasClass("sidebar-fixed");
    if (!('ontouchstart' in document.documentElement)) {
      if (sidebarIconOnly) {
        if (sidebarFixed) {
          if (ev.type === 'mouseenter') {
            body.removeClass('sidebar-icon-only');
          }
        } else {
          var $menuItem = $(this);
          if (ev.type === 'mouseenter') {
            $menuItem.addClass('hover-open')
          } else {
            $menuItem.removeClass('hover-open')
          }
        }
      }
    }
  });
  $('.aside-toggler').click(function(){
    $('.chat-list-wrapper').toggleClass('slide')
  });
	$('[data-toggle="tooltip"]').tooltip();
	
})(jQuery);

$(function() {
	$( "#from" ).datepicker({
	dateFormat: "d M yy",
			defaultDate: "w",
			changeMonth: false,
			numberOfMonths: 1,
			onSelect: function( selectedDate ) {
					$( "#to" ).datepicker( "option", "minDate", selectedDate );
			}
	});
	$( "#to" ).datepicker({
	dateFormat: "d M yy",
			defaultDate: "-w",
			changeMonth: false,
			numberOfMonths: 1,
			onSelect: function( selectedDate ) {
					$( "#from" ).datepicker( "option", "maxDate", selectedDate );
			}
	});
});
function validationFrom(d1)
{
	var dateTo = new Date($('#to').val());
	var dd = dateTo.getDate(); 	
	//var mm = dateTo.getMonth(); 
	var mm = ("0" + (dateTo.getMonth() + 1)).slice(-2);
	var yy = dateTo.getFullYear();
	
	var todt = (yy*10000)+(mm*100)+leadingZero(dd);
	var d1 =parseInt(d1,10);
	var d2 =parseInt(todt,10);

	if(d1 >= d2)
	{
		var dateFrom = new Date($('#from').val());
		var dd = dateFrom.getDate();
		var mm = dateFrom.getMonth(); 	
		var yy = dateFrom.getFullYear();
		
		
		var newDateFrom = new Date(yy,mm,dd);
		var dd = newDateFrom.getDate();	
		//var mm = newDateFrom.getMonth();	
		var mm = ("0" + (newDateFrom.getMonth() + 1)).slice(-2);	
		var yy = newDateFrom.getFullYear();

		var frmdt = yy+"-"+mm+"-"+leadingZero(dd);
		$('#to').val($.datepicker.formatDate( "d M yy", new Date(frmdt) ));
	}	
}
function dateFromNext()
{
	//alert('datefromNext');
	var dateFrom = new Date($('#from').val());
	//alert();
	var dd = dateFrom.getDate(); 	
	var mm = dateFrom.getMonth(); 	
	var yy = dateFrom.getFullYear();
	
	
	var newDateFrom = new Date(yy,mm,dd+1);
	var dd = newDateFrom.getDate();	

	//var mm = newDateFrom.getMonth();	
	var mm = ("0" + (newDateFrom.getMonth() + 1)).slice(-2);	
	var yy = newDateFrom.getFullYear();
	var frmdt = yy+"-"+mm+"-"+leadingZero(dd);
	$('#from').val($.datepicker.formatDate( "d M yy", new Date(frmdt) ));
	var d1 = (yy*10000)+(mm*100)+leadingZero(dd);
	 validationFrom(d1);
}
function dateFromPrev()
{
	var dateFrom = new Date($('#from').val());

	var dd = dateFrom.getDate(); 
	
	
	var mm = dateFrom.getMonth(); 	
	var yy = dateFrom.getFullYear();
	
	var newDateFrom = new Date(yy,mm,dd-1);
	var dd = newDateFrom.getDate();	
	//var mm = newDateFrom.getMonth();	
	var mm = ("0" + (newDateFrom.getMonth() + 1)).slice(-2);	
	var yy = newDateFrom.getFullYear();
	var frmdt = yy+"-"+mm+"-"+leadingZero(dd);
	$('#from').val($.datepicker.formatDate( "d M yy", new Date(frmdt) ));
}
function validationTo(d1)
{
	var dateFrom = new Date($('#from').val());
	var dd = dateFrom.getDate(); 	
	var mm = dateFrom.getMonth(); 	
	var yy = dateFrom.getFullYear();
		
	var frmdt = (yy*10000)+(mm*100)+leadingZero(dd);
	var d1 =parseInt(d1,10);
	var d2 =parseInt(frmdt,10);
	//alert(d1+' '+d2);
	if(d1 <= d2)
	{
		var dateTo = new Date($('#to').val());
		var dd = dateTo.getDate(); 	
		var mm = dateTo.getMonth(); 	
		var yy = dateTo.getFullYear();
	
		var newDateTo = new Date(yy,mm,dd);
		var dd = newDateTo.getDate();	
		//var mm = newDateTo.getMonth();	
		var mm = ("0" + (newDateTo.getMonth() + 1)).slice(-2);	
		var yy = newDateTo.getFullYear();
		var todt = yy+"-"+mm+"-"+leadingZero(dd);
		$('#from').val($.datepicker.formatDate( "d M yy", new Date(todt) ));
	}	
}
function dateToNext()
{
	var dateTo = new Date($('#to').val());
	
	var dd = dateTo.getDate(); 	
	var mm = dateTo.getMonth(); 	
	var yy = dateTo.getFullYear();
	
	var newDateTo = new Date(yy,mm,dd+1);
	var dd = newDateTo.getDate();	
	//var mm = newDateTo.getMonth();	
	var mm = ("0" + (newDateTo.getMonth() + 1)).slice(-2);	
	var yy = newDateTo.getFullYear();
	var todt = yy+"-"+mm+"-"+leadingZero(dd);
	$('#to').val($.datepicker.formatDate( "d M yy", new Date(todt) ));
	
	 
}
function dateToPrev()
{
	var dateTo = new Date($('#to').val());
	
	var dd = dateTo.getDate(); 	
	var mm = dateTo.getMonth(); 	
	var yy = dateTo.getFullYear();
	
	var newDateTo = new Date(yy,mm,dd-1);
	var dd = newDateTo.getDate();	
	//var mm = newDateTo.getMonth();	
	var mm = ("0" + (newDateTo.getMonth() + 1)).slice(-2);	
	var yy = newDateTo.getFullYear();
	var todt = yy+"-"+mm+"-"+leadingZero(dd);
	$('#to').val($.datepicker.formatDate( "d M yy", new Date(todt) ));
	var d1 = (yy*10000)+(mm*100)+leadingZero(dd);
	 validationTo(d1);
} 

function leadingZero(value){
   if(value < 10){
      return "0" + value.toString();
   }
   return value.toString();    
}

$("#change_password").validate({
	rules : {
		password : {
			minlength : 5
		},
		confirm_password : {
			minlength : 5,
			equalTo : '[name="password"]'
		}
	}
});

$(document).ready(function (){
   var table = $('#dataTable').DataTable({
      'columnDefs': [{
         'targets': 0,
         'searchable': false,
         'orderable': false,
         'className': 'dt-body-center',
         'render': function (data, type, full, meta){
             return '<input type="checkbox" name="id[]" value="' + $('<div/>').text(data).html() + '">';
         }
      }],
      'order': [[1, 'asc']]
   });
	 
	 $('.dataTable').each(function(){
		var nofilter = $(this).attr('data-nofilter');
		$(this).DataTable({
			'columnDefs': [{
				'targets': 0,
			}],
		 });
	 });

   // Handle click on "Select all" control
   $('#example-select-all').on('click', function(){
      // Get all rows with search applied
      var rows = table.rows({ 'search': 'applied' }).nodes();
      // Check/uncheck checkboxes for all rows in the table
      $('input[type="checkbox"]', rows).prop('checked', this.checked);
   });

   // Handle click on checkbox to set state of "Select all" control
   $('#example tbody').on('change', 'input[type="checkbox"]', function(){
      // If checkbox is not checked
      if(!this.checked){
         var el = $('#example-select-all').get(0);
         // If "Select all" control is checked and has 'indeterminate' property
         if(el && el.checked && ('indeterminate' in el)){
            // Set visual state of "Select all" control
            // as 'indeterminate'
            el.indeterminate = true;
         }
      }
   });

   // Handle form submission event
   $('#frm-example').on('submit', function(e){
      var form = this;

      // Iterate over all checkboxes in the table
      table.$('input[type="checkbox"]').each(function(){
         // If checkbox doesn't exist in DOM
         if(!$.contains(document, this)){
            // If checkbox is checked
            if(this.checked){
               // Create a hidden element
               $(form).append(
                  $('<input>')
                     .attr('type', 'hidden')
                     .attr('name', this.name)
                     .val(this.value)
               );
            }
         }
      });
   });

});
$(document).ready(function() {
	$("#dataTable1").DataTable();
} );

(function(factory) {
	if (typeof module === 'object' && module.exports) {
		module.exports = factory;
	} else {
		factory(Highcharts);
	}
}(function(Highcharts) {
	(function(H) {
		H.wrap(H.seriesTypes.column.prototype, 'translate', function(proceed) {
			const options = this.options;
			const topMargin = options.topMargin || 0;
			const bottomMargin = options.bottomMargin || 0;

			proceed.call(this);

			H.each(this.points, function(point) {
				if (options.borderRadiusTopLeft || options.borderRadiusTopRight || options.borderRadiusBottomRight || options.borderRadiusBottomLeft) {
					const w = point.shapeArgs.width;
					const h = point.shapeArgs.height;
					const x = point.shapeArgs.x;
					const y = point.shapeArgs.y;

					let radiusTopLeft = H.relativeLength(options.borderRadiusTopLeft || 0, w);
					let radiusTopRight = H.relativeLength(options.borderRadiusTopRight || 0, w);
					let radiusBottomRight = H.relativeLength(options.borderRadiusBottomRight || 0, w);
					let radiusBottomLeft = H.relativeLength(options.borderRadiusBottomLeft || 0, w);

					const maxR = Math.min(w, h) / 2

					radiusTopLeft = radiusTopLeft > maxR ? maxR : radiusTopLeft;
					radiusTopRight = radiusTopRight > maxR ? maxR : radiusTopRight;
					radiusBottomRight = radiusBottomRight > maxR ? maxR : radiusBottomRight;
					radiusBottomLeft = radiusBottomLeft > maxR ? maxR : radiusBottomLeft;

					point.dlBox = point.shapeArgs;

					point.shapeType = 'path';
					point.shapeArgs = {
						d: [
							'M', x + radiusTopLeft, y + topMargin,
							'L', x + w - radiusTopRight, y + topMargin,
							'C', x + w - radiusTopRight / 2, y, x + w, y + radiusTopRight / 2, x + w, y + radiusTopRight,
							'L', x + w, y + h - radiusBottomRight,
							'C', x + w, y + h - radiusBottomRight / 2, x + w - radiusBottomRight / 2, y + h, x + w - radiusBottomRight, y + h + bottomMargin,
							'L', x + radiusBottomLeft, y + h + bottomMargin,
							'C', x + radiusBottomLeft / 2, y + h, x, y + h - radiusBottomLeft / 2, x, y + h - radiusBottomLeft,
							'L', x, y + radiusTopLeft,
							'C', x, y + radiusTopLeft / 2, x + radiusTopLeft / 2, y, x + radiusTopLeft, y,
							'Z'
						]
					};
				}

			});
		});
	}(Highcharts));
}))

CKEDITOR.replace('summernote')
CKEDITOR.config.height = 500;	
