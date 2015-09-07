<?php

namespace Studip\Mobile;

// TODO (nbussman):
// !!! totaler pfad zum bild -> ändern
// require evtl nicht möglich!
require_once($STUDIP_BASE_PATH."lib/user_visible.inc.php");
if (version_compare($GLOBALS['SOFTWARE_VERSION'], "3.1", '<=')) {
    require_once($STUDIP_BASE_PATH."lib/classes/Institute.class.php");
}

class Profile {

    static function findUser($id)
    {
        if (get_visibility_by_id($id)) {

            $user_data = \User::find($id);

            // pre 2.5 check
            if (method_exists($user_data, 'getData')) {
                $user_data = $user_data->getData();
            }

            if ($user_data["visible"] == "no" || $user_data["visible"] == "never") {
                return null;
            }

            $inst_fields = "Institut_id, user_id, sprechzeiten, raum, Telefon, Fax, visible";
            $query = "SELECT $inst_fields FROM `user_inst` WHERE user_inst.user_id = ? AND user_inst.externdefault='1'";
            $stmt = \DBManager::get()->prepare($query);
            $stmt->execute(array($id));
            $user_inst = $stmt->fetch();

            if (!empty($user_inst["Institut_id"])) {
                $inst = \Institute::find($user_inst["Institut_id"]);

                $institute = array(
                    "inst_name"    => $inst->name,
                    "inst_strasse" => $inst->strasse,
                    "inst_url"     => $inst->url,
                    "inst_plz"     => $inst->plz,
                    "inst_telefon" => $inst->telefon,
                    "inst_email"   => $inst->email,
                    "inst_fax"     => $inst->fax
                );

            } else {
                $user_inst = null;
            }

            return array(
                "user_data" => $user_data,
                "user_inst" => $user_inst,
                "inst_info" => $institute
            );
        }
        return null;
    }
}
