<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">Información Personal</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
      </button>
    </div>
    <!-- /.card-tools -->
  </div>
  <div class="card-body box-profile">
    <h3 class="profile-username text-center">Fernando Mauricio Monzon</h3>

    <p class="text-muted text-center">Banco HSBC</p>

    <ul class="list-group list-group-unbordered mb-3">
      <li class="list-group-item">
        <b>CUIL</b> <a class="float-right">20-36611173-6</a>
      </li>
      <li class="list-group-item">
        <b>Sucursal</b> <a class="float-right">253 San Nicolás, Capital Federal</a>
      </li>
      <li class="list-group-item">
        <b>Paquete</b> <a class="float-right">Premier</a>
      </li>
    </ul>
  </div>
  <!-- /.card-body -->
</div>

<div class="card card-outline card-primary">
  <div class="card-header">
    <h3 class="card-title">Balance anual</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
      </button>
    </div>
    <!-- /.card-tools -->
  </div>
  <!-- /.card-header -->
  <div class="card-body pt-2">
    <div class="d-flex">
      <p class="d-flex flex-column">
        <span class="text-bold text-lg">%27 de excedente mensual</span>
        <span>Ingresos/Egresos</span>
      </p>
      <p class="ml-auto d-flex flex-column text-right">
        <span class="text-success">
          <i class="fas fa-arrow-up"></i> 12.5%
        </span>
        <span class="text-muted">Los ultimos 3 meses</span>
      </p>
    </div>
    <!-- /.d-flex -->

    <div class="position-relative mb-4"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
      <canvas id="income-chart" height="400" width="1304" class="chartjs-render-monitor" style="display: block; height: 200px; width: 652px;"></canvas>
    </div>

    <div class="d-flex flex-row justify-content-end">
      <span class="mr-2">
        <i class="fas fa-square text-success"></i> Ingresos
      </span>

      <span>
        <i class="fas fa-square text-danger"></i> Egresos
      </span>
    </div>
  </div>
</div>

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

<script type="text/javascript">
  $(function () {
    'use strict'

    var ticksStyle = {
      fontColor: '#495057',
      fontStyle: 'bold'
    }

    var mode      = 'index'
    var intersect = true

    var $visitorsChart = $('#income-chart')
    var visitorsChart  = new Chart($visitorsChart, {
      data   : {
        labels  : ['Dic', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov'],
        datasets: [{
          type                : 'line',
          data                : [50750, 35000, 42000, 42000, 42000, 42000, 42000, 76125, 53500, 52500, 52500, 65100],
          backgroundColor     : 'transparent',
          borderColor         : '#67AB81',
          pointBorderColor    : '#67AB81',
          pointBackgroundColor: '#67AB81',
          fill                : false
          // pointHoverBackgroundColor: '#67AB81',
          // pointHoverBorderColor    : '#67AB81'
        },
        {
          type                : 'line',
          data                : [45150, 21450, 23504, 31042, 44315, 43900, 42000, 72125, 42760, 38500, 41175, 43128],
          backgroundColor     : 'tansparent',
          borderColor         : '#DE7777',
          pointBorderColor    : '#DE7777',
          pointBackgroundColor: '#DE7777',
          fill                : false
          // pointHoverBackgroundColor: '#DE7777',
          // pointHoverBorderColor    : '#DE7777'
        }]
      },
      options: {
        maintainAspectRatio: false,
        tooltips           : {
          mode     : mode,
          intersect: intersect
        },
        hover              : {
          mode     : mode,
          intersect: intersect
        },
        legend             : {
          display: false
        },
        scales             : {
          yAxes: [{
            // display: false,
            gridLines: {
              display      : true,
              lineWidth    : '4px',
              color        : 'rgba(0, 0, 0, .2)',
              zeroLineColor: 'transparent'
            },
            ticks    : $.extend({
              beginAtZero : true,
              suggestedMax: 200
            }, ticksStyle)
          }],
          xAxes: [{
            display  : true,
            gridLines: {
              display: false
            },
            ticks    : ticksStyle
          }]
        }
      }
    })
  })
</script>
