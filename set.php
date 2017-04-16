<?php
	require_once "Medoo.php";
	require_once "database_info.php";
	use Medoo\Medoo;
	$database=new Medoo([	
	  'database_type'=>DATABASE_TYPE,
	  'database_name'=>DATABASE_NAME,
	  'server'=>SERVER,
	  'username'=>USERNAME,
	  'password'=>PASSWORD,
	  'charset'=>CHARSET
	  ]);
	$database->insert("keywords",[
		"keyword"=>$_POST["keyword"]
	]);
	if($database->get("check_interval","check_interval")=="")
	{
		$database->insert("check_interval",[
			"check_interval"=>$_POST["interval"]
		]);
	}
	else
	{
		$database->update("check_interval",[
			"check_interval"=>$_POST["interval"]
		]);
	}
	echo "success";
?>
