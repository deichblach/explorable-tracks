<?php

class Converter {
    private $log;
    private $fileFormats;
    public function __construct() {
        $this->log = KLogger::instance('/tmp');
        $this->fileFormats = array('.fit'=>'garmin_fit', '.gpx'=>'gpx', '.kml'=>'kml');
    }
    /**
     * Converts the provided file that can be retrieved from the passed url to an optimized kml.
     * 
     * Currently GPX and FIT files are supported.
     * 
     * @param type $unconvertedWordpressUrl
     * @return null, was not successful |string, the local path to the converted file.
     */
    public function getConverted($attachment_file) {       
        $f = escapeshellarg($attachment_file);
        $suffix = $this->getFileFormat($attachment_file);
        $targetFile = dirname($attachment_file) . '/' . basename($attachment_file,$suffix) . '.kml';
        $t = escapeshellarg($targetFile);
        $this->log->logWarn('Input: ' . $attachment_file . ' Output: ' . $targetFile);
        $shellCmd = 'gpsbabel -i '.$this->fileFormats[$suffix].' -f ' . $f . ' -x simplify,crosstrack,error=0.01k -o kml,points=0 -F ' . $t;
        $output = trim(shell_exec($shellCmd));
        $this->log->logError($output);
        if ($output !== '') {
            trigger_error("Output is " . $output);
            return null;
        } else {
            return $targetFile;
        }
    }
    public function getFileFormat($file){
        $fileName = basename($file);
        foreach($this->fileFormats as $suffix => $babel)
        {
            if(strpos($fileName, $suffix)){
                return $suffix;
            }
        }
        return false;
    }

}
