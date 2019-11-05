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
            <h1 class="m-0 text-dark">Para comenzar escoge tu banco:</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->


    <div class="row" style="margin-top:100px">
      <div class="table-responsive">
        <table id="banks" class="table">
          <tbody>
              <tr>
            <?php $i=1; foreach($banks as $bank) { ?>
              <td class="<?php echo $bank['alias']; ?> background-bw bank-background" >
                <a href="/form/<?php echo $bank['alias']; ?>" title="<?php echo $bank['name']; ?>" ></a>
              </td>
              <?php if ($i % 4 == 0) { ?> </tr><tr> <?php } ?>
            <?php $i++; } ?>
              </tr>
          </tbody>
        </table>
      </div>

    </div>



  </div>
  <!-- /.content-wrapper -->

</div>
<!-- ./wrapper -->

</body>

</html>
