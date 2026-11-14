<?= $this->include('layouts/partials/header') ?>
<?= $this->include('layouts/partials/sidebar_ekas') ?>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
  <div class="body-wrapper">
    <?= $this->include('layouts/partials/navbar') ?>
    <div class="container-fluid" style="max-width: 100%">
      <?= $this->renderSection('content') ?>
    </div>
  </div>
</div>

<?= $this->renderSection('script') ?>
<?= $this->include('layouts/partials/footer') ?>