$(window).load(function() {
  // Animate loader off screen
  $(".se-pre-con").fadeOut("slow");

  $('#convert').attr('disabled', true);

  if (window.location.href.indexOf('?m=ec') > 0) { // SLA delay error message
    history.pushState({}, null, "index.php");
    show_error('One of the following columns were not found:<br>1-Ticket Number<br>2-Date Created<br>3-Subject<br>4-From<br>5-Partner Name<br><br>Kindly refresh the page and get the correct excel and try again.', 10);
  }

   if (window.location.href.indexOf('?m=epd') > 0) { // partner delay error message
    history.pushState({}, null, "index.php");
    show_error('One of the following columns were not found:<br>1-Ticket Number<br>2-Date Created<br>3-Subject<br>4-From Email<br>5-Partner Name<br><br>Kindly refresh the page and get the correct excel and try again.', 10);
  }

  if (window.location.href.indexOf('?m=el') > 0) { // file size error message
    history.pushState({}, null, "index.php");
    show_error('File is larger than 35MB, it should be less', 5);
  }

});

$('input:file').change(
    function(){

      var ext = this.value.match(/\.([^\.]+)$/)[1];
      var file = document.getElementById('ofile').files[0];

      if( $(this).val() && !(ext == 'xlsx' || ext == 'xls' || ext == 'csv') ){
          show_error('File type \'.' + ext + '\' is not allowed. Kindly select (.xlsx, .xls, .csv) file types.', 5);
          this.value = '';
          $('#convert').attr('disabled', true);
      }
      else if( $(this).val() && file.size > 35 * 1024 * 1024 ) {
          show_error('File is larger than 35MB, it should be less', 5);
          this.value = '';
          $('#convert').attr('disabled', true);
      }
      else if( $(this).val() )
          $('#convert').removeAttr('disabled');

    }
);

function v_info(){
  var pre = document.createElement('pre');
  pre.appendChild(document.createTextNode($('#version_info').text()));

  if(!alertify.myAlert){
    alertify.dialog('myAlert',function(){
      return{
        main:function(message){
          this.message = message;
        },
        setup:function(){
            return { 
              focus: { element:0 },
              options: {
                          title: "Versions",
                          closableByDimmer: true,
                          maximizable: false,
                          movable: false,
                          padding: "24px",
                      }
            };
        },
        prepare:function(){
          this.setContent(this.message);
        }
    }});
  }

  alertify.myAlert(pre).resizeTo(500, 500); 

}

$('#bg_music').prop("volume", 0.3);

function show_error(text, delay){
  alertify.set('notifier','delay', delay);
  alertify.set('notifier','position', 'top-right');
  alertify.error(text);
}

$("#ofile").change(function() {
  $( "#delaySLA" ).prop( "disabled", false );
  $( "#pdDate" ).prop( "disabled", false );
});


$("#pdDate").change(function() {
  $( "#partnerDelay" ).prop( "disabled", false );
});


