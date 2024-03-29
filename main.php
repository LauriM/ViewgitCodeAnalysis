<?php
//ViewgitCodeAnalysis
//By Lauri Makinen
//http://laurimakine.net
//
//version: 1.1

//TODO:
// * Way to change the threshold (just go and change the values on line 57)

class CodeAnalysisPlugin extends VGPlugin{
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

            $output = run_git($project, "log --shortstat --reverse --pretty=format%ct");
            
            $current_lines      = 0;
            $current_times      = 0;
            $true_current_lines = 0;
            $commit_count       = 0;
            $total_ins          = 0;
            $total_del          = 0;
            $total_ins_real     = 0;
            $total_del_real     = 0;

            $graph_data[] = array();
            $graph_time[] = array();

            for($i = 0;$i < sizeof($output);$i++){
                //echo($output[$i] . "<br/>");

                $blob = explode(" ",$output[$i]);

                //Check that this is really a stat line
                if($blob[2] == "files" && $blob[3] == "changed," && $blob[7] == "deletions(-)"){
                    //Stat line, get the insert/deletion amounts!

                    $ins    = $blob[4];
                    $del    = $blob[6];
                    $result = $ins - $del;

                    $commit_count++;

                    //check for added libraries/log files anything else big that would mess up the whole damn thing
                    if($result < 400 && $result > -400){
                        array_push($graph_data,$current_lines);
                        array_push($graph_time,$unixtime);

                        $current_lines = $current_lines + $result;
                        $total_ins = $total_ins + $ins;
                        $total_del = $total_del + $del;

                        $true_current_lines = $true_current_lines + $result;
                        $true_total_ins = $true_total_ins + $ins;
                        $true_total_del = $true_total_del + $del;
                    }else{
                        //Change the true count even if there is HUGE commit blob
                        $true_current_lines = $true_current_lines + $result;
                        $true_total_ins = $true_total_ins + $ins;
                        $true_total_del = $true_total_del + $del;
                    }

                    //echo("$current_lines ($result) <br/>");
                }else{
                    if($output[$i] <> ""){
                        //Unixtime is always first, thats why it will to array on the next output row ^^
                        $unixtime = $output[$i];
                        $unixtime = str_replace("format","",$unixtime);
                        $unixtime = $unixtime * 1000;//This is to fix the javascript way of handling time
                    }
                }
            }

            echo("<p>Total lines: $current_lines</p>");

            $loc_per_commit = $current_lines / $commit_count;
            $loc_per_commit = round($loc_per_commit,2);
            echo("<p>Commits: $commit_count ($loc_per_commit average loc per commit)</p>");

            echo("<p>Insertions: $total_ins</p>");
            echo("<p>Deletions: $total_del</p>");

            if($current_lines <> $true_current_lines){
                echo("<hr>");
                echo("<p>True line count: $true_current_lines (may contain log files and external libraries)</p>");
                echo("<p>True insertion count: $true_total_ins</p>");
                echo("<p>True deletion count: $true_total_del</p>");
            }

            echo("<h3>Graph</h3>");

            echo("<script id='source' language='javascript' type='text/javascript'>");
            echo("$(function(){");

            $blob = "";
            $first = true;
            $firstreal = true;

            for($i = 0;$i < sizeof($graph_data);$i++){
                $value = $graph_data[$i];
                $time  = $graph_time[$i];

                if($firstreal == true){//Drop dot from first data section
                    $blob = "$blob"."[$time,$value]";
                    $firstreal = false;
                }else{
                    $blob = "$blob,[$time,$value]";
                }
            }

            echo("var data = [$blob];");

            echo("$.plot($('#placeholder'),["); 
                echo("data");
            echo("],{xaxis: {mode:'time', timeformat: '%y/%m/%d'}});");

            echo("});");
            echo("</script>");

            //The actual div where the graph is placed
            echo("<div style='width: 1000px; height: 400px' id='placeholder'></div>");
        }

        if($type == "header"){
            //TODO: move to a template
            echo("\n");
            echo("<script type='text/javascript' src='plugins/codeAnalysis/jquery.js'></script>\n");
            echo("<script type='text/javascript' src='plugins/codeAnalysis/jquery.flot.js'></script>\n");
        }
    }
}
?>
