<?php

require_once 'Converter.php';

/**
 * Class documentation
 */
class CartoDB {

    const TRACK_TABLE = 'tracks';
    const TRACK_TO_TAXONOMY = 'tracktotaxonomy';
    const TRACK_VEHICLE = 'vehicle';

    private $_user;
    private $_apiKey;
    private $log;
    private $converter;

    public function __construct($user, $apiKey) {
        $this->_apiKey = $apiKey;
        $this->_user = $user;
        $this->converter = new Converter();
        $this->log = KLogger::instance('/tmp');
    }
    public function updatePost($post){
        $this->deleteAttachments($post);
        $this->insertAttachments($post);
    }
    public function insertAttachments($post) {
        $types = get_the_terms($post, 'listing_type');
        $locations = get_the_terms($post, 'listing_location');
        $taxonomies = array();
        if($types){
            $taxonomies = $types;
        }
        if($locations){
            $taxonomies = array_merge($taxonomies, $locations);
        }
        $attachments = new Attachments('tracks', $post->ID);
        if ($attachments->exist()) {
            //Make sure to convert it prior to insert
            while ($attachments->get()) {
                //Check if start flag is set
                $end = $attachments->field('endflag');
                $vehicle = $attachments->field(self::TRACK_VEHICLE);
                $attachmentFile = get_attached_file($attachments->id());
                $kml_file = $this->converter->getConverted($attachmentFile);
                if ($kml_file) {
                    $xml = simplexml_load_file($kml_file);
                    $this->insertTracks($post, $xml, $attachments->id(), $vehicle );
                    //Remove KML afterwards -> no longer needed.
                    unlink($kml_file);
                    //Add the start point information to the post meta-data
                    if($end != 'false'){
                        $startPoint = $this->getEnd($attachments->id());
                        $_POST['et_listing_lat'] = $startPoint->coordinates[1];
                        $_POST['et_listing_lng'] = $startPoint->coordinates[0];
                        var_dump($startPoint);                       
                    }
                }
            }
            //Add the taxonomies
            foreach($taxonomies as $taxonomy){
                $this->execute("INSERT INTO ".self::TRACK_TO_TAXONOMY." (postid, taxid) VALUES (".$post->ID.", ".$taxonomy->term_taxonomy_id.")");
            }
        }
    }
    public function getEnd($attachmentId){
        $result = $this->execute('SELECT ST_AsGeoJSON(ST_EndPoint(the_geom)) as start FROM '.self::TRACK_TABLE.' WHERE attachmentid = '.$attachmentId);
        return json_decode($result->rows[0]->start);
    }
    private function insertTracks($post, $xml, $attachmentId, $vehicle){
        //First extract all tracks one by one
        $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');
        foreach($xml->xpath("//kml:LineString") as $kmlTrack){
            //First insert the path to the track table
            $this->execute("INSERT INTO ".self::TRACK_TABLE." (the_geom,attachmentid,postid,tracktype) VALUES (ST_Force_2D(ST_GeomFromKML('" . $kmlTrack->asXml() . "')),".$attachmentId.",".$post->ID.",'".$vehicle."' )");
        }
        
    }

    public function deleteAttachments($post) {
        $this->execute("DELETE FROM " . self::TRACK_TABLE . " WHERE postid = " . $post->ID);
        $this->execute("DELETE FROM " . self::TRACK_TO_TAXONOMY . " WHERE postid = " . $post->ID);
    }

    private function execute($sql) {
        // Initializing curl
        $ch = curl_init("https://" . $this->_user . ".cartodb.com/api/v2/sql");
        $query = http_build_query(array('q' => $sql, 'api_key' => $this->_apiKey));
        // Configuring curl options
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result_not_parsed = curl_exec($ch);
        $this->log->logWarn($result_not_parsed);
//----------------
        return json_decode($result_not_parsed);
    }

}

