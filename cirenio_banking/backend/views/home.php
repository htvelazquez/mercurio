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
    <div class="row justify-content-center mb-3 pb-3">
        <div class="col-md-7 text-center heading-section ftco-animate fadeInUp ftco-animated">
            <h2 class="mb-4" style="text-align:left">Para comenzar escoge tu banco:</h2>
        </div>
    </div>
    <div class="row" style="margin-top:100px">
      <div class="table-responsive">
        <table id="banks" class="table">
          <tbody>
              <tr>
            <?php $i=1; foreach($banks as $bank) { ?>
              <td class="<?php echo $bank['alias']; ?> background-bw bank-background" >
                <a href="/form/<?php echo $bank['alias'].'/'.$bank['id']; ?>" title="<?php echo $bank['name']; ?>" ></a>
              </td>
              <?php if ($i % 4 == 0) { ?> </tr><tr> <?php } ?>
            <?php $i++; } ?>
              </tr>
          </tbody>
        </table>
      </div>

    </div>
</div>
</body>

</html>
