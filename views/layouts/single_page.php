<? $this->set_layout("layouts/base") ?>

<?= $this->render_partial('layouts/_page') ?>

<?= isset($additional_pages) ? $additional_pages : "" ?>

<?= $this->render_partial("layouts/_side_menu") ?>
