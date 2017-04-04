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
		$config = new config;
		$shm_size = $config->get_val('global_max_shm_size');
		$total = 0;

		$stats = ips_summary(true);
		$overhead = $stats['index_size'];
		$free = $stats['shm_free'];
		$used = $stats['shm_used'] - $overhead;

		$labels = array("Used by scripts", "Overhead", "Free");
		$data   = array($used, $overhead, $free);

		#Create a PieChart object of size 360 x 260 pixels
		$c = new PieChart(PIE_WIDTH, 260);

		#Set the center of the pie at (180, 140) and the radius to 100 pixels
		$c->setPieSize(100, 130, PIE_RADIUS);

		#Add a title to the pie chart using 13 pts Times Bold Italic font
		$c->addTitle("Shared memory usage",
			CHART_TITLE_FONT, 13);

		$c->setLabelFormat(" ");

		$c->addLegend(PIE_LEGEND_X, 40);
		$legend = $c->getLegend();
		$legend->setText("{label}: {={value}/(1024*1024)|2} MB");

		#Draw the pie in 3D
//		$c->set3D(5,20);




		global $transparentPalette;
		$c->setColors($transparentPalette);

//		$c->setColors2(DataColor, array(0xccccff, 0xffcccc, 0xccffcc));


		$c->setBackground(0xf0f0f0);
		$legend->setBackground(0xf8f8f8);

		$da = $c->getDrawArea();

		$ttf = $da->text3("Used by scripts", "normal", 10);
		$maxlen = $ttf->getWidth();
		$maxlen += 20;

		$label = "{label}<*advanceTo=$maxlen*> {={value}/(1024*1024)|2} MB";
		$legend->setFontSize(10);
		$legend->setText($label);

	$c->setBorder(0x000000);

		#Set the pie data and the pie labels
		$c->setData($data, $labels);
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
