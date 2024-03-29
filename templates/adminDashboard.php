<div class="wrap">
  <h1>Geslib DASHBOARD</h1>
   <?php settings_errors(); ?>

  <ul class="nav nav-tabs">
    <li class="active"><a href="#tab-1">Acciones</a></li>
    <li><a href="#tab-2">Manage Settings</a></li>
    <!-- <li><a href="#tab-3">Files</a></li>
    <li><a href="#tab-4">Logs</a></li>
    <li><a href="#tab-5">Lines</a></li>
    <li><a href="#tab-6">Queues</a></li>
    <li><a href="#tab-7">Logger</a></li> -->
  </ul>

  <div class="tab-content">
    <div id="tab-1" class="tab-pane active">
      <?php include 'tab1_content.php'; ?>
    </div>
    <div id="tab-2" class="tab-pane">
      <?php include 'tab2_content.php'; ?>
    </div>
    <!-- <div id="tab-3" class="tab-pane">
      <?php //include 'tab3_content.php'; ?>
    </div>
    <div id="tab-4" class="tab-pane">
      <?php //include 'tab4_content.php'; ?>
    </div>
    <div id="tab-5" class="tab-pane">
      <?php //include 'tab5_content.php'; ?>
    </div>
    <div id="tab-6" class="tab-pane">
      <?php //include 'tab6_content.php'; ?>
    </div>
    <div id="tab-7" class="tab-pane">
      <?php //include 'tab7_content.php'; ?>
    </div> -->
  </div>
</div>