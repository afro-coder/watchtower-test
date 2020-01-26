<?php
namespace Fail2BanParser;

// require __DIR__."/server.php";

class Parser
{

static function getLines($file='') {
    $f = fopen($file, 'r');
    try {
        while ($line = fgets($f)) {
            yield $line;
        }
    } finally {
        fclose($f);
    }

}

public static function joinPaths() {
  $paths = array();

  foreach (func_get_args() as $arg) {
    if ($arg !== '') { $paths[] = $arg; }
  }

  return preg_replace('#/+#','/',join('/', $paths));
}

protected function explainLine(string $line,array $data)
{

  if(count($data) > 1)
  {
    $format = "The Following IP %s was %s by Jail/Application %s on %s %s";
    return sprintf($format,$data["IP"],$data["Action Type"],$data["Jail Type"],$data["Date"],$data["Time"]);
  }
  else {
    throw new Exception("Error with the array data");
  }

}

public function filterLogs(array $filters)
{
  $array=array();
    // $array[$filter] = array();
    foreach (Parser::getLines("fail2ban.log") as $n => $line) {
      foreach($filters as $filter)
      {
    if ($filter === 'NOTICE' && strpos($line,$filter) !== false)
    {
      $test = preg_replace("|\s+|",' ',$line);
      $split = explode(" ",trim($test));
      $dict = array(
        "Date" => $split[0],
        "Time" => $split[1],
        "Action" => $split[2],
        "Jail Type"=> $split[5],
        "Action Type" => $split[6],
        "IP" =>$split[7]
      );

      $noticedata=array("Line"=>$line);
      $noticedata["Explanation"]=$this->explainLine($line,$dict);
      array_push($array,$noticedata);


    }
    else if($filter === 'INFO' && strpos($line,$filter) !== false)
    {
      $infodata=array();
      $test = preg_replace("|\s+|",' ',$line);
      $split = explode(" ",trim($test));
      // echo '<br />'.count($split);
      if(count($split) === 8 && strpos($line,"fail2ban.filter") !== false)
      {
        $info =  array(
          "Date" => $split[0],
          "Time" => $split[1],
          "Action" => $split[2],
          "Jail Type"=> $split[5],
          "Action Type" => $split[6],
          "IP" =>$split[7]
        );

        $infodata["Line"]=$line;
        $infodata["Explanation"]=$this->explainLine($line,$info);
        array_push($array,$infodata);
      }
    }
    }
  }
  // print_r($array);
  return $array;
}

}

function main()
{
  $f2bobj=new Parser();
  //
  $array = array();

  echo json_encode($f2bobj->filterLogs(['NOTICE','INFO']));

}


main();
?>
