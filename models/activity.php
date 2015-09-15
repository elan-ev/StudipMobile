<?php

namespace Studip\Mobile;

/**
 *    Activity Class for newest informations
 *    @author Elmar Ludwig - elmar@uos.de
 *    @author Nils Bussmann - nbussman@uos.de
 */
class Activity {

    static function findAllByUser($user_id, $range = null, $days = 30)
    {
        # force an absolute URL
        \URLHelper::setBaseUrl($GLOBALS['ABSOLUTE_URI_STUDIP']);

        $items = self::get_activities($user_id, $range, $days);

        # reset to the default set in plugins.php
        \URLHelper::setBaseUrl($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']);

        return $items;
    }

    /**
     * Get all activities for this user as an array.
     */
    private static function get_activities($user_id, $range, $days)
    {

        $db = \DBManager::get();
        $now = time();
        $chdate = (is_finite($days) && $days > 0) ? $now - 24 * 60 * 60 * $days : 0;
        $items = array();
        $limit = " LIMIT 100";

        if ($range === 'user') {
            $sem_filter = "seminar_user.user_id = '$user_id' AND auth_user_md5.user_id = '$user_id'";
            $inst_filter = "user_inst.user_id = '$user_id' AND auth_user_md5.user_id = '$user_id'";
        } else if (isset($range)) {
            $sem_filter = "seminar_user.user_id = '$user_id' AND Seminar_id = '$range'";
            $inst_filter = "user_inst.user_id = '$user_id' AND Institut_id = '$range'";
        } else {
            $sem_filter = "seminar_user.user_id = '$user_id'";
            $inst_filter = "user_inst.user_id = '$user_id'";
        }

        $sem_fields = 'auth_user_md5.user_id AS author_id, auth_user_md5.Vorname, auth_user_md5.Nachname, seminare.Name, auth_user_md5.username';
        $inst_fields = 'auth_user_md5.user_id AS author_id, auth_user_md5.Vorname, auth_user_md5.Nachname, Institute.Name, auth_user_md5.username';
        $user_fields = 'auth_user_md5.user_id AS author_id, auth_user_md5.Vorname, auth_user_md5.Nachname, auth_user_md5.username';

        // news

        $sql = "SELECT news.*, news_range.range_id, $sem_fields
                FROM news
                JOIN news_range USING (news_id)
                JOIN auth_user_md5 USING (user_id)
                JOIN seminar_user ON (range_id = Seminar_id)
                JOIN seminare USING (Seminar_id)
                WHERE $sem_filter AND news.date BETWEEN $chdate AND $now $limit";

        $result = $db->query($sql);

        foreach ($result as $row) {
            $items[] = array(
                'id' => $row['news_id'],
                'title' => 'Ankündigung: ' . $row['topic'],
                'author' => $row['Vorname'] . ' ' . $row['Nachname'],
                'author_id' => $row['author_id'],
                'link' => htmlReady("news/show/" . $row['news_id']),
                'updated' => max($row['date'], $row['chdate']),
                'summary' => sprintf('%s %s hat in der Veranstaltung "%s" die Ankündigung "%s" eingestellt.',
                    $row['Vorname'], $row['Nachname'], $row['Name'], $row['topic']),
                'content' => $row['body'],
                'username' => $row['username'],
                'item_name' => $row['topic'],
                'range_name' => $row['Name'],
                'category' => 'news'
            );
        }

        $sql = "SELECT news.*, news_range.range_id, $inst_fields
                FROM news
                JOIN news_range USING (news_id)
                JOIN auth_user_md5 USING (user_id)
                JOIN user_inst ON (range_id = Institut_id)
                JOIN Institute USING (Institut_id)
                WHERE $inst_filter AND news.date BETWEEN $chdate AND $now $limit";

        $result = $db->query($sql);

        foreach ($result as $row) {
            $items[] = array(
                'id' => $row['news_id'],
                'title' => 'Ankündigung: ' . $row['topic'],
                'author' => $row['Vorname'] . ' ' . $row['Nachname'],
                'author_id' => $row['author_id'],
                'link' => htmlReady("news/show/" . $row['news_id']),
                'updated' => max($row['date'], $row['chdate']),
                'summary' => sprintf('%s %s hat in der Einrichtung "%s" die Ankündigung "%s" eingestellt.',
                    $row['Vorname'], $row['Nachname'], $row['Name'], $row['topic']),
                'content' => $row['body'],
                'username' => $row['username'],
                'item_name' => $row['topic'],
                'range_name' => $row['Name'],
                'category' => 'news'
            );
        }

        // votings

        if ($range === 'user') {
            $sql = "SELECT vote.*, $user_fields
                    FROM vote
                    JOIN auth_user_md5 ON (author_id = user_id)
                    WHERE range_id = '$user_id' AND vote.startdate BETWEEN $chdate AND $now $limit";

            $result = $db->query($sql);

            foreach ($result as $row) {
                $items[] = array(
                    'id' => $row['vote_id'],
                    'title' => 'Umfrage: ' . $row['title'],
                    'author' => $row['Vorname'] . ' ' . $row['Nachname'],
                    'author_id' => $row['author_id'],
                    'link' => \URLHelper::getLink('about.php#openvote',
                        array('username' => $row['username'], 'voteopenID' => $row['vote_id'])),
                    'updated' => max($row['startdate'], $row['chdate']),
                    'summary' => sprintf('%s %s hat die persönliche Umfrage "%s" gestartet.',
                        $row['Vorname'], $row['Nachname'], $row['title']),
                    'content' => $row['question'],
                    'username' => $row['username'],
                    'item_name' => $row['title'],
                    'category' => 'votings'
                );
            }
        }

        $sql = "SELECT vote.*, $sem_fields
                FROM vote
                JOIN auth_user_md5 ON (author_id = user_id)
                JOIN seminar_user ON (range_id = Seminar_id)
                JOIN seminare USING (Seminar_id)
                WHERE $sem_filter AND vote.startdate BETWEEN $chdate AND $now $limit";

        $result = $db->query($sql);

        foreach ($result as $row) {
            $items[] = array(
                'id' => $row['vote_id'],
                'title' => 'Umfrage: ' . $row['title'],
                'author' => $row['Vorname'] . ' ' . $row['Nachname'],
                'author_id' => $row['author_id'],
                'link' => \URLHelper::getLink('seminar_main.php#openvote',
                    array('cid' => $row['range_id'], 'voteopenID' => $row['vote_id'])),
                'updated' => max($row['startdate'], $row['chdate']),
                'summary' => sprintf('%s %s hat in der Veranstaltung "%s" die Umfrage "%s" gestartet.',
                    $row['Vorname'], $row['Nachname'], $row['Name'], $row['title']),
                'content' => $row['question'],
                'username' => $row['username'],
                'item_name' => $row['title'],
                'range_name' => $row['Name'],
                'category' => 'votings'
            );
        }

        $sql = "SELECT vote.*, $inst_fields
                FROM vote
                JOIN auth_user_md5 ON (author_id = user_id)
                JOIN user_inst ON (range_id = Institut_id)
                JOIN Institute USING (Institut_id)
                WHERE $inst_filter AND vote.startdate BETWEEN $chdate AND $now $limit";

        $result = $db->query($sql);

        foreach ($result as $row) {
            $items[] = array(
                'id' => $row['vote_id'],
                'title' => 'Umfrage: ' . $row['title'],
                'author' => $row['Vorname'] . ' ' . $row['Nachname'],
                'author_id' => $row['author_id'],
                'link' => \URLHelper::getLink('institut_main.php#openvote',
                    array('cid' => $row['range_id'], 'voteopenID' => $row['vote_id'])),
                'updated' => max($row['startdate'], $row['chdate']),
                'summary' => sprintf('%s %s hat in der Einrichtung "%s" die Umfrage "%s" gestartet.',
                    $row['Vorname'], $row['Nachname'], $row['Name'], $row['title']),
                'content' => $row['question'],
                'username' => $row['username'],
                'item_name' => $row['title'],
                'range_name' => $row['Name'],
                'category' => 'votings'
            );
        }

        // surveys

        if ($range === 'user') {
            $sql = "SELECT eval.*, $user_fields
                    FROM eval
                    JOIN eval_range USING (eval_id)
                    JOIN auth_user_md5 ON (author_id = user_id)
                    WHERE range_id = '$user_id' AND eval.startdate BETWEEN $chdate AND $now $limit";

            $result = $db->query($sql);

            foreach ($result as $row) {
                $items[] = array(
                    'id' => $row['eval_id'],
                    'title' => 'Evaluation: ' . $row['title'],
                    'author' => $row['Vorname'] . ' ' . $row['Nachname'],
                    'author_id' => $row['author_id'],
                    'link' => \URLHelper::getLink('about.php#openvote',
                        array('username' => $row['username'], 'voteopenID' => $row['eval_id'])),
                    'updated' => max($row['startdate'], $row['chdate']),
                    'summary' => sprintf('%s %s hat die persönliche Evaluation "%s" gestartet.',
                        $row['Vorname'], $row['Nachname'], $row['title']),
                    'content' => $row['text'],
                    'username' => $row['username'],
                'item_name' => $row['title'],
                    'category' => 'surveys'
                );
            }
        }

        $sql = "SELECT eval.*, $sem_fields
                FROM eval
                JOIN eval_range USING (eval_id)
                JOIN auth_user_md5 ON (author_id = user_id)
                JOIN seminar_user ON (range_id = Seminar_id)
                JOIN seminare USING (Seminar_id)
                WHERE $sem_filter AND eval.startdate BETWEEN $chdate AND $now $limit";

        $result = $db->query($sql);

        foreach ($result as $row) {
            $items[] = array(
                'id' => $row['eval_id'],
                'title' => 'Evaluation: ' . $row['title'],
                'author' => $row['Vorname'] . ' ' . $row['Nachname'],
                'author_id' => $row['author_id'],
                'link' => \URLHelper::getLink('seminar_main.php#openvote',
                    array('cid' => $row['range_id'], 'voteopenID' => $row['eval_id'])),
                'updated' => max($row['startdate'], $row['chdate']),
                'summary' => sprintf('%s %s hat in der Veranstaltung "%s" die Evaluation "%s" gestartet.',
                    $row['Vorname'], $row['Nachname'], $row['Name'], $row['title']),
                'content' => $row['text'],
                'username' => $row['username'],
                'item_name' => $row['title'],
                'range_name' => $row['Name'],
                'category' => 'surveys'
            );
        }

        $sql = "SELECT eval.*, $inst_fields
                FROM eval
                JOIN eval_range USING (eval_id)
                JOIN auth_user_md5 ON (author_id = user_id)
                JOIN user_inst ON (range_id = Institut_id)
                JOIN Institute USING (Institut_id)
                WHERE $inst_filter AND eval.startdate BETWEEN $chdate AND $now $limit";

        $result = $db->query($sql);

        foreach ($result as $row) {
            $items[] = array(
                'id' => $row['eval_id'],
                'title' => 'Evaluation: ' . $row['title'],
                'author' => $row['Vorname'] . ' ' . $row['Nachname'],
                'author_id' => $row['author_id'],
                'link' => \URLHelper::getLink('institut_main.php#openvote',
                    array('cid' => $row['range_id'], 'voteopenID' => $row['eval_id'])),
                'updated' => max($row['startdate'], $row['chdate']),
                'summary' => sprintf('%s %s hat in der Einrichtung "%s" die Evaluation "%s" gestartet.',
                    $row['Vorname'], $row['Nachname'], $row['Name'], $row['title']),
                'content' => $row['text'],
                'username' => $row['username'],
                'item_name' => $row['title'],
                'range_name' => $row['Name'],
                'category' => 'surveys'
            );
        }


        // activity providing plugins
        $plugin_items = \PluginEngine::sendMessage(
            'ActivityProvider',
            'getActivities',
            $user_id, $range, $days);

        foreach ($plugin_items as $array) {
            $items = array_merge($items, $array);
        }

        // get content-elements from all modules and plugins
        if (isset($range)) {
            $query = "SELECT seminare.* FROM seminar_user
                      LEFT JOIN seminare USING (Seminar_id)
                      WHERE user_id = :user_id AND Seminar_id = :seminar_id";
        } else {
            $query = "SELECT seminare.* FROM seminar_user
                      LEFT JOIN seminare USING (Seminar_id)
                      WHERE user_id = :user_id";
        }
        $stmt = \DBManager::get()->prepare($query);
        $stmt->execute(array('user_id' => $user_id, 'seminar_id' => $range));

        // 'forum participants documents news scm schedule wiki vote literature elearning_interface'
        $module_slots = words('forum documents scm wiki');

        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $seminar) {
            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$seminar['status']]['class']] ?: \SemClass::getDefaultSemClass();
            foreach ($module_slots as $slot) {
                if ($module = $sem_class->getModule($slot)) {
                    $items = array_merge($items, self::getNotificationObjects($sem_class, $module, $slot, $seminar['Seminar_id'], $chdate, $user_id));
                }
            }

            // workaround for v2.5 CoreForum that does not implement
            // a working #getNotificationObjects
            if (version_compare($GLOBALS['SOFTWARE_VERSION'], '3.0') < 0) {
                $items = array_merge(
                    $items,
                    self::workaroundGetNotificationObjectsFromForum($user_id, $seminar['Seminar_id'], $chdate));
            }
        }

        $stmt = \DBManager::get()->prepare("SELECT Institute.*
            FROM user_inst
            LEFT JOIN Institute USING (Institut_id)
            WHERE user_id = ?");
        $stmt->execute(array($user_id));


        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $institute) {
            foreach ($module_slots as $slot) {
                $class = 'Core' . $slot;
                if (class_exists($class)) {
                    $module = new $class;
                    $items = array_merge($items, self::getNotificationObjects($sem_class, $module, $slot, $institute['Institut_id'], $chdate, $user_id));
                }
            }

            // workaround for v2.5 CoreForum that does not implement
            // a working #getNotificationObjects
            if (version_compare($GLOBALS['SOFTWARE_VERSION'], '3.0') < 0) {
                $items = array_merge(
                    $items,
                    self::workaroundGetNotificationObjectsFromForum($user_id, $institute['Institut_id'], $chdate));
            }
        }

        // sort everything

        usort($items, function ($a, $b) { return $b["updated"] - $a["updated"];});
        $items = array_slice($items, 0, 100);

        return $items;
    }


    private function getNotificationObjects($sem_class, $module, $slot, $id, $chdate, $user_id)
    {
        $items = array();

        $notifications = $module->getNotificationObjects($id, $chdate, $user_id);

        if ($notifications) {
            foreach ($notifications as $ce) {

                $json = studip_utf8decode(json_decode($ce->toJSON(), TRUE));

                $url = $json['url'];

                switch ($slot) {

                case 'documents':
                    if (preg_match('/folder.php/', $url, $matches)) {
                        $url = 'courses/list_files/'.$id;
                    }
                    break;
                }


                $items[] = array(
                    'title'     => $json['title'],
                    'author'    => $json['creator'],
                    'author_id' => $json['creatorid'],
                    'link'      => $url,
                    'updated'   => $json['date'],
                    'summary'   => $json['summary'],
                    'content'   => $json['content'],
                    'category'  => $slot
                );
            }
        }

        return $items;
    }

    private function workaroundGetNotificationObjectsFromForum($user_id, $course_id, $since)
    {
        $contents = array();

        if (\ForumPerm::has('search', $course_id, $user_id)) {

            $postings = self::forumGetLatestSince($course_id, $since);

            foreach ($postings as $post) {
                $obj = get_object_name($course_id, 'sem');
                $summary = sprintf(_('%s hat im Forum der Veranstaltung "%s" einen Forenbeitrag verfasst.'),
                                   get_fullname($post['user_id']),
                                   $obj['name']
                );

                $contents[] = array(
                    'id'        => $post['topic_id'],
                    'title'     => _('Forum: ') . $obj['name'],
                    'author'    => $post['author'],
                    'author_id' => $post['user_id'],
                    'link'      => \PluginEngine::getURL('coreforum', array(), 'index/index/' . $post['topic_id'] .'?cid='. $course_id .'#'. $post['topic_id']),
                    'updated'   => $post['mkdate'],
                    'summary'   => $summary,
                    'content'   => formatReady($post['content']),
                    'category'  => 'forum'
                );
            }
        }

        return $contents;
    }

    private static function forumGetLatestSince($parent_id, $since)
    {
        $constraint = \ForumEntry::getConstraints($parent_id);
        $stmt = \DBManager::get()->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM forum_entries WHERE lft > ? AND rgt < ? AND seminar_id = ? AND mkdate >= ? ORDER BY name ASC");
        $stmt->execute(array($constraint['lft'], $constraint['rgt'], $constraint['seminar_id'], $since));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    const SMART_ACTIVITIES_THRESHOLD = 3;

    public static function getSmartActivities($user, $course = null, $days = 1)
    {
        $activities = self::findAllByUser($user->id, $course->id, $days);

        if (is_finite($days) && sizeof($activities) < self::SMART_ACTIVITIES_THRESHOLD) {
            return self::getSmartActivities($user, $course, self::getNextInterval($days));
        }

        return array($days, $activities);
    }


    private static $intervals = array(1, 7, 30, 365);

    public static function getNextInterval($days)
    {
        $next = NAN;
        foreach (self::$intervals as $interval) {
            if ($interval > $days) {
                $next = $interval;
                break;
            }
        }

        return $next;
    }

}
