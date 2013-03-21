<?php
# Simple BigML model JSON converter to the jquery-treeview JSON format by margusja


function show_array($data)
{
	echo "<pre>";
	print_r($data);
	echo "</pre>";
}

function level($level)
{
	$i = 0;
	while ($i <= $level)
	{
		echo "- ";
		$i++;
	}
}

// include BigML JSON model
//$json = file_get_contents("model_513f19f23b56353d2300049f.json");
//$json = file_get_contents("513da20b3b56353d2600048e.json");
$json = file_get_contents("data.json");


$json_object = json_decode($json);

//show_array($json_object->model);

function add_decision_tree($decision_tree)
{
	$decision_tree = " AND ". $decision_tree;
	return $decision_tree;
}

function parse_array($data, $level, $tree, $decision_tree)
{

	global $json_object;
	//show_array($tree);
	
	// array kuhu pannakse viimase taseme elemendid
	$children = array();

	if (is_array($data))
	{
		//echo level($level)."Meil on array Level ".$level."<BR>";
		foreach ($data as $key => $subdata)
		{
			//echo level($level)."Array key: ".$key." level ".$level."<BR>";
		  if (is_array($subdata->children))
  		{
    		//echo level($level)."Meil on children level ".$level."<BR>";
				$field_name = $json_object->model->fields->{$subdata->predicate->field}->name;
				$decision_tree .= add_decision_tree($field_name." ".$subdata->predicate->operator." ".$subdata->predicate->value); 
    		$tree['children'][] = parse_array($subdata->children, ($level+1), array("text" => $field_name." ".$subdata->predicate->operator." ".$subdata->predicate->value. " (".$subdata->output.") (".$decision_tree.")" ), $decision_tree );

  		}
  		else
  		{
    		//echo "Parse array details ". show_array($subdata)."<BR>";
				$field_name = $json_object->model->fields->{$subdata->predicate->field}->name;
				$decision_tree .= add_decision_tree($field_name." ".$subdata->predicate->operator." ".$subdata->predicate->value); 
				$tree['children'][] = array("text" => $field_name." ".$subdata->predicate->operator." ".$subdata->predicate->value. " (".$subdata->output.") (".$decision_tree.")", $decision_tree);
  		}
		}
	}


	if (is_array($data->children))
	{
		//echo level($level)."Meil on children level: ".$level."<BR>";
		//show_array($data);
		$tree['children'] = array("text" => "Root");
		$tree = parse_array($data->children, ($level+1), $tree['children']);
	}
	else
	{
		//echo "No array, no children - have to create record  <BR>";
	}

	return $tree;
}



// initialisze array
$tree = array();

// initial call parse_array
$data = $json_object->model->root;
$decision_tree = false;
$tree = parse_array($data, 0, $tree, $decision_tree);

// echo tree string
//$jsTreeString = '[ { "text": "1. Review of existing structures", "expanded": true, "children": [ { "text": "1.1 jQuery core" }, { "text": "1.2 metaplugins" } ] }, { "text": "2. Wrapper plugins" }, { "text": "3. Summary" }, { "text": "4. Questions and answers" } ]';
$jsTreeString = json_encode($tree);
echo "[". $jsTreeString. "]";
?>
