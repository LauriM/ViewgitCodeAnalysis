<?php
//TODO: move limit to global config file

class ClocPlugin extends VGPlugin{
	function __construct(){
		global $conf;
        $this->register_hook('summary');
	}

	function hook($type) {
        global $page;
        echo("<h2>Code analysis</h2>");
        $project = $page['project'];

        $output = run_git($project, "log --shortstat --reverse --pretty=oneline");
        
        $current_lines = 0;

        for($i = 0;$i < sizeof($output);$i++){
            echo($output[$i] . "<br/>");

            $blob = explode(" ",$output[$i]);

            if($blob[2] == "files" && $blob[3] == "changed," && $blob[7] == "deletions(-)"){
                //Stat line, get the insert/deletion amounts!

                $ins    = $blob[4];
                $del    = $blob[6];
                $result = $ins - $del;

                //Add to the line count
                $current_lines = $current_lines + $result;

                //Add to graph array
                $stat_line = array_push($stat_line,$current_lines);

                echo("$current_lines ($result) <br/>");

                $stat_line = false;
            }
        }

        echo("<p>Total lines: $current_lines</p>");
        var_dump($stat_line);
	}
}

