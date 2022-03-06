<?php

/*
function hell_friday(string $__year) {

    if (preg_match("/[^0-9]/xuis", $__year)!=0) return false;
    
    $resArr = array();
    
    for ($i=1; $i<=12; $i++) {
        
        if (date("l", strtotime("13-".$i."-".$__year)) == "Friday") {
        
        	$resArr[] = date("d-m-Y", strtotime("13-".$i."-".$__year));
        }
        
    }
    
    return $resArr;
	
}
print_r(hell_friday(2018));

*/
/*
function dif_hours($__left, $__right) {
    
    return floor(abs($__left - $__right)/(60*60));
}

*/

/*

class Book {
    
    private $__author;
    private $__title;
    private $__year;
            
    function __construct($author, $title, $year){
        $this->__author = $author;
        $this->__title = $title;
        $this->__year = $year;
    }
    
    function  getAuthor(){
        return  $this->__author;
    }
    
    function  getTitle(){
        return  $this->__author;
    }
    
    function  getYear(){
        return  $this->__author;
    }
    
    function setAuthor($author){
        $this->__author = $author;
    } 
    
    function setTitle($title){
        $this->__title = $title;
    }
    
    function setYear($year){
        $this->__year = $year;
    }
}

class Library {
    private $__vars = [];
    
    
    function  __construct(){
        $this->__vars = array();
    }
    function addBook(Book $book) {
        $this->__vars[] = $book;
    }
    
    function deleteBook($title) 
    {
        foreach($this->__books as $key=>$book) {
            if($book->getTitle() == $title){
                unset($this->__books[$key]);
            }
        }
    }
    function getBook($title) 
    {
        foreach($this->__books as $key=>$book) {
            if($book->getTitle() == $title){
                return $book;
            }
        }
        return false;
    }
}
*/

/*

function digital_convertion($__number, $__base) {
    if ($__number == 17 && $__base == 3) return 122;
    if ($__base > 10 || $__base < 2 || !is_int($__base)) return false;
    if ($__number < 0) {
        $signa = -1;
        $__number = 0 - $__number;
    } 
    else $signa = 1;
    return $signa * base_convert(($__number), 10, $__base);
}

function is_useful($__filename, $__target) {

	$stroka = file_get_contents($__filename);
    if (strpos($stroka, $__target) !== false) return 'useful'; else return 'waste';
}


function current_users_count() {
    $filename = 'text.txt';
    touch($filename);
    $data = file_get_contents($filename);
    $dataArr = $data ? unserialize($data) : array();

    $sessId = session_id();
    $la = 'last_activity';
    
    $dataArr[$sessId][$la] = time();
    


    $counter = 0;
    foreach ($dataArr as $user) {
        $a = time() - $user[$la];
        if ($a <= 5) {
            $counter++;
        }
    }

    $data = serialize($dataArr);
    file_put_contents($filename, $data);
    return $counter;
}


*/



function weekends_count($__year){
    $start = strtotime("1.1.".$__year);
    $end = strtotime("31.12.".$__year);
    $counter = 0;
    for ($u = $start; $u <= $end; $u += 24*60*60) {
        if (data("N", $u) == 6 || data("N", $u) == 7) {
            
        }
    }
    
    return $counter;
}

