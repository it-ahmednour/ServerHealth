<?php
class SystemHealth {
    public $Service = NULL;
    public $_uptime;
    public $_cpuload;
    public $_freemem;
    public $_freehdd;

    public function __construct() {
        $this->GetSystemHealth();
    }

    # Get System Health ##
    private function GetSystemHealth() {
        $this->_uptime = $this->ServerUptime();
        $this->_cpuload = $this->ServerCPULoad();
        $this->_freemem = $this->ServerFreeMemory();
        $this->_freehdd = $this->ServerFreeHDDSpace();
        return $this;
    }

    ## Get Server Uptime ##
    private function ServerUptime() {
        $file_name = "/proc/uptime";
        $fopen_file = fopen($file_name, 'r');
        $buffer = explode(' ', fgets($fopen_file, 4096));
        fclose($fopen_file);
        $sys_ticks = trim($buffer[0]);
        $min = $sys_ticks / 60;
        $hours = $min / 60;
        $days = floor($hours / 24);
        $hours = floor($hours - ($days * 24));
        $min = floor($min - ($days * 60 * 24) - ($hours * 60));
        $uptime = "";
        if ($days != 0) {
            $uptime = "$days" . "d ";
        }
        if ($hours != 0) {
            $uptime .= "$hours" . "h ";
        }
        if ($min > 1 || $min == 0)
            $uptime .= "$min" . "m ";
        
        return $uptime;
    }

    ## Get Server CPU Load Avrage ##
    private function ServerCPULoad() {
        $load = sys_getloadavg();
	    $cpuavg = $load[0] . '<sup style="font-size: 20px">%</sup>';
        return $cpuavg;
    }

    ## Get Server Free Memory ##
    private function ServerFreeMemory() {
        $file_name = "/proc/meminfo";
        $mem_array = array();
        $buffer = file($file_name);
        while (list($key, $value) = each($buffer)) {
            if (strpos($value, ':') !== false) {
                $match_line = explode(':', $value);
                $match_value = explode(' ', trim($match_line[1]));
                if (is_numeric($match_value[0])) {
                    $mem_array[trim($match_line[0])] = trim($match_value[0]);
                }
            }
        }
        $freememory = $this->ReadableMemSize($mem_array['MemFree']);
        return $freememory;
    }
    ## Get HDD Free Space  ##
    private function ServerFreeHDDSpace() {
        $space = disk_free_space("/");
        $freespace = $this->RadableHDDSize($space);
        return $freespace;
    }
    
    // Check Service Status
    public function CheckService($Service) {
        if ($Service) {
            system("pgrep ".escapeshellarg($Service)." >/dev/null 2>&1", $ret_service);
            if ($ret_service == 0) {
                return 1;
            }else{
                return 0;
            }
        }else{
            return 0;
        }
    }

    ## Readable Memory Size ##
    private function ReadableMemSize($value) {
        return round($value / 1024) . " MB\n";
    }

    ## Readable HDD Size ##
    private function RadableHDDSize($size){
        // Gigabytes
        if ( $size > 1073741824 ){
                $ret = $size / 1073741824;
                $ret = round($ret,2)." GB";
                return $ret;
        }
        // Megabytes
        if ( $size > 1048576 ){
                $ret = $size / 1048576;
                $ret = round($ret,2)." Mb";
                return $ret;
        }
        // Kilobytes
        if ($size > 1024 ){
                $ret = $size / 1024;
                $ret = round($ret,2)." Kb";
                return $ret;
        }
        // Bytes
        if ( ($size != "") && ($size <= 1024 ) ){
                $ret = $size." B";
                return $ret;
        }
    }
}
?>
