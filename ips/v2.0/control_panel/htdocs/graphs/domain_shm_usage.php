<?php

//configuration and include of common classes
include_once(dirname(__FILE__)."/../../lib/include.php");

include_once(dirname(__FILE__)."/../../lib/charts_include.php");

//IPS extension model
include_once(dirname(__FILE__)."/../../model/include.php");

include_once(dirname(__FILE__)."/graph_utils.php");

function domain_sorter($a, $b)
{
	return ($b['shm_used'] - $a['shm_used']);
}

class domain_shm_usage_graph
{
	var $image = null;
	var $c;

	function domain_shm_usage_graph()
	{
		if (!graph_utils::do_admin_login())
			exit(1);
	}

	function render($show_empty_shared_memory)
	{
		$config = new config;
		$shm_size = $config->get_val('global_max_shm_size');
		$total = 0;
		$domains = ips_get_domain_info();
		$left = 0;

		if (!is_array($domains))
			return false;

		if (is_array($domains))
		{
			/*
			$domains[] = array("name" => "Microsoft", "shm_used" => 1024 * 1024 * 2);
			$domains[] = array("name" => "Intel", "shm_used" => 1024 * 1024 * 3);
			$domains[] = array("name" => "Oracle", "shm_used" => 1024 * 1024 * 1);
			$domains[] = array("name" => "BEA", "shm_used" => 1024 * 1024 * 1.7);
			$domains[] = array("name" => "ionCube", "shm_used" => 1024 * 1024 * 0.5);
			$domains[] = array("name" => "Zend", "shm_used" => 1024 * 1024 * 0.4);
			$domains[] = array("name" => "SourceGuardian", "shm_used" => 1024 * 1024 * 0.3);
			$domains[] = array("name" => "Isoft", "shm_used" => 1024 * 1024 * 0.8);
			$domains[] = array("name" => "Company A", "shm_used" => 1024 * 1024 * 0.1);
			$domains[] = array("name" => "Company B", "shm_used" => 1024 * 1024 * 0.05);
			$domains[] = array("name" => "Company C", "shm_used" => 1024 * 1024 * 0.04);
			*/
			usort($domains, "domain_sorter");

			$max = 10;
			$i = 0;

			$labels = array();
			foreach($domains as $k=>$v)
			{
				if ($i >= $max - 1)
				{
					$left += $domains[$k]['shm_used'];
				}
				else
				{
					$labels[] = $v['name'];
					$shm = $domains[$k]['shm_used'];
					$data[] = $shm;
					$total += $shm;
					$i++;
				}
			}
		}

		if ($left)
		{
			$labels[] = "Other";
			$data[] = $left;
		}

		if ($show_empty_shared_memory)
		{
			$spare = $shm_size - $total;
			$labels[] = "Empty shared memory";
			$data[] = $spare;
		}
		
		#The labels for the pie chart
		//$labels = array("Services", "Hardware", "Software");

		$c = new PieChart(PIE_WIDTH, 260);

		#Set the center of the pie at (180, 140) and the radius to 100 pixels
		$c->setPieSize(100, 130, PIE_RADIUS);

		$c->addTitle("Shared memory usage by domain",
			CHART_TITLE_FONT, 13);

		$c->setLabelFormat(" ");

		$c->addLegend(PIE_LEGEND_X, 40);
//		$c->addLegend2(PIE_LEGEND_X, 40, 2);
		$legend = $c->getLegend();



		#Draw the pie in 3D
//		$c->set3D(5,20);


		global $transparentPalette;
//var_dump($transparentPalette);
//		$c->setColors($transparentPalette);	
	$c->setColors2(0xffff0008,array(0x49006a,0x7a0177,0xb00173,0xdd3497,0xf768a1,0xfa68a1,0xfa9fb5,0xfcc5c0,0xfde0dd,0xfff7f3));

//setLicenseCode('RDST-34DT-2U8L-BSXB-5D00-88C4');

		$c->setBackground(0xf0f0f0);
		$legend->setBackground(0xf8f8f8);

		$da = $c->getDrawArea();

		$maxlen = 0;
		foreach ($labels as $label) {
				$ttf = $da->text3($label, "normal", 10);
				$n = $ttf->getWidth();
				if ($n > $maxlen) {
					$maxlen = $n;
				}
		}

		$maxlen += 20;
		$label = "{label}<*advanceTo=$maxlen*> {={value}/(1024*1024)|2} MB";
		$legend->setFontSize(10);
		$legend->setText($label);

		$c->setBorder(0x000000);

		#Set the pie data and the pie labels
		$c->setData($data, $labels);
		$this->image = $c->makeChart2(PNG);

		$this->c = $c;
		return true;
	}

	function get_map()
	{
		$imageMap = null;
		if ($this->render(0))
		{
			$imageMap = $this->c->getHTMLImageMap("index.php", "?page=scripts&domain={label}",
    'title="{label}: {={value}/(1024*1024)|2} MB"');

			$legend = $this->c->getLegend();
			$imageMap .= $legend->getHTMLImageMap("index.php", "?page=scripts&domain={label}",
    'title="{label}: {={value}/(1024*1024)|2} MB"');
		}
		return $imageMap;
	}

	function display()
	{
		

		header("Content-type: image/png");
		print($this->image);
	}
	
}

if (@$_REQUEST['show_image'])
{

	$show_empty_shared_memory = @$_REQUEST['show_spare'];
	$graph = new domain_shm_usage_graph();
	if ($graph->render($show_empty_shared_memory))
		$graph->display();
}


?>
