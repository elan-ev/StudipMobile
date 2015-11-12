<?php
namespace Studip\Mobile;

require_once $this->trails_root .'/models/helper.php';
require_once $this->trails_root .'/lib/delegating_php_template.php';
require_once $this->trails_root .'/helpers/format.php';
require_once $this->trails_root .'/helpers/jqm.php';

/**
 *    global usefull stuff
 *    @author Marcus Lunzenauer - mlunzena@uos.de
 *    @author André Klaßen - aklassen@uos.de
 *    @author Nils Bussmann - nbussman@uos.de
 */
class Controller extends \Trails_Controller
{
    static $view_helper_classes = array("Studip\Mobile\FormatHelper", "Studip\Mobile\JQMHelper");

    /**
     * Applikationsübergreifender before_filter mit Trick:
     *
     * Controller-Methoden, die mit "before" anfangen werden in
     * Quellcode-Reihenfolge als weitere before_filter ausgeführt.
     * Geben diese FALSE zurück, bricht Trails genau wie beim normalen
     * before_filter ab.
     */
    function before_filter(&$action, &$args)
    {
        $this->plugin_path = \URLHelper::getURL($this->dispatcher->plugin->getPluginPath());
        list($this->plugin_path) = explode("?cid=",$this->plugin_path);

        // notify on mobile trails action
        $klass = substr(get_called_class(), 0, -10);
        $name = sprintf('mobile.performed.%s_%s', $klass, $action);
        \NotificationCenter::postNotification($name, $this);

        $this->flash = \Trails_Flash::instance();

        // notify on automatic redirect
        if (\Request::submitted("redirected")) {
            \NotificationCenter::postNotification("mobile.ClientDidRedirect", $this);
        }
        $this->response->add_header('Content-Type', 'text/html;charset=utf-8');
    }

    /**
     * Return currently logged in user
     */
    function currentUser()
    {
        global $user;

        if (!is_object($user) || $user->id == 'nobody') {
            return null;
        }

        return $user;
    }


    /**
     * Helper method to ensure a logged in user
     */
    function requireUser()
    {
        if (!$this->currentUser()) {
            # TODO (mlunzena): store_location
            // $this->flash["notice"] = "You must be logged in to access this page";
            \NotificationCenter::postNotification('mobile.SessionIsMissing', $this);
            $this->redirect("session/new");
            return FALSE;
        }
    }

    function render_json($data)
    {
        $this->response->add_header('Content-Type', 'application/json');
        $this->render_text(json_encode($this->filter_utf8($data)));
    }


    function filter_utf8($data)
    {
        // array-artiges wird rekursiv durchlaufen
        if (is_array($data) || $data instanceof \Traversable) {
            $new_data = array();
            foreach ($data as $key => $value) {
                $key = studip_utf8encode((string) $key);
                $new_data[$key] = self::filter_utf8($value);
            }
            return $new_data;
        }

        // string-artiges wird an die nicht-rekursive Variante übergeben
        else if (is_string($data) || is_callable(array($data, '__toString'))) {
            return studip_utf8encode((string) $data);
        }

        // skalare Werte und `null` wird so durchgeschleift
        elseif (is_null($data) || is_scalar($data)) {
            return $data;
        }

        // alles andere ist ungültig
        throw new \InvalidArgumentException();
    }

    function url_for($to)
    {
        if (Helper::isExternalLink($to)) {
            return $to;
        }

        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return \PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }

    # ignore namespace of controllers
    function get_default_template($action)
    {
        $class = array_pop(explode('\\', get_class($this)));
        $controller_name =
            \Trails_Inflector::underscore(substr($class, 0, -10));
        return $controller_name.'/'.$action;
    }


    # use my own template class
    function get_template_factory()
    {
        $factory = parent::get_template_factory();
        if (isset(static::$view_helper_classes)) {
            $options = array(
                'view_helper_classes' => static::$view_helper_classes
            );
            $factory->add_handler('php', 'Studip\Mobile\DelegatingPhpTemplate', $options);
        }
        return $factory;
    }

    # send back an error
    function error($code, $reason = null)
    {
        throw new \Trails_Exception($code, $reason);
    }
}
