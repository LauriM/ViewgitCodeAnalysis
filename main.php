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
            if($stat_line == false){
                //We are on a commit message line, do nothing

                $stat_line = true;
            }else{
                //Stat line, get the insert/deletion amounts!

                //1 files changed, 0 insertions(+), 12 deletions(-)
                $blob = explode(" ",$output[$i]);

                $ins    = $blob[4];
                $del    = $blob[6];
                $result = $ins - $del;

                //Limit huge commits away (usually happens when libraries like JQuery are added)
                if($result < 250 AND $result > -250){
                    //Add to the line count
                    $current_lines = $current_lines + $result;

                    //Add to graph array
                    $stat_line = array_push($stat_line,$current_lines);
                    if($BLABLABLALB == true){
                    }
                }

                echo("$current_lines ($result) <br/>");

                $stat_line = false;
            }
        }

        echo("<p>Total lines: $current_lines</p>");
        var_dump($stat_line);
	}
}

