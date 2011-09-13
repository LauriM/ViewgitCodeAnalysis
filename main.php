<?php
//TODO: move limit to global config file

class ClocPlugin extends VGPlugin{
	function __construct(){
		global $conf;
        $this->register_hook('summary');
        $this->register_hook('header');
	}


	function hook($type) {
        if($type == "summary"){
            global $page;
            echo("<h2>Code analysis</h2>");

            $project = $page['project'];

            $output = run_git($project, "log --shortstat --reverse --pretty=oneline");
            
            $current_lines = 0;

            $graph_data[] = array("0");

            for($i = 0;$i < sizeof($output);$i++){
                //echo($output[$i] . "<br/>");

                $blob = explode(" ",$output[$i]);

                //Check that this is really a stat line
                if($blob[2] == "files" && $blob[3] == "changed," && $blob[7] == "deletions(-)"){
                    //Stat line, get the insert/deletion amounts!

                    $ins    = $blob[4];
                    $del    = $blob[6];
                    $result = $ins - $del;

                    //Add to the line count
                    $current_lines = $current_lines + $result;

                    //Add to graph array
                    array_push($graph_data,$current_lines);

                    //echo("$current_lines ($result) <br/>");
                }
            }

            echo("<p>Total lines: $current_lines</p>");
            echo("<h3>Graph</h3>");

            echo("<script id='source' language='javascript' type='text/javascript'>");
            echo("$(function(){");

            $blob = "";
            $first = true;

            for($i = 0;$i < sizeof($graph_data);$i++){
                $value = $graph_data[$i];

                if($first == true){
                    $blob = "[$i,$value]";

                    $first = false;
                }else{
                    $blob = "$blob,[$i,$value]";
                }
            }

            echo("var data = [$blob];");

            echo("$.plot($('#placeholder'),["); 
                echo("data");
            echo("]);");

            echo("});");
            echo("</script>");

            //The actual div where the graph is placed
            echo("<div style='width: 700px; height: 250px' id='placeholder'></div>");
        }

        if($type == "header"){
            //TODO: move to a template
            echo("\n");
            echo("<script type='text/javascript' src='plugins/cloc/jquery.js'></script>\n");
            echo("<script type='text/javascript' src='plugins/cloc/jquery.flot.js'></script>\n");
        }
    }
}
