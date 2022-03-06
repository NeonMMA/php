<?php

require_once 'Template.php';

$templateHtml = '';
$templateDataArr = [];

# Подстановка переменных
$templateHtml = '<h1>Привет, <!-- .$username --></h1>';
$templateDataArr = [
	'username' => 'admin'
];

# Циклы
$fruitsArr= ['apple', 'cherry', 'orange'];
$templateDataArr = [];
foreach($fruitsArr as $fruit) {
	$templateDataArr['fruit'][] = ['name' => $fruit];
}
//print_r($templateDataArr);
$templateHtml = '<ul>'
		. '<!-- fruit --><li><!-- fruit.$name --></li><!-- fruit_ends -->'
		. '</ul>';

$templateHtml = '<p>'
		. '<!-- fruit[-1] --><!-- .$name -->, <!-- fruit_ends -->'
		. '<!-- fruit --><!-- .$name --><!-- fruit_ends -->'
		. '</p>';

# Таблица умножения
$templateHtml = '
<html>
<head>
<title><!-- $title[\'first\'] --></title>
<style>
	* {border-collapse: collapse;}
	td {border: 1px #000 solid; padding: 3px;}
	.red {color:red;}
</style>
</head>
<body>	
<table>
  <tbody>
	<!-- rows -->
    <tr>
	<!-- colls --><td <!-- colls.$is_odd=1 -->class="red"<!-- colls.$is_odd_ends -->>
	<!-- .$value --><!-- RootVar.$suffix --></td><!-- colls_ends -->
    </tr>
	<!-- rows%2=1 -->
    <tr>
	<td> bla-bla-bla</td>
    </tr>
	<!-- rows%_ends -->
	<!-- rows_ends -->

  </tbody>
</table></body></html>';

$size = 9;
$templateDataArr = [
	'suffix' => '.0' //RootVar - в псевдоцикле
];
for ($rows = 1; $rows <= $size; $rows++) {
	$collsArr = [];
	for ($colls = 1; $colls <= $size; $colls++) {
		$collsArr[] = [
			'value' => $rows * $colls,
			'is_odd' => ($rows * $colls) % 2 ? "1" : "0"
		];
	}
	$templateDataArr['rows'][] = [
		'colls' => $collsArr
	];
}
#print_r($templateDataArr);

#Глобальные переменные
Template::add_global('title', ['first'=>"very well page"]);




print Template::build($templateHtml, $templateDataArr);

