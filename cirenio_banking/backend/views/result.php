<?php if (!empty($data->tarjetas)) { ?>
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title">Tarjetas de Crédito</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
          </button>
        </div>
        <!-- /.card-tools -->
      </div>
      <!-- /.card-header -->
      <div class="card-body pt-2">
        <table class="table">
          <thead>
            <tr>
              <th style="width: 10px">#</th>
              <th>Número</th>
              <th>Marca</th>
              <th>Límite</th>
              <th>Utilizado</th>
              <th style="width: 40px">Disponible</th>
            </tr>
          </thead>
          <tbody>
            <?php $i=0; foreach($data->tarjetas as $tarjeta) { $i++; ?>
              <tr>
                <td><?php echo $i; ?>.</td>
                <td><?php echo $tarjeta->id; ?></td>
                <td><img src="/views/img/credit/<?php echo str_replace(" ","-",strtolower($tarjeta->type)); ?>.png" alt="<?php echo $tarjeta->type; ?>"></td>
                <?php if (!empty($tarjeta->limit) && !empty($tarjeta->limit->amount)){
                    $usedLimit = round(100-($tarjeta->available->amount/$tarjeta->limit->amount*100));
                    $availableLimit = round($tarjeta->available->amount/$tarjeta->limit->amount*100);
                    $color = ($availableLimit < 40 )? 'danger' : (($availableLimit > 80)? 'success' : 'warning'); ?>

                  <td class="text-right"><span class="float-left"><?php echo (!empty($tarjeta->limit->currency))? $tarjeta->limit->currency : '$'?></span><?php echo $tarjeta->limit->amount; ?></td>
                  <td>
                    <div class="progress progress-xs">
                      <div class="progress-bar bg-<?php echo $color; ?>" style="width: <?php echo $usedLimit; ?>%"></div>
                    </div>
                  </td>
                  <td><span class="badge bg-<?php echo $color; ?>"><?php echo $availableLimit; ?>%</span></td>
                <?php }else{ ?>
                  <td class="text-right"><span class="float-left">$</span>-</td>
                  <td>
                    <div class="progress progress-xs progress-striped active">
                      <div class="progress-bar bg-primary" style="width: 0%"></div>
                    </div>
                  </td>
                  <td><span class="badge bg-gray">0%</span></td>
                <?php } ?>
              </tr>
            <?php } ?>
          </tbody>
        </table>

      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
<?php } ?>

<?php if (!empty($data->cajas)) { ?>
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">Cajas de ahorro</h3>

      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
        </button>
      </div>
      <!-- /.card-tools -->
    </div>
    <!-- /.card-header -->
    <div class="card-body pt-2">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 10px">#</th>
            <th>Número</th>
            <th>Alias</th>
            <th>CBU</th>
            <th>Moneda</th>
            <th>Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php $i=0; foreach($data->cajas as $caja) { $i++; ?>
            <tr>
              <td><?php echo $i; ?>.</td>
              <td><?php echo $caja->id; ?></td>
              <td><?php echo $caja->alias; ?></td>
              <td><?php echo $caja->cbu; ?></td>
              <td><?php echo $caja->currency; ?></td>
              <td class="text-right"><span class="float-left">$</span><?php echo $caja->balance; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
<?php } ?>

<?php if (!empty($data->cuentas)) { ?>
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">Cuentas Corrientes</h3>

      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
        </button>
      </div>
      <!-- /.card-tools -->
    </div>
    <!-- /.card-header -->
    <div class="card-body pt-2">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 10px">#</th>
            <th>Número</th>
            <th>Alias</th>
            <th>CBU</th>
            <th>Moneda</th>
            <th>Balance</th>
            <th>Limite</th>
          </tr>
        </thead>
        <tbody>
          <?php $i=0; foreach($data->cuentas as $cuenta) { $i++; ?>
            <tr>
              <td><?php echo $i; ?>.</td>
              <td><?php echo $cuenta->id; ?></td>
              <td><?php echo $cuenta->alias; ?></td>
              <td><?php echo $cuenta->cbu; ?></td>
              <td><?php echo $cuenta->currency; ?></td>
              <td class="text-right"><span class="float-left">$</span><?php echo $cuenta->balance; ?></td>
              <td class="text-right"><span class="float-left">$</span><?php echo $cuenta->limit; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
<?php } ?>

<?php if (!empty($data->prestamos)) { ?>

  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">Préstamos</h3>

      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
        </button>
      </div>
      <!-- /.card-tools -->
    </div>
    <!-- /.card-header -->
    <div class="card-body pt-2">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 10px">#</th>
            <th>Número</th>
            <th>Alias</th>
            <th>CBU</th>
            <th>Moneda</th>
            <th>Balance</th>
            <th>Limite</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1.</td>
            <td>******0054</td>
            <td>PUMA.LICOR.GENIO</td>
            <td>15006235-00062332500546</td>
            <td>ARS</td>
            <td class="text-right"><span class="float-left">$</span>0</td>
            <td class="text-right"><span class="float-left">$</span>15.000</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
<?php } ?>
