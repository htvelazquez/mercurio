<?php Flight::render("header"); ?>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
  <?php Flight::render("navbar"); ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Banco Galicia</h1>
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
             <!-- general form elements -->
             <div class="card card-primary">
               <div class="card-header">
                 <h3 class="card-title">Ingresa tus credenciales</h3>
               </div>
               <!-- /.card-header -->
               <!-- form start -->
               <form id="bankForm" autocomplete="off" role="form">
                 <input id="bankId" type="hidden" value="1" />
                 <div class="card-body">
                   <div class="form-group">
                     <label for="document">Tu DNI</label>
                     <input type="input" class="form-control" id="document" placeholder="DNI">
                     <small class="form-text text-muted">No guardaremos ninguno de tus datos.</small>
                   </div>
                   <div class="form-group">
                     <label for="username">Tu usuario Galicia</label>
                     <input type="input" class="form-control" id="username" placeholder="Usuario">
                   </div>
                   <div class="form-group">
                     <label for="password">Tu clave Galicia</label>
                     <input type="password" size="4" class="form-control" id="password" placeholder="Clave">
                   </div>
                   <div class="form-check">
                     <input type="checkbox" class="form-check-input" id="tyc">
                     <label class="form-check-label" for="tyc">Acepto los Términos y Condiciones de CIRENIO</label>
                   </div>
                 </div>
                 <div class="card-footer">
                   <button id="submit" type="submit" class="btn btn-primary float-right">Iniciar Sesión</button>
                 </div>
               </form>
             </div>
           </div>
           <!--/.col (left) -->

           <?php Flight::render("overlay"); ?>
           <?php Flight::render("result-container"); ?>

         </div>
         <!-- /.row -->
       </div><!-- /.container-fluid -->
     </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <?php Flight::render("footer"); ?>
</div>

<?php Flight::render("admin-scripts"); ?>

<script type="text/javascript">
  function waitReply(hash,callback) {
    $.ajax({
        url: "/data/"+hash,
        type: "get",
    }).done(function (response, textStatus, jqXHR){
      if (response){
        callback(response);
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
    $('.is-invalid').removeClass('is-invalid');
    var data = {
      bank_id: $('#bankId').val(),
      document_num: '',
      user: '',
      password: ''
    };
    data.user = $("#username").val();
    data.password = $("#password").val();
    data.document_num = $('#document').val();
    if (data.user == '' || data.password == '' || !data.password.match(/\d{4}/) || data.document_num == '' || !data.document_num.match(/\d+/)){
      // if (data.document_num == '' || data.document_num.match(/\d+/)) $("#document").addClass('is-invalid');
      // if (data.user == '') $("#username").addClass('is-invalid');
      // if (data.password == '' || data.password.match(/\d{4}/)) $("#password").addClass('is-invalid');

      if (data.user == '' || data.password == '' || data.document_num == ''){
        alert('Todos los datos son necesarios para continuar');
      }else if (!data.password.match(/\d{4}/)){
        alert('La clave debe ser númerica y de 4 dígitos');
      }else if (!data.document_num.match(/\d+/)){
        alert('El DNI debe cargarse solo con números');
      }
      return;
    }
    if (!$("#tyc").is(':checked')){
      alert('Debes aceptar nuestros términos y condiciones de uso');
      return;
    }
    $("#formContainer").addClass('d-none');
    $("#overlayContainer").removeClass('d-none');

    $.ajax({
        url: "/job",
        type: "post",
        dataType: "json",
        contentType: "application/json",
        data: JSON.stringify(data)
    }).done(function (response, textStatus, jqXHR){
      if (response.success){
        waitReply(response.hash, function(data){
          $("#overlayContainer").addClass('d-none');
          $("#resultContainer").html(data).removeClass('d-none');
        });
      }
    }).fail(function (jqXHR, textStatus, errorThrown){
      alert('Algo anda mal... vuelve a intentarlo en unos minutos');
    });

  });
</script>
</body>

</html>
