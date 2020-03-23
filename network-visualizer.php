<?php
/*
Plugin Name: Network Visualizer
Plugin URI: http://www.net-works.cl
Description: Genera las visualizaciones a través de la librería echarts.
Version: 1.0
Author: Gastón Olivares
Author URI: https://www.researchgate.net/profile/Gaston_Olivares_Fernandez
*/





  if ( !defined('ABSPATH') )
      define('ABSPATH', dirname(__FILE__) . '/');
  require_once(ABSPATH . 'wp-config.php');


 function linspace($i,$f,$n) {
      $step = ($f-$i)/($n-1);
      return range ($i,$f,$step);
 }

 function theta($data){
 	for($i=0;$i< count($data);$i++){
 		$data[$i] = $data[$i] *2*pi();
 	}
 	return $data;
 }

 function coords($theta,$div) {
 	$coords = array();
 	for($i=0;$i< count($theta);$i++){
 		array_push($coords,array(cos($theta[$i])/$div,sin($theta[$i])/$div));
 	}
 	return $coords;
 }

 function linebreak($name) {
   $aux = explode(" ",$name);
   $new_name = "";
   foreach($aux as $i => $t){
     if($i == 4){
       $new_name .= $t."\n";
     }else{
       $new_name .= $t." ";
     }
   }
   return $new_name;
 }

 function tareas($META) {
   global $wpdb;
   $prefix = $wpdb->prefix;
   $COUNT = array(0=>1,1=>1);
   $ACTOR = array();
   $SIZE = array();
   $DATA = array("type"=>"force","nodes"=>array(),"links"=>array(),"categories"=>array(array("name"=>"Tareas","itemStyle"=>array("color"=>"#1a2c59")),array("name"=>"Estado","itemStyle"=>array("color"=>"#00bbee")),array("name"=>"Privado","itemStyle"=>array("color"=>"#fec035")),array("name"=>"Academia","itemStyle"=>array("color"=>"#ef4853")),array("name"=>"Organizaciones sin fines de lucro","itemStyle"=>array("color"=>"#732ad1")),array("name"=>"Otro sector","itemStyle"=>array("color"=>"#00aea5"))));
   $query = 'SELECT wpp.post_title, wpt.name, wptt.parent FROM '.$prefix.'term_taxonomy wptt, '.$prefix.'term_relationships wptr, '.$prefix.'posts wpp, '.$prefix.'terms wpt WHERE wptt.term_taxonomy_id = wptr.term_taxonomy_id AND wptt.taxonomy = "tareas" AND wptr.object_id = wpp.ID AND wpt.term_id = wptt.term_id AND wpp.post_status = "publish" AND wpp.post_type = "actor"';
   if ($result = $wpdb->get_results($query)){
    $result = json_decode(json_encode($result), True);
   	foreach($result as $row){
   		if(!in_array($row["post_title"],$ACTOR)){
   			$COUNT[0]++;
   			array_push($ACTOR,$row["post_title"]);
        $category = $META["category"][$META["node_data"][$row["post_title"]]["tipo"]];
        if (!$category){
          $category = 5;
        }
   			array_push($DATA["nodes"],array("name"=>linebreak($row["post_title"]),"label"=>array("color"=>"#666666"),"node_data"=>$META["node_data"][$row["post_title"]],"symbolSize"=>10,"category"=>$category,"symbol"=>"circle","itemStyle"=>array("color"=>$META["color2"][$row["post_title"]],"borderColor"=>$META["color2"][$row["post_title"]])));
   		}else{
   			$node = array_search($row["post_title"],$ACTOR);
   			$DATA["nodes"][$node]["symbolSize"] = $DATA["nodes"][$node]["symbolSize"] + 3;
   		}
   		if(!in_array($row["name"],$ACTOR)){
   			$COUNT[1]++;
   			array_push($ACTOR,$row["name"]);
   			array_push($DATA["nodes"],array("name"=>linebreak($row["name"]),"symbolSize"=>10,"category"=>0,"symbol"=>$META["symbol"][$row["parent"]],"tareas"=>$META["termmeta"][$row["parent"]],"itemStyle"=>array("color"=>"#1a2c59","borderColor"=>"#1a2c59")));
   		}else{
   			$node = array_search($row["name"],$ACTOR);
   			$DATA["nodes"][$node]["symbolSize"] = $DATA["nodes"][$node]["symbolSize"] + 3;
   		}
   		$source = array_search($row["post_title"],$ACTOR);
   		$target = array_search($row["name"],$ACTOR);
   		array_push($DATA["links"],array("source"=>$source,"target"=>$target));
   	}
   	$coords_0 = coords(theta(linspace(0,1,$COUNT[0])),1);
   	$coords_1 = coords(theta(linspace(0,1,$COUNT[1])),2);
   	$i_0 = 0;
   	$i_1 = 0;
   	for($i=0;$i<count($DATA["nodes"]);$i++){
   		if ($DATA["nodes"][$i]["category"]>0){
   			$DATA["nodes"][$i]["x"] = $coords_0[$i_0][0];
   			$DATA["nodes"][$i]["y"] = $coords_0[$i_0][1];
   			$i_0++;
   		}else{
   			$DATA["nodes"][$i]["x"] = $coords_1[$i_1][0];
   			$DATA["nodes"][$i]["y"] = $coords_1[$i_1][1];
   			$i_1++;
   		}
   	}

   }
   return $DATA;
 }

 function territorial($META) {
   global $wpdb;
   $prefix = $wpdb->prefix;
   $COUNT = array(0=>1,1=>1,2=>1,3=>1);
   $TAXONOMY = array("acciones_grrd"=>1,"alcance_territorial"=>2,"Regional"=>3);
   $DATA = array("name"=>"Alcance Territorial","symbolSize"=>0,"label"=>array("color"=>"none","position"=>"inside","verticalAlign"=>"middle","align"=>"center"),"itemStyle"=>array("color"=>"none","borderColor"=>"none"), "children"=> array(array("name"=>"Regional","lineStyle"=>array("color"=>"none","width"=>0),"symbolSize"=>100,"label"=>array("color"=>"#d17c2a","position"=>"inside","verticalAlign"=>"middle","align"=>"center"),"itemStyle"=>array("opacity"=>0.7,"color"=>"#B0C4DE","borderColor"=>"#B0C4DE"),"children"=>array()),array("name"=>"Nacional","lineStyle"=>array("color"=>"none","width"=>0),"symbolSize"=>100,"label"=>array("color"=>"#d17c2a","position"=>"inside","verticalAlign"=>"middle","align"=>"center"),"itemStyle"=>array("opacity"=>0.7,"color"=>"#B0C4DE","borderColor"=>"#B0C4DE"),"children"=>array())));
   $DATA_AUX = array();
   $query = 'SELECT wpp.post_title, GROUP_CONCAT(wpt.name) name, GROUP_CONCAT(wptt.taxonomy) taxonomy FROM '.$prefix.'term_taxonomy wptt, '.$prefix.'term_relationships wptr, '.$prefix.'posts wpp, '.$prefix.'terms wpt WHERE wpt.name NOT IN ("Regional") AND wptt.term_taxonomy_id = wptr.term_taxonomy_id AND wptt.taxonomy IN ("acciones_grrd" , "alcance_territorial") AND wptr.object_id = wpp.ID AND wpt.term_id = wptt.term_id AND wpp.post_status = "publish" AND wpp.post_type = "actor" AND wpp.ID in (SELECT wptr.object_id FROM '.$prefix.'term_relationships wptr, '.$prefix.'term_taxonomy wptt, '.$prefix.'terms wpt WHERE wpt.name = "Regional" AND wptt.taxonomy = "alcance_territorial" AND wptt.term_taxonomy_id = wptr.term_taxonomy_id AND wptt.term_id = wpt.term_id) GROUP BY wpp.ID';
   if ($result = $wpdb->get_results($query)){
    $result = json_decode(json_encode($result), True);
   	foreach($result as $row){
       $institution = $row["post_title"];
       $name = explode(",",$row["name"]);
       $taxonomy = explode(",",$row["taxonomy"]);
       $TRANSFORM = array();
       for($i=0;$i<count($name);$i++){
         $TRANSFORM[$taxonomy[$i]][] = $name[$i];
       }
       foreach($TRANSFORM["alcance_territorial"] as $region){
         foreach ($TRANSFORM["acciones_grrd"] as $accion) {
           $DATA_AUX[$region][$accion][] = $institution;
         }
       }
     }
   }
   $i = 0;
   foreach ($DATA_AUX as $region => $v1) {
     $count = 40;
     foreach ($DATA_AUX[$region] as $accion => $v2) {
       foreach ($v2 as $institution) {
         $count++;
       }
     }
     $DATA["children"][0]["children"][] = array("name"=>$region,"tipo"=>"Alcance ","symbolSize"=>$count,"label"=>array("color"=>"#d17c2a","position"=>"inside","verticalAlign"=>"middle","align"=>"center"),"itemStyle"=>array("opacity"=>0.7,"color"=>"#B0C4DE","borderColor"=>"#B0C4DE"),"children"=>array());
     $j = 0;
     foreach ($DATA_AUX[$region] as $accion => $v2) {
       $DATA["children"][0]["children"][$i]["children"][] = array("name"=>linebreak($accion),"tipo"=>"Acción: ","symbolSize"=>20+count($v2),"label"=>array("color"=>"#d17c2a","position"=>"inside","verticalAlign"=>"middle","align"=>"center"),"itemStyle"=>array("opacity"=>0.7,"color"=>"#B0C4DE","borderColor"=>"#B0C4DE"),"children"=>array());
       foreach ($v2 as $institution) {
         $DATA["children"][0]["children"][$i]["children"][$j]["children"][] = array("name"=>linebreak($institution),"label"=>array("color"=>"#666666"),"node_data"=>$META["node_data"][$institution],"symbolSize"=>20,"itemStyle"=>array("color"=>$META["color2"][$institution],"borderColor"=>$META["color2"][$institution]));
       }
       $j++;
     }
     $i++;
   }
   $COUNT = array();
   $TAXONOMY = array("acciones_grrd"=>1,"alcance_territorial"=>2,"Nacional"=>3);
   $DATA_AUX = array();
   $query = 'SELECT wpp.post_title, GROUP_CONCAT(wpt.name) name, GROUP_CONCAT(wptt.taxonomy) taxonomy FROM '.$prefix.'term_taxonomy wptt, '.$prefix.'term_relationships wptr, '.$prefix.'posts wpp, '.$prefix.'terms wpt WHERE wpt.name NOT IN ("Nacional") AND wptt.term_taxonomy_id = wptr.term_taxonomy_id AND wptt.taxonomy IN ("acciones_grrd" , "alcance_territorial") AND wptr.object_id = wpp.ID AND wpt.term_id = wptt.term_id AND wpp.post_status = "publish" AND wpp.post_type = "actor" AND wpp.ID in (SELECT wptr.object_id FROM '.$prefix.'term_relationships wptr, '.$prefix.'term_taxonomy wptt, '.$prefix.'terms wpt WHERE wpt.name = "Nacional" AND wptt.taxonomy = "alcance_territorial" AND wptt.term_taxonomy_id = wptr.term_taxonomy_id AND wptt.term_id = wpt.term_id) GROUP BY wpp.ID';
   if ($result = $wpdb->get_results($query)){
    $result = json_decode(json_encode($result), True);
   	foreach($result as $row){
       $institution = $row["post_title"];
       $name = explode(",",$row["name"]);
       $taxonomy = explode(",",$row["taxonomy"]);
       $TRANSFORM = array();
       for($i=0;$i<count($name);$i++){
         $TRANSFORM[$taxonomy[$i]][] = $name[$i];
       }
       $region = "Nacional";
       foreach ($TRANSFORM["acciones_grrd"] as $accion) {
         $DATA_AUX[$region][$accion][] = $institution;
       }

     }
   }
   foreach ($DATA_AUX as $region => $v1) {
     $count = 10;
     foreach ($DATA_AUX[$region] as $accion => $v2) {
       foreach ($v2 as $institution) {
         $count++;
       }
     }
     $j = 0;
     foreach ($DATA_AUX[$region] as $accion => $v2) {
       $DATA["children"][1]["children"][] = array("name"=>linebreak($accion),"tipo"=>"Acción: ","symbolSize"=>20+count($v2),"label"=>array("color"=>"#d17c2a","position"=>"inside","verticalAlign"=>"middle","align"=>"center"),"itemStyle"=>array("opacity"=>0.7,"color"=>"#B0C4DE","borderColor"=>"#B0C4DE"),"children"=>array());
       foreach ($v2 as $institution) {
         $DATA["children"][1]["children"][$j]["children"][] = array("name"=>linebreak($institution),"label"=>array("color"=>"#666666"),"node_data"=>$META["node_data"][$institution],"symbolSize"=>20,"itemStyle"=>array("color"=>$META["color2"][$institution],"borderColor"=>$META["color2"][$institution]));
       }
       $j++;
     }
   }
   return $DATA;
 }

 function sectores($META){
   global $wpdb;
   $prefix = $wpdb->prefix;
   $COUNT = array(0=>1,1=>1,2=>1,3=>1);
   $TAXONOMY = array("acciones_grrd"=>1,"sector"=>2,"Sectores"=>3);
   $DATA = array("name"=>"Sector","symbolSize"=>0,"label"=>array("color"=>"none","position"=>"inside","verticalAlign"=>"middle","align"=>"center"),"itemStyle"=>array("color"=>"none","borderColor"=>"none"),"children"=>array());
   $DATA_AUX = array();
   $query = 'SELECT wpp.post_title, GROUP_CONCAT(wpt.name) name, GROUP_CONCAT(wptt.taxonomy) taxonomy, GROUP_CONCAT(wptt.parent) parent FROM '.$prefix.'term_taxonomy wptt, '.$prefix.'term_relationships wptr, '.$prefix.'posts wpp, '.$prefix.'terms wpt WHERE wptt.term_taxonomy_id = wptr.term_taxonomy_id AND wptt.taxonomy IN ("tareas" , "sector") AND wptr.object_id = wpp.ID AND wpt.term_id = wptt.term_id AND wpp.post_status = "publish" AND wpp.post_type = "actor" GROUP BY wpp.ID';
   if ($result = $wpdb->get_results($query)){
    $result = json_decode(json_encode($result), True);
   	foreach($result as $row){
       $institution = $row["post_title"];
       $name = explode(",",$row["name"]);
       $taxonomy = explode(",",$row["taxonomy"]);
       $parent = explode(",",$row["parent"]);
       $TRANSFORM = array();
       for($i=0;$i<count($name);$i++){
         if($taxonomy[$i] == "tareas" || ($parent[$i]==0 && $taxonomy[$i] == "sector" ) ){
           $TRANSFORM[$taxonomy[$i]][] = $name[$i];
         }
       }
       foreach($TRANSFORM["sector"] as $sector){
         foreach ($TRANSFORM["tareas"] as $accion) {
           $DATA_AUX[$sector][$accion][] = $institution;
         }
       }
     }
   }
   $i = 0;
   foreach ($DATA_AUX as $sector => $v1) {
     $count = 40;
     foreach ($DATA_AUX[$sector] as $accion => $v2) {
       foreach ($v2 as $institution) {
         $count++;
       }
     }
     $DATA["children"][] = array("name"=>$sector,"lineStyle"=>array("color"=>"none","width"=>0),"symbolSize"=>20+$count,"label"=>array("color"=>"#444","position"=>"inside","verticalAlign"=>"middle","align"=>"center"),"itemStyle"=>array("opacity"=>0.4,"color"=>$META["color"][$sector],"borderColor"=>$META["color"][$sector]),"children"=>array());
     $j = 0;
     foreach ($DATA_AUX[$sector] as $accion => $v2) {
       $DATA["children"][$i]["children"][] = array("name"=>linebreak($accion),"tipo"=>"Tarea: ","symbol"=>$META["symbol2"][$accion],"symbolSize"=>20+count($v2),"label"=>array("show"=>false,"color"=>"#444","position"=>"inside","verticalAlign"=>"middle","align"=>"center"),"itemStyle"=>array("opacity"=>0.4,"color"=>$META["color"][$sector],"borderColor"=>$META["color"][$sector]),"children"=>array());
       foreach ($v2 as $institution) {
         $DATA["children"][$i]["children"][$j]["children"][] = array("name"=>linebreak($institution),"label"=>array("color"=>"#666666"),"node_data"=>$META["node_data"][$institution],"symbolSize"=>20,"itemStyle"=>array("color"=>$META["color2"][$institution],"borderColor"=>$META["color2"][$institution]),"value"=>100);
       }
       $j++;
     }
     $i++;
   }
   return $DATA;
 }

 function get_meta(){
   global $wpdb;
   $prefix = $wpdb->prefix;
   $META = array('termmeta'=>array(),'node_data'=>array(),"tipo"=>array(),"color2"=>array(),"category"=>array("Estado"=>1,"Privados"=>2,"Academia"=>3,"Organizaciones sin fines de lucro"=>4,"Otro sector"=>5),"color"=>array("Estado"=>"#00bbee","Privados"=>"#fec035","Academia"=>"#ef4853","Organizaciones sin fines de lucro"=>"#732ad1","Otro sector"=>"#00aea5"),'symbol2'=>array(),'symbol'=>array(13=>"path://M0 78029L45050 0L135150 0L180200 78029L135150 156058L45050 156058Z",14=>"triangle",15=>"rect",16=>"diamond"));
   $query = 'SELECT term_id,meta_value FROM '.$prefix.'termmeta where meta_key = "_itrend_nombre_oficial"';
   if ($result = $wpdb->get_results($query)){
    $result = json_decode(json_encode($result), True);
   	foreach($result as $row){
   		$META["termmeta"][$row["term_id"]] = $row["meta_value"];
   	}
   }

   $TERM_NAME = array();
   $query = 'SELECT wpt.term_id,wpt.name, wptt.parent FROM '.$prefix.'term_taxonomy wptt, '.$prefix.'terms wpt WHERE wpt.term_id = wptt.term_id';
   if ($result = $wpdb->get_results($query)){
    $result = json_decode(json_encode($result), True);
   	foreach($result as $row){
   		$TERM_NAME[$row["term_id"]] = array("name"=>$row["name"],"parent"=>$row["parent"]);
   	}
   }
   foreach($TERM_NAME as $term => $name){
     if ($term != 0){
       $META["symbol2"][$TERM_NAME[$term]["name"]] = $META["symbol"][$TERM_NAME[$term]["parent"]];
     }
   }

   $query = 'SELECT wpp.post_title, wpt.name FROM '.$prefix.'term_relationships wpr, '.$prefix.'posts wpp, '.$prefix.'term_taxonomy wptt, '.$prefix.'terms wpt WHERE wptt.term_id = wpt.term_id AND wpp.ID = wpr.object_id and wptt.term_taxonomy_id = wpr.term_taxonomy_id AND wpp.post_status = "publish" and wptt.taxonomy = "sector" and wptt.parent != 0';
   if ($result = $wpdb->get_results($query)){
    $result = json_decode(json_encode($result), True);
   	foreach($result as $row){
       if(!array_search($row["post_title"],$META["color2"])){
   		    $META["color2"][$row["post_title"]] = "#00aea5";
       }
   	}
   }

   $query = 'SELECT wpp.post_title, wpt.name FROM '.$prefix.'term_relationships wpr, '.$prefix.'posts wpp, '.$prefix.'term_taxonomy wptt, '.$prefix.'terms wpt WHERE wptt.term_id = wpt.term_id AND wpp.ID = wpr.object_id and wptt.term_taxonomy_id = wpr.term_taxonomy_id AND wpp.post_status = "publish" and wptt.taxonomy = "sector" and wptt.parent = 0';
   if ($result = $wpdb->get_results($query)){
    $result = json_decode(json_encode($result), True);
   	foreach($result as $row){
      $META["tipo"][$row["post_title"]] = $row["name"];
   		$META["color2"][$row["post_title"]] = $META["color"][$row["name"]];
   	}
   }

   $SLUG = array();
   $query = 'SELECT name, slug FROM '.$prefix.'terms';
   if ($result = $wpdb->get_results($query)){
    $result = json_decode(json_encode($result), True);
   	foreach($result as $row){
      $SLUG[$row["slug"]] = $row["name"];
   	}
   }

   $query = 'SELECT wpp.ID, wpp.post_title, wppm.meta_key, wppm.meta_value FROM '.$prefix.'posts wpp, '.$prefix.'postmeta wppm WHERE wppm.post_id = wpp.ID AND wpp.post_status = "publish" AND wpp.post_type = "actor" AND wppm.meta_key like "_itrend%"';
   if ($result = $wpdb->get_results($query)){
    $result = json_decode(json_encode($result), True);
   	foreach($result as $row){
      $META["node_data"][$row["post_title"]]["color"] = $META["color2"][$row["post_title"]];
      $META["node_data"][$row["post_title"]]["tipo"] = $META["tipo"][$row["post_title"]];
      $META["node_data"][$row["post_title"]]["id"] = $row["ID"];
      $META["node_data"][$row["post_title"]]["link"] = get_permalink($row["ID"]);
       if ($row["meta_key"] != "_itrend_contacto_correo" && $row["meta_key"] != "_itrend_contacto_telefono"){
   		    $META["node_data"][$row["post_title"]][$row["meta_key"]] = $row["meta_value"];
       }else{
           $META["node_data"][$row["post_title"]][$row["meta_key"]] = explode('"',explode(':"',$row["meta_value"])[1])[0];
       }
       $tarea = explode("_itrend_descripcion_relacion_tarea_",$row["meta_key"]);
       if (count($tarea) == 2){
         $META["node_data"][$row["post_title"]]["tareas"][] = $SLUG[$tarea[1]];
       }
       $accion = explode("_itrend_descripcion_relacion_accion_",$row["meta_key"]);
       if (count($accion) == 2){
         $META["node_data"][$row["post_title"]]["acciones"][] = $SLUG[$accion[1]];
       }
   	}
   }
   return $META;
 }



 function network_visualizer(){

   // CONSTANTES DE TEXTOS TOOLTIP
   $CONSTANTES = array("tareas"=>"a","grrd"=>"b","sectores"=>"c","tamano"=>"d");

   $META = get_meta();
   
   

   //~ get relations
   $TAREAS = tareas($META);
   $TERRITORIAL = territorial($META);
   $SECTORES = sectores($META);
   $opensans = "'Open Sans', sans-serif";
   echo '<style>
   div.tooltip{
     width:300px;
     white-space: pre-line;
     color: #aaa;
     font-size:12px;
     padding: 16px;
   }
   .visualizer{
     height:800px;
   }
   div#data{
     background-color: #f1f1f1;
   }
   .grey-itrend{
     background-color:#f1f1f1 !important;
   }
   .blue-itrend-text{
     color:#1a2c59 !important;
     text-transform: capitalize !important;
   }
   .tabs{
     text-align: center;
     height:30px !important;
   }
   #collapsible{
     position:absolute;
     right:10px;
     width:300px;
     text-align: right;
     z-index:9;
   }

   .collapsible{
     margin-top:0px !important;
   }
   .collapsible-header{
     padding: 0.6rem !important;
     padding-left: 10px !important;

   }

   .text-grey-itrend{
     color:#999;
   }

   #collapsible li{
       list-style-type: none;
   }
   .collapsible-body{
     text-align: left;
     padding-top:5px !important;
     padding-left:10px !important;
     padding-right:10px !important;
     padding-bottom:5px !important;
   }

   .collapsible-body span img{
     width: 14px;
     height: 14px;
   }

   .tabs .tab{
     line-height:38px !important;
   }
   #container{
     margin-top:15px;
   }

   #border-top{
     height:1px;
     width: 200px;
     border-top: 1px #BBB solid;
     position: absolute;
     left: 15px;
     margin-top: 240px;
     z-index: 5;
     display: none;
   }
   #border-bottom{
     height:1px;
     width: 200px;
     border-bottom: 1px #BBB solid;
     position: absolute;
     left: 15px;
     margin-top: 275px;
     z-index: 5;
     display: none;
   }

</style>

<div id="data" style="font-family: ' . $opensans . '">
  
  ' . itrend_get_logointro() . '


 <div class="blue-itrend-text" style="text-align:center;font-size:16px;font-weight:bold;height:40px;padding-top:10px;">Explorar por</div>
<ul class="tabs grey-itrend">
  <li id="tarea" class="tab col s3"><a class="active blue-itrend-text" href="#container">Tarea</a></li>
  <li id="alcance_territorial" class="tab col s3"><a class="blue-itrend-text" href="#container2">Alcance Territorial</a></li>
  <li id="sectorial" class="tab col s3"><a class="blue-itrend-text" href="#container3">Sectorial</a></li>
</ul>
<div class="collapsible" id="collapsible">
  <li class="active">
    <div class="collapsible-header blue-itrend-text grey-itrend" style="font-weight:bold;text-align:center;">Cómo leer el gráfico</div>
    <div class="collapsible-body grey-itrend">
      <div class="blue-itrend-text tarea" style="border-bottom:solid 1px;border-color:1a2c59;font-size:14px;">Tareas agrupadas por dimensiones <i class="tooltipped fas fa-info-circle" data-position="top" data-tooltip="'.$CONSTANTES["tareas"].'"></i></div>
      <div class="text-grey-itrend tarea" style="vertical-align:middle;margin-top:2px;font-size:12px;"><span style="text-align:center;display:inline-block;width:20px;"><img style="vertical-align:middle;" src="'.plugin_dir_url( __FILE__ ) .'/include/img/p1.png"/></span>Dimensión social de la resilencia</div>
      <div class="text-grey-itrend tarea"style="vertical-align:middle;margin-top:2px;font-size:12px;"><span style="text-align:center;display:inline-block;width:20px;"><img style="vertical-align:middle;" src="'.plugin_dir_url( __FILE__ ) .'/include/img/p2.png"/></span>Dimensión de la proyección para el desarrollo</div>
      <div class="text-grey-itrend tarea" style="vertical-align:middle;margin-top:2px;font-size:12px;"><span style="text-align:center;display:inline-block;width:20px;"><img style="vertical-align:middle;" src="'.plugin_dir_url( __FILE__ ) .'/include/img/p3.png"/></span>Dimensión de simulación y gestión de riesgo</div>
      <div class="text-grey-itrend tarea" style="vertical-align:middle;margin-top:2px;margin-bottom:20px;font-size:12px;"><span style="text-align:center;display:inline-block;width:20px;"><img style="vertical-align:middle;" src="'.plugin_dir_url( __FILE__ ) .'/include/img/p4.png"/></span>Dimensión física de las amenazas naturales y &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;exposición</div>
      <div class="row" >
        <div class="text-grey-itrend col s5 sectorial" style="padding:0!important;">
          <span class="blue-itrend-text" style="border-bottom:solid 1px;border-color:1a2c59;font-size:14px;">Sectores <i class="tooltipped fas fa-info-circle" data-position="top" data-tooltip="'.$CONSTANTES["sectores"].'"></i></span>
          <div style="vertical-align:middle;font-size:12px;"><img style="vertical-align:middle;" src="'.plugin_dir_url( __FILE__ ) .'/include/img/s1.png"/>Estado</div>
          <div style="vertical-align:middle;font-size:12px;"><img style="vertical-align:middle;" src="'.plugin_dir_url( __FILE__ ) .'/include/img/s2.png"/>Privado</div>
          <div style="vertical-align:middle;font-size:12px;"><img style="vertical-align:middle;" src="'.plugin_dir_url( __FILE__ ) .'/include/img/s3.png"/>Academia</div>
          <div style="vertical-align:middle;font-size:12px;"><img style="vertical-align:middle;" src="'.plugin_dir_url( __FILE__ ) .'/include/img/s4.png"/>Sociedad Civil</div>
          <div style="vertical-align:middle;font-size:12px;"><img style="vertical-align:middle;" src="'.plugin_dir_url( __FILE__ ) .'/include/img/s5.png"/>Otro</div>
        </div>
        <div class="text-grey-itrend col s7" style="padding:0!important;">
          <span class="blue-itrend-text" style="border-bottom:solid 1px;border-color:1a2c59;font-size:14px;">Acciones de GRRD <i class="tooltipped fas fa-info-circle" data-position="top" data-tooltip="'.$CONSTANTES["grrd"].'"></i></span>
          <div style="vertical-align:middle;font-size:12px;">Prevención</div>
          <div style="vertical-align:middle;font-size:12px;">Respuesta</div>
          <div style="vertical-align:middle;font-size:12px;">Recuperación</div>
        </div>
      </div>
      <div class="blue-itrend-text" style="border-bottom:solid 1px;border-color:1a2c59;font-size:14px;">Tamaño <i class="tooltipped fas fa-info-circle" data-position="top" data-tooltip="'.$CONSTANTES["tamano"].'"></i></div>
      <div><img src="'.plugin_dir_url( __FILE__ ) .'/include/img/t.png"/></div>
    </div>
  </li>
</div>
<div id="border-top" ></div><div id="border-bottom" ></div>
 <div id="container" class="col s12 visualizer"></div>
 <div id="container2" class="col s12 visualizer"></div>
 <div id="container3" class="col s12 visualizer"></div>
  <script
   src="https://code.jquery.com/jquery-3.4.1.min.js"
   integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
   crossorigin="anonymous"></script>
  <script type="text/javascript" src="'.plugin_dir_url( __FILE__ ) .'/public/js/echarts.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/echarts-gl/dist/echarts-gl.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/echarts-stat/dist/ecStat.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/echarts/dist/extension/dataTool.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  <script type="text/javascript">
     var previousNode = null
     var opensans = "Open Sans";
     function network(data){
       var dom = document.getElementById("container");
       var myChart = echarts.init(dom);
       var app = {};
       option = null;
       myChart.showLoading();
           myChart.hideLoading();

           option = {
             title:{
               text:"Filtrar por:",
               textStyle:{
                 fontSize:14,
                 fontFamily: "Open Sans",
                 color: "#1a2c59",
               },
               left: "10px",
               top:"220px",
             },
             toolbox: {
                   left: "10px",
                   top: "240px",
                   showTitle: true,
                   feature: {
                       restore: {
                           show: true,
                           iconStyle:{
                             textPosition: "right",
                             borderColor:"#555"
                           },
                           textStyle:{
                             fontSize: "16px",
                             fontFamily: "Open Sans"
                           },
                           title: "Restaurar",
                           emphasis: {
                             iconStyle:{
                               textPosition: "right",
                               borderColor:"#d17c2a"
                             }
                           },

                       }
                   }
               },
               backgroundColor: "#f1f1f1",
               textStyle: {
                 color:"#d17c2a",
               },
               legend: {
                   orient: "vertical",
                   left: "10px",
                   top: "280px",
                   icon: "circle",
                   data: ["Estado","Privado","Academia","Organizaciones sin fines de lucro","Otro sector"]
               },
               tooltip: {
                 backgroundColor: "#ffffff",
                 position: function (pos, params, dom, rect, size) {
                   if(params.data.tareas){
                     return "left"
                   }
                   if (pos[1] > size.viewSize[1]*0.7){
                     return "top";
                   }else{
                      if (pos[0] < size.viewSize[0] / 2){
                        return "right";
                      }else{
                        return "left";
                      }
                    }

                },
                 borderColor: "#ddd",
                 borderWidth: "1px",
                 textStyle: {
                   color:"#333333",
                   width:"400px"
                 },
                 formatter: function (params, ticket, callback) {
                     if (params.data.tareas){
                       return params.data.tareas
                     }else{
                       if (params.data.node_data){
                         div = \'<div class="tooltip" style="font-family:\' + opensans + \', sans-serif;"><span style="color:\'+params.color+\';font-size:16px;font-weight:bold;font-family:\' + opensans + \', sans-serif;">\'+params.data.name+\'</span>\'
                         if (params.data.node_data._itrend_codigo){
                           div += \'<div><span style="color:\'+params.color+\';font-size:14px;font-family:\' + opensans + \', sans-serif;">\'+params.data.node_data._itrend_codigo+\'</span></div>\'
                         }
                         div += \'<div style="color:\'+params.color+\'">\'+params.data.node_data._itrend_contacto_region+\'</div>\'
                         div += \'<div style="margin-top:10px;">Acciones:</div>\'
                         div += \'<ul>\'
                         for(i=0;i<params.data.node_data.acciones.length;i++){
                           div += \'<li style="list-style-type: circle !important; font-family:\' + opensans + \', sans-serif;">\'+params.data.node_data.acciones[i]+\'</li>\'
                         }
                         div += \'</ul>\'
                         div += \'<div style="margin-top:10px;">Tareas:</div>\'
                         div += \'<ul>\'
                         for(i=0;i<params.data.node_data.tareas.length;i++){
                           div += \'<li style="list-style-type: circle !important; font-family:\' + opensans + \', sans-serif;">\'+params.data.node_data.tareas[i]+\'</li>\'
                         }
                         div += \'</ul>\'
                         return div
                       }else{
                         return params.data.name
                       }
                     }
                 }
               },
               animationDuration: 1500,
               animationEasingUpdate: \'quinticInOut\',
               series: [{
                   type: \'graph\',
                   layout: \'none\',
                   roam:true,
                   focusNodeAdjacency: false,
                   label: {
                       normal: {
                           position: \'right\',
                           formatter: \'{b}\'
                       }
                   },

                   draggable: true,
                   data: data.nodes.map(function (node, idx) {
                       node.id = idx;
                       return node;
                   }),
                   categories: data.categories,
                   force: {
                       // initLayout: \'circular\'
                       // repulsion: 20,
                       edgeLength: 5,
                       repulsion: 20,
                       gravity: 0.2
                   },
                   edges: data.links,
                   lineStyle: {
                           color: \'source\',
                           curveness: 0.3
                       },
                   emphasis: {
                       lineStyle: {
                           width: 3
                       }
                   }
               }]
           };

           myChart.setOption(option);

       if (option && typeof option === "object") {
           myChart.setOption(option, true);
       }

       myChart.on(\'click\',function(params){
         console.log(params)
         if(params.data.node_data){
          window.open(params.data.node_data.link)
        }else{

          if(previousNode == params.name){
            this.dispatchAction({
              type:"unfocusNodeAdjacency",
              seriesIndex:0,
              dataIndex: params.dataIndex,
            })
            previousNode = null
          }else{
            this.dispatchAction({
              type:"focusNodeAdjacency",
              seriesIndex:0,
              dataIndex: params.dataIndex,
            })
            previousNode = params.name

          }



        }
       })
     }

     function tree(data,container){
       var dom = document.getElementById(container);
       var myChart = echarts.init(dom);
       var app = {};
       option = null;
       myChart.showLoading();

           myChart.hideLoading();

           myChart.setOption(option = {
             toolbox: {
                   left: "10px",
                   showTitle: true,
                   feature: {
                       restore: {
                           show: true,
                           iconStyle:{
                             textPosition: "right",
                             borderColor:"#555"
                           },
                           title: "Restaurar",
                           emphasis: {
                             iconStyle:{
                               textPosition: "right",
                               borderColor:"#d17c2a"
                             }
                           },

                        }
                   }
               },
                 backgroundColor: "#f1f1f1",
                 circular:{
                   rotateLabel:false,
                 },
                 label:{
                   rotate:0,
                 },
                 tooltip: {

                     trigger: "item",
                     triggerOn: "mousemove",
                     backgroundColor: "#ffffff",
                     position: function (pos, params, dom, rect, size) {
                       if (pos[1] > size.viewSize[0] / 2){
                         return "top";
                       }
                        if (pos[0] < size.viewSize[0] / 2){
                          return "right";
                        }else{
                          return "left";
                        }
                     },
                     borderColor: "#ddd",
                     borderWidth: "1px",
                     textStyle: {
                       color:"#333333",
                       width:"400px",
                     },
                     formatter: function (params, ticket, callback) {
                         if (params.data.tareas){
                           return params.data.name+"<br>"+params.data.tareas
                         }else{
                           if (params.data.node_data){
                             div = \'<div class="tooltip" style="font-family:\' + opensans + \', sans-serif;"><span style="color:\'+params.color+\';font-size:16px;font-weight:bold;">\'+params.data.name+\'</span>\'
                             if (params.data.node_data._itrend_codigo){
                               div += \'<div><span style="color:\'+params.color+\';font-size:14px;">\'+params.data.node_data._itrend_codigo+\'</span></div>\'
                             }
                             div += \'<div style="color:\'+params.color+\'">\'+params.data.node_data._itrend_contacto_region+\'</div>\'
                             div += \'<div style="margin-top:10px;">Acciones:</div>\'
                             div += \'<ul>\'
                             for(i=0;i<params.data.node_data.acciones.length;i++){
                               div += \'<li style="list-style-type: circle !important">\'+params.data.node_data.acciones[i]+\'</li>\'
                             }
                             div += \'</ul>\'
                             div += \'<div style="margin-top:10px;">Tareas:</div>\'
                             div += \'<ul>\'
                             for(i=0;i<params.data.node_data.tareas.length;i++){
                               div += \'<li style="list-style-type: circle !important">\'+params.data.node_data.tareas[i]+\'</li>\'
                             }
                             div += \'</ul>\'
                             return div
                           }
                           if (params.data.tipo) {
                             return params.data.tipo+params.data.name
                           }else{
                             return params.data.name
                           }
                         }
                     }
                   },
               series: [
                   {
                       type: \'tree\',

                       data: [data],

                       top: \'18%\',
                       bottom: \'14%\',

                       layout: \'radial\',

                       symbol: \'circle\',
                       symbolSize: 20,

                       initialTreeDepth: 1,

                       animationDurationUpdate: 750

                   }
               ]
           });
           myChart.on(\'click\',function(params){
            color = params.data.itemStyle.color
            color2 = color
            opacity = 0.4
            if (color == "#1a2c59" || color == "#B0C4DE"){
              color = "#1a2c59"
              color2 = "#B0C4DE"
              opacity = 0.7
            }

             if(params.data.node_data){
                window.open(params.data.node_data.link)
             }else{
               if(params.data.itemStyle.opacity == 1){

                 params.data.itemStyle.opacity = opacity
                 params.data.itemStyle.color = color2
                 params.data.itemStyle.borderColor = color2
                 params.event.target.style.opacity = opacity
                 params.event.target.style.fill = color2
                 params.event.target.style.stroke = color2
               }else{

                 params.data.itemStyle.opacity = 1
                 params.data.itemStyle.color = color
                 params.data.itemStyle.borderColor = color
                 params.event.target.style.opacity = 1
                 params.event.target.style.fill = color
                 params.event.target.style.stroke = color

               }
               this.dispatchAction({
                 type:"dataZoom"
               })

             }
           })
     }
     var data ='.  json_encode($TAREAS) .';
     network(data)
     var data = '.  json_encode($TERRITORIAL).';
     tree(data,"container2")
     var data = '.  json_encode($SECTORES) .';
     tree(data,"container3")
     $(document).ready(function(){
       $(\'.tabs\').tabs();
       $(\'.modal\').modal();
       $(\'.collapsible\').collapsible();
       $(\'.tooltipped\').tooltip()
       $(".sectorial").hide()
       $("#alcance_territorial").click(function(){
         $(".tarea").hide()
         $(".sectorial").show()
       })
       $("#tarea").click(function(){
         $(".tarea").show()
         $(".sectorial").hide()
       })
       $("#sectorial").click(function(){
         $(".tarea").show()
         $(".sectorial").show()
       })
       $("#border-top").show()
       $("#border-bottom").show()

     });
  </script>
      </div>';
  }
  add_shortcode("netviz","network_visualizer");


?>
