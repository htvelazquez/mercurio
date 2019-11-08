<!DOCTYPE html>
<html lang="en">

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
            <h1 class="m-0 text-dark"></h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
       <div class="container-fluid">
         <div class="card card-default">
           <div class="card-header">
             <h3 class="card-title">Para comenzar escoge tu entidad bancaria:</h3>
           </div>
           <!-- /.card-header -->
           <div class="card-body">
             <div class="row">
               <div class="col-md-6">
                 <div class="form-group">
                   <select class="form-control bankselector select2-hidden-accessible" style="width: 100%;" tabindex="-1" aria-hidden="true">
                     <option data-select2-id="#" disabled selected>Selecciona tu banco</option>
                     <?php foreach($banks as $bank) { ?>
                       <option value="<?php echo $bank['alias']; ?>"><?php echo $bank['name']; ?></option>
                     <?php } ?>
                   </select>
                 </div>
                 <!-- /.form-group -->
               </div>
               <!-- /.col -->
             </div>
                <button id="submit" type="submit" class="btn btn-primary float-right">Continuar</button>
             <!-- /.row -->
           </div>
           <!-- /.card-body -->
           <div class="card-footer">

           </div>

         </div>
       </div>
     </section>

  </div>
  <!-- /.content-wrapper -->
  <?php Flight::render("footer"); ?>

</div>
<!-- ./wrapper -->

</body>
  <?php Flight::render("admin-scripts"); ?>
  <script>
    $(function () {
      //Initialize Select2 Elements
      $('.bankselector').select2({
        theme: 'bootstrap4'
      })

      $('#submit').click(function(){
        var bank = $('.bankselector option:selected').val();
        if (bank){
          window.location.href = "/form/"+bank;
        }
      });
    })
  </script>
</html>
