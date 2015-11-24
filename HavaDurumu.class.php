<?php

/**
 * Class HavaDurumu
 * @author Agit Çiçek
 * @mail agitcicek@outlook.com
 * @date 24.11.2015
 */

class HavaDurumu
{

	private $_cities = array(
		01 => 'Adana', 
		02 => 'Adıyaman',
		03 => 'Afyon',
		04 => 'Ağrı',
		05 => 'Amasya',
		06 => 'Ankara',
		07 => 'Antalya',
		08 => 'Artvin',
		09 => 'Aydın',
		10 => 'Balıkesir',
		11 => 'Bilecik',
		12 => 'Bingöl',
		13 => 'Bitlis',
		14 => 'Bolu',
		15 => 'Burdur', 
		16 => 'Bursa',
		17 => 'Çanakkale', 
		18 => 'Çankırı', 
		19 => 'Çorum', 
		20 => 'Denizli', 
		21 => 'Diyarbakır',  
		22 => 'Edirne', 
		23 => 'Elazığ', 
		24 => 'Erzincan', 
		25 => 'Erzurum', 
		26 => 'Eskişehir', 
		27 => 'Gaziantep', 
		28 => 'Giresun', 
		29 => 'Gümüşhane', 
		30 => 'Hakkari', 
		31 => 'Hatay', 
		32 => 'Isparta', 
		33 => 'İçel (Mersin)',  
		34 => 'İstanbul', 
		35 => 'İzmir',  
		36 => 'Kars',  
		37 => 'Kastamonu',  
		38 => 'Kayseri',  
		39 => 'Kırklareli',  
		40 => 'Kırşehir',  
		41 => 'Kocaeli',  
		42 => 'Konya',  
		43 => 'Kütahya', 
		44 => 'Malatya', 
		45 => 'Manisa', 
		46 => 'Kahramanmaraş', 
		47 => 'Mardin', 
		48 => 'Muğla', 
		49 => 'Muş', 
		50 => 'Nevşehir', 
		51 => 'Niğde',  
		52 => 'Ordu', 
		53 => 'Rize', 
		54 => 'Sakarya', 
		55 => 'Samsun',  
		56 => 'Siirt',  
		57 => 'Sinop',  
		58 => 'Sivas', 
		59 => 'Tekirdağ',  
		60 => 'Tokat',  
		61 => 'Trabzon', 
		62 => 'Tunceli',  
		63 => 'Şanlıurfa',  
		64 => 'Uşak',  
		65 => 'Van',  
		66 => 'Yozgat',  
		67 => 'Zonguldak',  
		68 => 'Aksaray',  
		69 => 'Bayburt',  
		70 => 'Karaman',  
		71 => 'Kırıkkale',  
		72 => 'Batman',  
		73 => 'Şırnak',  
		74 => 'Bartın ', 
		75 => 'Ardahan',  
		76 => 'Iğdır',  
		77 => 'Yalova', 
		78 => 'Karabük',  
		79 => 'Kilis',  
		80 => 'Osmaniye', 
		81 => 'Düzce'
	);
	
	private $_default_city = 34; # Varsayılan Şehir: İstanbul
	private $_selected_city = NULL;
	private $_results = NULL;

	public function __construct()
	{
		if($this->_selected_city == NULL)
		{
			$this->_selected_city = $this->_default_city;
		}
	}

	private function curlRequest($url){
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_URL,$url);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
		curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($curl, CURLOPT_REFERER, "googlebot");
		curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, 0);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)");
		$data = curl_exec($curl);
		curl_close($curl);
		$data = str_replace(array("\n","\t","\r"),NULL,$data);
		return $data;
	}

	private function cityNameFormat($string)
	{
		$tr		=	array('ş','ç','Ş','Ç','Ö','ö','Ü','ü','İ','ı','Ğ','ğ');
		$en		=	array('s','c','S','C','O','o','U','u','I','i','G','g');
		$string	=	str_replace($tr,$en,$string);
		return strtoupper($string);
	}

	private function process()
	{
		$city = $this->_cities[ $this->_selected_city ];
		$city = $this->cityNameFormat($city);

		$result = $this->curlRequest('http://www.mgm.gov.tr/tahmin/il-ve-ilceler.aspx?m=' . $city );

		if($result)
		{
			$data = array(
				'sehir_adi' => $this->_cities[ $this->_selected_city ],
				'genel_ozellikler' => array(), 
				'son_durum' => array(), 
				'tahminler' => array()
			);

			$result = str_replace('src="../', 'src="http://www.mgm.gov.tr/', $result);

			preg_match('#<div id="divMerkez">(.*?)</div><div id="divSonDurum">#', $result, $merkez_div);
			$merkez_div = $merkez_div[1];
			$merkez_div = str_replace('&nbsp;', '', $merkez_div);
			preg_match_all('#<strong>(.*?)</strong>(.*?)</p>#', $merkez_div, $merkez_div);

			$data['genel_ozellikler']['yukseklik'] = $merkez_div[2][0];
			$data['genel_ozellikler']['boylam'] = $merkez_div[2][1];
			$data['genel_ozellikler']['enlem'] = $merkez_div[2][2];
			$data['genel_ozellikler']['gun_batimi'] = $merkez_div[2][3];
			$data['genel_ozellikler']['gun_dogumu'] = $merkez_div[2][4];


			preg_match('#<table class="tbl_sond">(.*?)</table>#', $result, $tbl_sondurum);
			$tbl_sondurum = $tbl_sondurum[1];
			$tbl_sondurum = str_replace(array('<br .>','<br />','<em>','</em>','<em class="renkMax">'), array(' ','','','',''), $tbl_sondurum);
			preg_match_all('#<td(.*?)>(.*?)</td>#', $tbl_sondurum, $tbl_sondurum);
			
			$tbl_sondurum_durum = $tbl_sondurum[2][0];
			preg_match('#<img src="(.*?)" alt="(.*?)" />#', $tbl_sondurum_durum, $tbl_sondurum_durum);
				$data['son_durum']['durum']['baslik'] = $tbl_sondurum_durum[2];
				$data['son_durum']['durum']['icon'] = $tbl_sondurum_durum[1];
				$data['son_durum']['tarih'] = $tbl_sondurum[2][1];
				$data['son_durum']['sicaklik'] = $tbl_sondurum[2][2];
				$data['son_durum']['nem'] = $tbl_sondurum[2][3];

			$tbl_sondurum_ruzgar = $tbl_sondurum[2][4];
			preg_match('#<img src="(.*?)" alt="(.*?)" /> (.*?) km/sa#', $tbl_sondurum_ruzgar, $tbl_sondurum_ruzgar);

				$data['son_durum']['ruzgar']['hiz'] = $tbl_sondurum_ruzgar[3]." km/sa";
				$data['son_durum']['ruzgar']['yon'] = $tbl_sondurum_ruzgar[2];
				$data['son_durum']['ruzgar']['icon'] = $tbl_sondurum_ruzgar[1];
				
			$data['son_durum']['basinc'] = $tbl_sondurum[2][5];
			$data['son_durum']['gorus'] = $tbl_sondurum[2][6];

			preg_match('#<table class="tbl_thmn">(.*?)</table>#', $result, $tbl_thmn);
			$tbl_thmn = $tbl_thmn[1];
			preg_match('#<tbody>(.*?)</tbody>#', $tbl_thmn, $tbl_thmn);
			$tbl_thmn = $tbl_thmn[1];
			preg_match_all('#<tr>(.*?)</tr>#', $tbl_thmn, $tbl_thmn);
			$tbl_thmn = $tbl_thmn[1];

			$i = 0;
			foreach ($tbl_thmn as $tbl_thmn_item) {

				preg_match('#<th id="cp_sayfa_thmGun(.*?)" class="(.*?)">(.*?)</th>#', $tbl_thmn_item, $tbl_thmn_item_tarih);
				$data['tahminler'][$i]['tarih'] = $tbl_thmn_item_tarih[3];

				preg_match('#<td id="(.*?)" class="(.*?)minS">(.*?)</td>#', $tbl_thmn_item, $tbl_thmn_item_sicaklik_endusuk);
				$data['tahminler'][$i]['sicaklik']['endusuk'] = $tbl_thmn_item_sicaklik_endusuk[3];

				preg_match('#<td id="(.*?)" class="(.*?)maxS">(.*?)</td>#', $tbl_thmn_item, $tbl_thmn_item_sicaklik_enyuksek);
				$data['tahminler'][$i]['sicaklik']['enyuksek'] = $tbl_thmn_item_sicaklik_enyuksek[3];

				preg_match('#<td title="(.*?)"><img id="cp_sayfa_imgHadise(.*?)" src="(.*?)" alt="(.*?)" /></td>#', $tbl_thmn_item, $tbl_thmn_item_durum);
				$data['tahminler'][$i]['durum']['baslik'] = $tbl_thmn_item_durum[4];
				$data['tahminler'][$i]['durum']['icon'] = $tbl_thmn_item_durum[3];

				preg_match('#<td id="(.*?)" class="(.*?)minN">(.*?)</td>#', $tbl_thmn_item, $tbl_thmn_item_nem_endusuk);
				$data['tahminler'][$i]['nem']['endusuk'] = $tbl_thmn_item_nem_endusuk[3];

				preg_match('#<td id="(.*?)" class="(.*?)maxN">(.*?)</td>#', $tbl_thmn_item, $tbl_thmn_item_nem_enyuksek);
				$data['tahminler'][$i]['nem']['enyuksek'] = $tbl_thmn_item_nem_enyuksek[3];

				preg_match('#<td id="cp_sayfa_thmRuzgarHiz(.*?)">(.*?)</td>#', $tbl_thmn_item, $tbl_thmn_item_ruzgar_hiz);
				$data['tahminler'][$i]['ruzgar']['hiz'] = $tbl_thmn_item_ruzgar_hiz[2]." km/sa";

				preg_match('#<td title="(.*?)"><img id="cp_sayfa_imgRyon(.*?)" src="(.*?)" alt="(.*?)" /></td>#', $tbl_thmn_item, $tbl_thmn_item_ruzgar);
				$data['tahminler'][$i]['ruzgar']['yon'] = $tbl_thmn_item_ruzgar[4];
				$data['tahminler'][$i]['ruzgar']['icon'] = $tbl_thmn_item_ruzgar[3];

				$i++;
			}

			$this->_results = $data;
			return $data;

		}

		return false;
	}

	public function get($city = NULL)
	{

		if($city)
		{
			$city = intval($city);

			if( isset( $this->_cities[$city] ) )
			{
				$this->_selected_city = $city;
			}else
			{
				$this->_selected_city = $this->_default_city;
			}

		}

		$this->process();

		if( $this->_results )
		{
			return $this->_results;
		}

		return false;
	}

}
