<?php
class PouetBoxASCIIViewer extends PouetBox 
{
  function __construct() {
    parent::__construct();
  }

  function LoadFromDB()
  {
    $this->fonts = array(
      "none" => array(
        "name"=>"html",
        "class"=>"",
      ),
      "1" => array(
        "name"=>"dos 80*25",
        "class"=>"dos-80x25",
        "encoding"=>"cp437",
      ),
      "2" => array(
        "name"=>"dos 80*50",
        "class"=>"dos-80x50",
        "encoding"=>"cp437",
      ),
      "3" => array(
        "name"=>"rez's ascii",
        "image"=>true,
      ),
      "4" => array(
        "name"=>"amiga medres",
        "class"=>"amiga-medres",
        "encoding"=>"iso-8859-1",
      ),
      "5" => array(
        "name"=>"amiga hires",
        "class"=>"amiga-hires",
        "encoding"=>"iso-8859-1",
      ),
    );
  }
  function RenderHeader()
  {
    echo "\n\n";
    echo "<div class='pouettbl asciiviewer' id='".$this->uniqueID."'>\n";
  }
  function RenderBody()
  {
    echo "<div class='content' title='".$this->bodyTitle."'>\n";
    if (!$this->fonts[$_GET["font"]]["image"])
    {
      printf("<pre class='%s'>",_html($this->fonts[$_GET["font"]]["class"]));
      $text = file_get_contents( $this->asciiFilename );
      echo _html( process_ascii( $text, $this->fonts[$_GET["font"]]["encoding"] ) );
      printf("</pre>");
    }
    else
    {
      printf("<img src='%s&amp;font=%d' alt='nfo'/>\n",$this->imageURL,$_GET["font"]);
    }
    echo "</div>\n";
  }
  function RenderFooter()
  {
    echo "  <div class='content' id='fontlist'>";
    $a = array();
    foreach($this->fonts as $k=>$v)
    {
      $a[] = sprintf("<a href='%s'>%s</a>\n",adjust_query_header(array("font"=>$k)),$v["name"]);
    }
    echo "[ ".implode(" | \n",$a)." ]";
    echo "  </div>";
  }
};
?>