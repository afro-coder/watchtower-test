<?php

namespace server;

require __DIR__ . '/vendor/autoload.php';

use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;

/**
 *
 */
 // class ServerException extends Exception {}

/*
Implement the following
Free memory
List of processes.
Nproc
Cpu Usage
Disk Usage
Inode Usage
Reading Mail logs
Filter logs

*/

//Refactor
// Server class
// Connection class

class Server
{
protected $name;

public function __construct(string $servername)
{
  $this->name=$servername;
}


  //Helper function for ssh
public function initPublicKeyConnection(string $username,string $address,int $port=22,$secret=false)
  {
    $ssh=new SSH2($address,$port);
    $key = new RSA();
    $key->loadKey(file_get_contents('test_rsa'),$secret);
    // print_r($ssh);
    if (!$ssh->login($username, $key)) {
      exit(json_encode(array("message"=>'Login Failed')));
    }
    return $ssh;

  }

  public function initPasswordConnection(string $username,string $address,int $port=22)
  {
    $ssh = new SSH2($this->address);
    if (!$ssh->login($username, $password)) {
      throw new \Exception("Error while Authenticating", 1);
    }
  }

protected function processUptime($data)
{

    $data_exp=explode(",",$data);
    $load_avg=explode("average:",$data);
    $uptime=explode("up",$data_exp[0]);

    return json_encode(array("uptime"=>trim($uptime[1]),"load_average"=>trim($load_avg[1])));
}
public function Uptime($connection=null)
{
    $data=null;
    if(is_object($connection))
    {
      $data=$connection->exec("uptime");
    }
    if(is_null($connection))
    {
      $data=shell_exec("uptime");

    }
    return $this->processUptime($data);
}

protected function ProcessDiskUsage($data)
{
  if(isset($data))
  {
    $d=explode("\n",$data);

    $table=array();

    foreach ($d as $lines) {
      $line=explode("\t", $lines);
      if(count($line) >= 2)
      {
        array_push($table,array("size"=>$line[0],"path"=>$line[1]));
      }

  }
  if(sizeof($table) > 0)
  {
    return json_encode($table);
  }
  else {
    return json_encode(array("message"=>"Unable to load data, try again."));
  }

  }
  else{
    return json_encode($data);
  }
}
public function DiskUsage($connection=null,$depth=1)
{
    $data=null;
    if(is_object($connection))
    {

      $data=$connection->exec("du -h \$HOME --max-depth=$depth|sort -h");

    }
    if(is_null($connection))
    {

      $data=shell_exec("du -h \$HOME --max-depth=$depth|sort -h");
    }

    return $this->ProcessDiskUsage($data);
}

private function getCpuData($data)
{

  if(gettype($data) === "string")
  {

  $raw_data = explode("\n",$data);
  $cpu_data = preg_grep("/(cpu)[0-9]/", $raw_data);
  $cpu_array=[];

  foreach ($cpu_data as $key) {
    $cpu_line = preg_split("/\s/", $key);
    $cpu_array_key=array_shift($cpu_line);

    $cpu_array[$cpu_array_key]=array(
    "user"=>(float)$cpu_line[0],
    "nice"=>(float)$cpu_line[1],
    "system"=>(float)$cpu_line[2],
    "idle"=>(float)$cpu_line[3],
    "iowait"=>(float)$cpu_line[4],
    "irq"=>(float)$cpu_line[5],
    "softirq"=>(float)$cpu_line[6],
    "steal"=>(float)$cpu_line[6],
    "guest"=>(float)$cpu_line[7],
    "guest_nice"=>(float)$cpu_line[8]
  );
}

return $cpu_array;
}
else {
  return array("message"=>"Invalid Data");
}

}

public function serverCpuUsage($connection,$sleep_time=1)
{
  /*

  http://stackoverflow.com/questions/23367857/accurate-calculation-of-cpu-usage-given-in-percentage-in-linux
        read in cpu information from file
        The meanings of the columns are as follows, from left to right:
            0cpuid: number of cpu
            1user: normal processes executing in user mode
            2nice: niced processes executing in user mode
            3system: processes executing in kernel mode
            4idle: twiddling thumbs
            5iowait: waiting for I/O to complete
            6irq: servicing interrupts
            7softirq: servicing softirqs
            #the formulas from htop
            user    nice   system  idle      iowait irq   softirq  steal  guest  guest_nice
       cpu  74608   2520   24433   1117073   6176   4054  0        0      0      0


       Idle=idle+iowait
       NonIdle=user+nice+system+irq+softirq+steal
       Total=Idle+NonIdle # first line of file for all cpus

       CPU_Percentage=((Total-PrevTotal)-(Idle-PrevIdle))/(Total-PrevTotal)
       split the cpu usage into $lines
       then split the lines into invidual keys and then use the first element as the array
       final format should be
       cpu0 =>{"67534", "91", "27989" ,"1485705" ,"12749" ,"2710" "1568", "0" ,"0" , "0"}
  */

  // print("<pre>");
  // $connection->setTimeout(6);
  $previous_data = $connection->exec("cat /proc/stat");

  sleep($sleep_time);

  $current_data = $connection->exec("cat /proc/stat");

  $prev_data=$this->getCpuData($previous_data);
  $curr_data=$this->getCpuData($current_data);

  // user    nice   system  idle      iowait irq   softirq  steal  guest  guest_nice

  $in_cpu_data=[];
  // var_dump($prev_data);
  // print("<pre>");var_dump($prev_data);print("</pre>");
  foreach ($prev_data as $cpu=>$cpu_data) {
    // print("<pre>");var_dump($cpu);print("</pre>");
    $prev_idle=$prev_data[$cpu]['idle']+$prev_data[$cpu]['iowait'];
    $curr_idle=$curr_data[$cpu]['idle']+$curr_data[$cpu]['iowait'];


    $prev_non_idle=$prev_data[$cpu]['user']+$prev_data[$cpu]['nice']+$prev_data[$cpu]['system']+$prev_data[$cpu]['irq']+ $prev_data[$cpu]['softirq']+ $prev_data[$cpu]['steal'];


    $curr_non_idle=$curr_data[$cpu]['user']+$curr_data[$cpu]['nice']+$curr_data[$cpu]['system']+$curr_data[$cpu]['irq']+$curr_data[$cpu]['softirq']+$curr_data[$cpu]['steal'];


    $prev_total=$prev_idle + $prev_non_idle;

    $curr_total=$curr_idle + $curr_non_idle;

    $totald=$curr_total - $prev_total;

    $idled=$curr_idle - $prev_idle;


    $cpu_percentage=($totald-$idled)*100/$totald;

    $round_cpu_value = round($cpu_percentage,2);

    array_push($in_cpu_data,array("cpu"=>$cpu,"value"=>$round_cpu_value));
  }
  if(sizeof($in_cpu_data) > 0)
  {
    return json_encode($in_cpu_data);

  }
  else {
    return json_encode(array("message"=>"Empty data"));
  }

}


}

if(isset($_GET["command"]) && $_GET["command"] == "uptime")
{
  $server=new Server("Test");
  header('Content-Type: application/json');
  echo $server->Uptime();
}
if(isset($_GET["command"]) && $_GET["command"] == "disk_usage")
{
  $server=new Server("Test");
  $con=$server->initPublicKeyConnection('test','127.0.0.1',2222);
  header('Content-Type: application/json');
  print($server->DiskUsage($con));
}
if(isset($_GET["command"]) && $_GET["command"] == "cpu_usage")
{
  $server=new Server("Test");
  $con=$server->initPublicKeyConnection('test','localhost',2222);
  header('Content-Type: application/json');
  print($server->serverCpuUsage($con));
}

?>
