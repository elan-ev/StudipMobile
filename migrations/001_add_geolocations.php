<?
class AddGeolocations extends DBMigration {

    function up() {
      //$this->addProperty();
      //$this->connectProperties();
  }

  function down() {
      //$this->unconnectProperties();
      //$this->deleteProperty();
  }


  private static function getPropertyID()
  {
      return md5("geoLocation");
  }

  private function addProperty()
  {
      $query =
        "INSERT INTO resources_properties " .
        "SET options='', property_id=?, name='geoLocation', description='Geographical Location', type='text'";

      $stmt = DBManager::get()->prepare($query);
      $stmt->execute(array(self::getPropertyID()));
  }


  private function deleteProperty()
  {
      $query =
        "DELETE FROM resources_properties " .
        "WHERE property_id=?";

      $stmt = DBManager::get()->prepare($query);
      $stmt->execute(array(self::getPropertyID()));
  }

  private function connectProperties()
  {
      $data = $this->getData();
      $query = "";
      foreach ($data as $_resource) {
          list($id, $name, $lat, $lon) = $_resource;
          $resource = ResourceObject::Factory($id);
          if (!$resource->id) continue;
          $state = sprintf("%s-%s", $lat, $lon);
          #var_dump("connect:", $id, $state);
          $resource->storeProperty(self::getPropertyID(), $state);
      }
  }

  private function unconnectProperties()
  {
      $query = "DELETE FROM resources_objects_properties WHERE property_id=?";
      $stmt = DBManager::get()->prepare($query);
      $stmt->execute(array(self::getPropertyID()));
  }

  private function getData()
  {
      $content = file_get_contents(__FILE__, null, null,
                                   __COMPILER_HALT_OFFSET__);
      $lines = explode("\n", $content);
      return array_map(function ($line) {
              return str_getcsv($line);
          }, $lines);
  }
}

__halt_compiler();
"0dbf79e43e799fb397a3962ab4d042ee","01 Kolpingstraße 7 (Hörsaal- und Verfügungszentrum)","52.2704936","8.0479081"
"421c747101b281deda721ca651ae0573","02 Seminarstraße 19 A/B (HdL)","52.27151","8.04616"
"f256589f53fc99d3085bf47795a78529","04 Seminarstraße 33 (Titgemeyer Bauteil A+B)","52.27152","8.047614"
"426145b2a5ce18c709f9aefed0570adc","05 Seminarstraße 33 (Titgemeyer Bauteil C)","52.2712242","8.047709"
"bd6f6c77bd2b74cdce40ab1f62f763e4","08 Alte Münze 12","52.27273","8.04577"
"7ed51b2dca82eb50284dc5e4bac9592a","11 Neuer Graben 29/30 (Schloß-Hauptflügel)","52.27134","8.04421"
"5727fd7d46e37d906091fa50fedb3ff4","15 Seminarstraße 20 (EW)","52.2711406","8.0461403"
"9a25eaae7fc06d1467cc30feff5a506b","22 Heger-Tor-Wall 14","52.2725653","8.0399094"
"4617f6b6dd8c4998ee099724cea57223","31 Albrechtstraße 28 (AVZ)","52.28364","8.02553"
"15bd828f0e58e465f03ad222ba6b85fe","32 Barbarastraße 7 (Physik)","52.2846996","8.0251044"
"b2bed5d51d58124bb4948e5abf0bf9c9","35 Barbarastraße 11 (Biologie Bauteil C, D, E)","52.282535","8.0227099"
"2d931974e993a715dbf9e8c8d09de1b2","41 Neuer Graben 40 (ehem. Kreishaus)","52.2722421","8.0422868"
"f2ea0f2065b707b2b67958dc709847dd","54 Knollstraße 15 (LKH)","52.2841708","8.0500127"
"5e29d03b4859f34820ba87657932ef70","66 Barbarastr. 12 (Hörsaal und Institut Umweltsystemforschung)","52.28303","8.02282"
"0e2f9ede976139a9486e773b3b744096","03 Neuer Graben 19/21 (HdL)","52.27188","8.04589"
"a1a5aeecac711ccb9f1e4b670ce4441e","07 Alte Münze 10","52.27271","8.04591"
"9ae8c028610ce8c99463cb7023e6b7e6","09 Alte Münze 14-16 (Bibliothek-Altbau)","52.273411","8.0454539"
"4649a22a80b9b560ffd7b3d647f4a739","12 Neuer Graben 29/30 (Schloß-Westflügel)","52.2715876","8.0435886"
"128f2c7b33ca2e5a8e49eb4303b3f3b9","13 Neuer Graben 29/30 (Schloß-Ostflügel)","52.2715387","8.0447917"
"5988ef19326daa496d670d585ee41c60","14 Neuer Graben 29/30 (Schloß-Nordflügel)","52.27177","8.04431"
"40a9c3b57e84e87f95e7a63ec0ca1df9","17 Schloßstraße 4","52.2701893","8.0470414"
"4903a73ea34af36868b34afaa9168e8f","18 Schloßstraße 8","52.26999","8.04673"
"527f93605b836bcbe21323d0ea5b5d9d","20 Martinistraße 8","52.27207","8.03887"
"ac1d361702913bf50bc55173733d5953","21 Martinistraße 2-6","52.2722543","8.0396427"
"6e688ce05a363fd1178280bffea42ae0","25 Martinistraße 10","52.27205","8.03869"
"73635be6a019578b647982fb08e9889a","26 Katharinenstraße 24","52.2729937","8.0381651"
"9b18997a00ffa86fab9fac8afeff54a5","27 Martinistraße 12","52.27203","8.03849"
"6b9e0591c444fb18c863b654dd746608","28 Katharinenstraße 13/15","52.2728","8.03921"
"e99d33f81449f2b8dede661b3d394ba4","29 Rolandstraße 8","52.2740703","8.0372415"
"2af1894133a8ad68e7cd8c342385be4b","33 Barbarastraße 7 (Physik-Werkstätten)","52.28515","8.02432"
"ddc877b60e1ea2af26f633a713eef560","34 Barbarastraße 7 (Chemie)","52.28465","8.02432"
"3b70be5be7e24a47fbda6f041f7b96fa","36 Barbarastraße 11 (Biologie Bauteil B)","52.28264","8.02159"
"b6b15d24ecba096010d5252ce2b4e9a8","37 Barbarastraße 11 (Biologie Bauteil A)","52.2827","8.02074"
"c5d9cd57a63d1aa12a22bc25d9f741e5","38 Barbarastraße 11 (Bio. Versuchsgewächshaus)","52.28277","8.02027"
"f5ba0b0eb74c1ad74c887bbade0355c7","42 Heger-Tor-Wall 12","52.2724418","8.0411008"
"2da51c48e5ccdeebe1359397ad2c1f37","43 Heger-Tor-Wall 9","52.27275","8.04093"
"526cb02638f79f0c296c28ad64da19e9","45 Katharinenstraße 7","52.27296","8.04114"
"2559b85439e54fd70d55493ab4f5fd65","46 Katharinenstraße 5","52.272939","8.0414612"
"98a536ff96c9fc13f4ec3c61d699273c","47 Katharinenstraße 1-3 (IKK)","52.273","8.04182"
"b2ddf99a18fcbcf016e8d76beb7b7995","61 Albrechtstraße 29 (Verwaltung Bot. Garten)","52.28209","8.02682"
"d31c1a0ba76ab7aaf41e836fddfdb42d","62 Albrechtstraße 29 (Tischlerei)","52.2823","8.02707"
"9236d0d30a6d01ea8b8f817c03986737","63 Albrechtstraße 29 (Lager Bot. Garten)","52.28235","8.0277"
"5c035ba15b41fb9dc7798fbb6b057e62","64 Albrechtstraße 29 (Tropenhaus Bot. Garten)","52.28191","8.02819"
"a07870e0588c934c11fb3f4c6d5060e4","68 Artilleriestraße 34","52.28583","8.02049"
"6d79a3ee06e197231ed863f9ad6fb09c","70 Sedanstraße 115 (ehem. BKH)","52.290636","8.004866"
"02f724db6a88c09c1dde8baed01561ab","69 Albrechtstraße 28a","52.284201","8.0260337"
"2dcf179276555f4a7f6d37ab2cc82e8c","67  Barbarastr. 13 (Erweiterung Biologie)","52.2834638","8.0227742"
"36cc207c62b088f19060bfc56762ead9","19 ehem. AOK, Neuer Graben 27 (Dienstleistungszentrum für Studierende)","52.27166","8.04526"
"56b09eeee73581b05c09d0f927eef378","40 NeuerGraben 39 (ehem. Gewerkschaftshaus)","52.2719354","8.0419326"
"81a5d88a6daf1f5cc7b528abc66948f4","44 ELSI Süsterstr. 28","52.2694217","8.0483725"
"67bfc256c34bc6aaba736064dfec607b","91 Barbarastr. 22a","52.28565","8.02318"
"936a73d33107664a5424f5f749875815","92 Barbarastr. 22b","52.28545","8.02078"
"80f38bc685b5cb58d0df28854e5b7391","Gebäude NG der Fachhochschule, Neuer Graben 39","52.2719354","8.0419326"
"6d80845921e3bac866e3684b459484ab","49 An der Katharinenkirche 8a","52.27288","8.04226"
