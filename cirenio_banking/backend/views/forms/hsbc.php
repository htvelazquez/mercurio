<!DOCTYPE html>
<html lang="en">

<?php Flight::render("header"); ?>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php Flight::render("navbar"); ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Banco HSBC</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
    <!-- Main content -->
    <section class="content">
       <div class="container-fluid">
         <div class="row">
           <!-- left column -->
           <div id="formContainer" class="col-md-4 mx-auto">
             <div class="card card-primary">
               <div class="card-header">
                 <h3 class="card-title">Ingresa tus credenciales</h3>
               </div>
               <form id="bankForm" autocomplete="off" role="form">
                 <input id="bankId" type="hidden" value="21" />
                 <div class="card-body">
                   <div class="form-group">
                     <label for="username">Nombre de usuario</label>
                     <input type="input" class="form-control" id="username" placeholder="Usuario">
                     <small class="form-text text-muted">No guardaremos ninguno de tus datos.</small>
                   </div>
                   <div class="form-group">
                     <label for="password">Contraseña</label>
                     <input type="password" class="form-control" id="password" placeholder="Contraseña">
                   </div>
                   <div class="form-check">
                     <input type="checkbox" class="form-check-input" id="tyc">
                     <label class="form-check-label" for="tyc">Acepto los Términos y Condiciones de CIRENIO</label>
                   </div>
                 </div>
                 <div class="card-footer">
                   <button id="submit" type="submit" class="btn btn-primary float-right">Ingresar</button>
                 </div>
               </form>
             </div>
           </div>
           <!--/.col (left) -->

           <?php Flight::render("overlay"); ?>
           <?php Flight::render("result"); ?>

         </div>
         <!-- /.row -->
       </div><!-- /.container-fluid -->
     </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <?php Flight::render("footer"); ?>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="/views/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="/views/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="/views/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="/views/plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="/views/plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="/views/plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="/views/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="/views/plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="/views/plugins/moment/moment.min.js"></script>
<script src="/views/plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="/views/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="/views/plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="/views/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="/views/js/adminlte.min.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="/views/js/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="/views/js/demo.js"></script>

<script type="text/javascript">
  function waitReply(hash,callback) {
    $.ajax({
        url: "/data/"+hash,
        type: "get",
    }).done(function (response, textStatus, jqXHR){
      if (response.success){
        callback(response.data);
      }else{
        setTimeout(function() {
          waitReply(hash,callback);
        }, 8000);
      }
    }).fail(function (){
      setTimeout(function() {
        waitReply(hash,callback);
      }, 8000);
    });
  };

  $("form#bankForm").submit(function(e){
    e.preventDefault();
  });

  $("button#submit").click(function(){
    $('.has-error').removeClass('has-error');
    var data = {
      bank_id: $('#bankId').val(),
      user: '',
      password: ''
    };
    data.user = $("#username").val();
    data.password = $("#password").val();
    if (data.user == '' || data.password == ''){
      if (data.user == '') $("#username").parent('.form-group').addClass('has-error');
      if (data.password == '') $("#password").parent('.form-group').addClass('has-error');
      alert('Todos los datos son necesarios para continuar');
      return;
    }
    if (!$("#tyc").is(':checked')){
      alert('Debes aceptar nuestros términos y condiciones de uso');
      return;
    }
    $("#formContainer").addClass("d-none");
    $("#overlayContainer").removeClass("d-none");

    $.ajax({
        url: "/job",
        type: "post",
        dataType: "json",
        contentType: "application/json",
        data: JSON.stringify(data)
    }).done(function (response, textStatus, jqXHR){
      if (response.success){
        waitReply(response.hash, function(data){
          // var textedJson = JSON.stringify(data, undefined, 4);
          $("#overlayContainer").addClass("d-none");
          // $("#result").text(textedJson);
          $("#resultContainer").removeClass("d-none");
        });
      }
    }).fail(function (jqXHR, textStatus, errorThrown){
      alert('Algo anda mal... vuelve a intentarlo en unos minutos');
    });

  });
</script>

</body>
</html>
