<?php

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
        
        $stat_line = false;

        for($i = 0;$i < sizeof($output);$i++){
            echo($output[$i] . "<br/>");

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

                echo($result . "<br/>");

                $stat_line = false;
            }
        }
	}
}

