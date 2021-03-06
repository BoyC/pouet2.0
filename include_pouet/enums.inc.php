<?
global $PLATFORMS;
$PLATFORMS = handle_db_cache( POUET_ROOT_LOCAL . "/cache/enum-platforms.cache", function() {
  $rows = SQLLIB::selectRows("select * from platforms");
  $platforms = array();
  foreach($rows as $r)
  {
    $platforms[ $r->id ] = get_object_vars($r);
    unset($platforms[ $r->id ]["id"]);
    $platforms[ $r->id ]["slug"] = strtolower(preg_replace("/[^a-zA-Z0-9]+/","",$platforms[ $r->id ]["name"]));
  }
  ksort($platforms);
  return $platforms;
});

global $COMPOTYPES;
$COMPOTYPES = handle_db_cache( POUET_ROOT_LOCAL . "/cache/enum-compotypes.cache", function() {
  $rows = SQLLib::selectRows("select * from compotypes");
  
  $compos = array();
  foreach($rows as $v) $compos[$v->id] = $v->componame;
  ksort($compos);
  return $compos;
});

$AFFILIATIONS_ORIGINAL = array(
  "remix" => "remixed in",
  "port" => "ported to",
  "final" => "final version",
  "pack" => "packed in",
  "related" => "related to",
  "sequel" => "continued in",
);
$AFFILIATIONS_INVERSE = array(
  "remix" => "remix of",
  "port" => "ported from",
  "final" => "party version",
  "pack" => "includes",
  "sequel" => "sequel to",
);

$verificationStrings = array(
  "CELEBRANDIL-VECTOR",
  "MEKKA-SYMPOSIUM",
  "MEDIA-ERROR",
);

?>
