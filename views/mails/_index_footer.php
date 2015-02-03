<div data-role="footer" data-id="mails-index-footer" data-position="fixed">
  <div data-role="navbar">
    <ul>
      <li><a class="<?= $selected === 'inbox'   ? 'ui-btn-active' : '' ?>" data-icon="arrow-r" href="<?= $controller->url_for("mails/inbox")       ?>">Eingang</a></li>
      <li><a class="<?= $selected === 'outbox'  ? 'ui-btn-active' : '' ?>" data-icon="arrow-l" href="<?= $controller->url_for("mails/outbox") ?>">Ausgang</a></li>
      <li><a class="<?= $selected === 'compose' ? 'ui-btn-active' : '' ?>" data-icon="edit"    href="<?= $controller->url_for("mails/compose")     ?>">Schreiben</a></li>
    </ul>
  </div>
</div>
