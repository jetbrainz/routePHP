<?php

class Country
{
	static private $listCodes = null;
	static private $listPhonesCodes = null;
	static private $listNames;
	static private $list;
	
	static public function isExist($code)
	{
		self::formatList();
		return isset(self::$list[$code]);
	}
	
	static public function getCodeByIP($ip=null)
	{
		if (!$ip && isset ($_SERVER['REMOTE_ADDR'])) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		if (!$ip) {
			return 'CY';
		}

		$fc = strtoupper(trim(file_get_contents("http://ipinfo.io/{$ip}/country")));
		return $fc;
	}
	
	static public function getCode($string)
	{
		$string = strtolower($string);
		self::formatList();
		foreach (self::getList() as $code=>$name) {
			if ($string == strtolower($code)) {
				return $code;
			}
			if ($string == strtolower($name)) {
				return $code;
			}
		}
		return false;
	}
	
	static public function getPhoneCode($countryCode)
	{
		self::formatPhonesList();
		if (isset (self::$listPhonesCodes[$countryCode])) {
			return self::$listPhonesCodes[$countryCode];
		}
		return null;
	}
	
	static public function getName($string)
	{
		$string = strtolower($string);
		self::formatList();
		foreach (self::getList() as $code=>$name) {
			if ($string == strtolower($code)) {
				return $name;
			}
			if ($string == strtolower($name)) {
				return $name;
			}
		}
		return false;
	}
	
	static public function getList()
	{
		self::formatList();
		return self::$list;
	}
	
	static public function getListCodes()
	{
		self::formatList();
		return self::$listCodes;
	}
	
	static public function getListNames()
	{
		self::formatList();
		return self::$listNames;
	}
	
	static private function formatList()
	{
		if (self::$listCodes !== null) {
			return;
		}
		$listCodes = array ();
		$listNames = array ();
		$list = array ();
		preg_match_all('/([^\;]+);(\w{2})/', self::$listSrc, $m);
		foreach ($m[2] as $index=>$code) {
			if ($code == 'US' || $code == 'UM') {
				continue;
			}
			$listCodes[] = trim($code);
			$listNames[] = trim($m[1][$index]);
			$list[$code] = trim($m[1][$index]);
		}
		self::$listCodes = $listCodes;
		self::$listNames = $listNames;
		self::$list = $list;
	}
	
	static public function getPhonesCodes()
	{
		self::formatPhonesList();
		return self::$listPhonesCodes;
	}
	
	static private function formatPhonesList()
	{
		if (self::$listPhonesCodes !== null) {
			return;
		}
		$list = array ();
		preg_match_all('/(\w{2}),([\d\-]*)/', self::$phonesListSrc, $m);
		foreach ($m[2] as $index=>$code) {
			$list[strtoupper(trim($m[1][$index]))] = preg_replace('/\D/', '', trim($m[2][$index]));
		}
		self::$listPhonesCodes = $list;
	}
	
	/**
	 *  http://www.iso.org/iso/country_names_and_code_elements_txt
	 *  ISO-3166-1 alpha-2
	 */
	static private $listSrc = "
		AFGHANISTAN;AF
		ÅLAND ISLANDS;AX
		ALBANIA;AL
		ALGERIA;DZ
		AMERICAN SAMOA;AS
		ANDORRA;AD
		ANGOLA;AO
		ANGUILLA;AI
		ANTARCTICA;AQ
		ANTIGUA AND BARBUDA;AG
		ARGENTINA;AR
		ARMENIA;AM
		ARUBA;AW
		AUSTRALIA;AU
		AUSTRIA;AT
		AZERBAIJAN;AZ
		BAHAMAS;BS
		BAHRAIN;BH
		BANGLADESH;BD
		BARBADOS;BB
		BELARUS;BY
		BELGIUM;BE
		BELIZE;BZ
		BENIN;BJ
		BERMUDA;BM
		BHUTAN;BT
		BOLIVIA, PLURINATIONAL STATE OF;BO
		BONAIRE, SINT EUSTATIUS AND SABA;BQ
		BOSNIA AND HERZEGOVINA;BA
		BOTSWANA;BW
		BOUVET ISLAND;BV
		BRAZIL;BR
		BRITISH INDIAN OCEAN TERRITORY;IO
		BRUNEI DARUSSALAM;BN
		BULGARIA;BG
		BURKINA FASO;BF
		BURUNDI;BI
		CAMBODIA;KH
		CAMEROON;CM
		CANADA;CA
		CAPE VERDE;CV
		CAYMAN ISLANDS;KY
		CENTRAL AFRICAN REPUBLIC;CF
		CHAD;TD
		CHILE;CL
		CHINA;CN
		CHRISTMAS ISLAND;CX
		COCOS (KEELING) ISLANDS;CC
		COLOMBIA;CO
		COMOROS;KM
		CONGO;CG
		CONGO, THE DEMOCRATIC REPUBLIC OF THE;CD
		COOK ISLANDS;CK
		COSTA RICA;CR
		CÔTE D'IVOIRE;CI
		CROATIA;HR
		CUBA;CU
		CURAÇAO;CW
		CYPRUS;CY
		CZECH REPUBLIC;CZ
		DENMARK;DK
		DJIBOUTI;DJ
		DOMINICA;DM
		DOMINICAN REPUBLIC;DO
		ECUADOR;EC
		EGYPT;EG
		EL SALVADOR;SV
		EQUATORIAL GUINEA;GQ
		ERITREA;ER
		ESTONIA;EE
		ETHIOPIA;ET
		FALKLAND ISLANDS (MALVINAS);FK
		FAROE ISLANDS;FO
		FIJI;FJ
		FINLAND;FI
		FRANCE;FR
		FRENCH GUIANA;GF
		FRENCH POLYNESIA;PF
		FRENCH SOUTHERN TERRITORIES;TF
		GABON;GA
		GAMBIA;GM
		GEORGIA;GE
		GERMANY;DE
		GHANA;GH
		GIBRALTAR;GI
		GREECE;GR
		GREENLAND;GL
		GRENADA;GD
		GUADELOUPE;GP
		GUAM;GU
		GUATEMALA;GT
		GUERNSEY;GG
		GUINEA;GN
		GUINEA-BISSAU;GW
		GUYANA;GY
		HAITI;HT
		HEARD ISLAND AND MCDONALD ISLANDS;HM
		HOLY SEE (VATICAN CITY STATE);VA
		HONDURAS;HN
		HONG KONG;HK
		HUNGARY;HU
		ICELAND;IS
		INDIA;IN
		INDONESIA;ID
		IRAN, ISLAMIC REPUBLIC OF;IR
		IRAQ;IQ
		IRELAND;IE
		ISLE OF MAN;IM
		ISRAEL;IL
		ITALY;IT
		JAMAICA;JM
		JAPAN;JP
		JERSEY;JE
		JORDAN;JO
		KAZAKHSTAN;KZ
		KENYA;KE
		KIRIBATI;KI
		KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF;KP
		KOREA, REPUBLIC OF;KR
		KUWAIT;KW
		KYRGYZSTAN;KG
		LAO PEOPLE'S DEMOCRATIC REPUBLIC;LA
		LATVIA;LV
		LEBANON;LB
		LESOTHO;LS
		LIBERIA;LR
		LIBYA;LY
		LIECHTENSTEIN;LI
		LITHUANIA;LT
		LUXEMBOURG;LU
		MACAO;MO
		MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF;MK
		MADAGASCAR;MG
		MALAWI;MW
		MALAYSIA;MY
		MALDIVES;MV
		MALI;ML
		MALTA;MT
		MARSHALL ISLANDS;MH
		MARTINIQUE;MQ
		MAURITANIA;MR
		MAURITIUS;MU
		MAYOTTE;YT
		MEXICO;MX
		MICRONESIA, FEDERATED STATES OF;FM
		MOLDOVA, REPUBLIC OF;MD
		MONACO;MC
		MONGOLIA;MN
		MONTENEGRO;ME
		MONTSERRAT;MS
		MOROCCO;MA
		MOZAMBIQUE;MZ
		MYANMAR;MM
		NAMIBIA;NA
		NAURU;NR
		NEPAL;NP
		NETHERLANDS;NL
		NEW CALEDONIA;NC
		NEW ZEALAND;NZ
		NICARAGUA;NI
		NIGER;NE
		NIGERIA;NG
		NIUE;NU
		NORFOLK ISLAND;NF
		NORTHERN MARIANA ISLANDS;MP
		NORWAY;NO
		OMAN;OM
		PAKISTAN;PK
		PALAU;PW
		PALESTINIAN TERRITORY, OCCUPIED;PS
		PANAMA;PA
		PAPUA NEW GUINEA;PG
		PARAGUAY;PY
		PERU;PE
		PHILIPPINES;PH
		PITCAIRN;PN
		POLAND;PL
		PORTUGAL;PT
		PUERTO RICO;PR
		QATAR;QA
		RÉUNION;RE
		ROMANIA;RO
		RUSSIAN FEDERATION;RU
		RWANDA;RW
		SAINT BARTHÉLEMY;BL
		SAINT HELENA, ASCENSION AND TRISTAN DA CUNHA;SH
		SAINT KITTS AND NEVIS;KN
		SAINT LUCIA;LC
		SAINT MARTIN (FRENCH PART);MF
		SAINT PIERRE AND MIQUELON;PM
		SAINT VINCENT AND THE GRENADINES;VC
		SAMOA;WS
		SAN MARINO;SM
		SAO TOME AND PRINCIPE;ST
		SAUDI ARABIA;SA
		SENEGAL;SN
		SERBIA;RS
		SEYCHELLES;SC
		SIERRA LEONE;SL
		SINGAPORE;SG
		SINT MAARTEN (DUTCH PART);SX
		SLOVAKIA;SK
		SLOVENIA;SI
		SOLOMON ISLANDS;SB
		SOMALIA;SO
		SOUTH AFRICA;ZA
		SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS;GS
		SOUTH SUDAN;SS
		SPAIN;ES
		SRI LANKA;LK
		SUDAN;SD
		SURINAME;SR
		SVALBARD AND JAN MAYEN;SJ
		SWAZILAND;SZ
		SWEDEN;SE
		SWITZERLAND;CH
		SYRIAN ARAB REPUBLIC;SY
		TAIWAN, PROVINCE OF CHINA;TW
		TAJIKISTAN;TJ
		TANZANIA, UNITED REPUBLIC OF;TZ
		THAILAND;TH
		TIMOR-LESTE;TL
		TOGO;TG
		TOKELAU;TK
		TONGA;TO
		TRINIDAD AND TOBAGO;TT
		TUNISIA;TN
		TURKEY;TR
		TURKMENISTAN;TM
		TURKS AND CAICOS ISLANDS;TC
		TUVALU;TV
		UGANDA;UG
		UKRAINE;UA
		UNITED ARAB EMIRATES;AE
		UNITED KINGDOM;GB
		UNITED STATES;US
		UNITED STATES MINOR OUTLYING ISLANDS;UM
		URUGUAY;UY
		UZBEKISTAN;UZ
		VANUATU;VU
		VENEZUELA, BOLIVARIAN REPUBLIC OF;VE
		VIET NAM;VN
		VIRGIN ISLANDS, BRITISH;VG
		VIRGIN ISLANDS, U.S.;VI
		WALLIS AND FUTUNA;WF
		WESTERN SAHARA;EH
		YEMEN;YE
		ZAMBIA;ZM
		ZIMBABWE;ZW";
	
	/**
	 * http://www.science.co.il/International/Country-codes.asp
	 * @var type 
	 */
	static private $phonesListSrc="
		af,93
		al,355
		dz,213
		as,684
		ad,376
		ao,244
		ai,1-264
		aq,672
		ag,1-268
		ar,54
		am,374
		aw,297
		au,61
		at,43
		az,994
		bs,1-242
		bh,973
		bd,880
		bb,1-246
		by,375
		be,32
		bz,501
		bj,229
		bm,1-441
		bt,975
		bo,591
		ba,387
		bw,267
		bv,
		br,55
		io,
		bn,673
		bg,359
		bf,226
		bi,257
		kh,855
		cm,237
		ca,1
		cv,238
		ky,1-345
		cf,236
		td,235
		cl,56
		cn,86
		cx,61
		cc,61
		co,57
		km,269
		cg,242
		cd,243
		ck,682
		cr,506
		hr,385
		cu,53
		cy,357
		cz,420
		dk,45
		dj,253
		dm,1-767
		do,809
		ec,593
		eg,20
		sv,503
		gq,240
		er,291
		ee,372
		et,251
		eu.int,
		fk,500
		fo,298
		fj,679
		fi,358
		fr,33
		gf,594
		tf,
		ga,241
		gm,220
		ge,995
		de,49
		gh,233
		gi,350
		gb,44
		gr,30
		gl,299
		gd,1-473
		gp,590
		gu,1-671
		gt,502
		gg,
		gn,224
		gw,245
		gy,592
		ht,509
		hm,
		hn,504
		hk,852
		hu,36
		is,354
		in,91
		id,62
		ir,98
		iq,964
		ie,353
		im,
		il,972
		it,39
		ci,225
		jm,1-876
		jp,81
		je,
		jo,962
		kz,7
		ke,254
		ki,686
		kp,850
		kr,82
		kw,965
		kg,996
		la,856
		lv,371
		lb,961
		ls,266
		lr,231
		ly,218
		li,423
		lt,370
		lu,352
		mo,853
		mk,389
		mg,261
		mw,265
		my,60
		mv,960
		ml,223
		mt,356
		mh,692
		mq,596
		mr,222
		mu,230
		yt,269
		mx,52
		fm,691
		md,373
		mc,377
		mn,976
		me,382
		ms,1-664
		ma,212
		mz,258
		mm,95
		na,264
		nr,674
		np,977
		nl,31
		an,599
		nc,687
		nz,64
		ni,505
		ne,227
		ng,234
		nu,683
		nf,672
		mp,670
		no,47
		om,968
		pk,92
		pw,680
		pa,507
		pg,675
		py,595
		pe,51
		ph,63
		pn,
		pl,48
		pf,689
		pt,351
		pr,1-787
		qa,974
		re,262
		ro,40
		ru,7
		rw,250
		sh,290
		kn,1-869
		lc,1-758
		pm,508
		vc,1-784
		ws,684
		sm,378
		st,239
		sa,966
		sn,221
		rs,381
		sc,248
		sl,232
		sg,65
		sk,421
		si,386
		sb,677
		so,252
		za,27
		gs,
		ss,
		es,34
		lk,94
		sd,249
		sr,597
		sj,
		sz,268
		se,46
		ch,41
		sy,963
		tw,886
		tj,992
		tz,255
		th,66
		tg,228
		tk,690
		to,676
		tt,1-868
		tn,216
		tr,90
		tm,993
		tc,1-649
		tv,688
		uk,44
		ug,256
		ua,380
		ae,971
		uy,598
		us,1
		um,
		uz,998
		vu,678
		va,39
		ve,58
		vn,84
		vg,1-284
		vi,1-340
		wf,681
		eh,
		ye,967
		zm,260
		zw,263";
}
