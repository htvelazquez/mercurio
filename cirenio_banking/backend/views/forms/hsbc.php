<!DOCTYPE html>
<html lang="en">

<?php Flight::render("header"); ?>

<body>
  <nav class="navbar navbar-default top-navbar" role="navigation">
    <div class="navbar-header">
        <a class="navbar-brand" href="/"><span class="brand" >Cirenio</span> Banking</a>
    </div>
  </nav>

  <div class="container" style="margin-top:80px;">
    <div class="form-header row bank-background hsbc"></div>
    <div id="formContainer" class="row justify-content-center mb-3 pb-3">
      <form id="bankForm" autocomplete="off">
        <input id="bankId" type="hidden" value="21" />
        <div class="form-group">
          <label for="username">Ingresá tu nombre de usuario</label>
          <input type="input" class="form-control" id="username" aria-describedby="emailHelp" placeholder="Usuario">
          <small id="emailHelp" class="form-text text-muted">No guardaremos ninguno de tus datos.</small>
        </div>
        <div class="form-group">
          <label for="password">Ingresá tu contraseña</label>
          <input type="password" class="form-control" id="password" placeholder="Contraseña">
        </div>
        <div class="form-group form-check">
          <input type="checkbox" class="form-check-input" id="tyc">
          <label class="form-check-label" for="tyc">Acepto los Términos y Condiciones de CIRENIO</label>
        </div>
        <button id="submit" type="submit" class="btn btn-primary">Ingresar</button>
      </form>
    </div>

    <?php Flight::render("overlay"); ?>
    <?php Flight::render("result"); ?>

  </div>
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
      $("#formContainer").hide();
      $("#overlayContainer").show();

      $.ajax({
          url: "/job",
          type: "post",
          dataType: "json",
          contentType: "application/json",
          data: JSON.stringify(data)
      }).done(function (response, textStatus, jqXHR){
        if (response.success){
          waitReply(response.hash, function(data){
            var textedJson = JSON.stringify(data, undefined, 4);
            $("#overlayContainer").hide();
            $("#result").text(textedJson);
            $("#resultContainer").show();
          });
        }
      }).fail(function (jqXHR, textStatus, errorThrown){
        alert('Algo anda mal... vuelve a intentarlo en unos minutos');
      });

    });
  </script>
</body>

</html>
