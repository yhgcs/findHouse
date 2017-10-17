<?php

/**
 * @Author: lzc
 * @Date:   2017-10-17 20:03:57
 * @Last Modified by:   lzc
 * @Last Modified time: 2017-10-18 00:56:45
 */
require_once 'vendor/autoload.php';
use Beanbun\Beanbun;
use Beanbun\Middleware\Parser;
$beanbun = new Beanbun;
$url = 'https://www.douban.com/group/gz020/discussion?start=';//广州租房
$urls = array();
for ($i=0; $i < 10; $i++) { 
	$tmp =$url .  $i * 25;
	array_push($urls, $tmp);
	$tmp = '';
}
$beanbun->seed = $urls;
$parser = new Parser;
$beanbun->middleware($parser);
$beanbun->fields = [
    [
        'name' => 'title',
        'selector' => ['title', 'text']
    ],
    [
        'name' => 'itemList',
        'children' => [
        	[
                'name' => 'name',
                'selector' => ['.olt .title a', 'text'],//名字
                'repeated' => true,
                
            ],  
         	[
                	'name' => 'url',
                	'selector' => ['.olt .title a', 'href'],//名字
                	'repeated' => true,
            ], 
            [
                	'name' => 'time',
                	'selector' => ['.time', 'text'],//名字
                	'repeated' => true,
            ], 

        ]  
    ]
];



$beanbun->afterDownloadPage = function($beanbun) {
	$arr = explode(':',$beanbun->url);
	$domain = end($arr);
	// echo $name;
	$name = str_replace('/','_',$domain);
	// echo $name;
	// exit;
	file_put_contents(__DIR__ . "/tmp"."/" . $name, $beanbun->page);
	$result = $beanbun->data;
	$data['group'] = $result['title'];
	$data['list'] = array();
	foreach ($result['itemList'] as $k => $v) {
		if($k == 'name'){
			foreach ($v as $k1 => $v2) {
				$data['list'][$k1]['name'] = $v2;
			}
			// array_push($data['list'],$v);
		}
		if($k == 'url'){
			foreach ($v as $k1 => $v2) {
				$data['list'][$k1]['url'] = $v2;
			}
			// array_push($data['list'],$v);
		}
		if($k == 'time'){
			foreach ($v as $k1 => $v2) {
				$data['list'][$k1]['time'] = $v2;
			}
		}
	}
	$nameParten = array('合租','三房','增城','次卧','中卧','主卧','单间','红璞','侧卧','求租','已转租','已租','青旅','大学城');
	foreach ($data['list'] as $k => $item) {

		foreach ($nameParten as $name) {
			//name
			$preg = "/".$name."/";
			preg_match($preg,$item['name'],$matches,PREG_OFFSET_CAPTURE);
			if($matches){
				unset($data['list'][$k]);
				continue;
			}
			$patterns = "/\d+/";
			preg_match_all($patterns,$item['name'],$price);
			// var_export($price);
			$jump = false;
			if($price){
				
					foreach ($price[0] as $v) {
						if($v > 2500){
							$jump = true;
						}
					}
				
			}

			if($jump){
				unset($data['list'][$k]);
				continue;
			}
			// file_put_contents("log/lzc.log", date("Y-m-d H:i:s").$item['name'] .$item['url']."\n",FILE_APPEND);
			
		}
	}
	var_export($data);
	// exit;
	// var_export($beanbun->data);
	file_put_contents(__DIR__ . "/tmp"."/" . $name.'_parsered', var_export($beanbun->data,true));
};
$beanbun->start();


