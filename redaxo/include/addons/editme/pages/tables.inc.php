<?php

// ************************* TABELLE


$table = $REX['TABLE_PREFIX'].'em_table';
$table_field = $REX['TABLE_PREFIX'].'em_field';


$bezeichner = "Tabelle";

$func = rex_request("func","string","");
$page = rex_request("page","string","");
$subpage = rex_request("subpage","string","");
$table_id = rex_request("table_id","int");


//------------------------------
if($func == "add" || $func == "edit")
{
	
	if($func == "edit")
		echo '<div class="rex-area"><h3 class="rex-hl2">Tabelle editieren</h3><div class="rex-area-content">';
	else
		echo '<div class="rex-area"><h3 class="rex-hl2">Tabelle hinzuf�gen</h3><div class="rex-area-content">';
		
	// ***** Allgemeine BE Felder reinlegen
	$form_data = "\n".'hidden|page|'.$page.'|REQUEST|no_db'."\n".'hidden|subpage|'.$subpage.'|REQUEST|no_db';
	$form_data.= "\n".'hidden|func|'.$func.'|REQUEST|no_db';


	$xform = new rex_xform;
	// $xform->setDebug(TRUE);
	$xform->objparams["actions"][] = array("type" => "showtext","elements" => array("action","showtext",'','<p>Vielen Dank f�r die Eintragung</p>',"",),);
	$xform->setObjectparams("main_table",$table); // f�r db speicherungen und unique abfragen

	if($func == "edit")
	{
    $form_data .= "\nshowvalue|label|Label";
		$form_data .= "\n".'hidden|table_id|'.$table_id.'|REQUEST|no_db';
		$xform->objparams["actions"][] = array("type" => "db","elements" => array("action","db",$table,"id=$table_id"),);
		$xform->setObjectparams("main_id",$table_id);
		$xform->setObjectparams("main_where","id=$table_id");
		$xform->setGetdata(true); // Datein vorher auslesen
	}elseif($func == "add")
	{
    $form_data .= "\ntext|label|Label";
    $form_data .= "\nvalidate|notEmpty|label|Bitte tragen Sie das Label ein"; // nicht leer
    $form_data .= "\nvalidate|preg_match|label|/[a-z_]*/i|Bitte tragen Sie beim Label nur Buchstaben ein";
    $form_data .= "\n".'validate|customfunction|label|rex_em_checkLabelInTable||Dieses Label ist bereits vorhanden|';
		$xform->objparams["actions"][] = array("type" => "db","elements" => array("action","db",$table),);
	}
	
  $form_data.= "\n".'text|name|Name|';
  $form_data.= "\n".'textarea|description|Beschreibung|';
  $form_data.= "\n".'checkbox|status|Aktiv|';
  $form_data.= "\n".'validate|empty|name|Bitte den Namen eingeben';
	
  $form_data = trim(str_replace("<br />","",rex_xform::unhtmlentities($form_data)));
  $xform->setFormData($form_data);
	echo $xform->getForm();

	echo '</div></div>';
	
	echo '<br />&nbsp;<br /><table cellpadding="5" class="rex-table"><tr><td><a href="index.php?page='.$page.'&amp;subpage='.$subpage.'"><b>&laquo; '.$I18N->msg('back_to_overview').'</b></a></td></tr></table>';
	
}






//------------------------------> L�schen
if($func == "delete"){
	$query = "delete from $table where id='".$table_id."' ";
	$delsql = new rex_sql;
	// $delsql->debugsql=1;
	$delsql->setQuery($query);
  $query = "delete from $table_field where table_id='".$table_id."' ";
	$delsql->setQuery($query);
  
	$func = "";
	echo rex_info($bezeichner." wurde gel&ouml;scht");
}


//------------------------------> Liste
if($func == ""){
	
	echo "<table cellpadding=5 class=rex-table><tr><td><a href=index.php?page=".$page."&subpage=".$subpage."&func=add><b>+ $bezeichner anlegen</b></a></td></tr></table><br />";
	
	$sql = "select * from $table order by name";

	$list = rex_list::factory($sql,30);
	$list->setColumnFormat('id', 'Id');

	$list->setColumnParams("id", array("table_id"=>"###id###","func"=>"edit"));
	// $list->setColumnParams("login", array("table_id"=>"###id###","func"=>"edit"));

	$list->removeColumn("id");
	
	$list->addColumn('editieren','editieren');
	$list->setColumnParams("editieren", array("table_id"=>"###id###","func"=>"edit"));
	
	$list->addColumn('Felder_editieren','Felder editieren');
	$list->setColumnParams("Felder_editieren", array("subpage"=>"field","table_id"=>"###id###"));

	$list->addColumn('l&ouml;schen','l&ouml;schen');
	$list->setColumnParams("l&ouml;schen", array("table_id"=>"###id###","func"=>"delete"));
	

	echo $list->get();

}