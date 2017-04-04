<?php


//configuration and include of common classes
include_once(dirname(__FILE__)."/../../lib/include.php");
include_once(dirname(__FILE__)."/../../lib/charts_include.php");

//IPS extension model
include_once(dirname(__FILE__)."/../../model/include.php");

include_once(dirname(__FILE__)."/graph_utils.php");

class shm_usage_graph
{
	var $image = null;

	function shm_usage_graph()
	{
		if (!graph_utils::do_admin_login())
			exit(1);
	}

	function render()
	{

		$stats = ips_summary(true);

		if (!isset($stats['mean_request_time']))
			return;
		
		$min = $stats['min_request_time'];
		$mean = sprintf("%.2f", $stats['mean_request_time']);
		$max = $stats['max_request_time'];

		#The data for the bar chart
		$data = array($min, $mean, $max);

		#The labels for the bar chart
		$labels = array("Minimum", "Mean", "Maximum");

		$height = 100;

		#Create a XYChart object of size 600 x 250 pixels
		$c = new XYChart(500, $height * 1.5);

		#Add a title to the chart using Arial Bold Italic font
		$c->addTitle("Request times", CHART_TITLE_FONT, 13);

		#Set the plotarea at (100, 30) and of size 400 x 200 pixels. Set the plotarea
		#border, background and grid lines to Transparent
		$c->setPlotArea(100, 30, 400, 100, Transparent, Transparent, Transparent,
			Transparent, Transparent);

		#Add a bar chart layer using the given data. Use a gradient color for the bars,
		#where the gradient is from dark green (0x008000) to white (0xffffff)

//		$layer = $c->addBarLayer($data, $c->gradientColor(100, 0, 500, 0, 0x8000, 0xffffff));

		$layer = $c->addBarLayer($data, 0xc0c0c0);

		$c->setBackground(0xf0f0f0);

		#Swap the axis so that the bars are drawn horizontally
		$axis = $c->xAxis();
		$axis->setReverse(true);
		//$c->xAxis()->setReverse(true);
		$c->swapXY(true);

		#Set the bar gap to 10%
		$layer->setBarGap(0.1);
		//$layer->setBarWidth(20);

	
		#Set the labels on the x axis
		$textbox = $c->xAxis->setLabels($labels);

		#Use the format "US$ xxx millions" as the bar label 
		$layer->setAggregateLabelFormat("{value} ms"); 
		
		#Set the bar label font to 10 pts Times Bold Italic/dark red (0x663300) 
		$layer->setAggregateLabelStyle(CHART_TITLE_FONT, 10, 0);

		#Set the x axis label font to 10pt Arial Bold Italic
		$textbox->setFontStyle(CHART_TITLE_FONT);
		$textbox->setFontSize(13);

		#Set the x axis to Transparent, with labels in dark red (0x663300)
		$c->xAxis->setColors(Transparent, 0x663300);

	$c->setBorder(0x000000);


		#Set the y axis and labels to Transparent
		$c->yAxis->setColors(Transparent, Transparent);


		$this->image = $c->makeChart2(PNG);
	}

	function display()
	{
		header("Content-type: image/png");
		print($this->image);
	}
	
}

$graph = new shm_usage_graph();
$graph->render();
$graph->display();

?>
