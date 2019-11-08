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
            <h1 class="m-0 text-dark">Bienvenido a CIRENIO</h1>
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
                 <h3 class="card-title">Inicia sesión para continuar</h3>
               </div>
               <form id="loginForm" autocomplete="off" role="form">
                 <div class="card-body">
                   <div class="form-group">
                     <label for="username">Usuario</label>
                     <input type="input" class="form-control" id="username" placeholder="Usuario">
                   </div>
                   <div class="form-group">
                     <label for="password">Contraseña</label>
                     <input type="password" class="form-control" id="password" placeholder="Contraseña">
                   </div>
                   <div class="form-check">
                     <input type="checkbox" class="form-check-input" id="tyc">
                     <label class="form-check-label" for="tyc">Reconozco que CIRENIO es un DEMO y no guardará ninguno de mis datos bancarios</label>
                   </div>
                   <div id="invalidCredentials" class="alert alert-warning alert-dismissible mt-3 d-none">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-exclamation-triangle"></i> Atención!</h5>
                    Las credenciales ingresadas son inválidas
                   </div>
                   <div id="internalError" class="alert alert-danger alert-dismissible mt-3 d-none">
                     <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                     <h5><i class="icon fas fa-ban"></i> Atención!</h5>
                       Estamos teniendo problemas técnicos, vuelve a intentar en unos minutos
                   </div>
                 </div>
                 <div class="card-footer">
                   <button id="submit" type="submit" class="btn btn-primary float-right">Ingresar</button>
                 </div>
               </form>
             </div>
           </div>
           <!--/.col (left) -->
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
<!-- ./wrapper -->

<?php Flight::render("admin-scripts"); ?>

<script type="text/javascript">
  $("form#loginForm").submit(function(e){
    e.preventDefault();
  });

  $("button#submit").click(function(){
    $('.is-invalid').removeClass('is-invalid');
    var data = {
      user: '',
      password: ''
    };
    data.user = $("#username").val();
    data.password = $("#password").val();
    if (data.user == '' || data.password == ''){
      if (data.user == '') $("#username").addClass('is-invalid');
      if (data.password == '') $("#password").addClass('is-invalid');
      alert('Todos los datos son necesarios para continuar');
      return;
    }
    if (!$("#tyc").is(':checked')){
      alert('Debes aceptar nuestros términos y condiciones de uso');
      return;
    }
    $("#invalidCredentials, #internalError").addClass("d-none");

    $.ajax({
        url: "/login",
        type: "post",
        dataType: "json",
        contentType: "application/json",
        data: JSON.stringify(data)
    }).done(function (response, textStatus, jqXHR){
      if (response.success){
        window.location.href = "/";
      }else{
        $("#invalidCredentials").removeClass("d-none");
      }
    }).fail(function (jqXHR, textStatus, errorThrown){
      $("#internalError").removeClass("d-none");
    });

  });
</script>

</body>
</html>
