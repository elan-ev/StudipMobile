helpers = require './helpers.coffee'

template = _.template """
    <% time = timeString === "00:00-23:59" ? "ganztägig" : timeString %>

    <a href=#popup-<%- event.id %> data-rel=popup class=ui-icon-info>
      <span class=time><%= time %></span> <%- summary %>
    </a>

    <div data-role="popup" id="popup-<%- event.id %>"
         class="calendar-popup ui-content"
         style="min-width:240px;">

      <div class="time ui-bar ui-bar-d">
        <%= time %>:
      </div>

      <dl class=properties>
        <dt>
          Zusammenfassung:
        </dt>
        <dd>
          <%- event.summary %>
        </dd>

        <% if (event.description) { %>
        <dt>
          Beschreibung:
        </dt>
        <dd>
          <%- event.description %>
        </dd>
        <% } %>

        <% if (event.location) { %>
        <dt>
          Ort:
        </dt>
        <dd>
          <%- event.location %>
        </dd>
        <% } %>
      </dl>

      <% if (event.sem_id) { %>
        <a class="ui-btn ui-mini" href="<%- helpers.url_for("courses/show/" + event.sem_id) %>" role=button>
          Zur Veranstaltung
        </a>
      <% } %>

      <a href="#" data-rel="back" class="ui-btn-right ui-link ui-btn ui-btn-b ui-icon-delete ui-btn-icon-notext ui-shadow" role="button">Schließen</a>
    </div
     """

listItemView = (data) ->
  template _.extend data, helpers: helpers


module.exports = listItemView
