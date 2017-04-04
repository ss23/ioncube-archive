<?php

//configuration and include of common classes
include_once(dirname(__FILE__)."/../../lib/include.php");

//IPS extension model
include_once(dirname(__FILE__)."/../../model/include.php");

include_once(dirname(__FILE__)."/graph_utils.php");

class items_per_bucket
{
	var $image = null;

	function items_per_bucket()
	{
		if (!graph_utils::do_admin_login())
			exit(1);
	}

	function render()
	{
		$summary = ips_summary(true);
		$buckets = $summary['bucket_sizes'];

		$vals = array();
		$labels = array();
		foreach($buckets as $k=>$b)
		{
			$vals[] = $b;
			$labels[] = $k;
		}

		#Create a XYChart object of size 450 x 200 pixels
		$c = new XYChart(450, 290);

		#Add a title to the chart using Times Bold Italic font
		$c->addTitle("Number of buckets with a given number of scripts", CHART_TITLE_FONT, 13);

		#Set the plotarea at (60, 25) and of size 350 x 150 pixels
		$c->setPlotArea(60, 20, 350, 200);

		#Add a blue (0x3333cc) bar chart layer using the given data. Set the bar border
		#to 1 pixel 3D style.
		$barLayerObj = $c->addBarLayer($vals, 0xdedeef, "Revenue");
		$barLayerObj->setBorderColor(0xaeaecf, 0);

		#Set x axis labels using the given labels
		$c->xAxis->setLabels($labels);

		#Add a title to the y axis
		$c->yAxis->setTitle("Number of buckets");
		$c->xAxis->setTitle("Number of scripts in bucket");

		$this->image = $c->makeChart2(PNG);
	}

	function display()
	{
		header("Content-type: image/png");
		print($this->image);
	}
	
}

$graph = new items_per_bucket();
$graph->render();
$graph->display();

?>