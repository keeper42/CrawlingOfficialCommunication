<?php
/*
 * User: LJF
 * Date: 2017-3-27
 */
	require 'Medoo.php';
	require 'database_info.php';
	use Medoo\Medoo;
	$database=new Medoo([
	  'database_type'=>DATABASE_TYPE,
	  'database_name'=>DATABASE_NAME,
	  'server'=>SERVER,
	  'username'=>USERNAME,
	  'password'=>PASSWORD,
	  'charset'=>CHARSET
	  ]);

	class ContentProcessor
	{
		private $database;
		protected $URL="http://www.szu.edu.cn/board/";
		protected $RawContent;
		public $ParsedContent=array();

		function Get()
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->URL);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			$this->RawContent= curl_exec($curl);
			curl_close($curl);
			$this->RawContent=iconv("GBK","UTF-8",$this->RawContent);
		}

		function Parse() 
		{
			preg_match_all('/<td align="center">\d+<\/td>(.*)<td align="center" style="font-size: 9pt">.*<\/td>.*<\/tr>/iUs', $this->RawContent, $table);
			foreach ($table[0] as $table)
			{
				preg_match_all('/<td align="center" style=".*">(.*)<\/td>/', $table, $date);
				preg_match_all('/<a href="\?infotype=.*">(.*)<\/a>/', $table, $category);
				preg_match_all('/<a href=# onclick=".*">(.*)<\/a>/', $table, $department);
				preg_match_all('/<a.*>(.*)<\/a>/', $table, $title);
				$titletxt = strip_tags($title[0][2]);
				preg_match_all('/<a target=_blank href="(.*)".*<\/a>/', $table, $detail);
				$full_url = $this->URL."{$detail[1][0]}";
				$this->ParsedContent[]=array(
					"category"=>$category[1][0],
					"department"=>$department[1][0],
					"title"=>$titletxt,
					"date"=>$date[1][2],
					"url"=>$full_url
				);
			}
		}

		function Hash()
		{
			return md5(serialize($this->ParsedContent));
		}

		function Store()
		{
			foreach ($this->ParsedContent as $item) 
			{
				$this->database->insert("remind",[
					"category"=>$item['category'],
					"department"=>$item['department'],
					"title"=>$item['title'],
					"date"=>$item['date'],
					"url"=>$item['url']
				]);
			}
			//generate a hash for the later check if content is updated
			if($this->database->get("last_hash","last_hash")=="")
			{
				$this->database->insert("last_hash",[
					"last_hash"=>$this->Hash()
				]);
			}
			else
			{
				$this->database->update("last_hash",[
					"last_hash"=>$this->Hash()
				]);
			}
		}

		public function __construct(Medoo $database)
		{
			$this->database=$database;
		}

		function isUpdated()
		{
			$last_hash=$this->database->get("last_hash","last_hash");
			if ($last_hash[0] == $this->Hash())
			{
				return false;
			}
			else return true;
		}
	}

	$content=new ContentProcessor($database);
	$content->Get();
	$content->Parse();
	if ($content->isUpdated()==true) $content->Store();

?>