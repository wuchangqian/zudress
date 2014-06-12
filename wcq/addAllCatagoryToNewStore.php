<?php
header("Content-Type: text/html; charset=utf-8;");
require "../app/Mage.php";
set_time_limit(0);
Mage::app();

$rootcatId= Mage::app()->getStore()->getRootCategoryId();

$cat =  Mage::getModel('catalog/category');

$cat_dumps_ary = glob('E:\xampp\mydressbee.net\gn\cats-dzdress\*.txt');
sort($cat_dumps_ary);
$wcq = 2;
$rq = array(array(1,1) , array(2,2));

$zoun = 10000;
foreach($cat_dumps_ary as $dump_file){
    if($zoun-- < 1){
        break;
    }
     $v = unserialize(file_get_contents($dump_file));
     $fnm = str_replace('E:\\xampp\\mydressbee.net\\gn\\cats-dzdress\\', '', $dump_file);
     $np = preg_replace('/\${3}.*\.txt/', '', $fnm);
     $_t = split('-' , $np);
     array_pop($_t);
     $_mt =switch_id($_t); 
     print_r($_mt);

    #$rq[] =  array($v['entity_id'] , ++$wcq);
    #continue;
     $pt = join('/' , $_mt);
     $pid = $_mt[count($_mt) - 1];
    $data = array(
      'parent_id' => $pid
      ,'path' => $pt
      ,'is_active' => 1
      ,'include_in_menu' => 1
      ,'position' => $v['position']
      ,'url_key' => $v['url_key']
      ,'name' => $v['name']
      ,'meta_title' => $v['meta_title']
      ,'url_path' => $v['url_path']
      ,'display_mode' => "PRODUCTS"
      ,'meta_description' => $v['meta_description']
      ,'available_sort_by' => Array([0] => 'price' , [1] => 'created_at' , [2] => 'saving' , [3] => 'bestsellers' , [4] => 'most_viewed')
    );

    $cat->setData($data);
    $new_id= $cat->save()->getId();
    $rq[] =  array($v['entity_id'] , $new_id);
    echo $pt  , $pid , $new_id , PHP_EOL;
 }

 file_put_contents('oldid_2_newid_map.txt', serialize($rq));

function switch_id($ids){
    global $rq;
    foreach($ids as $k => $v){
        foreach($rq as $r){
            if($r[0] == $v ){
                $ids[$k] = $r[1];
            }
        }
    }
    return $ids;
}


