<div data-role="navbar">
  <ul>
    <li>
      <a class="<?= $active === 'index' ? 'ui-btn-active' : '' ?>" href="<?= $controller->url_for("calendar/index") ?>">
        Stundenplan
      </a>
    </li>

    <li>
      <a class="<?= $active === 'kalender' ? 'ui-btn-active' : '' ?>" href="<?= $controller->url_for("calendar/kalender/".date("Y")."/".date("n")) ?>">
        Kalender
      </a>
    </li>
  </ul>
</div>
