<?php

class StudipMobile extends StudipPlugin implements SystemPlugin
{
    const DEFAULT_CONTROLLER = "quickdial";

    const DROPBOX_ENABLED = false;

    const AUTOMATIC_REDIRECT = true;

    public function __construct() {
        parent::__construct();

        if (self::AUTOMATIC_REDIRECT) {
            $this->redirectToMobile();
        }

        $mobile_url = PluginEngine::getLink($this, null, '', true);

        /*
        $top_navigation = new Navigation('Mobil', $mobile_url);
        $top_navigation->setImage($this->getPluginUrl() . '/public/images/header.png',
                                  array('title' => 'Zur mobilen Ansicht wechseln'));
        Navigation::insertItem('/studip_mobile', $top_navigation, 'start');*/

        $footer_navigation = clone $top_navigation;
        $footer_navigation->setTitle('Mobilansicht');
        Navigation::insertItem('/footer/studip_mobile', $footer_navigation, 'help');

        PageLayout::addStylesheet($this->getPluginUrl() . '/public/stylesheets/studip.css');
    }


    /**
     * This method dispatches and displays all actions. It uses the template
     * method design pattern, so you may want to implement the methods #route
     * and/or #display to adapt to your needs.
     *
     * @param  string  the part of the dispatch path, that were not consumed yet
     */
    function perform($unconsumed_path) {

        // set include path
        $inc_path = ini_get('include_path') . PATH_SEPARATOR . __DIR__ . '/vendor';
        ini_set('include_path', $inc_path);

        require 'lib/dispatcher.php';

        $trails_root = $this->getPluginPath();
        $dispatcher = new \Studip\Mobile\Dispatcher(
            $trails_root,
            rtrim(PluginEngine::getURL($this, null, ''), '/'),
            self::DEFAULT_CONTROLLER);
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    // enable nobody role by default
    static function onEnable($id)
    {
        RolePersistence::assignPluginRoles($id, array(7));
    }


    const DETECTION_REGEXP = '(function(a,b){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))window.location=b})(navigator.userAgent||navigator.vendor||window.opera,"%s?redirected");';

    const REDIRECTION_STOP_WORD = 'stopMobileRedirect';

    private function redirectToMobile()
    {
        // make sure to break out if asked for
        $this->handleStopWord();

        if (!$this->shouldRedirect()) {
            return;
        }

        // add mobile detection script from http://detectmobilebrowsers.com/
        $url = PluginEngine::getLink($this, null, '', true);
        $js = sprintf(self::DETECTION_REGEXP, $url);
        PageLayout::addHeadElement('script', array(), $js);
    }

    private function shouldRedirect()
    {
        // you shall not redirect an authorized user
        global $user;
        if (is_object($user) && !in_array($user->id, words('nobody form'))) {
            return false;
        }

        // you shall not redirect if asked to desist from it
        if (isset($_COOKIE[self::REDIRECTION_STOP_WORD])) {
            return false;
        }

        // you shall not redirect from plugins.php
        $script = basename($_SERVER['PHP_SELF'], '.php');
        if (in_array($script, words('api plugins')) ||
            ($script === 'dispatch' && strpos($_SERVER['PATH_INFO'], '/api') === 0))
        {
            return false;
        }

        return true;
    }

    private function handleStopWord()
    {
        # handle stop word
        if (Request::submitted(self::REDIRECTION_STOP_WORD)) {
            \NotificationCenter::postNotification('mobile.RedirectDidPreclude', $this);
            setcookie(self::REDIRECTION_STOP_WORD, 1);
            $_COOKIE[self::REDIRECTION_STOP_WORD] = 1;
        }
    }
}
