<?php
/*
 yr.php  -  YR.no forecast on YOUR page!

 This script was downloaded from http://www.yr.no/verdata/1.5542682
 New page: http://om.yr.no/verdata/php/
 Please read the tips on that page on how you would/should use this script

 You need a webserver with PHP version 5 or later to run this script.
 A lot of comments are in Norwegian only. We will be translating to english whenever we have the opportunity.
 For feedback / bug repports / feature requests, please contact us: http://om.yr.no/sporsmal/kontakt-yr-no

 ###### Changelog

 Version 3.1 - Andreas Røste (andreas200@live.no) 2016.08.15 16.45
 * Fixed multiple declaretions in generateHTMLCached.
 * Corrected link to copyright.
 * Small bugfixes. This API now fully works with PHP 7.1!

 Version: 3.0 - Marius Undrum (marius.undrum@nrk.no) / NRK 2015.09.02
 * Changed encoding from ISO-8859-1 to UTF-8
 * Updated css url
 * Updated weather symbols url

 Versjon: 2.6 - Lennart André Rolland (lennart.andre.rolland@nrk.no) / NRK - 2008.11.11 11:48
 * Added option to remove banner ($yr_use_banner)
 * Added option to allow any target for yr.no urls ($yr_link_target)

 Versjon: 2.5 - Lennart André Rolland (lennart.andre.rolland@nrk.no) / NRK - 2008.09.25 09:24
 * Cache will now update on parameter changes (cache file is prefixed with md5 digest of all relevant parameters)
   This change will in the future make it easier to use the script for multiple locations in one go.
 * Most relevant comments translated to english

 Versjon 2.4 - Sven-Ove Bjerkan (sven-ove@smart-media.no) / Smart-Media AS - 2008.10.22 12:14
 * Endret funksjonalitet ifbm med visning av PHP-feil (fjernet blant annet alle "@", dette styres av error_reporting())
 * Ved feilmelding så ble denne lagret i lokal cache slik at man fikk opp feilmld hver gang inntil "$yr_maxage" inntreffer og den forsøker å laste på nytt - den cacher nå ikke hvis det oppstår en feil
 * $yr_use_text, $yr_use_links og $yr_use_table ble overstyrt til "true" uavhengig av brukerens innstilling - rettet!

 Versjon: 2.3 - Lennart André Rolland (lennart.andre.rolland@nrk.no) / NRK - 2008.09.25 09:24
 * File permissions updated
 * Caching is stored in HTML isntead of XML for security
 * Other security and efficiency improvements



 ###### INSTRUCTIONS:

 1. Edit this script in editors with UTF-8 character set.
 2. Edit the settings below
 3. Transfer the script to a folder in your webroot.
 4. Make sure that the webserver has write access to the folder where thsi script is placed. It will create a folder called yr-cache and place cached HTML data in that directory.

 */

///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  /
///  ///  ///  ///  ///  Settings  ///  ///  ///  ///  ///  ///  ///  ///  //
//  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///

// 1. Lenke: Lenke til stedet på yr.no (Uten siste skråstrek. Bruk vanlig æøå i lenka )
//    Link: Link to the url for the location on yr.no (Without the last Slash.)
$yr_url='https://www.yr.no/sted/Norge/Buskerud/Ringerike/Hønefoss';

// 2. Stedsnavnet: Skriv inn navnet på stedet. La stå tom for å falle tilbake til navnet i lenken
//    Location: The name of the location. Leave empty to fallback to the location in the url.
$yr_name='Hønefoss';

// 3. Bruk header og footer: Velg om du vil ha med header og/eller  footer
//    Use Header and footers: Select to have HTML headers/footers wrapping the content (useful for debugging)
//PS: Header for HTML dokumentet er XHTML 1.0 Strict
//    Skrus som regel av når du inlemmer i eksisterende dokument!
//
$yr_use_header=$yr_use_footer=true;

// 4. Deler: Velg delene av varselet du vil ta med!
//    Parts: Choose which parts of the forecast to include
$yr_use_banner=true; //yr.no Banner
$yr_use_text=false;   //Tekstvarsel
$yr_use_links=true;  //Lenker til varsel på yr.no
$yr_use_table=true;  //Tabellen med varselet

// 5. Mellomlagringstid: Antall sekunder før nytt varsel hentes fra yr.no.
//    Cachetime: Number of seconds to keep forecast in local cache
//    Den anbefalt verdien på 1200 vil oppdatere siden hver 20. minutt.
//
//    PS: Vi ønsker at du setter 20 minutters mellomlagringstid fordi
//    det vil gi høyere ytelse, både for yr.no og deg! MEN for å få til dette
//    vil vi opprette en mappe og lagre en fil i denne mappen. Vi har gått
//    gjennom scriptet veldig nøye for å forsikre oss om at det er feilfritt.
//    Likevel er dette ikke helt uproblematisk i forhold til sikkerhet.
//    Hvis du har problemer med dette kan du sette $yr_maxage til 0 for å skru
//    av mellomlagringen helt!
$yr_maxage=1200;

// 6. Utløpstid: Denne instillingen lar deg velge hvor lenge yr.no har på å
//    levere varselet i sekunder.
//    Timeout: How long before this script gives up fetching data from yr.no
//
//    Hvis yr.no skulle være nede eller det er
//    forstyrrelser i båndbredden ellers, vil varselet erstattes med en
//    feilmelding til situasjonen er bedret igjen. PS: gjelder kun når nytt
//    varsel hentes! Påvirker ikke varsel mens siden viser varsel fra
//    mellomlageret. Den anbefalte verdien på 10 sekunder fungerer bra.
$yr_timeout=10;

// 7. Mellomlagrinsmappe: Velg navn på mappen til mellomlagret data.
//    Cachefolder: Where to put cache data
//
//Dette scriptet vil forsøke å opprette mappen om den ikke finnes.
$yr_datadir='yr_cache';


// 8. Lenke mål: Velg hvilken target som skal brukes på lenker til yr.no
//    Link target: Choose which target to use for links to yr.no
$yr_link_target='_top';

// 9. Vis feilmeldinger: Sett til "true" hvis du vil ha feilmeldinger.
//    Show errors: Useful while debugging.
//
//greit ved feilsøking, men bør ikke være aktivert i drift.
$yr_vis_php_feilmeldinger=true;















///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  /
///  ///  ///  ///  ///  Code ///  ///  ///  ///  ///  ///  ///  ///  ///  //
//  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///
// Skru på feilmeldinger i starten
if($yr_vis_php_feilmeldinger) {
    error_reporting(E_ALL);
    ini_set('display_errors', true);
}
else {
    error_reporting(0);
    ini_set('display_errors', false);
}

//Opprett en komunikasjon med yr
$yr_xmlparse = new YRComms();
//Opprett en presentasjon
$yr_xmldisplay = new YRDisplay();

$yr_try_curl=true;

//Gjenomfør oppdraget basta bom.
die($yr_xmldisplay->generateHTMLCached($yr_url, $yr_name, $yr_xmlparse, $yr_try_curl, $yr_use_header, $yr_use_footer, $yr_use_banner, $yr_use_text, $yr_use_links, $yr_use_table, $yr_maxage, $yr_timeout, $yr_link_target));


///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  /
///  ///  ///  ///  ///  Hjelpekode starter her   ///  ///  ///  ///  ///  //
//  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///  ///


function retar($array, $html = false, $level = 0) {
    if(is_array($array)){
        $space = $html ? "&nbsp;" : " ";
        $newline = $html ? "<br />" : "\n";
        $spaces='';
        for ($i = 1; $i <= 3; $i++)$spaces .= $space;
        $tabs=$spaces;
        for ($i = 1; $i <= $level; $i++)$tabs .= $spaces;
        $output = "Array(" . $newline . $newline;
        $cnt=sizeof($array);
        $j=0;
        foreach($array as $key => $value) {
            $j++;
            if (is_array($value)) {
                $level++;
                $value = retar($value, $html, $level);
                $level--;
            }
            else $value="'$value'";
            $output .=  "$tabs'$key'=> $value";
            if($j<$cnt)$output .=  ',';
            $output .=  $newline;
        }
        $output.=$tabs.')'.$newline;
    }
    else{
        $output="'$array'";
    }
    return $output;
}


// Klasse for lesing og tilrettelegging av YR data
class YRComms{

    //Generer gyldig yr.no array med værdata byttet ut med en enkel feilmelding
    private function getYrDataErrorMessage($msg="Feil"){
        return Array(
            '0'=> Array('tag'=> 'WEATHERDATA','type'=> 'open','level'=> '1'),
            '1'=> Array('tag'=> 'LOCATION','type'=> 'open','level'=> '2'),
            '2'=> Array('tag'=> 'NAME','type'=> 'complete','level'=> '3','value'=> $msg),
            '3'=> Array('tag'=> 'LOCATION','type'=> 'complete','level'=> '3'),
            '4'=> Array( 'tag'=> 'LOCATION', 'type'=> 'close', 'level'=> '2'),
            '5'=> Array( 'tag'=> 'FORECAST', 'type'=> 'open', 'level'=> '2'),
            '6'=> Array( 'tag'=> 'ERROR', 'type'=> 'complete', 'level'=> '3', 'value'=> $msg),
            '7'=> Array( 'tag'=> 'FORECAST', 'type'=> 'close', 'level'=> '2'),
            '8'=> Array( 'tag'=> 'WEATHERDATA', 'type'=> 'close', 'level'=> '1')
        );
    }

    //Generer gyldig yr.no XML med værdata byttet ut med en enkel feilmelding
    private function getYrXMLErrorMessage($msg="Feil"){
        $msg=$this->getXMLEntities($msg);
        //die('errmsg:'.$msg);
        $data=<<<EOT
<weatherdata>
  <location />
  <forecast>
  <error>$msg</error>
    <text>
      <location />
    </text>
  </forecast>
</weatherdata>

EOT
        ;
        //die($data);
        return $data;
    }

    // Sørger for å laste ned XML fra yr.no og leverer data tilbake i en streng
    private function loadXMLData($xml_url,$try_curl=true,$timeout=10){
        global $yr_datadir;
        $xml_url.='/varsel.xml';
        // Lag en timeout på contexten
        $ctx = stream_context_create(array( 'http' => array('timeout' => $timeout)));

        // Prøv å åpne direkte først
        //NOTE: This will spew ugly errors even when they are handled later. There is no way to avoid this but prefixing with @ (slow) or turning off error reporting
        $data=file_get_contents($xml_url,0,$ctx);

        if(false!=$data){
            //Jippi vi klarte det med vanlig fopen url wrappers!
        }
        // Vanlig fopen_wrapper feilet, men vi har cURL tilgjengelig
        else if($try_curl && function_exists('curl_init')){
            $lokal_xml_url = $yr_datadir .'/curl.temp.xml';
            $data='';
            $ch = curl_init($xml_url);
            // Åpne den lokale temp filen for skrive tilgang (med cURL hooks enablet)
            $fp = fopen($lokal_xml_url, "w");
            // Last fra yr.no til lokal kopi med curl
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '');
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_exec($ch);
            curl_close($ch);
            // Lukk lokal kopi
            fclose($fp);
            // Åpne lokal kopi igjen og les in alt innholdet
            $data=file_get_contents($lokal_xml_url,0,$ctx);
            //Slett temp data
            unlink($lokal_xml_url);
            // Sjekk for feil
            if(false==$data)$data=$this->getYrXMLErrorMessage('Det oppstod en feil mens værdata ble lest fra yr.no. Teknisk info: Mest antakelig: kobling feilet. Nest mest antakelig: Det mangler støtte for fopen wrapper, og cURL feilet også. Minst antakelig: cURL har ikke rettigheter til å lagre temp.xml');
        }
        // Vi har verken fopen_wrappers eller cURL
        else{
            $data=$this->getYrXMLErrorMessage('Det oppstod en feil mens værdata ble forsøkt lest fra yr.no. Teknisk info: Denne PHP-installasjon har verken URL enablede fopen_wrappers eller cURL. Dette gjør det umulig å hente ned værdata. Se imiddlertid følgende dokumentasjon: http://no.php.net/manual/en/wrappers.php, http://no.php.net/manual/en/book.curl.php');
            //die('<pre>LO:'.retar($data));
        }
        //die('<pre>XML for:'.$xml_url.' WAS: '.$data);
        // Når vi har kommet hit er det noe som tyder på at vi har lykkes med å laste værdata, ller i det minste lage en teilmelding som beskriver eventuelle problemer
        return $data;
    }

    // Last XML til en array struktur
    private function parseXMLIntoStruct($data){
        global $yr_datadir;
        $parser = xml_parser_create('ISO-8859-1');
        if((0==$parser)||(FALSE==$parser))return $this->getYrDataErrorMessage('Det oppstod en feil mens værdata ble forsøkt hentet fra yr.no. Teknisk info: Kunne ikke lage XML parseren.');
        $vals = array();
        //die('<pre>'.retar($data).'</pre>');
        if(FALSE==xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1))return $this->getYrDataErrorMessage('Det oppstod en feil mens værdata ble forsøkt hentet fra yr.no. Teknisk info: Kunne ikke stille inn XML-parseren.');
        if(0==xml_parse_into_struct($parser, $data, $vals, $index))return $this->getYrDataErrorMessage('Det oppstod en feil mens værdata ble forsøkt hentet fra yr.no. Teknisk info: Parsing av XML feilet.');
        if(FALSE==xml_parser_free($parser))return $this->getYrDataErrorMessage('Det oppstod en feil mens værdata ble forsøkt hentet fra yr.no. Kunne ikke frigjøre XML-parseren.');
        //die('<pre>'.retar($vals).'</pre>');
        return $vals;
    }


    // Rense tekst data (av sikkerhetshensyn)
    private function sanitizeString($in){
        //return $in;
        if(is_array($in))return $in;
        if(null==$in)return null;
        return htmlentities(strip_tags($in));
    }

    // Rense tekst data (av sikkerhetshensyn)
    public function reviveSafeTags($in){
        //$in=$in.'<strong>STRONG</strong> <u>UNDERLINE</u> <b>BOLD</b> <i>ITALICS</i>';
        return str_ireplace(array('&lt;strong&gt;','&lt;/strong&gt;','&lt;u&gt;','&lt;/u&gt;','&lt;b&gt;','&lt;/b&gt;','&lt;i&gt;','&lt;/i&gt;'),array('<strong>','</strong>','<u>','</u>','<b>','</b>','<i>','</i>'),$in);
    }



    private function rearrangeChildren($vals, &$i) {
        $children = array(); // Contains node data
        // Sikkerhet: sørg for at all data som parses strippes for farlige ting
        if (isset($vals[$i]['value']))$children['VALUE'] = $this->sanitizeString($vals[$i]['value']);
        while (++$i < count($vals)){
            // Sikkerhet: sørg for at all data som parses strippes for farlige ting
            if(isset($vals[$i]['value']))$val=$this->sanitizeString($vals[$i]['value']);
            else unset($val);
            if(isset($vals[$i]['type']))$typ=$this->sanitizeString($vals[$i]['type']);
            else unset($typ);
            if(isset($vals[$i]['attributes']))$atr=$this->sanitizeString($vals[$i]['attributes']);
            else unset($atr);
            if(isset($vals[$i]['tag']))$tag=$this->sanitizeString($vals[$i]['tag']);
            else unset($tag);
            // Fyll inn strukturen vær slik vi vil ha den
            switch ($vals[$i]['type']){
                case 'cdata': $children['VALUE']=(isset($children['VALUE']))?$val:$children['VALUE'].$val; break;
                case 'complete':
                    if (isset($atr)) {
                        $children[$tag][]['ATTRIBUTES'] = $atr;
                        $index = count($children[$tag])-1;
                        if (isset($val))$children[$tag][$index]['VALUE'] = $val;
                        else $children[$tag][$index]['VALUE'] = '';
                    } else {
                        if (isset($val))$children[$tag][]['VALUE'] = $val;
                        else $children[$tag][]['VALUE'] = '';
                    }
                    break;
                case 'open':
                    if (isset($atr)) {
                        $children[$tag][]['ATTRIBUTES'] = $atr;
                        $index = count($children[$tag])-1;
                        $children[$tag][$index] = array_merge($children[$tag][$index],$this->rearrangeChildren($vals, $i));
                    } else $children[$tag][] = $this->rearrangeChildren($vals, $i);
                    break;
                case 'close': return $children;
            }
        }
    }
    // Ommøbler data til å passe vårt formål, og returner
    private function rearrangeDataStruct($vals){
        //die('<pre>'.$this->retar($vals).'<\pre>');
        $tree = array();
        $i = 0;
        if (isset($vals[$i]['attributes'])) {
            $tree[$vals[$i]['tag']][]['ATTRIBUTES']=$vals[$i]['attributes'];
            $index=count($tree[$vals[$i]['tag']])-1;
            $tree[$vals[$i]['tag']][$index]=array_merge($tree[$vals[$i]['tag']][$index], $this->rearrangeChildren($vals, $i));
        } else $tree[$vals[$i]['tag']][] = $this->rearrangeChildren($vals, $i);
        //die("<pre>".retar($tree));
        //Hent ut det vi bryr oss om
        if(isset($tree['WEATHERDATA'][0]['FORECAST'][0]))return $tree['WEATHERDATA'][0]['FORECAST'][0];
        else return YrComms::getYrDataErrorMessage('Det oppstod en feil ved behandling av data fra yr.no. Vennligst gjør administrator oppmerksom på dette! Teknisk: data har feil format.');
    }

    // Hovedmetode. Laster XML fra en yr.no URI og parser denne
    public function getXMLTree($xml_url, $try_curl, $timeout){
        // Last inn XML fil og parse til et array hierarcki, ommøbler data til å passe vårt formål, og returner
        return $this->rearrangeDataStruct($this->parseXMLIntoStruct($this->loadXMLData($xml_url,$try_curl,$timeout)));
    }

    // Statisk hjelper for å parse ut tid i yr format
    public static function parseTime($yr_time, $do24_00=false){
        $yr_time=str_replace(":00:00", "", $yr_time);
        if($do24_00)$yr_time=str_replace("00", "24", $yr_time);
        return $yr_time;
    }

    // Statisk hjelper for å besørge riktig encoding ved å oversette spesielle ISO-8859-1 karakterer til HTML/XHTML entiteter
    public static function convertEncodingEntities($yrraw){
        $conv=str_replace("æ", "&aelig;", $yrraw);
        $conv=str_replace("ø", "&oslash;", $conv);
        $conv=str_replace("å", "&aring;", $conv);
        $conv=str_replace("Æ", "&AElig;", $conv);
        $conv=str_replace("Ø", "&Oslash;", $conv);
        $conv=str_replace("Å", "&Aring;", $conv);
        return $conv;
    }

    // Statisk hjelper for å besørge riktig encoding vedå oversette spesielle UTF karakterer til ISO-8859-1
    public static function convertEncodingUTF($yrraw){
        $conv=str_replace("Ã¦", "æ", $yrraw);
        $conv=str_replace("Ã¸", "ø", $conv);
        $conv=str_replace("Ã¥", "å", $conv);
        $conv=str_replace("Ã", "Æ", $conv);
        $conv=str_replace("Ã", "Ø", $conv);
        $conv=str_replace("Ã", "Å", $conv);
        return $conv;
    }


    public function getXMLEntities($string){
        return preg_replace('/[^\x09\x0A\x0D\x20-\x7F]/e', '$this->_privateXMLEntities("$0")', $string);
    }

    private function _privateXMLEntities($num){
        $chars = array(
            128 => '&#8364;', 130 => '&#8218;',
            131 => '&#402;', 132 => '&#8222;',
            133 => '&#8230;', 134 => '&#8224;',
            135 => '&#8225;',136 => '&#710;',
            137 => '&#8240;',138 => '&#352;',
            139 => '&#8249;',140 => '&#338;',
            142 => '&#381;', 145 => '&#8216;',
            146 => '&#8217;',147 => '&#8220;',
            148 => '&#8221;',149 => '&#8226;',
            150 => '&#8211;',151 => '&#8212;',
            152 => '&#732;',153 => '&#8482;',
            154 => '&#353;',155 => '&#8250;',
            156 => '&#339;',158 => '&#382;',
            159 => '&#376;');
        $num = ord($num);
        return (($num > 127 && $num < 160) ? $chars[$num] : "&#".$num.";" );
    }
}

// Klasse for å vise data fra yr. Kompatibel med YRComms sin datastruktur
class YRDisplay{

    // Akkumulator variabl for å holde på generert HTML
    var $ht='';
    // Yr Url
    var $yr_url='';
    // Yr stedsnavn
    var $yr_name='';
    // Yr data
    var $yr_data=Array();

    //Filename for cached HTML. MD5 hash will be prepended to allow caching of several pages
    var $datafile='yr.html';
    //The complete path to the cache file
    var $datapath='';

    // Norsk grovinndeling av de 360 grader vindretning
    var $yr_vindrettninger=array(
        'nord','nord-nord&oslash;st','nord&oslash;st','&oslash;st-nord&oslash;st',
        '&oslash;st','&oslash;st-s&oslash;r&oslash;st','s&oslash;r&oslash;st','s&oslash;r-s&oslash;r&oslash;st',
        's&oslash;r','s&oslash;r-s&oslash;rvest', 's&oslash;rvest','vest-s&oslash;rvest',
        'vest', 'vest-nordvest','nordvest', 'nord-nordvest', 'nord');

    // Hvor hentes bilder til symboler fra?
    var $yr_imgpath='https://www.yr.no/grafikk/sym/b38';


    //Generer header for varselet
    public function getHeader($use_full_html){
        // Her kan du endre header til hva du vil. NB! Husk å skru det på, ved å endre instillingene i toppen av dokumentet
        if($use_full_html){
            $this->ht.=<<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>V&aelig;rvarsel fra yr.no</title>
    <link href="css/yr-php.css" rel="stylesheet" type="text/css" />
  </head>
  <body>

EOT
            ;
        }
        $this->ht.=<<<EOT
    <div id="yr-varsel">

EOT
        ;
    }

    //Generer footer for varselet
    public function getFooter($use_full_html){
        $this->ht.=<<<EOT
    </div>

EOT
        ;
        // Her kan du endre footer til hva du vil. NB! Husk å skru det på, ved å endre instillingene i toppen av dokumentet
        if($use_full_html){
            $this->ht.=<<<EOT
  </body>
</html>

EOT
            ;
        }
    }


    //Generer Copyright for data fra yr.no
    public function getBanner($target='_top'){
        $url=YRComms::convertEncodingEntities($this->yr_url);
        $this->ht.=<<<EOT
      <h1><a href="https://www.yr.no/" target="$target"><img src="https://www.yr.no/grafikk/sym/php-varsel/topp.png" alt="yr.no" title="yr.no er en tjeneste fra Meteorologisk institutt og NRK" /></a></h1>

EOT
        ;
    }


    //Generer Copyright for data fra yr.no
    public function getCopyright($target='_top'){
        $url=YRComms::convertEncodingEntities($this->yr_url);
        /*
         Du må ta med teksten nedenfor og ha med lenke til yr.no.
         Om du fjerner denne teksten og lenkene, bryter du vilkårene for bruk av data fra yr.no.
         Det er straffbart å bruke data fra yr.no i strid med vilkårene.
         Du finner vilkårene på http://om.yr.no/verdata/vilkar/
         */
        $this->ht.=<<<EOT
      <h2><a href="$url" target="$target">V&aelig;rvarsel for $this->yr_name</a></h2>
      <p><a href="https://www.yr.no/" target="$target"><strong>V&aelig;rvarsel fra yr.no, levert av Meteorologisk institutt og NRK.</strong></a></p>

EOT
        ;
    }


    //Generer tekst for været
    public function getWeatherText(){
        if((isset($this->yr_data['TEXT'])) && (isset($this->yr_data['TEXT'][0]['LOCATION']))&& (isset($this->yr_data['TEXT'][0]['LOCATION'][0]['ATTRIBUTES'])) ){
            $yr_place=$this->yr_data['TEXT'][0]['LOCATION'][0]['ATTRIBUTES']['NAME'];
            if(!isset($this->yr_data['TEXT'][0]['LOCATION'][0]['TIME']))return;
            foreach($this->yr_data['TEXT'][0]['LOCATION'][0]['TIME'] as $yr_var2){
                // Små bokstaver
                $l=(YRComms::convertEncodingUTF($yr_var2['TITLE'][0]['VALUE']));
                // Rettet encoding
                $e=YRComms::reviveSafeTags(YRComms::convertEncodingUTF($yr_var2['BODY'][0]['VALUE']));
                // Spytt ut!
                $this->ht.=<<<EOT
      <p><strong>$yr_place $l</strong>:$e</p>

EOT
                ;
            }
        }
    }

    //Generer lenker til andre varsel
    public function getLinks($target='_top'){
        // Rens url
        $url=YRComms::convertEncodingEntities($this->yr_url);
        // Spytt ut
        $this->ht.=<<<EOT
      <p class="yr-lenker">$this->yr_name p&aring; yr.no:
        <a href="$url/" target="$target">Varsel med kart</a>
        <a href="$url/time_for_time.html" target="$target">Time for time</a>
        <a href="$url/helg.html" target="$target">Helg</a>
        <a href="$url/langtidsvarsel.html" target="$target">Langtidsvarsel</a>
      </p>

EOT
        ;
    }

    //Generer header for værdatatabellen
    public function getWeatherTableHeader(){
        $name=$this->yr_name;
        $this->ht.=<<<EOT
      <table summary="V&aelig;rvarsel for $name fra yr.no">
        <thead>
          <tr>
            <th class="v" colspan="3"><strong>Varsel for $name</strong></th>
            <th>Nedb&oslash;r</th>
            <th>Temp.</th>
            <th class="v">Vind</th>
            <th>Vindstyrke</th>
          </tr>
        </thead>
        <tbody>

EOT
        ;
    }


    //Generer innholdet i værdatatabellen
    public function getWeatherTableContent(){
        $thisdate='';
        $dayctr=0;
        if(!isset($this->yr_data['TABULAR'][0]['TIME']))return;
        $a=$this->yr_data['TABULAR'][0]['TIME'];

        foreach($a as $yr_var3){
            list($fromdate, $fromtime)=explode('T', $yr_var3['ATTRIBUTES']['FROM']);
            list($todate, $totime)=explode('T', $yr_var3['ATTRIBUTES']['TO']);
            $fromtime=YRComms::parseTime($fromtime);
            $totime=YRComms::parseTime($totime, 1);
            if($fromdate!=$thisdate){
                $divider=<<<EOT
          <tr>
            <td colspan="7" class="skilje"></td>
          </tr>

EOT
                ;
                list($thisyear, $thismonth, $thisdate)=explode('-', $fromdate);
                $displaydate=$thisdate.".".$thismonth.".".$thisyear;
                $firstcellcont=$displaydate;
                $thisdate=$fromdate;
                ++$dayctr;
            }else $divider=$firstcellcont='';

            // Vis ny dato
            if($dayctr<7){
                $this->ht.=$divider;
                // Behandle symbol
                $imgno=$yr_var3['SYMBOL'][0]['ATTRIBUTES']['NUMBER'];
                if($imgno<10)$imgno='0'.$imgno;
                switch($imgno){
                    case '01': case '02': case '03': case '05': case '06': case '07': case '08':
                    $imgno.="d"; $do_daynight=1; break;
                    default: $do_daynight=0;
                }
                // Behandle regn
                $rain=$yr_var3['PRECIPITATION'][0]['ATTRIBUTES']['VALUE'];
                if($rain==0.0)$rain="0";
                else{
                    $rain=intval($rain);
                    if($rain<1)$rain='&lt;1';
                    else $rain=round($rain);
                }
                $rain.=" mm";
                // Behandle vind
                $winddir=round($yr_var3['WINDDIRECTION'][0]['ATTRIBUTES']['DEG']/22.5);
                $winddirtext=$this->yr_vindrettninger[$winddir];
                // Behandle temperatur
                $temper=round($yr_var3['TEMPERATURE'][0]['ATTRIBUTES']['VALUE']);
                if($temper>=0)$tempclass='pluss';
                else $tempclass='minus';

                // Rund av vindhastighet
                $r=round($yr_var3['WINDSPEED'][0]['ATTRIBUTES']['MPS']);
                // Så legger vi ut hele den ferdige linjen
                $s=$yr_var3['SYMBOL'][0]['ATTRIBUTES']['NAME'];
                $w=$yr_var3['WINDSPEED'][0]['ATTRIBUTES']['NAME'];

                $this->ht.=<<<EOT
          <tr>
            <th>$firstcellcont</th>
            <th>$fromtime&#8211;$totime</th>
            <td><img src="$this->yr_imgpath/$imgno.png" width="38" height="38" alt="$s" /></td>
            <td>$rain</td>
            <td class="$tempclass">$temper&deg;</td>
            <td class="v">$w fra $winddirtext</td>
            <td>$r m/s</td>
          </tr>

EOT
                ;
            }
        }
    }

    //Generer footer for værdatatabellen
    public function getWeatherTableFooter($target='_top'){
        $this->ht.=<<<EOT
          <tr>
            <td colspan="7" class="skilje"></td>
          </tr>
        </tbody>
      </table>
      <p>V&aelig;rsymbolet og nedb&oslash;rsvarselet gjelder for hele perioden, temperatur- og vindvarselet er for det f&oslash;rste tidspunktet. &lt;1 mm betyr at det vil komme mellom 0,1 og 0,9 mm nedb&oslash;r.<br />
      <a href="http://www.yr.no/1.3362862" target="$target">Slik forst&aring;r du varslene fra yr.no</a>.</p>
      <p>Vil du ogs&aring; ha <a href="http://www.yr.no/verdata/" target="$target">v&aelig;rvarsel fra yr.no p&aring; dine nettsider</a>?</p>
EOT
        ;
    }


    // Handle cache directory (re)creation and cachefile name selection
    private function handleDataDir($clean_datadir=false,$summary=''){
        global $yr_datadir;
        // The md5 sum is to avoid caching to the same file on parameter changes
        $this->datapath=$yr_datadir .'/'. ($summary!='' ? (md5($summary).'['.$summary.']_') : '').$this->datafile;
        // Delete cache dir
        if ($clean_datadir) {
            unlink($this->datapath);
            rmdir($yr_datadir);
        }
        // Create new cache folder with correct permissions
        if(!is_dir($yr_datadir))mkdir($yr_datadir,0300);
    }


    //Main with caching
    public function generateHTMLCached($url,$name,$xml, $try_curl, $useHtmlHeader=true, $useHtmlFooter=true, $useBanner=true, $useText=true, $useLinks=true, $useTable=true, $maxage=0, $timeout=10, $urlTarget='_top'){
        //Default to the name in the url
        if(null==$name||''==trim($name))$name=array_pop(explode('/',$url));
        $this->handleDataDir(false,htmlentities("$name.$useHtmlHeader.$useHtmlFooter.$useBanner.$useText.$useLinks.$useTable.$maxage.$timeout.$urlTarget"));
        $yr_cached = $this->datapath;
        // Clean name
        $name=YRComms::convertEncodingUTF($name);
        $name=YRComms::convertEncodingEntities($name);
        // Clean URL
        $url=YRComms::convertEncodingUTF($url);
        // Er mellomlagring enablet, og trenger vi egentlig laste ny data, eller holder mellomlagret data?
        if(($maxage>0)&&((file_exists($yr_cached))&&((time()-filemtime($yr_cached))<$maxage))){
            $data['value']=file_get_contents($yr_cached);
            // Sjekk for feil
            if(false==$data['value']){
                $data['value']='<p>Det oppstod en feil mens værdata ble lest fra lokalt mellomlager. Vennligst gjør administrator oppmerksom på dette! Teknisk: Sjekk at rettighetene er i orden som beskrevet i bruksanvisningen for dette scriptet</p>';
                $data['error'] = true;
            }
        }
        // Vi kjører live, og saver samtidig en versjon til mellomlager
        else{
            $data=$this->generateHTML($url,$name,$xml->getXMLTree($url, $try_curl, $timeout),$useHtmlHeader,$useHtmlFooter,$useBanner,$useText,$useLinks,$useTable,$urlTarget);
            // Lagre til mellomlager
            if($maxage>0 && !$data['error'] ){
                $f=fopen($yr_cached,"w");
                if(null!=$f){
                    fwrite($f,$data['value']);
                    fclose($f);
                }
            }
        }
        // Returner resultat
        return $data['value'];
    }

    private function getErrorMessage(){
        if(isset($this->yr_data['ERROR'])){
            $error=$this->yr_data['ERROR'][0]['VALUE'];
            //die(retar($error));
            $this->ht.='<p style="color:red; background:black; font-weight:900px">' .$error.'</p>';
            return true;
        }
        return false;
    }

    //Main
    public function generateHTML($url,$name,$data,$useHtmlHeader=true,$useHtmlFooter=true,$useBanner=true,$useText=true,$useLinks=true,$useTable=true,$urlTarget='_top'){
        // Fyll inn data fra parametrene
        $this->ht='';
        $this->yr_url=$url;
        $this->yr_name=$name;
        $this->yr_data=$data;

        // Generer HTML i $ht
        $this->getHeader($useHtmlHeader);
        $data['error'] = $this->getErrorMessage();
        if($useBanner)$this->getBanner($urlTarget);
        $this->getCopyright($urlTarget);
        if($useText)$this->getWeatherText();
        if($useLinks)$this->getLinks($urlTarget);
        if($useTable){
            $this->getWeatherTableHeader();
            $this->getWeatherTableContent();
            $this->getWeatherTableFooter($urlTarget);
        }
        $this->getFooter($useHtmlFooter);

        // Returner resultat
        //return YRComms::convertEncodingEntities($this->ht);
        $data['value'] = $this->ht;
        return $data;
    }
}

?>
